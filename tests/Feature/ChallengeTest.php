<?php

namespace Tests\Feature;

use App\Models\Challenge;
use App\Models\ChallengeDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChallengeTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create([
            'default_capital' => 5000000,
            'trader_type'     => 'moderate',
        ]);
    }

    public function test_challenge_index_loads(): void
    {
        $this->actingAs($this->user)
            ->get('/challenge')
            ->assertStatus(200);
    }

    public function test_user_can_start_challenge(): void
    {
        $response = $this->actingAs($this->user)->post('/challenge/start', [
            'initial_capital' => 1000000,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('challenges', [
            'user_id'         => $this->user->id,
            'initial_capital' => 1000000,
            'status'          => 'active',
        ]);
    }

    public function test_starting_new_challenge_fails_old_one(): void
    {
        Challenge::create([
            'user_id'         => $this->user->id,
            'initial_capital' => 1000000,
            'target_capital'  => 2000000,
            'status'          => 'active',
            'started_at'      => now(),
        ]);

        $this->actingAs($this->user)->post('/challenge/start', [
            'initial_capital' => 2000000,
        ]);

        $active = Challenge::where('user_id', $this->user->id)
            ->where('status', 'active')
            ->count();

        $this->assertEquals(1, $active);

        $failed = Challenge::where('user_id', $this->user->id)
            ->where('status', 'failed')
            ->count();

        $this->assertEquals(1, $failed);
    }

    public function test_challenge_generates_30_days(): void
    {
        $this->actingAs($this->user)->post('/challenge/start', [
            'initial_capital' => 1000000,
        ]);

        $challenge = Challenge::where('user_id', $this->user->id)->first();
        $this->assertEquals(30, $challenge->days()->count());
    }

    public function test_user_can_update_challenge_day(): void
    {
        $this->actingAs($this->user)->post('/challenge/start', [
            'initial_capital' => 1000000,
        ]);

        $challenge = Challenge::where('user_id', $this->user->id)->first();

        $response = $this->actingAs($this->user)->patch(
            "/challenge/{$challenge->id}/day/1",
            ['actual_result' => 25000, 'notes' => 'Good day']
        );

        $response->assertRedirect();

        $this->assertDatabaseHas('challenge_days', [
            'challenge_id'  => $challenge->id,
            'day_number'    => 1,
            'actual_result' => 25000,
            'status'        => 'completed',
        ]);
    }

    public function test_user_cannot_access_other_users_challenge(): void
    {
        $otherUser = User::factory()->create();
        $challenge = Challenge::create([
            'user_id'         => $otherUser->id,
            'initial_capital' => 1000000,
            'target_capital'  => 2000000,
            'status'          => 'active',
            'started_at'      => now(),
        ]);

        $this->actingAs($this->user)
            ->get("/challenge/{$challenge->id}")
            ->assertStatus(403);
    }

    public function test_user_can_reset_challenge(): void
    {
        $challenge = Challenge::create([
            'user_id'         => $this->user->id,
            'initial_capital' => 1000000,
            'target_capital'  => 2000000,
            'status'          => 'active',
            'started_at'      => now(),
        ]);

        $this->actingAs($this->user)->delete("/challenge/{$challenge->id}");

        $this->assertDatabaseHas('challenges', [
            'id'     => $challenge->id,
            'status' => 'failed',
        ]);
    }
}
