<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up()
    {
        Schema::create('consultas', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(uuid_generate_v4())'));
            $table->string('Question', 255);
            $table->string('Answer', 255);
            $table->timestamp('Date_query')->useCurrent();
            $table->timestamp('Last_update')->useCurrent()->useCurrentOnUpdate();
            $table->string('Category', 100)->nullable();
            $table->string('support_email', 20)->nullable();
            $table->uuid('user_id');

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('consultas');
    }
};
