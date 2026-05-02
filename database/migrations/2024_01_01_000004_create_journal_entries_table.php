<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('entry_date');
            $table->string('currency_pair', 20)->nullable();
            $table->enum('trade_type', ['buy', 'sell', 'analysis'])->default('analysis');
            $table->decimal('profit_loss', 15, 2)->default(0);
            $table->enum('result', ['win', 'loss', 'breakeven'])->nullable();
            $table->text('analysis')->nullable();
            $table->text('lesson_learned')->nullable();
            $table->integer('emotion_score')->nullable();
            $table->enum('market_condition', ['trending', 'ranging', 'volatile'])->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journal_entries');
    }
};
