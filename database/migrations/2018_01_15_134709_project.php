<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Project extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uId',50);
            $table->string('title')->comment('名称');
            $table->string('branch')->nullable()->comment('部门');
            $table->string('category', 300)->nullable()->comment('分类');
            $table->string('tag')->nullable()->comment('标签');
            $table->string('ext')->nullable()->comment('附件格式');
            $table->string('size')->nullable()->default(0)->comment('附件大小');
            $table->string('author')->nullable()->comment('上传者');
            $table->string('attach', 1000)->nullable()->comment('附件');
            $table->string('cover', 300)->nullable()->comment('封面');
            $table->string('github')->nullable()->comment('github');
            $table->string('content', 200)->nullable()->comment('项目介绍');
            $table->integer('view')->nullable()->default(0)->comment('浏览');
            $table->integer('download')->nullable()->default(0)->comment('下载');
            $table->rememberToken();
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
        Schema::dropIfExists('projects');
    }
}
