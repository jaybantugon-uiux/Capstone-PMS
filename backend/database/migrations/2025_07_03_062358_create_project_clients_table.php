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
        Schema::create('project_clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->onDelete('cascade');
            $table->foreignId('client_id')->constrained('users')->onDelete('cascade');
            $table->enum('access_level', ['view', 'limited', 'full'])->default('view');
            $table->boolean('can_view_photos')->default(true);
            $table->boolean('can_view_reports')->default(true);
            $table->boolean('can_view_issues')->default(false);
            $table->boolean('can_receive_notifications')->default(true);
            $table->timestamp('assigned_at')->useCurrent();
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            // Unique constraint to prevent duplicate assignments
            $table->unique(['project_id', 'client_id']);

            // Indexes for performance
            $table->index(['project_id', 'access_level']);
            $table->index(['client_id', 'can_receive_notifications']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('project_clients');
    }
};