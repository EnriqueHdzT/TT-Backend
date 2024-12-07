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
        Schema::create('staff_academy', function (Blueprint $table) {
            $table->uuid('staff_id')->index();
            $table->uuid('academy_id')->index();
            $table->enum('role', ['Prof', 'PresAcad', 'JefeDepAcad'])->default('Prof');
            $table->timestamps();
            
            $table->primary(['staff_id', 'academy_id']);
            $table->foreign('staff_id')->references('id')->on('staff')->onDelete('cascade');
            $table->foreign('academy_id')->references('id')->on('academies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('staff_academy');
    }
};
