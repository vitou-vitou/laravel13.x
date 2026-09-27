<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Swipe;
use App\Models\DatingMatch;
use App\Models\Message;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DatingAppFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_welcome_page_renders_dating_app(): void
    {
        $this->seed();

        $response = $this->get('/');
        $response->assertStatus(200);
        $response->assertSee('Spark');
        $response->assertSee('Acting as:');
    }

    public function test_mutual_swipe_creates_match(): void
    {
        $this->seed();

        $user1 = User::first();
        $user2 = User::where('id', '>', 6)->first(); // Pick unswiped persona

        Swipe::create(['swiper_id' => $user1->id, 'swiped_id' => $user2->id, 'type' => 'like']);
        Swipe::create(['swiper_id' => $user2->id, 'swiped_id' => $user1->id, 'type' => 'like']);

        $match = DatingMatch::create([
            'user_one_id' => min($user1->id, $user2->id),
            'user_two_id' => max($user1->id, $user2->id),
            'matched_at' => now(),
        ]);

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'user_one_id' => min($user1->id, $user2->id),
            'user_two_id' => max($user1->id, $user2->id),
        ]);
    }

    public function test_message_can_be_sent_between_matches(): void
    {
        $this->seed();

        $match = DatingMatch::first();
        $this->assertNotNull($match);

        $msg = Message::create([
            'match_id' => $match->id,
            'sender_id' => $match->user_one_id,
            'body' => 'Test message from unit test!',
        ]);

        $this->assertDatabaseHas('messages', [
            'id' => $msg->id,
            'body' => 'Test message from unit test!',
        ]);
    }
}
