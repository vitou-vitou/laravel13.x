<?php

namespace Database\Seeders;

use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $owner = User::query()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $member = User::factory()->create([
            'name' => 'Team Member',
        ]);

        $workspaceA = Workspace::query()->create(['name' => 'Acme Workspace']);
        $workspaceB = Workspace::query()->create(['name' => 'Growth Workspace']);

        $workspaceA->users()->attach([
            $owner->getKey() => ['role' => 'owner'],
            $member->getKey() => ['role' => 'member'],
        ]);

        $workspaceB->users()->attach($owner->getKey(), ['role' => 'owner']);

        $owner->forceFill(['current_workspace_id' => $workspaceA->getKey()])->save();
        $member->forceFill(['current_workspace_id' => $workspaceA->getKey()])->save();

        Post::query()->create([
            'workspace_id' => $workspaceA->getKey(),
            'title' => 'Welcome to Acme',
            'slug' => 'welcome-to-acme',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now()->subDay(),
            'excerpt' => 'First post in Acme workspace.',
            'content' => 'Full body content here.',
        ]);

        Post::query()->create([
            'workspace_id' => $workspaceA->getKey(),
            'title' => 'Draft announcement',
            'slug' => 'draft-announcement',
            'status' => Post::STATUS_DRAFT,
            'published_at' => null,
            'excerpt' => 'Not published yet.',
            'content' => null,
        ]);

        Post::query()->create([
            'workspace_id' => $workspaceB->getKey(),
            'title' => 'Growth only post',
            'slug' => 'growth-only',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
            'excerpt' => 'Visible only in Growth workspace.',
            'content' => 'Secret growth content.',
        ]);
    }
}
