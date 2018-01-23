<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Luck extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lucks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uId');
            $table->string('ider',50);
            $table->string('name',16)->nullable();
            $table->string('face',50)->nullable();
            $table->integer('sex')->nullable();
            $table->integer('year')->nullable();
            $table->integer('grade')->nullable();
            $table->string('gift')->nullable();
            $table->string('area')->nullable();
            $table->integer('created_at');
            $table->integer('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('lucks');
    }
}
