<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Comments extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('comments', function (Blueprint $table) {
        $table->increments('id');
        $table->string('uId');
        $table->string('topicId',50)->nullable();
        $table->string('topicType',16)->nullable();
        $table->string('parentId',50)->nullable();
        $table->string('fromId',50)->nullable();
        $table->string('toId')->nullable();
        $table->string('content', 120)->nullable();
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
      Schema::dropIfExists('comments');
    }
}
