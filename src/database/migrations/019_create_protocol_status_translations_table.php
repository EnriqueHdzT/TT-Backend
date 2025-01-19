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
        Schema::create('protocol_status_translations', function (Blueprint $table) {
            $table->id();
            $table->text('name')->nullable();
            $table->text('raw_name')->nullable();
            
        });

        DB::table('protocol_status_translations')->insert([
            ['name' => 'Validando', 'raw_name' => 'validating'],
            ['name' => 'Clasificando', 'raw_name' => 'classifying'],
            ['name' => 'Seleccionando', 'raw_name' => 'selecting'],
            ['name' => 'Primera Evaluación', 'raw_name' => 'evaluatingFirst'],
            ['name' => 'Corrigiendo', 'raw_name' => 'correcting'],
            ['name' => 'Segunda Evaluación', 'raw_name' => 'evaluatingSecond'],
            ['name' => 'Activo', 'raw_name' => 'active'],
            ['name' => 'Cancelado', 'raw_name' => 'canceled'],
            // Agrega más registros según sea necesario
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_status_translations');
    }
};
