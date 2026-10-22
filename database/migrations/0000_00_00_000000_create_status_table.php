<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('status', function (Blueprint $table) {
            $table->id('id_sta');
            $table->string('nombre_sta', 50);
            $table->text('descripcion_sta')->nullable();
            $table->string('modulo_sta', 100)->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->unsignedBigInteger('created_id')->nullable();
            $table->unsignedBigInteger('updated_id')->nullable();
        });

        DB::table('status')->insert([
            ['nombre_sta' => 'Activo', 'descripcion_sta' => 'Registro vigente', 'created_at' => now(), 'updated_at' => now()],
            ['nombre_sta' => 'Inactivo', 'descripcion_sta' => 'Registro desactivado', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('status');
    }
};
