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
        Schema::create('protocol_academy', function (Blueprint $table) {
            $table->id();
            $table->uuid('protocol_id')->index();
            $table->uuid('academy_id')->index();
            $table->timestamps();

            $table->foreign('protocol_id')->references('id')->on('protocols')->onDelete('cascade');
            $table->foreign('academy_id')->references('id')->on('academies')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_academy');
    }
};
