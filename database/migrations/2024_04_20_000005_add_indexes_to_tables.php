<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add indexes to trading_plans table
        Schema::table('trading_plans', function (Blueprint $table) {
            $table->index(['user_id', 'is_active']); // For active plan queries
            $table->index(['user_id', 'created_at']); // For history pagination
            $table->index('is_active'); // For global active plans query
            $table->index('created_at'); // For date-based queries
        });

        // Add indexes to challenges table
        Schema::table('challenges', function (Blueprint $table) {
            $table->index(['user_id', 'status']); // For user's active challenges
            $table->index(['user_id', 'created_at']); // For challenge history
            $table->index('status'); // For global challenge status queries
        });

        // Add indexes to challenge_days table
        Schema::table('challenge_days', function (Blueprint $table) {
            $table->index(['challenge_id', 'day_number']); // For specific day queries
            $table->index('challenge_id'); // For all days of a challenge
            $table->index('day_number'); // For day-based analytics
        });

        // Add indexes to journal_entries table
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->index(['user_id', 'entry_date']); // For user's journal by date
            $table->index(['user_id', 'created_at']); // For recent entries
            $table->index('entry_date'); // For date-range queries
            $table->index('result'); // For win/loss analytics
        });
    }

    public function down(): void
    {
        // Remove indexes from journal_entries
        Schema::table('journal_entries', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'entry_date']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['entry_date']);
            $table->dropIndex(['result']);
        });

        // Remove indexes from challenge_days
        Schema::table('challenge_days', function (Blueprint $table) {
            $table->dropIndex(['challenge_id', 'day_number']);
            $table->dropIndex(['challenge_id']);
            $table->dropIndex(['day_number']);
        });

        // Remove indexes from challenges
        Schema::table('challenges', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'status']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['status']);
        });

        // Remove indexes from trading_plans
        Schema::table('trading_plans', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'is_active']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['created_at']);
        });
    }
};