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
        Schema::create('equipment_maintenance', function (Blueprint $table) {
            $table->id();
            $table->foreignId('monitored_equipment_id')->constrained('monitored_equipment')->onDelete('cascade')->comment('Equipment being maintained');
            $table->enum('maintenance_type', ['routine', 'repair', 'inspection', 'calibration', 'replacement'])->default('routine');
            $table->timestamp('scheduled_date')->comment('When maintenance is scheduled');
            $table->timestamp('completed_date')->nullable()->comment('When maintenance was completed');
            $table->integer('estimated_duration')->nullable()->comment('Estimated duration in minutes');
            $table->integer('actual_duration')->nullable()->comment('Actual duration in minutes');
            $table->text('description')->comment('Description of maintenance work');
            $table->enum('priority', ['low', 'medium', 'high', 'critical'])->default('medium');
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled', 'overdue'])->default('scheduled');
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null')->comment('User who performed the maintenance');
            $table->decimal('cost', 10, 2)->nullable()->comment('Cost of maintenance');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->text('completion_notes')->nullable()->comment('Notes upon completion');
            $table->timestamps();

            // Indexes for performance with shorter names
            $table->index('monitored_equipment_id', 'idx_equip_maint_equipment');
            $table->index('status', 'idx_equip_maint_status');
            $table->index('maintenance_type', 'idx_equip_maint_type');
            $table->index('priority', 'idx_equip_maint_priority');
            $table->index('scheduled_date', 'idx_equip_maint_scheduled');
            $table->index('completed_date', 'idx_equip_maint_completed');
            $table->index('performed_by', 'idx_equip_maint_performed');
            $table->index(['status', 'scheduled_date'], 'idx_equip_maint_status_date');
            $table->index(['monitored_equipment_id', 'status'], 'idx_equip_maint_equip_status');
            $table->index(['monitored_equipment_id', 'maintenance_type'], 'idx_equip_maint_equip_type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_maintenance');
    }
};
