<?php

namespace Tests\Feature;

use App\Filament\Resources\Posts\PostResource;
use App\Models\Post;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PostDatatableScopeTest extends TestCase
{
    use RefreshDatabase;

    public function test_post_resource_query_only_includes_active_workspace(): void
    {
        $user = User::factory()->create();
        $workspaceA = Workspace::query()->create(['name' => 'A']);
        $workspaceB = Workspace::query()->create(['name' => 'B']);
        $user->workspaces()->attach([
            $workspaceA->id => ['role' => 'owner'],
            $workspaceB->id => ['role' => 'member'],
        ]);
        $user->forceFill(['current_workspace_id' => $workspaceA->id])->save();

        Post::query()->create([
            'workspace_id' => $workspaceA->id,
            'title' => 'In A',
            'slug' => 'in-a',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        Post::query()->create([
            'workspace_id' => $workspaceB->id,
            'title' => 'In B',
            'slug' => 'in-b',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);

        $this->actingAs($user);

        $ids = PostResource::getEloquentQuery()->pluck('id')->all();
        $this->assertCount(1, $ids);
        $this->assertSame('In A', Post::query()->find($ids[0])?->title);
    }

    public function test_status_filter_on_list_page_reduces_rows(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::query()->create(['name' => 'Solo']);
        $user->workspaces()->attach($workspace->id, ['role' => 'owner']);
        $user->forceFill(['current_workspace_id' => $workspace->id])->save();

        Post::query()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Published',
            'slug' => 'pub',
            'status' => Post::STATUS_PUBLISHED,
            'published_at' => now(),
        ]);
        Post::query()->create([
            'workspace_id' => $workspace->id,
            'title' => 'Draft',
            'slug' => 'drf',
            'status' => Post::STATUS_DRAFT,
        ]);

        $this->actingAs($user);

        Livewire::test(\App\Filament\Resources\Posts\Pages\ListPosts::class)
            ->assertCanSeeTableRecords(Post::query()->where('workspace_id', $workspace->id)->get())
            ->filterTable('status', Post::STATUS_PUBLISHED)
            ->assertCanSeeTableRecords(Post::query()->where('slug', 'pub')->get())
            ->assertCanNotSeeTableRecords(Post::query()->where('slug', 'drf')->get());
    }
}
