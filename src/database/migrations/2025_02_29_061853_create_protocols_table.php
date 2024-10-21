<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateProtocolsTable extends Migration
{
    public function up()
    {
        Schema::create('protocols', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('(uuid_generate_v4())'));
            $table->string('title');
            $table->text('resume');
            
            // Foreign keys
            $table->uuid('protocol_id')->index();

            $table->uuid('student1_id')->index()->nullable();
            $table->json('student1_data')->nullable();
            
            $table->uuid('student2_id')->index()->nullable();
            $table->json('student2_data')->nullable();
            
            $table->uuid('student3_id')->index()->nullable();
            $table->json('student3_data')->nullable();
            
            $table->uuid('student4_id')->index()->nullable();
            $table->json('student4_data')->nullable();
            
            $table->uuid('director1_id')->index()->nullable();
            $table->json('director1_data')->nullable();
            
            $table->uuid('director2_id')->index()->nullable();
            $table->json('director2_data')->nullable();
            
            $table->uuid('sinodal1_id')->index()->nullable();
            $table->json('sinodal1_data')->nullable();
            
            $table->uuid('sinodal2_id')->index()->nullable();
            $table->json('sinodal2_data')->nullable();
            
            $table->uuid('sinodal3_id')->index()->nullable();
            $table->json('sinodal3_data')->nullable();
            
            $table->enum('status', ['waiting', 'validated', 'classified', 'evaluated', 'active', 'canceled'])->default('waiting');
            $table->json('keywords')->nullable();
            $table->binary('pdf')->nullable();
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('protocol_id')->references('id')->on('dates_and_terms');

            $table->foreign('student1_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('student2_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('student3_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('student4_id')->references('id')->on('students')->onDelete('set null');
            $table->foreign('director1_id')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('director2_id')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('sinodal1_id')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('sinodal2_id')->references('id')->on('staff')->onDelete('set null');
            $table->foreign('sinodal3_id')->references('id')->on('staff')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('protocols');
    }
}
