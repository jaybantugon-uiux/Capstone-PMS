<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('project_updates')) {
            Schema::create('project_updates', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('created_by')->references('id')->on('users')->onDelete('cascade');
                $table->string('title');
                $table->text('description');
                $table->enum('update_type', ['progress', 'milestone', 'completion', 'issue', 'announcement', 'other'])->default('progress');
                $table->enum('visibility', ['public', 'client', 'internal'])->default('client');
                $table->boolean('is_major_milestone')->default(false);
                $table->integer('progress_percentage')->nullable();
                $table->json('attachments')->nullable();
                $table->json('tags')->nullable();
                $table->timestamp('posted_at')->useCurrent();
                $table->boolean('notify_clients')->default(true);
                $table->timestamps();
                
                $table->index(['project_id', 'visibility', 'posted_at']);
                $table->index(['update_type', 'is_major_milestone']);
                $table->index(['created_by', 'posted_at']);
                $table->index(['posted_at']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_updates');
    }
};