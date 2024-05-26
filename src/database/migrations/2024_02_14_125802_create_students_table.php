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
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('lastname');
            $table->string('second_lastname')->nullable();
            $table->string('name')->nullable();
            $table->string('student_id', 10);
            $table->enum('career', ['ISW', 'ICD', 'IIA'])->default('ISW');
            $table->integer('curriculum')->default(2020);
            $table->string('altern_email')->nullable();
            $table->string('phone_number', 15)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
