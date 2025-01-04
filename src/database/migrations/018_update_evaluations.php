<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->renameColumn('evaluation_response', 'first_evaluation');
            $table->json('second_evaluation')->default('{}');
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropColumn('second_evaluation');
            $table->renameColumn('first_evaluation', 'evaluation_response');
        });
    }
};
