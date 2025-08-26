<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing trigger that's causing the CASE statement error
        DB::unprepared('DROP TRIGGER IF EXISTS before_liquidated_form_update');
        
        // Create a new trigger that properly handles all status transitions
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
                
                -- Track unflagging (clear flag-related fields when status changes from flagged to pending)
                IF NEW.status = "pending" AND OLD.status = "flagged" THEN
                    SET NEW.flagged_at = NULL;
                    SET NEW.flagged_by = NULL;
                    SET NEW.flag_reason = NULL;
                    SET NEW.flag_priority = NULL;
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
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the trigger
        DB::unprepared('DROP TRIGGER IF EXISTS before_liquidated_form_update');
        
        // Recreate the original trigger (without the unflag handling)
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
    }
};
