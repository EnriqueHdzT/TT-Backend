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
        Schema::create('dates_and_terms', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(uuid_generate_v4())'));
            $table->string('cycle', 6)->unique();
            $table->boolean('status')->default(true);
            $table->dateTime('ord_start_update_protocols')->nullable();
            $table->dateTime('ord_end__update_protocols')->nullable();
            $table->dateTime('ord_start_sort_protocols')->nullable();
            $table->dateTime('ord_end_sort_protocols')->nullable();
            $table->dateTime('ord_start_eval_protocols')->nullable();
            $table->dateTime('ord_end_eval_protocols')->nullable();
            $table->dateTime('ord_start_change_protocols')->nullable();
            $table->dateTime('ord_end_change_protocols')->nullable();
            
            $table->dateTime('ext_start_update_protocols')->nullable();
            $table->dateTime('ext_end__update_protocols')->nullable();
            $table->dateTime('ext_start_sort_protocols')->nullable();
            $table->dateTime('ext_end_sort_protocols')->nullable();
            $table->dateTime('ext_start_eval_protocols')->nullable();
            $table->dateTime('ext_end_eval_protocols')->nullable();
            $table->dateTime('ext_start_change_protocols')->nullable();
            $table->dateTime('ext_end_change_protocols')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dates_and_terms');
    }
};
