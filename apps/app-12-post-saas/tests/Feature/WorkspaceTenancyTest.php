<?php

namespace Tests\Feature;

use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;
use App\Support\WorkspaceContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTenancyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_stores_and_resolves_active_workspace_for_a_user(): void
    {
        $user = User::factory()->create();
        $workspaceA = Workspace::query()->create(['name' => 'Workspace A']);
        $workspaceB = Workspace::query()->create(['name' => 'Workspace B']);

        $user->workspaces()->attach([
            $workspaceA->id => ['role' => 'member'],
            $workspaceB->id => ['role' => 'owner'],
        ]);

        $this->assertSame($workspaceA->id, WorkspaceContext::id($user));

        WorkspaceContext::setForUser($user, $workspaceB);
        $user->refresh();

        $this->assertSame($workspaceB->id, $user->current_workspace_id);
        $this->assertSame($workspaceB->id, WorkspaceContext::id($user));
    }

    public function test_it_scopes_posts_by_workspace(): void
    {
        $workspaceA = Workspace::query()->create(['name' => 'Workspace A']);
        $workspaceB = Workspace::query()->create(['name' => 'Workspace B']);

        Post::query()->create([
            'workspace_id' => $workspaceA->id,
            'title' => 'Post A',
            'slug' => 'post-a',
            'status' => Post::STATUS_DRAFT,
        ]);

        Post::query()->create([
            'workspace_id' => $workspaceB->id,
            'title' => 'Post B',
            'slug' => 'post-b',
            'status' => Post::STATUS_DRAFT,
        ]);

        $this->assertSame(1, Post::query()->forWorkspace($workspaceA->id)->count());
        $this->assertSame(1, Post::query()->forWorkspace($workspaceB->id)->count());
        $this->assertSame(0, Post::query()->forWorkspace(null)->count());
    }
}
