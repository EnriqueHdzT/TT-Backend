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
            $table->dateTime('start_recv_date_ord')->nullable();
            $table->dateTime('end_recv_date_ord')->nullable();
            $table->dateTime('recom_classif_end_date_ord')->nullable();
            $table->dateTime('recom_eval_end_date_ord')->nullable();
            $table->dateTime('correc_end_date_ord')->nullable();
            $table->dateTime('recom_second_eval_end_date_ord')->nullable();
            $table->dateTime('start_recv_date_ext')->nullable();
            $table->dateTime('end_recv_date_ext')->nullable();
            $table->dateTime('recom_classif_end_date_ext')->nullable();
            $table->dateTime('recom_eval_end_date_ext')->nullable();
            $table->dateTime('correc_end_date_ext')->nullable();
            $table->dateTime('recom_second_eval_end_date_ext')->nullable();

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
