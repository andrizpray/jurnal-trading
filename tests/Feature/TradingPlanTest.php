<?php

namespace Tests\Feature;

use App\Models\TradingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TradingPlanTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_trading_plan_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/trading-plan');
        $response->assertStatus(200);
    }

    public function test_user_can_create_trading_plan(): void
    {
        $response = $this->actingAs($this->user)->post('/trading-plan', [
            'currency'         => 'IDR',
            'capital'          => 5000000,
            'trader_type'      => 'moderate',
            'currency_pair'    => 'EUR/USD',
            'stop_loss_pips'   => 20,
            'take_profit_pips' => 40,
            'risk_per_trade'   => 2.0,
        ]);

        $response->assertRedirect(route('trading-plan.index'));
        $this->assertDatabaseHas('trading_plans', [
            'user_id'      => $this->user->id,
            'currency_pair' => 'EUR/USD',
            'is_active'    => true,
        ]);
    }

    public function test_creating_new_plan_deactivates_old_plan(): void
    {
        TradingPlan::create([
            'user_id'          => $this->user->id,
            'currency'         => 'IDR',
            'capital'          => 3000000,
            'trader_type'      => 'conservative',
            'currency_pair'    => 'GBP/USD',
            'stop_loss_pips'   => 15,
            'take_profit_pips' => 30,
            'risk_per_trade'   => 1.0,
            'is_active'        => true,
        ]);

        $this->actingAs($this->user)->post('/trading-plan', [
            'currency'         => 'IDR',
            'capital'          => 5000000,
            'trader_type'      => 'moderate',
            'currency_pair'    => 'EUR/USD',
            'stop_loss_pips'   => 20,
            'take_profit_pips' => 40,
        ]);

        $activePlans = TradingPlan::where('user_id', $this->user->id)
            ->where('is_active', true)
            ->count();

        $this->assertEquals(1, $activePlans);
    }

    public function test_trading_plan_history_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/trading-plan/history');
        $response->assertStatus(200);
    }

    public function test_create_plan_validation_fails_with_invalid_data(): void
    {
        $response = $this->actingAs($this->user)->post('/trading-plan', [
            'currency'         => 'INVALID',
            'capital'          => -100,
            'trader_type'      => 'unknown',
            'currency_pair'    => '',
            'stop_loss_pips'   => 0,
            'take_profit_pips' => 0,
        ]);

        $response->assertSessionHasErrors(['currency', 'capital', 'trader_type', 'currency_pair']);
    }

    public function test_guest_cannot_access_trading_plan(): void
    {
        $this->get('/trading-plan')->assertRedirect('/login');
    }
}
