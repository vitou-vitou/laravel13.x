<?php

namespace Database\Seeders;

use App\Models\ShipNote;
use Illuminate\Database\Seeder;

class ShipNoteSeeder extends Seeder
{
    public function run(): void
    {
        ShipNote::query()->insert([
            [
                'weekday' => 'mon',
                'title' => 'SEA pragmatism',
                'region' => 'Southeast Asia',
                'company_habit' => 'Ship under pressure + multi-language',
                'project_type' => 'Internal tools',
                'practice' => 'Listed 3 steal habits: client speed, EN+local, SME scope. Skip trap: process without shipping.',
                'verdict' => 'keep',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'tue',
                'title' => 'Basecamp Shape Up',
                'region' => 'North America',
                'company_habit' => 'Shape / calm product — fixed appetite',
                'project_type' => 'Internal tools',
                'practice' => 'Set 1-day appetite for one vertical slice. No endless backlog for this week.',
                'verdict' => 'keep',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'weekday' => 'wed',
                'title' => 'Insurance admin CRUD',
                'region' => 'Southeast Asia',
                'company_habit' => 'Spec + evidence before polish',
                'project_type' => 'Internal tools / insurance admin CRUD',
                'practice' => 'Wrote must-haves: week board, note CRUD, seed. Out of scope: auth, multi-tenant.',
                'verdict' => 'pending',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
