<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Ajusta `consents` a la hoja física de consentimiento informado de la clínica (Sprint 5):
 *
 *  - `proposed_treatment` → `treatment_plan`   ("Plan de tratamiento")
 *  - `specific_risks`     → `risks_complications` ("Riesgos y complicaciones posibles")
 *  - `diagnosis` pasa a nullable (la hoja no tiene un renglón de diagnóstico libre obligatorio)
 *  - `+ prognosis`               ("Pronóstico")
 *  - `+ management_alternatives` ("Alternativas de manejo")
 *  - `+ health_snapshot` (JSON cifrado): copia fija de las respuestas de salud del paciente
 *    tomadas de su historia clínica en el momento de crear el consentimiento.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('consents', function (Blueprint $table) {
            $table->renameColumn('proposed_treatment', 'treatment_plan');
            $table->renameColumn('specific_risks', 'risks_complications');
        });

        Schema::table('consents', function (Blueprint $table) {
            $table->text('diagnosis')->nullable()->change();
            $table->text('prognosis')->nullable()->after('treatment_plan');
            $table->text('management_alternatives')->nullable()->after('risks_complications');
            $table->text('health_snapshot')->nullable()->after('management_alternatives');
        });
    }

    public function down(): void
    {
        Schema::table('consents', function (Blueprint $table) {
            $table->dropColumn(['prognosis', 'management_alternatives', 'health_snapshot']);
        });

        Schema::table('consents', function (Blueprint $table) {
            $table->renameColumn('treatment_plan', 'proposed_treatment');
            $table->renameColumn('risks_complications', 'specific_risks');
            // `diagnosis` se deja nullable al revertir: volver a NOT NULL fallaría si hay filas
            // con NULL, y la migración original ya lo definía NOT NULL solo por herencia de §6.
        });
    }
};
