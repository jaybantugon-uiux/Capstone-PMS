<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTasksTable extends Migration
{
    public function up()
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->id();
            $table->string('task_name');
            $table->text('description');
            $table->unsignedBigInteger('assigned_to'); // This will link to the User (Site Coordinator)
            $table->unsignedBigInteger('project_id');
            $table->enum('status', ['pending', 'in_progress', 'completed']);
            $table->timestamps();

            // Foreign Keys
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('project_id')->references('id')->on('projects');
        });
    }

    public function down()
    {
        Schema::dropIfExists('tasks');
    }
}
