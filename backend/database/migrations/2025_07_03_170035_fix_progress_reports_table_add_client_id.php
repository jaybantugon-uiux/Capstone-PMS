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
        // Drop and recreate the progress_reports table with the correct schema
        Schema::dropIfExists('progress_reports');
        
        Schema::create('progress_reports', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('attachment_path', 500)->nullable();
            $table->string('original_filename')->nullable();
            $table->unsignedInteger('file_size')->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('created_by')->comment('Admin or PM who created the report');
            $table->enum('created_by_role', ['admin', 'pm'])->default('admin');
            $table->unsignedBigInteger('client_id')->comment('Client who receives the report');
            $table->unsignedBigInteger('project_id')->nullable()->comment('Optional project this report is about');
            $table->enum('status', ['draft', 'sent', 'viewed', 'archived'])->default('draft');
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('first_viewed_at')->nullable();
            $table->unsignedInteger('view_count')->default(0);
            $table->timestamps();
            
            // Foreign key constraints
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('set null');
            
            // Indexes
            $table->index(['client_id', 'status'], 'idx_progress_reports_client_status');
            $table->index(['created_by', 'created_by_role'], 'idx_progress_reports_created_by');
            $table->index('project_id', 'idx_progress_reports_project');
            $table->index(['status', 'sent_at'], 'idx_progress_reports_status_sent');
            $table->index('created_at', 'idx_progress_reports_created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('progress_reports');
    }
};