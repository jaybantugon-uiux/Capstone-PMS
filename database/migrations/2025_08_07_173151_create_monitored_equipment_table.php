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
        Schema::create('monitored_equipment', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade')->comment('Site coordinator who owns/manages this equipment');
            $table->foreignId('project_id')->nullable()->constrained()->onDelete('set null')->comment('Project this equipment is assigned to (if project_site usage)');
            $table->unsignedBigInteger('equipment_request_id')->nullable()->comment('Reference to the equipment request that created this equipment');
            $table->string('equipment_name');
            $table->text('equipment_description');
            $table->enum('usage_type', ['personal', 'project_site'])->default('personal');
            $table->integer('quantity')->default(1);
            $table->enum('status', ['pending_approval', 'active', 'inactive', 'maintenance', 'declined'])->default('pending_approval');
            $table->enum('availability_status', ['available', 'in_use', 'maintenance', 'out_of_order'])->default('available');
            $table->string('location')->nullable();
            $table->string('serial_number')->nullable();
            $table->date('purchase_date')->nullable();
            $table->date('warranty_expiry')->nullable();
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->timestamp('last_status_update')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes for performance with shorter names
            $table->index('user_id', 'idx_mon_equip_user');
            $table->index('project_id', 'idx_mon_equip_project');
            $table->index('status', 'idx_mon_equip_status');
            $table->index('availability_status', 'idx_mon_equip_availability');
            $table->index('usage_type', 'idx_mon_equip_usage');
            $table->index('next_maintenance_date', 'idx_mon_equip_maintenance');
            $table->index('equipment_request_id', 'idx_mon_equip_request');
            $table->index(['user_id', 'status'], 'idx_mon_equip_user_status');
            $table->index(['project_id', 'status'], 'idx_mon_equip_project_status');
            $table->index(['user_id', 'usage_type'], 'idx_mon_equip_user_usage');
            $table->index(['project_id', 'usage_type'], 'idx_mon_equip_project_usage');
            $table->index(['status', 'availability_status'], 'idx_mon_equip_status_avail');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monitored_equipment');
    }
};
