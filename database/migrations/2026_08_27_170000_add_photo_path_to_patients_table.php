<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Foto de identificación del paciente (parte de la ficha, NOM-004). Se guarda en el
     * disco privado `local` (nunca en `public/`) y se sirve por una ruta autorizada
     * `patients.photo` — CLAUDE.md §5. Solo la ruta relativa dentro del disco.
     */
    public function up(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->string('photo_path')->nullable()->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('patients', function (Blueprint $table) {
            $table->dropColumn('photo_path');
        });
    }
};
