<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('parada_transporte', function (Blueprint $table) {
            $table->id('id_ptr');
            $table->string('nombre_ptr', 100);
            $table->decimal('lat_ptr', 11, 7);
            $table->decimal('lng_ptr', 11, 7);
            $table->string('direccion_ptr', 255)->nullable();
            $table->timestamps();
            $table->auditoria();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('parada_transporte');
    }
};
