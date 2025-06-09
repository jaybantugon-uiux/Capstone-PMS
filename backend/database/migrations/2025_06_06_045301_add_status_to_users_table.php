<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        if (!Schema::hasColumn('users', 'status')) {
            $table->enum('status', ['active', 'deactivated'])
                  ->default('active')
                  ->after('email');
        }
        if (!Schema::hasColumn('users', 'deactivated_at')) {
            $table->timestamp('deactivated_at')
                  ->nullable()
                  ->after('status');
        }
    });
}
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['status', 'deactivated_at']);
        });
    }
};