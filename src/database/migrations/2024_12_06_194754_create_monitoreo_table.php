<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMonitoreoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('monitoreo', function (Blueprint $table) {
            $table->id(); // ID as a primary key with auto-increment
            $table->string('NombreProyecto', 50);
            $table->string('Sinodal1Es', 20);
            $table->string('EstatusSin1', 20);
            $table->date('FechaSinodal1')->nullable();
            $table->string('ObservacionSino1', 200)->nullable();
            $table->string('Sinodal2Es', 20);
            $table->string('EstatusSin2', 20);
            $table->date('FechaSinodal2')->nullable();
            $table->string('ObservacionSino2', 200)->nullable();
            $table->string('Sinodal3Es', 20);
            $table->string('EstatusSin3', 20);
            $table->date('FechaSinodal3')->nullable();
            $table->string('ObservacionSino3', 200)->nullable();
            $table->timestamps(); // Adds created_at and updated_at
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('monitoreo');
    }
}
