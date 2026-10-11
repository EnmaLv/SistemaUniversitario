<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deporte_horario', function (Blueprint $table) {
            $table->text('motivo_cancelacion')->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('deporte_horario', function (Blueprint $table) {
            $table->dropColumn('motivo_cancelacion');
        });
    }
};
