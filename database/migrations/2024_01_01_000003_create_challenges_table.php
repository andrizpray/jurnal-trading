<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('challenges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('initial_capital', 15, 2);
            $table->decimal('target_capital', 15, 2);
            $table->decimal('total_profit', 15, 2)->default(0);
            $table->integer('current_day')->default(1);
            $table->integer('progress_percent')->default(0);
            $table->enum('status', ['pending', 'active', 'completed', 'failed'])->default('pending');
            $table->date('started_at')->nullable();
            $table->timestamps();
        });

        Schema::create('challenge_days', function (Blueprint $table) {
            $table->id();
            $table->foreignId('challenge_id')->constrained()->onDelete('cascade');
            $table->integer('day_number');
            $table->decimal('start_capital', 15, 2)->default(0);
            $table->decimal('target_profit', 15, 2)->default(0);
            $table->decimal('actual_result', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'completed', 'failed', 'skipped'])->default('pending');
            $table->date('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('challenge_days');
        Schema::dropIfExists('challenges');
    }
};
