<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class UpdateColumns extends Migration
{
    public function up()
    {
        // Step 1: Add a new uuid column
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->uuid('tokenable_id_uuid')->nullable();
        });

        // Step 2: Migrate existing data (make sure to adapt this logic to your needs)
        DB::table('personal_access_tokens')->update([
            'tokenable_id_uuid' => DB::raw('uuid_generate_v4()') // or another method of generating UUIDs
        ]);

        // Step 3: Drop old column if it exists
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->dropColumn('tokenable_id');
        });

        // Step 4: Rename new column to tokenable_id
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->renameColumn('tokenable_id_uuid', 'tokenable_id');
        });
    }

    public function down()
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            $table->bigInteger('tokenable_id')->unsigned()->nullable();
        });
    }
}
