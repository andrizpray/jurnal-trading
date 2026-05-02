<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            // SQLite treats enum as TEXT, no alteration needed
            return;
        }

        // MySQL: alter enum columns to include 'USC'
        DB::statement("ALTER TABLE users MODIFY currency ENUM('IDR', 'USD', 'USC') DEFAULT 'IDR'");
        DB::statement("ALTER TABLE trading_plans MODIFY currency ENUM('IDR', 'USD', 'USC') DEFAULT 'IDR'");
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            return;
        }

        DB::statement("ALTER TABLE users MODIFY currency ENUM('IDR', 'USD') DEFAULT 'IDR'");
        DB::statement("ALTER TABLE trading_plans MODIFY currency ENUM('IDR', 'USD') DEFAULT 'IDR'");
    }
};
