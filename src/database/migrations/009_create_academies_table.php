<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('academies', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(uuid_generate_v4())'));
            $table->string('name')->unique();
            $table->timestamps();
        });

        DB::table('academies')->insert([
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Ciencias Básicas', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Ciencias Sociales', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Trabajo Terminal', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Ciencias de la Computación', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Ciencias de Datos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Fundamentos de Sistemas Electrónicos', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Inteligencia Artificial', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Ingenieria de Software', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Proyectos Estrategicos y Toma de Decisiones', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Sistemas Digitales', 'created_at' => now(), 'updated_at' => now()],
            ['id' => DB::raw('(uuid_generate_v4())'), 'name' => 'Sistemas Distribuidos', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('academies');
    }
};
