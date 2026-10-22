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
        Schema::create('universidad', function (Blueprint $table) {
            $table->id('id_uni');
            $table->string('codigo_ministerio_uni', 30)->nullable();
            $table->string('nombre_uni');
            $table->string('siglas_uni', 20)->nullable();
            $table->string('rif_uni', 20)->nullable();
            $table->string('telefono_uni', 50)->nullable();
            $table->string('correo_uni')->nullable();
            $table->string('sitio_web_uni')->nullable();
            $table->unsignedBigInteger('loc_id')->nullable();
            $table->timestamps();
            $table->auditoria();
        });

        Schema::create('sede', function (Blueprint $table) {
            $table->id('id_sed');
            $table->string('nombre_sed', 100);
            $table->string('siglas_sed', 20)->nullable();
            $table->string('codigo_ministerio_sed', 30)->nullable();
            $table->string('direccion_sed', 255);
            $table->string('telefono_sed', 100)->nullable();
            $table->string('correo_sed')->nullable();
            $table->foreignId('uni_id')->nullable()->constrained('universidad', 'id_uni');
            $table->unsignedBigInteger('loc_id')->nullable();
            $table->boolean('es_principal_sed')->default(false);
            $table->timestamps();
            $table->auditoria();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sede');
        Schema::dropIfExists('universidad');
    }
};
