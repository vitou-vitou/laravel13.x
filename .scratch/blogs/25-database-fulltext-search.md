# Full-Text Search in MySQL and PostgreSQL: Fast Queries Without Elasticsearch

Your product team requests a search bar for your store or documentation portal. Someone on the team immediately suggests provisioning a managed Elasticsearch or Meilisearch cluster. Within a week, you are debugging queue workers that failed to sync deleted records, fixing index drift between your database and the search engine, and paying $250 a month for dedicated search nodes on a site with only 50,000 products.

External search engines add immense operational complexity. For datasets under one million records, MySQL and PostgreSQL provide powerful, native Full-Text Search capabilities right inside your relational database. If you use Laravel's native schema tools and `whereFullText()` methods, you can deliver relevant, multi-column search results with zero external infrastructure.

## The Flaw of LIKE Queries

When developers build search without a dedicated engine, they often reach for SQL `LIKE`:

```php
// Anti-pattern: leading wildcards bypass all standard B-tree indexes
$products = Product::query()
    ->where('title', 'like', "%{$search}%")
    ->orWhere('description', 'like', "%{$search}%")
    ->get();
```

Because of the leading wildcard (`%`), relational databases cannot use a standard B-tree index. The database engine must perform a full table scan, checking every single string byte-by-byte. On a table with 100,000 articles, this query takes several seconds and grinds your database server to a halt.

## Add Native Full-Text Indexes in Migrations

Laravel includes first-class support for full-text indexes in schema migrations:

database/migrations/2024_05_01_000001_add_fulltext_index_to_products_table.php:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Creates a native FULLTEXT index across both columns
            $table->fullText(['title', 'description'], 'products_fulltext_idx');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropFullText('products_fulltext_idx');
        });
    }
};
```

This migration creates an inverted index inside your database engine. MySQL and PostgreSQL break text into word tokens, strip common punctuation, and index terms directly, allowing search lookups in single-digit milliseconds.

## Query with Laravel's whereFullText Method

Laravel's query builder provides clean methods for querying full-text indexes:

app/Http/Controllers/ProductSearchController.php:
```php
namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductSearchController extends Controller
{
    public function index(Request $request): View
    {
        $searchTerm = $request->input('q', '');

        $products = Product::query()
            ->when($searchTerm, function ($query, $search) {
                // Utilizes the native full-text index
                $query->whereFullText(['title', 'description'], $search);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('products.search', compact('products', 'searchTerm'));
    }
}
```

The underlying SQL executes using optimized database syntax:

```sql
SELECT * FROM `products`
WHERE MATCH(`title`, `description`) AGAINST('mechanical keyboard' IN NATURAL LANGUAGE MODE)
ORDER BY `created_at` DESC
LIMIT 20;
```

Search terms match complete words and rank results by natural relevance, avoiding the performance penalties of unindexed substring searches.

## Laravel Scout's Built-In Database Engine

If you want the clean abstraction of Laravel Scout without managing a separate service, Scout includes a built-in `database` driver:

.env:
```ini
SCOUT_DRIVER=database
```

Add the `Searchable` trait to your Eloquent model:

app/Models/Product.php:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

class Product extends Model
{
    use Searchable;

    public function toSearchableArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
        ];
    }
}
```

Now you can search using standard Scout syntax:

```php
// Clean Scout search syntax using your relational database
$products = Product::search('mechanical keyboard')->paginate(20);
```

If your application eventually grows to tens of millions of records and truly requires Elasticsearch or Meilisearch in year five, you can swap the driver in `.env` without modifying a single search controller in your codebase.

## What Can Go Wrong

A frequent surprise in MySQL Full-Text Search is the **minimum word token length** (`innodb_ft_min_token_size`).

By default, MySQL InnoDB ignores search terms with fewer than 3 characters (e.g., search queries for "PHP", "Vue", or "Go" return zero results).

To support shorter tech acronyms in MySQL, configure your database parameters:

/etc/mysql/my.cnf:
```ini
[mysqld]
innodb_ft_min_token_size = 2
```

After changing this setting, restart MySQL and rebuild the index by dropping and recreating it.

Also, be aware of database **stopword lists**. Common English words like "about", "after", and "before" are ignored by default.

## Summary

Do not add external search infrastructure until your scale genuinely demands it. Avoid slow `LIKE '%query%'` scans on large tables.

Create native full-text indexes using Laravel migrations, query them with `whereFullText()`, or use Laravel Scout with the `database` driver.

You keep your tech stack simple, eliminate index synchronization headaches, and deliver fast, relevant search to your users with the database you already run.

## Further Reading

- [Laravel Query Builder: Full-Text Where Clauses](https://laravel.com/docs/queries#full-text-where-clauses)
- [Laravel Scout Database Engine](https://laravel.com/docs/scout#database-engine)
- [MySQL 8.0 Full-Text Search Functions](https://dev.mysql.com/doc/refman/8.0/en/fulltext-search.html)
- [PostgreSQL Full-Text Search Overview](https://www.postgresql.org/docs/current/textsearch.html)

Have you replaced an external search cluster with native database full-text search? Share your experience in the comments below.
