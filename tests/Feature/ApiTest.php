<?php

namespace Tests\Feature;

use App\Models\TradingPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_preview_api_returns_data(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/preview', [
            'currency'         => 'IDR',
            'capital'          => 5000000,
            'trader_type'      => 'moderate',
            'currency_pair'    => 'EUR/USD',
            'stop_loss_pips'   => 20,
            'take_profit_pips' => 40,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => [
                         'preview_days',
                         'summary' => ['initial_capital', 'final_capital', 'total_profit', 'growth_pct'],
                         'risk_reward' => ['stop_loss', 'take_profit', 'ratio'],
                     ],
                 ]);
    }

    public function test_preview_api_returns_7_days(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/preview', [
            'currency'         => 'IDR',
            'capital'          => 5000000,
            'trader_type'      => 'moderate',
            'currency_pair'    => 'EUR/USD',
            'stop_loss_pips'   => 20,
            'take_profit_pips' => 40,
        ]);

        $data = $response->json('data.preview_days');
        $this->assertCount(7, $data);
    }

    public function test_preview_api_validates_input(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/preview', [
            'currency' => 'INVALID',
            'capital'  => 100, // below minimum
        ]);

        $response->assertStatus(422)
                 ->assertJsonStructure(['success', 'errors']);
    }

    public function test_calculate_plan_api(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/calculate-plan', [
            'currency'         => 'IDR',
            'capital'          => 5000000,
            'trader_type'      => 'moderate',
            'currency_pair'    => 'EUR/USD',
            'stop_loss_pips'   => 20,
            'take_profit_pips' => 40,
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['days', 'final_capital', 'total_profit', 'growth_pct']]);
    }

    public function test_currency_pairs_api(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/preview/currency-pairs');

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['pairs']]);

        $pairs = $response->json('data.pairs');
        $this->assertContains('EUR/USD', $pairs);
    }

    public function test_trader_configs_api(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/preview/trader-configs');

        $response->assertStatus(200);
        $configs = $response->json('data.configs');

        $this->assertArrayHasKey('conservative', $configs);
        $this->assertArrayHasKey('moderate', $configs);
        $this->assertArrayHasKey('aggressive', $configs);
    }

    public function test_lot_size_api(): void
    {
        $response = $this->actingAs($this->user)->postJson('/api/preview/lot-size', [
            'risk_amount'    => 100000,
            'stop_loss_pips' => 20,
            'currency_pair'  => 'EUR/USD',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure(['success', 'data' => ['lot_size']]);

        // 100000 / (20 * 10) = 500
        $this->assertEquals(500, $response->json('data.lot_size'));
    }

    public function test_guest_cannot_access_api(): void
    {
        $this->postJson('/api/preview', [])->assertStatus(401);
    }

    public function test_notifications_api_returns_data(): void
    {
        $response = $this->actingAs($this->user)->getJson('/api/notifications');

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'success',
                     'data' => ['notifications', 'unread_count'],
                 ]);
    }
}
