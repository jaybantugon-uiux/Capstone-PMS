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
        Schema::create('site_issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->onDelete('cascade');
            $table->foreignId('task_id')->nullable()->constrained()->onDelete('set null');
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Site coordinator who reported the issue');
            $table->string('issue_title');
            $table->enum('issue_type', ['safety', 'equipment', 'environmental', 'personnel', 'quality', 'timeline', 'other'])->default('other');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['open', 'in_progress', 'resolved', 'closed', 'escalated'])->default('open');
            $table->text('description');
            $table->string('location')->nullable();
            $table->text('affected_areas')->nullable();
            $table->text('immediate_actions_taken')->nullable();
            $table->text('suggested_solutions')->nullable();
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->json('photos')->nullable()->comment('Array of photo file paths');
            $table->json('attachments')->nullable()->comment('Array of attachment file paths');
            
            // Admin management fields
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null')->comment('Admin/PM assigned to handle the issue');
            $table->text('admin_notes')->nullable();
            $table->text('resolution_description')->nullable();
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Timeline tracking
            $table->timestamp('reported_at')->useCurrent();
            $table->timestamp('acknowledged_at')->nullable();
            $table->foreignId('acknowledged_by')->nullable()->constrained('users')->onDelete('set null');
            
            $table->timestamps();
            
            // Indexes for performance
            $table->index(['project_id', 'status']);
            $table->index(['user_id', 'reported_at']);
            $table->index(['status', 'priority']);
            $table->index(['issue_type', 'status']);
            $table->index(['assigned_to']);
            $table->index(['reported_at']);
            $table->index(['resolved_at']);
            $table->index(['priority']);
            $table->index(['task_id']);
        });

        Schema::create('site_issue_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('site_issue_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->text('comment');
            $table->boolean('is_internal')->default(false)->comment('Internal admin comments vs external visible to SC');
            $table->json('attachments')->nullable();
            $table->timestamps();
            
            $table->index(['site_issue_id', 'created_at']);
            $table->index(['user_id']);
            $table->index(['is_internal']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('site_issue_comments');
        Schema::dropIfExists('site_issues');
    }
};