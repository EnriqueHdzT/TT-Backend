<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProtocolsTable extends Migration
{
    public function up()
    {
        Schema::create('protocols', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('protocol_id')->unique();
            $table->enum('status', ['waiting', 'validated', 'classified', 'evaluated', 'active', 'canceled'])->default('waiting');
            $table->json('keywords')->nullable();
            $table->binary('pdf')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
}

