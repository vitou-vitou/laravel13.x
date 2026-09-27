<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Profile;
use App\Models\Swipe;
use App\Models\DatingMatch;
use App\Models\Message;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $personas = [
            [
                'name' => 'Alex Rivera',
                'email' => 'alex@example.com',
                'gender' => 'man',
                'age' => 28,
                'occupation' => 'Product Designer @ Figma',
                'city' => 'Brooklyn, NY',
                'distance_km' => 3,
                'bio' => 'Coffee snob, vinyl collector, and weekend trail runner. Always down for spontaneous taco runs and art gallery hops. Looking for genuine connection & laughs.',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Design', 'Coffee', 'Running', 'Vinyl', 'Photography'],
            ],
            [
                'name' => 'Sophia Chen',
                'email' => 'sophia@example.com',
                'gender' => 'woman',
                'age' => 26,
                'occupation' => 'Architectural Visualizer',
                'city' => 'Manhattan, NY',
                'distance_km' => 2,
                'bio' => 'Building 3D worlds by day, exploring hidden rooftop bars by night. Big fan of brutalist buildings, matcha lattes, and deep podcasts.',
                'avatar_url' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Architecture', 'Matcha', '3D Art', 'Podcasts', 'Museums'],
            ],
            [
                'name' => 'Elena Rostova',
                'email' => 'elena@example.com',
                'gender' => 'woman',
                'age' => 27,
                'occupation' => 'Sommelier & Writer',
                'city' => 'SoHo, NY',
                'distance_km' => 4,
                'bio' => 'Can pair your chaotic energy with natural orange wine. Passport always stamped, French bulldog co-parent, fluent in sarcasm.',
                'avatar_url' => 'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1494790108377-be9c29b29330?w=800&auto=format&fit=crop&q=80',
                    'https://images.unsplash.com/photo-1529626455594-4ff0802cfb7e?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Wine', 'Writing', 'Dogs', 'Travel', 'Jazz'],
            ],
            [
                'name' => 'Marcus Vance',
                'email' => 'marcus@example.com',
                'gender' => 'man',
                'age' => 30,
                'occupation' => 'Founder & Seed Investor',
                'city' => 'Williamsburg, NY',
                'distance_km' => 5,
                'bio' => 'Recovering corporate banker turned early-stage founder. Marathon trainee. Let us debate whether deep dish is actually pizza (it is not).',
                'avatar_url' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Startups', 'Marathon', 'Sailing', 'Reading', 'Italian Food'],
            ],
            [
                'name' => 'Maya Lin',
                'email' => 'maya@example.com',
                'gender' => 'woman',
                'age' => 25,
                'occupation' => 'Ceramicist & Studio Owner',
                'city' => 'Greenpoint, NY',
                'distance_km' => 1,
                'bio' => 'Clay on my hands 90% of the time. Plant mom to 24 ferns. Looking for someone who will teach me how to make fresh pasta.',
                'avatar_url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Ceramics', 'Plants', 'Cooking', 'Thrifting', 'Indie Rock'],
            ],
            [
                'name' => 'Liam O’Connor',
                'email' => 'liam@example.com',
                'gender' => 'man',
                'age' => 29,
                'occupation' => 'Cinematographer',
                'city' => 'DUMBO, NY',
                'distance_km' => 6,
                'bio' => 'Shooting indie documentaries and music videos. 35mm film purist. Give me your top 3 cinema recommendations.',
                'avatar_url' => 'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1492562080023-ab3db95bfbce?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Film', 'Analog Cameras', 'Concerts', 'Bouldering', 'Ramen'],
            ],
            [
                'name' => 'Zara Morales',
                'email' => 'zara@example.com',
                'gender' => 'woman',
                'age' => 28,
                'occupation' => 'Biotech Research Fellow',
                'city' => 'Upper West Side, NY',
                'distance_km' => 7,
                'bio' => 'DNA sequencer by trade, salsa dancer by night. Half Cuban, half nerd. Will challenge you to Mario Kart and definitely win.',
                'avatar_url' => 'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1517841905240-472988babdf9?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Science', 'Dancing', 'Gaming', 'Spicy Food', 'Scuba Diving'],
            ],
            [
                'name' => 'Julian Park',
                'email' => 'julian@example.com',
                'gender' => 'man',
                'age' => 27,
                'occupation' => 'Sound Engineer & DJ',
                'city' => 'Bushwick, NY',
                'distance_km' => 4,
                'bio' => 'Producing ambient electronica and DJing weekend warehouse sessions. Obsessed with synthesizers, late-night diners, and Japanese curry.',
                'avatar_url' => 'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Electronic Music', 'Synths', 'Nightlife', 'Curry', 'Cyberpunk'],
            ],
            [
                'name' => 'Chloe Bennett',
                'email' => 'chloe@example.com',
                'gender' => 'woman',
                'age' => 24,
                'occupation' => 'Brand Strategist',
                'city' => 'East Village, NY',
                'distance_km' => 2,
                'bio' => 'Lives on iced Americanos and Pinterest boards. Golden retriever energy. Let us go to a bookstore and judge books purely by their cover.',
                'avatar_url' => 'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1524504388940-b1c1722653e1?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Books', 'Coffee', 'Fashion', 'Pilates', 'Thrifting'],
            ],
            [
                'name' => 'Noah Fischer',
                'email' => 'noah@example.com',
                'gender' => 'man',
                'age' => 31,
                'occupation' => 'Pastry Chef & Baker',
                'city' => 'Lower East Side, NY',
                'distance_km' => 3,
                'bio' => 'Wakes up at 4am to fold sourdough croissants. Yes, I will bake for you. Seeking someone to explore farmer markets and record shops.',
                'avatar_url' => 'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1519085360753-af0119f7cbe7?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Baking', 'Food Markets', 'Cycling', 'Jazz', 'Dogs'],
            ],
            [
                'name' => 'Aria Thorne',
                'email' => 'aria@example.com',
                'gender' => 'woman',
                'age' => 29,
                'occupation' => 'Environmental Lawyer',
                'city' => 'Cobble Hill, NY',
                'distance_km' => 5,
                'bio' => 'Fighting for green energy by day, baking homemade focaccia on Sundays. High energy, loves hiking upstate, and open-mic comedy.',
                'avatar_url' => 'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1534528741775-53994a69daeb?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Hiking', 'Environment', 'Comedy', 'Focaccia', 'Camping'],
            ],
            [
                'name' => 'Jordan Brooks',
                'email' => 'jordan@example.com',
                'gender' => 'non-binary',
                'age' => 26,
                'occupation' => 'Game Narrative Writer',
                'city' => 'Astoria, NY',
                'distance_km' => 8,
                'bio' => 'Writing interactive sci-fi games. Cat human, mechanical keyboard collector, certified tea geek. Tell me your weirdest conspiracy theory.',
                'avatar_url' => 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=800&auto=format&fit=crop&q=80',
                'photos' => [
                    'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?w=800&auto=format&fit=crop&q=80',
                ],
                'interests' => ['Sci-Fi', 'Gaming', 'Cats', 'Tea', 'Keyboards'],
            ],
        ];

        $users = [];
        foreach ($personas as $p) {
            $user = User::create([
                'name' => $p['name'],
                'email' => $p['email'],
                'password' => Hash::make('password'),
            ]);

            Profile::create([
                'user_id' => $user->id,
                'age' => $p['age'],
                'gender' => $p['gender'],
                'interested_in' => 'all',
                'occupation' => $p['occupation'],
                'city' => $p['city'],
                'distance_km' => $p['distance_km'],
                'bio' => $p['bio'],
                'avatar_url' => $p['avatar_url'],
                'photos' => $p['photos'],
                'interests' => $p['interests'],
            ]);

            $users[] = $user;
        }

        // Setup pre-existing matches for user #1 (Alex) with Sophia (#2) and Elena (#3)
        $alex = $users[0];
        $sophia = $users[1];
        $elena = $users[2];

        // Sophia & Alex mutual like
        Swipe::create(['swiper_id' => $alex->id, 'swiped_id' => $sophia->id, 'type' => 'like']);
        Swipe::create(['swiper_id' => $sophia->id, 'swiped_id' => $alex->id, 'type' => 'like']);
        $match1 = DatingMatch::create([
            'user_one_id' => $alex->id,
            'user_two_id' => $sophia->id,
            'matched_at' => now()->subHours(5),
        ]);

        Message::create([
            'match_id' => $match1->id,
            'sender_id' => $sophia->id,
            'body' => 'Hey Alex! Loved your photography shots from Brooklyn. Do you shoot on film?',
            'created_at' => now()->subHours(4),
        ]);
        Message::create([
            'match_id' => $match1->id,
            'sender_id' => $alex->id,
            'body' => 'Hey Sophia! Yes, mostly 35mm on a classic Olympus OM-1. Rooftop shots are my favorite.',
            'created_at' => now()->subHours(3),
        ]);
        Message::create([
            'match_id' => $match1->id,
            'sender_id' => $sophia->id,
            'body' => 'That is awesome! We should check out that new gallery opening in Chelsea this Friday!',
            'created_at' => now()->subMinutes(25),
        ]);

        // Elena & Alex mutual like
        Swipe::create(['swiper_id' => $alex->id, 'swiped_id' => $elena->id, 'type' => 'superlike']);
        Swipe::create(['swiper_id' => $elena->id, 'swiped_id' => $alex->id, 'type' => 'like']);
        $match2 = DatingMatch::create([
            'user_one_id' => $alex->id,
            'user_two_id' => $elena->id,
            'matched_at' => now()->subDays(1),
        ]);

        Message::create([
            'match_id' => $match2->id,
            'sender_id' => $elena->id,
            'body' => 'Superlike! I am flattered haha. What is your go-to wine order?',
            'created_at' => now()->subHours(12),
        ]);

        // Maya (#5) already liked Alex -> when Alex swipes Maya right in UI, triggers instant "It's a Match!" celebration!
        $maya = $users[4];
        Swipe::create(['swiper_id' => $maya->id, 'swiped_id' => $alex->id, 'type' => 'like']);
    }
}
