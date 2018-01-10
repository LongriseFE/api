<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uId',50);
            $table->string('username');
            $table->string('name')->nullable();
            $table->string('cover')->nullable();
            $table->string('github')->nullable();
            $table->string('weibo')->nullable();
            $table->string('birthday')->nullable();
            $table->string('hometown')->nullable();
            $table->string('living')->nullable();
            $table->integer('online')->nullable();
            $table->integer('sex')->nullable();
            $table->integer('status')->nullable();
            $table->integer('score')->nullable();
            $table->string('qq')->nullable();
            $table->string('wechat')->nullable();
            $table->string('email', 25)->unique()->nullable();
            $table->string('phone', 11)->unique()->nullable();
            $table->string('password');
            $table->string('theme')->nullable();
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
