<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * El borrado permanente de un paciente (forceDelete, solo superadmin) hacía que MySQL
 * purgara en cascada su historia clínica y sus consultas — registros del expediente
 * clínico (NOM-004) — sin dejar rastro en audit_logs.
 *
 * Se cambia la FK a RESTRICT: ahora el purgado debe pasar por el modelo Patient
 * (evento forceDeleting), que elimina cada registro hijo de forma explícita para que
 * el trait Auditable registre su propio evento `deleted`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->restrictOnDelete();
        });

        Schema::table('medical_histories', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('consultations', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });

        Schema::table('medical_histories', function (Blueprint $table) {
            $table->dropForeign(['patient_id']);
            $table->foreign('patient_id')->references('id')->on('patients')->cascadeOnDelete();
        });
    }
};
