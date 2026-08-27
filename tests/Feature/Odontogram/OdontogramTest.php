<?php

use App\Enums\ToothStatus;
use App\Enums\ToothSurface;
use App\Enums\UserRole;
use App\Livewire\Patients\Odontogram;
use App\Models\OdontogramRecord;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

function actingDoctor(): User
{
    $doctor = User::factory()->role(UserRole::Doctor)->create();
    test()->actingAs($doctor);

    return $doctor;
}

test('el componente renderiza las 32 piezas de la dentición permanente', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->assertSee('Arcada superior')
        ->assertSee('Arcada inferior')
        ->assertSeeHtml('wire:click="select(18)"')
        ->assertSeeHtml('wire:click="select(41)"');
});

test('registrar un hallazgo de superficie crea una fila con el usuario actual y audita', function () {
    $doctor = actingDoctor();
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 46, ToothSurface::Oclusal->value)
        ->set('status', ToothStatus::Caries->value)
        ->set('note', 'Caries oclusal profunda')
        ->call('save')
        ->assertHasNoErrors();

    $record = OdontogramRecord::query()->firstOrFail();

    expect($record->patient_id)->toBe($patient->id)
        ->and($record->recorded_by)->toBe($doctor->id)
        ->and($record->tooth_number)->toBe(46)
        ->and($record->surface)->toBe(ToothSurface::Oclusal)
        ->and($record->status)->toBe(ToothStatus::Caries)
        ->and($record->note)->toBe('Caries oclusal profunda');

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'created',
        'auditable_type' => OdontogramRecord::class,
        'auditable_id' => $record->id,
    ]);
});

test('la nota clínica se guarda cifrada en reposo', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 21, ToothSurface::Vestibular->value)
        ->set('status', ToothStatus::Fractura->value)
        ->set('note', 'Fractura de esmalte por trauma')
        ->call('save')
        ->assertHasNoErrors();

    $raw = DB::table('odontogram_records')->value('note');

    expect($raw)->not->toBeNull()
        ->and($raw)->not->toContain('Fractura de esmalte por trauma')
        ->and(OdontogramRecord::first()->note)->toBe('Fractura de esmalte por trauma');
});

test('el historial se preserva: dos registros en la misma superficie y el vigente es el más reciente', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    $component = Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 36, ToothSurface::Oclusal->value)
        ->set('status', ToothStatus::Caries->value)
        ->set('recordedAt', now()->subMonth()->toDateString())
        ->call('save')
        ->assertHasNoErrors()
        ->call('select', 36, ToothSurface::Oclusal->value)
        ->set('status', ToothStatus::Obturado->value)
        ->set('recordedAt', now()->toDateString())
        ->call('save')
        ->assertHasNoErrors();

    expect(OdontogramRecord::where('tooth_number', 36)->count())->toBe(2);

    $state = $component->instance()->currentState;
    expect($state[36][ToothSurface::Oclusal->value])->toBe(ToothStatus::Obturado);
});

test('un estado de superficie no se puede registrar sobre el diente completo', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 11)
        ->set('status', ToothStatus::Caries->value)
        ->call('save')
        ->assertHasErrors('status');

    expect(OdontogramRecord::count())->toBe(0);
});

test('un estado de diente completo no se puede registrar sobre una superficie', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 11, ToothSurface::Mesial->value)
        ->set('status', ToothStatus::Corona->value)
        ->call('save')
        ->assertHasErrors('status');

    expect(OdontogramRecord::count())->toBe(0);
});

test('el estado es obligatorio y la fecha no puede ser futura', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 16, ToothSurface::Oclusal->value)
        ->set('status', '')
        ->set('recordedAt', now()->addWeek()->toDateString())
        ->call('save')
        ->assertHasErrors(['status' => 'required', 'recordedAt' => 'before_or_equal']);
});

test('archivar un hallazgo lo saca del estado vigente pero conserva la auditoría', function () {
    $doctor = actingDoctor();
    $patient = Patient::factory()->create();
    $record = OdontogramRecord::factory()->for($patient)->forSurface(ToothSurface::Distal, ToothStatus::Caries)->create([
        'tooth_number' => 27,
    ]);

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 27, ToothSurface::Distal->value)
        ->call('deleteRecord', $record->id)
        ->assertHasNoErrors();

    $this->assertSoftDeleted($record);

    $component = Livewire::test(Odontogram::class, ['patient' => $patient])->call('select', 27);
    expect($component->instance()->currentState)->not->toHaveKey(27);

    $this->assertDatabaseHas('audit_logs', [
        'patient_id' => $patient->id,
        'action' => 'deleted',
        'auditable_type' => OdontogramRecord::class,
        'auditable_id' => $record->id,
    ]);
});

test('restaurar un hallazgo archivado lo devuelve al estado vigente y deja rastro en audit_logs', function () {
    $doctor = actingDoctor();
    $patient = Patient::factory()->create();
    $record = OdontogramRecord::factory()->for($patient)->forSurface(ToothSurface::Mesial, ToothStatus::Obturado)->create([
        'tooth_number' => 15,
    ]);
    $record->delete();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 15, ToothSurface::Mesial->value)
        ->call('restoreRecord', $record->id)
        ->assertHasNoErrors();

    expect($record->fresh()->trashed())->toBeFalse();

    $component = Livewire::test(Odontogram::class, ['patient' => $patient])->call('select', 15);
    expect($component->instance()->currentState[15][ToothSurface::Mesial->value])->toBe(ToothStatus::Obturado);

    $this->assertDatabaseHas('audit_logs', [
        'user_id' => $doctor->id,
        'patient_id' => $patient->id,
        'action' => 'restored',
        'auditable_type' => OdontogramRecord::class,
        'auditable_id' => $record->id,
    ]);
});

test('la lista de archivados solo muestra hallazgos soft-deleted de la pieza seleccionada', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    $activo = OdontogramRecord::factory()->for($patient)->forSurface(ToothSurface::Distal, ToothStatus::Caries)->create(['tooth_number' => 24]);
    $archivadoMismaPieza = OdontogramRecord::factory()->for($patient)->forSurface(ToothSurface::Oclusal, ToothStatus::Sellador)->create(['tooth_number' => 24]);
    $archivadoOtraPieza = OdontogramRecord::factory()->for($patient)->forSurface(ToothSurface::Mesial, ToothStatus::Caries)->create(['tooth_number' => 25]);
    $archivadoMismaPieza->delete();
    $archivadoOtraPieza->delete();

    $component = Livewire::test(Odontogram::class, ['patient' => $patient])->call('select', 24);

    $ids = $component->instance()->archivedToothHistory->pluck('id');

    expect($ids)->toContain($archivadoMismaPieza->id)
        ->and($ids)->not->toContain($activo->id)
        ->and($ids)->not->toContain($archivadoOtraPieza->id);
});

test('no se puede restaurar ni archivar un hallazgo de otro paciente', function () {
    actingDoctor();
    $patient = Patient::factory()->create();
    $otroArchivado = OdontogramRecord::factory()->create(['tooth_number' => 11]);
    $otroArchivado->delete();
    $otroActivo = OdontogramRecord::factory()->create(['tooth_number' => 12]);

    $component = Livewire::test(Odontogram::class, ['patient' => $patient]);

    expect(fn () => $component->call('restoreRecord', $otroArchivado->id))
        ->toThrow(ModelNotFoundException::class);
    expect(fn () => $component->call('deleteRecord', $otroActivo->id))
        ->toThrow(ModelNotFoundException::class);

    expect($otroArchivado->fresh()->trashed())->toBeTrue()
        ->and($otroActivo->fresh()->trashed())->toBeFalse();
});

test('un visitante no autenticado no puede montar el componente', function () {
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])->assertForbidden();
});

test('no se puede registrar un hallazgo sobre un número de diente inválido', function () {
    actingDoctor();
    $patient = Patient::factory()->create();

    Livewire::test(Odontogram::class, ['patient' => $patient])
        ->call('select', 99)
        ->assertStatus(404);
});
