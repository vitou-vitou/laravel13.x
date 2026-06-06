<?php

namespace Tests\Unit;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationTest extends TestCase
{
    use RefreshDatabase;

    public function test_category_name_is_translatable(): void
    {
        $category = Category::factory()->create([
            'name' => ['en' => 'Electronics', 'es' => 'Electrónica'],
        ]);

        $this->assertSame('Electronics', $category->getTranslation('name', 'en'));
        $this->assertSame('Electrónica', $category->getTranslation('name', 'es'));
    }

    public function test_product_name_and_description_are_translatable(): void
    {
        $product = Product::factory()->create([
            'name' => ['en' => 'Headphones', 'es' => 'Auriculares'],
            'description' => ['en' => 'Great sound', 'es' => 'Gran sonido'],
        ]);

        $this->assertSame('Headphones', $product->getTranslation('name', 'en'));
        $this->assertSame('Gran sonido', $product->getTranslation('description', 'es'));
    }
}
