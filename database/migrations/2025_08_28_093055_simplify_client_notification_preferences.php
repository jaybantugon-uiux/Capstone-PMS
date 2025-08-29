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
        Schema::table('client_notification_preferences', function (Blueprint $table) {
            // Remove all notification preference columns except progress reports
            $table->dropColumn([
                'project_updates_email',
                'project_updates_app',
                'task_completion_email',
                'task_completion_app',
                'milestone_email',
                'milestone_app',
                'issue_notifications_email',
                'issue_notifications_app',
                'photo_upload_email',
                'photo_upload_app',
                'general_announcements_email',
                'general_announcements_app',
                'marketing_email',
                'site_issue_notifications_email',
                'site_issue_notifications_app',
                'site_issue_email_preferences',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('client_notification_preferences', function (Blueprint $table) {
            // Add back the removed columns
            $table->boolean('project_updates_email')->default(true);
            $table->boolean('project_updates_app')->default(true);
            $table->boolean('task_completion_email')->default(true);
            $table->boolean('task_completion_app')->default(true);
            $table->boolean('milestone_email')->default(true);
            $table->boolean('milestone_app')->default(true);
            $table->boolean('issue_notifications_email')->default(false);
            $table->boolean('issue_notifications_app')->default(true);
            $table->boolean('photo_upload_email')->default(false);
            $table->boolean('photo_upload_app')->default(true);
            $table->boolean('general_announcements_email')->default(true);
            $table->boolean('general_announcements_app')->default(true);
            $table->boolean('marketing_email')->default(false);
            $table->boolean('site_issue_notifications_email')->default(true);
            $table->boolean('site_issue_notifications_app')->default(true);
            $table->json('site_issue_email_preferences')->nullable();
        });
    }
};
