<?php

namespace Tests\Feature\Filament;

use App\Filament\Resources\Categories\CategoryResource;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\RelationManagers\ProductsRelationManager;
use App\Models\Category;
use App\Models\Product;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class CategoryResourceTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Filament::setCurrentPanel(Filament::getPanel('admin'));
    }

    public function test_guest_cannot_access_categories_index(): void
    {
        $this->get(CategoryResource::getUrl('index'))
            ->assertRedirect('/admin/login');
    }

    public function test_category_edit_shows_products_relation_manager(): void
    {
        $user = $this->adminUser();
        $category = Category::factory()->create();
        $product = Product::factory()->create([
            'category_id' => $category->id,
            'name' => ['en' => 'Nested Product', 'es' => 'Producto anidado'],
        ]);

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $category,
                'pageClass' => EditCategory::class,
            ])
            ->assertCanSeeTableRecords([$product]);
    }

    public function test_category_products_relation_manager_can_create_product(): void
    {
        $user = $this->adminUser();
        $category = Category::factory()->create();

        Livewire::actingAs($user)
            ->test(ProductsRelationManager::class, [
                'ownerRecord' => $category,
                'pageClass' => EditCategory::class,
            ])
            ->mountTableAction('create')
            ->fillForm([
                'product_name.en' => 'New Product',
                'product_name.es' => 'Producto nuevo',
                'product_description.en' => 'Description',
                'product_description.es' => 'Descripción',
                'slug' => 'new-product',
                'price_cents' => 1_999,
                'is_active' => true,
            ])
            ->callMountedTableAction()
            ->assertHasNoTableActionErrors();

        $this->assertDatabaseHas('products', [
            'category_id' => $category->id,
            'slug' => 'new-product',
            'price_cents' => 1_999,
        ]);
    }
}
