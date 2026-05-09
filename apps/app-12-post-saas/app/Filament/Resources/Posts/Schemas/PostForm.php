<?php

namespace App\Filament\Resources\Posts\Schemas;

use App\Models\Post;
use App\Support\WorkspaceContext;
use Filament\Forms\Components\DateTimePicker;
use Illuminate\Validation\Rules\Unique;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PostForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required()
                    ->maxLength(255)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($state, callable $set, $get): void {
                        if (filled($get('slug'))) {
                            return;
                        }
                        $set('slug', Str::slug((string) $state));
                    }),
                TextInput::make('slug')
                    ->required()
                    ->maxLength(255)
                    ->alphaDash()
                    ->unique(
                        ignoreRecord: true,
                        modifyRuleUsing: function (Unique $rule) {
                            $ws = WorkspaceContext::id();

                            return $ws ? $rule->where('workspace_id', $ws) : $rule;
                        },
                    ),
                Select::make('status')
                    ->options(Post::statusOptions())
                    ->default(Post::STATUS_DRAFT)
                    ->required(),
                DateTimePicker::make('published_at')
                    ->seconds(false),
                Textarea::make('excerpt')
                    ->rows(3)
                    ->columnSpanFull(),
                Textarea::make('content')
                    ->rows(10)
                    ->columnSpanFull(),
            ]);
    }
}
