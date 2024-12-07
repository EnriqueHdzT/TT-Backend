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
        Schema::create('protocol_status', function (Blueprint $table) {
            $table->id();
            $table->uuid('protocol_id')->index();
            $table->enum('previous_status', ['', 'validating', 'classifying', 'selecting', 'evaluatingFirst', 'correcting', 'evaluatingSecond', 'active', 'canceled'])->default('');
            $table->enum('new_status', ['validating', 'classifying', 'selecting', 'evaluatingFirst', 'correcting', 'evaluatingSecond', 'active', 'canceled']);
            $table->text('comment')->default('');
            $table->timestamp('changed_at')->useCurrent();
            $table->timestamps();

            $table->foreign('protocol_id')->references('id')->on('protocols')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('protocol_status');
    }
};
