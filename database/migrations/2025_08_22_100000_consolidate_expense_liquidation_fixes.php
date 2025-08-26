<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Consolidates all expense liquidation subsystem fixes and improvements
     */
    public function up(): void
    {
        // 1. Add flag_priority to liquidated_forms table
        if (!Schema::hasColumn('liquidated_forms', 'flag_priority')) {
            Schema::table('liquidated_forms', function (Blueprint $table) {
                $table->enum('flag_priority', ['low', 'medium', 'high', 'critical'])->nullable()->after('flag_reason');
            });
        }

        // 2. Add financial_report_id to receipts table
        if (!Schema::hasColumn('receipts', 'financial_report_id')) {
            Schema::table('receipts', function (Blueprint $table) {
                $table->unsignedBigInteger('financial_report_id')->nullable()->after('liquidated_form_id');
                $table->foreign('financial_report_id')->references('id')->on('financial_reports')->onDelete('set null');
                $table->index('financial_report_id', 'idx_receipts_financial_report');
            });
        }

        // 3. Remove exchange_rate from financial_reports (as per previous migration)
        if (Schema::hasColumn('financial_reports', 'exchange_rate')) {
            Schema::table('financial_reports', function (Blueprint $table) {
                $table->dropColumn('exchange_rate');
            });
        }

        // 4. Remove approval fields from financial_reports (as per previous migration)
        $columnsToRemove = ['approved_by', 'approved_at', 'approval_notes', 'rejection_reason'];
        foreach ($columnsToRemove as $column) {
            if (Schema::hasColumn('financial_reports', $column)) {
                Schema::table('financial_reports', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        // 5. Fix liquidated_forms status enum
        DB::statement("ALTER TABLE liquidated_forms MODIFY COLUMN status ENUM('pending', 'under_review', 'approved', 'processed', 'revision_requested', 'flagged', 'clarification_requested', 'rejected') DEFAULT 'pending'");

        // 6. Fix liquidated_form_revisions status enum
        DB::statement("ALTER TABLE liquidated_form_revisions MODIFY COLUMN status ENUM('pending', 'addressed', 'rejected') DEFAULT 'pending'");

        // 7. Add approval fields to liquidated_form_revisions
        if (!Schema::hasColumn('liquidated_form_revisions', 'approved_by')) {
            Schema::table('liquidated_form_revisions', function (Blueprint $table) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('addressed_at');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->text('approval_notes')->nullable()->after('approved_at');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 8. Fix any liquidated forms that don't have a status
        DB::table('liquidated_forms')
            ->whereNull('status')
            ->orWhere('status', '')
            ->update(['status' => 'pending']);

        // 9. Create trigger for liquidated forms update tracking
        DB::unprepared('DROP TRIGGER IF EXISTS before_liquidated_form_update');
        
        DB::unprepared('
            CREATE TRIGGER before_liquidated_form_update
            BEFORE UPDATE ON liquidated_forms
            FOR EACH ROW
            BEGIN
                -- Track revision count
                IF NEW.status = "revision_requested" AND OLD.status != "revision_requested" THEN
                    SET NEW.revision_count = OLD.revision_count + 1;
                    SET NEW.last_revision_date = NOW();
                END IF;
                
                -- Track flagging
                IF NEW.status = "flagged" AND OLD.status != "flagged" THEN
                    SET NEW.flagged_at = NOW();
                END IF;
                
                -- Track clarification requests
                IF NEW.status = "clarification_requested" AND OLD.status != "clarification_requested" THEN
                    SET NEW.clarification_requested_at = NOW();
                END IF;
                
                -- Track printing
                IF NEW.printed_at IS NOT NULL AND OLD.printed_at IS NULL THEN
                    SET NEW.printed_by = @current_user_id;
                END IF;
                
                -- Update timestamp
                SET NEW.updated_at = NOW();
            END
        ');

        // 10. Fix daily_expenditures status enum to simplified workflow
        DB::statement("ALTER TABLE daily_expenditures MODIFY COLUMN status ENUM('draft', 'submitted') NOT NULL DEFAULT 'draft'");
        
        // Update existing records to match simplified workflow
        DB::table('daily_expenditures')
            ->whereIn('status', ['pending', 'approved', 'rejected'])
            ->update(['status' => 'submitted']);

        // 11. Remove approval fields from daily_expenditures (simplified workflow)
        $dailyExpenditureColumnsToRemove = ['approved_by', 'approved_at', 'approval_notes', 'rejection_reason'];
        foreach ($dailyExpenditureColumnsToRemove as $column) {
            if (Schema::hasColumn('daily_expenditures', $column)) {
                Schema::table('daily_expenditures', function (Blueprint $table) use ($column) {
                    $table->dropColumn($column);
                });
            }
        }

        // 12. Drop problematic daily expenditure triggers
        try {
            DB::unprepared('DROP TRIGGER IF EXISTS after_daily_expenditure_insert');
            DB::unprepared('DROP TRIGGER IF EXISTS after_daily_expenditure_status_update');
            DB::unprepared('DROP TRIGGER IF EXISTS after_expenditure_submission_notify');
        } catch (Exception $e) {
            \Log::warning('Failed to drop some daily expenditure triggers: ' . $e->getMessage());
        }

        // 13. Add additional indexes for better performance
        $indexes = [
            'idx_liquidated_forms_flag_priority' => 'liquidated_forms (flag_priority)',
            'idx_liquidated_forms_revision_count' => 'liquidated_forms (revision_count)',
            'idx_liquidated_forms_last_revision' => 'liquidated_forms (last_revision_date)',
            'idx_liquidated_form_revisions_approval' => 'liquidated_form_revisions (approved_by, approved_at)',
            'idx_receipts_financial_report_status' => 'receipts (financial_report_id, status)',
        ];

        foreach ($indexes as $indexName => $tableColumn) {
            if (!$this->indexExists($tableColumn)) {
                DB::statement("CREATE INDEX {$indexName} ON {$tableColumn}");
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // 1. Drop trigger
        DB::unprepared('DROP TRIGGER IF EXISTS before_liquidated_form_update');

        // 2. Drop additional indexes
        $indexes = [
            'idx_liquidated_forms_flag_priority',
            'idx_liquidated_forms_revision_count', 
            'idx_liquidated_forms_last_revision',
            'idx_liquidated_form_revisions_approval',
            'idx_receipts_financial_report_status',
        ];

        foreach ($indexes as $indexName) {
            if ($this->indexExists($indexName)) {
                DB::statement("DROP INDEX {$indexName}");
            }
        }

        // 3. Remove approval fields from liquidated_form_revisions
        Schema::table('liquidated_form_revisions', function (Blueprint $table) {
            if (Schema::hasColumn('liquidated_form_revisions', 'approval_notes')) {
                $table->dropColumn('approval_notes');
            }
            if (Schema::hasColumn('liquidated_form_revisions', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
            if (Schema::hasColumn('liquidated_form_revisions', 'approved_by')) {
                $table->dropForeign(['approved_by']);
                $table->dropColumn('approved_by');
            }
        });

        // 4. Revert liquidated_form_revisions status enum
        DB::statement("ALTER TABLE liquidated_form_revisions MODIFY COLUMN status ENUM('pending', 'addressed') DEFAULT 'pending'");

        // 5. Revert liquidated_forms status enum
        DB::statement("ALTER TABLE liquidated_forms MODIFY COLUMN status ENUM('pending', 'under_review', 'approved', 'processed', 'revision_requested', 'flagged') DEFAULT 'pending'");

        // 6. Remove financial_report_id from receipts
        Schema::table('receipts', function (Blueprint $table) {
            if (Schema::hasColumn('receipts', 'financial_report_id')) {
                $table->dropForeign(['financial_report_id']);
                $table->dropIndex('idx_receipts_financial_report');
                $table->dropColumn('financial_report_id');
            }
        });

        // 7. Remove flag_priority from liquidated_forms
        Schema::table('liquidated_forms', function (Blueprint $table) {
            if (Schema::hasColumn('liquidated_forms', 'flag_priority')) {
                $table->dropColumn('flag_priority');
            }
        });

        // 8. Restore exchange_rate to financial_reports
        if (!Schema::hasColumn('financial_reports', 'exchange_rate')) {
            Schema::table('financial_reports', function (Blueprint $table) {
                $table->decimal('exchange_rate', 8, 4)->default(1.0000)->after('currency');
            });
        }

        // 9. Restore approval fields to financial_reports
        if (!Schema::hasColumn('financial_reports', 'approved_by')) {
            Schema::table('financial_reports', function (Blueprint $table) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('generated_at');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->text('approval_notes')->nullable()->after('approved_at');
                $table->text('rejection_reason')->nullable()->after('approval_notes');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }

        // 10. Restore daily_expenditures status enum to original
        DB::statement("ALTER TABLE daily_expenditures MODIFY COLUMN status ENUM('draft', 'submitted', 'approved', 'rejected') NOT NULL DEFAULT 'draft'");

        // 11. Restore approval fields to daily_expenditures
        if (!Schema::hasColumn('daily_expenditures', 'approved_by')) {
            Schema::table('daily_expenditures', function (Blueprint $table) {
                $table->unsignedBigInteger('approved_by')->nullable()->after('submitted_at');
                $table->timestamp('approved_at')->nullable()->after('approved_by');
                $table->text('approval_notes')->nullable()->after('approved_at');
                $table->text('rejection_reason')->nullable()->after('approval_notes');
                $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            });
        }
    }

    /**
     * Check if an index exists
     */
    private function indexExists($indexName): bool
    {
        $indexParts = explode(' ', $indexName);
        $tableName = $indexParts[0];
        $columnName = trim($indexParts[1], '()');
        
        return DB::select("
            SELECT COUNT(*) as count 
            FROM INFORMATION_SCHEMA.STATISTICS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = '{$tableName}' 
            AND COLUMN_NAME = '{$columnName}'
        ")[0]->count > 0;
    }
};
