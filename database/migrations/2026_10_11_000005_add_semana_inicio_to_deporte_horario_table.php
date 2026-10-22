<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('deporte_horario', function (Blueprint $table) {
            $table->date('semana_inicio')->nullable()->after('fecha_especifica')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('deporte_horario', function (Blueprint $table) {
            $table->dropIndex(['semana_inicio']);
            $table->dropColumn('semana_inicio');
        });
    }
};
