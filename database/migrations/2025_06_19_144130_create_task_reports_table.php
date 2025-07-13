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
        // First, add missing columns to tasks table if they don't exist
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (!Schema::hasColumn('tasks', 'progress_percentage')) {
                    $table->integer('progress_percentage')->nullable()->after('status')->comment('0-100% completion');
                }
                if (!Schema::hasColumn('tasks', 'start_date')) {
                    $table->date('start_date')->nullable()->after('due_date');
                }
                if (!Schema::hasColumn('tasks', 'actual_start_date')) {
                    $table->date('actual_start_date')->nullable()->after('start_date');
                }
                if (!Schema::hasColumn('tasks', 'actual_end_date')) {
                    $table->date('actual_end_date')->nullable()->after('actual_start_date');
                }
            });
        }

        // Create task_reports table
        Schema::create('task_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('task_id')->constrained('tasks')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('report_title');
            $table->date('report_date');
            $table->enum('task_status', ['pending', 'in_progress', 'completed', 'on_hold', 'cancelled'])->default('pending');
            $table->text('work_description');
            $table->integer('progress_percentage')->default(0);
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->text('issues_encountered')->nullable();
            $table->text('next_steps')->nullable();
            $table->text('materials_used')->nullable();
            $table->text('equipment_used')->nullable();
            $table->json('photos')->nullable();
            $table->enum('weather_conditions', ['sunny', 'cloudy', 'rainy', 'stormy', 'windy'])->nullable();
            $table->text('additional_notes')->nullable();
            
            // Admin review fields
            $table->enum('review_status', ['pending', 'reviewed', 'needs_revision', 'approved'])->default('pending');
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->text('admin_comments')->nullable();
            $table->integer('admin_rating')->nullable();
            
            $table->timestamps();
            
            // Indexes
            $table->index(['task_id', 'report_date']);
            $table->index(['user_id', 'report_date']);
            $table->index(['review_status', 'created_at']);
            $table->index('report_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('task_reports');
        
        // Remove added columns from tasks table
        if (Schema::hasTable('tasks')) {
            Schema::table('tasks', function (Blueprint $table) {
                if (Schema::hasColumn('tasks', 'progress_percentage')) {
                    $table->dropColumn('progress_percentage');
                }
                if (Schema::hasColumn('tasks', 'start_date')) {
                    $table->dropColumn('start_date');
                }
                if (Schema::hasColumn('tasks', 'actual_start_date')) {
                    $table->dropColumn('actual_start_date');
                }
                if (Schema::hasColumn('tasks', 'actual_end_date')) {
                    $table->dropColumn('actual_end_date');
                }
            });
        }
    }
};