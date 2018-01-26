<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Categoryproject extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('categoryprojects', function (Blueprint $table) {
        $table->increments('id');
        $table->string('uId');
        $table->string('parent')->nullable();
        $table->string('name');
        $table->string('value');
        $table->string('author');
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
      Schema::dropIfExists('categoryprojects');
    }
}
