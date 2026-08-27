<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ValidatesConsultationData;
use App\Models\Consultation;
use Illuminate\Foundation\Http\FormRequest;

class StoreConsultationRequest extends FormRequest
{
    use ValidatesConsultationData;

    public function authorize(): bool
    {
        return $this->user()->can('create', Consultation::class);
    }

    /**
     * Si quien registra es un doctor, la consulta se asigna a él y el campo no es
     * seleccionable. administrador/superadmin sí eligen el doctor de una lista.
     */
    protected function prepareForValidation(): void
    {
        if ($this->user()->isDoctor()) {
            $this->merge(['doctor_id' => $this->user()->id]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'doctor_id' => ['required', ...$this->doctorIdRule()],
            ...$this->consultationRules(),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->consultationMessages();
    }
}
