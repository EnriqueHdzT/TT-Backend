<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('protocols', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(uuid_generate_v4())'));
            $table->string('protocol_id', 10)->unique();
            $table->string('title');
            $table->text('resume');
            $table->uuid('period')->index();
            $table->enum('current_status', ['validating', 'classifying', 'selecting', 'evaluatingFirst', 'correcting', 'evaluatingSecond', 'active', 'canceled'])->default('validating');
            $table->json('keywords')->nullable();
            $table->string('pdf');
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('period')->references('id')->on('dates_and_terms');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
};
