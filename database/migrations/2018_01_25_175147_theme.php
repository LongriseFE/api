<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Theme extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('themes', function (Blueprint $table) {
        $table->increments('id');
        $table->string('uId');
        $table->string('name',50);
        $table->string('file',16);
        $table->string('color1',30);
        $table->string('color2',30);
        $table->string('author')->nullable();
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
      Schema::dropIfExists('themes');
    }
}
