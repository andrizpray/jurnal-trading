<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trading_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('currency', ['IDR', 'USD'])->default('IDR');
            $table->decimal('capital', 20, 2);
            $table->enum('trader_type', ['conservative', 'moderate', 'aggressive']);
            $table->string('currency_pair', 20)->default('EUR/USD');
            $table->integer('stop_loss_pips');
            $table->integer('take_profit_pips');
            $table->decimal('risk_per_trade', 5, 2)->default(2.00);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trading_plans');
    }
};
