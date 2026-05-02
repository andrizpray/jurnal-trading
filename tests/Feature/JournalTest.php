<?php

namespace Tests\Feature;

use App\Models\JournalEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JournalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_journal_index_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/journal');
        $response->assertStatus(200);
    }

    public function test_journal_create_page_loads(): void
    {
        $response = $this->actingAs($this->user)->get('/journal/create');
        $response->assertStatus(200);
    }

    public function test_user_can_create_journal_entry(): void
    {
        $response = $this->actingAs($this->user)->post('/journal', [
            'entry_date'       => '2024-01-15',
            'currency_pair'    => 'EUR/USD',
            'trade_type'       => 'buy',
            'profit_loss'      => 150000,
            'result'           => 'win',
            'analysis'         => 'Strong bullish breakout on H4',
            'lesson_learned'   => 'Wait for candle close before entry',
            'emotion_score'    => 4,
            'market_condition' => 'trending',
        ]);

        $response->assertRedirect(route('journal.index'));
        $this->assertDatabaseHas('journal_entries', [
            'user_id'      => $this->user->id,
            'currency_pair' => 'EUR/USD',
            'result'       => 'win',
        ]);
    }

    public function test_user_can_update_journal_entry(): void
    {
        $entry = JournalEntry::create([
            'user_id'    => $this->user->id,
            'entry_date' => '2024-01-15',
            'trade_type' => 'buy',
        ]);

        $response = $this->actingAs($this->user)->put("/journal/{$entry->id}", [
            'entry_date'    => '2024-01-15',
            'trade_type'    => 'sell',
            'profit_loss'   => -50000,
            'result'        => 'loss',
        ]);

        $response->assertRedirect(route('journal.index'));
        $this->assertDatabaseHas('journal_entries', [
            'id'         => $entry->id,
            'trade_type' => 'sell',
            'result'     => 'loss',
        ]);
    }

    public function test_user_can_delete_journal_entry(): void
    {
        $entry = JournalEntry::create([
            'user_id'    => $this->user->id,
            'entry_date' => '2024-01-15',
            'trade_type' => 'buy',
        ]);

        $this->actingAs($this->user)->delete("/journal/{$entry->id}");

        $this->assertDatabaseMissing('journal_entries', ['id' => $entry->id]);
    }

    public function test_user_cannot_view_another_users_journal(): void
    {
        $otherUser = User::factory()->create();
        $entry = JournalEntry::create([
            'user_id'    => $otherUser->id,
            'entry_date' => '2024-01-15',
            'trade_type' => 'buy',
        ]);

        $this->actingAs($this->user)
            ->get("/journal/{$entry->id}")
            ->assertStatus(403);
    }

    public function test_journal_validates_required_fields(): void
    {
        $response = $this->actingAs($this->user)->post('/journal', []);
        $response->assertSessionHasErrors(['entry_date', 'trade_type']);
    }
}
