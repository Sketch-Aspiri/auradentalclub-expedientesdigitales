<?php

namespace Tests\Fixtures;

use App\Models\Concerns\Auditable;
use Illuminate\Database\Eloquent\Model;

/**
 * Modelo de prueba para ejercitar el trait Auditable sin depender de un módulo
 * clínico (que llega en el Sprint 1). Reutiliza la tabla `users`, ya migrada.
 */
class AuditableFixture extends Model
{
    use Auditable;

    protected $table = 'users';

    protected $guarded = [];
}
