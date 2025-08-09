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
        Schema::table('tasks', function (Blueprint $table) {
            // Add created_by field if it doesn't exist
            if (!Schema::hasColumn('tasks', 'created_by')) {
                $table->unsignedBigInteger('created_by')->after('assigned_to');
                $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
            }
            
            // Add due_date field if it doesn't exist
            if (!Schema::hasColumn('tasks', 'due_date')) {
                $table->date('due_date')->nullable()->after('project_id');
            }
            
            // Modify description to be nullable if it isn't already
            $table->text('description')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            if (Schema::hasColumn('tasks', 'created_by')) {
                $table->dropForeign(['created_by']);
                $table->dropColumn('created_by');
            }
            
            if (Schema::hasColumn('tasks', 'due_date')) {
                $table->dropColumn('due_date');
            }
        });
    }
};