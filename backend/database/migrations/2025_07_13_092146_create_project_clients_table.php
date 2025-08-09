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
        // Create project_clients pivot table if it doesn't exist
        if (!Schema::hasTable('project_clients')) {
            Schema::create('project_clients', function (Blueprint $table) {
                $table->id();
                $table->foreignId('project_id')->constrained()->onDelete('cascade');
                $table->foreignId('client_id')->references('id')->on('users')->onDelete('cascade');
                $table->enum('access_level', ['view', 'limited', 'full'])->default('view');
                $table->boolean('can_view_photos')->default(true);
                $table->boolean('can_view_reports')->default(true);
                $table->boolean('can_view_issues')->default(false);
                $table->boolean('can_receive_notifications')->default(true);
                $table->timestamp('assigned_at')->useCurrent();
                $table->foreignId('assigned_by')->references('id')->on('users')->onDelete('set null')->nullable();
                $table->timestamps();
                
                $table->unique(['project_id', 'client_id']);
                $table->index(['client_id', 'access_level']);
                $table->index(['project_id', 'can_view_photos']);
                $table->index(['assigned_at']);
            });
        }

        // Add columns to existing project_clients table if they don't exist
        if (Schema::hasTable('project_clients')) {
            Schema::table('project_clients', function (Blueprint $table) {
                if (!Schema::hasColumn('project_clients', 'access_level')) {
                    $table->enum('access_level', ['view', 'limited', 'full'])->default('view')->after('client_id');
                }
                if (!Schema::hasColumn('project_clients', 'can_view_photos')) {
                    $table->boolean('can_view_photos')->default(true)->after('access_level');
                }
                if (!Schema::hasColumn('project_clients', 'can_view_reports')) {
                    $table->boolean('can_view_reports')->default(true)->after('can_view_photos');
                }
                if (!Schema::hasColumn('project_clients', 'can_view_issues')) {
                    $table->boolean('can_view_issues')->default(false)->after('can_view_reports');
                }
                if (!Schema::hasColumn('project_clients', 'can_receive_notifications')) {
                    $table->boolean('can_receive_notifications')->default(true)->after('can_view_issues');
                }
                if (!Schema::hasColumn('project_clients', 'assigned_at')) {
                    $table->timestamp('assigned_at')->useCurrent()->after('can_receive_notifications');
                }
                if (!Schema::hasColumn('project_clients', 'assigned_by')) {
                    $table->foreignId('assigned_by')->references('id')->on('users')->onDelete('set null')->nullable()->after('assigned_at');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_clients');
    }
};