<?php

namespace Tests\Unit;

use App\Filament\Forms\TranslatableFields;
use PHPUnit\Framework\TestCase;

class TranslatableFieldsTest extends TestCase
{
    public function test_collapse_merges_dot_notation(): void
    {
        $result = TranslatableFields::collapse([
            'name.en' => 'English',
            'name.es' => 'Spanish',
            'slug' => 'demo',
        ], ['name']);

        $this->assertSame(['en' => 'English', 'es' => 'Spanish'], $result['name']);
        $this->assertSame('demo', $result['slug']);
    }

    public function test_collapse_preserves_nested_arrays(): void
    {
        $result = TranslatableFields::collapse([
            'name' => ['en' => 'English', 'es' => 'Spanish', 'fr' => 'Ignored'],
        ], ['name']);

        $this->assertSame(['en' => 'English', 'es' => 'Spanish'], $result['name']);
    }
}
