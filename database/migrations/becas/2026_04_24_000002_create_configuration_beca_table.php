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
        //Tabla periodo parte de configuracion del sistema
        Schema::create('be_lapsos', function (Blueprint $table) {
            $table->id();
            // Identificación
            $table->string('codigo', 10)->unique(); // Ej: 2026-1
            
            // Rango de clases
            $table->date('fecha_inicio');
            $table->date('fecha_fin')->nullable();

            // Control de Flujo (Manual)
            $table->boolean('es_actual')->default(false);
            $table->boolean('permite_solicitudes')->default(false);
            
            // Metadata
            $table->timestamps();
        });


        //Tabla condicion_estudiante parte de configuracion del sistema(NOTA: Dependiendo de la condicion del estudiante se le asigna un beneficio o no)
        //Funciona como una tabla de filtros
        Schema::create('condicion_estudiante', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_condicion')->comment('Regular, repitiente, etc');
            $table->foreignId('id_persona')->constrained('persona', 'id_persona')->onDelete('cascade');
            $table->foreignId('lapsos_id')->constrained('be_lapsos')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('be_jornadas_becas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_jornada'); // Ej: "Convocatoria Comedor 2025-2"
            $table->text('descripcion_jornada')->nullable();
            
            // Relación con el Beneficio (Qué se ofrece)
            $table->foreignId('beneficio_id')
                  ->constrained('be_beneficios')
                  ->onDelete('cascade');

            // Relación con el Periodo / Lapso (Cuándo ocurre)
            $table->foreignId('lapsos_id')
                  ->constrained('be_lapsos')
                  ->onDelete('cascade');

            // Configuración específica de esta jornada
            $table->date('fecha_inicio_solicitud');
            $table->date('fecha_fin_solicitud');
            $table->integer('cupos_maximos')->default(0);
            $table->integer('cupos_asignados')->default(0);
            
            $table->boolean('activa')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('be_lapsos');
        Schema::dropIfExists('be_jornadas_becas');
        Schema::dropIfExists('condicion_estudiante');
    }
};
