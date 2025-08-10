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
        Schema::create('equipment_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Site coordinator requesting equipment');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null')->comment('Project this equipment is for (if project_site usage)');
            $table->unsignedBigInteger('monitored_equipment_id')->nullable()->comment('Reference to created monitored equipment');
            $table->string('equipment_name');
            $table->text('equipment_description');
            $table->enum('usage_type', ['personal', 'project_site'])->default('personal');
            $table->integer('quantity')->default(1);
            $table->decimal('estimated_cost', 10, 2)->nullable();
            $table->text('justification');
            $table->enum('urgency_level', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->text('additional_notes')->nullable();
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null')->comment('Admin who approved/declined the request');
            $table->timestamp('approved_at')->nullable();
            $table->text('admin_notes')->nullable()->comment('Admin comments on approval/decline');
            $table->text('decline_reason')->nullable()->comment('Reason for declining the request');
            $table->timestamps();

            // Indexes for performance with shorter names
            $table->index('user_id', 'idx_equip_req_user');
            $table->index('project_id', 'idx_equip_req_project');
            $table->index('status', 'idx_equip_req_status');
            $table->index('urgency_level', 'idx_equip_req_urgency');
            $table->index('usage_type', 'idx_equip_req_usage');
            $table->index('approved_by', 'idx_equip_req_approved');
            $table->index('approved_at', 'idx_equip_req_approved_at');
            $table->index(['user_id', 'status'], 'idx_equip_req_user_status');
            $table->index(['project_id', 'status'], 'idx_equip_req_project_status');
            $table->index(['urgency_level', 'status'], 'idx_equip_req_urgency_status');
            $table->index(['user_id', 'urgency_level'], 'idx_equip_req_user_urgency');
            $table->index(['project_id', 'urgency_level'], 'idx_equip_req_project_urgency');
            $table->index(['created_at', 'urgency_level'], 'idx_equip_req_created_urgency');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_requests');
    }
};
