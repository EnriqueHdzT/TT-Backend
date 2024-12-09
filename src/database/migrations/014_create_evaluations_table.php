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
        Schema::create('evaluations', function (Blueprint $table) {
            $table->uuid('protocol_id')->index();
            $table->uuid('sinodal_id')->index();
            $table->enum('current_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->json('evaluation_response')->default('{}');
            $table->timestamps();

            $table->primary(['protocol_id', 'sinodal_id']);
            $table->foreign('protocol_id')->references('id')->on('protocols')->onDelete('cascade');
            $table->foreign('sinodal_id')->references('id')->on('users')->onDelete('no action');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('evaluations');
    }
};
