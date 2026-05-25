#!/usr/bin/env php
<?php
/**
 * gen-filament.php
 *
 * Generates Filament v5 files from a PHP definition file.
 *
 * Usage:
 *   php gen-filament.php <app-dir> <definition.php>
 *
 * Example:
 *   php gen-filament.php D:\laravel13.x\examples\jira-v2 D:\laravel13.x\examples\definitions\jira.php
 *
 * Generates per model:
 *   - database/migrations/
 *   - app/Models/
 *   - app/Filament/Resources/{Name}Resource.php
 *   - app/Filament/Resources/{Name}Resource/Pages/List|Create|Edit|View.php
 *   - app/Filament/Resources/{Name}Resource/RelationManagers/ (if has_many)
 *   - app/Filament/Widgets/StatsOverviewWidget.php
 *   - database/seeders/{App}Seeder.php (stub)
 */

if ($argc < 3) {
    fwrite(STDERR, "Usage: php gen-filament.php <app-dir> <definition.php>\n");
    exit(1);
}

$appDir  = rtrim($argv[1], '/\\');
$defFile = $argv[2];

if (!is_dir($appDir))       { fwrite(STDERR, "App dir not found: $appDir\n"); exit(1); }
if (!file_exists($defFile)) { fwrite(STDERR, "Definition not found: $defFile\n"); exit(1); }

$def     = require $defFile;
$models  = $def['models'] ?? [];
$stats   = $def['stats']  ?? [];
$appName = $def['app']['name'] ?? 'App';

echo "\n  Generating Filament files for: $appName\n";
echo "  Target: $appDir\n\n";

// ── string helpers ────────────────────────────────────────────────────────────

function studly(string $s): string {
    return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $s)));
}
function camel(string $s): string { return lcfirst(studly($s)); }
function snake_case(string $s): string {
    return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $s));
}
function plural(string $s): string {
    $lower = strtolower($s);
    if (preg_match('/(s|x|z|ch|sh)$/', $lower)) return $lower . 'es';
    if (preg_match('/[^aeiou]y$/i', $lower))     return substr($lower, 0, -1) . 'ies';
    return $lower . 's';
}
function write_file(string $path, string $content): void {
    $dir = dirname($path);
    if (!is_dir($dir)) mkdir($dir, 0755, true);
    file_put_contents($path, $content);
    echo "  + $path\n";
}
function ind(int $n): string { return str_repeat('    ', $n); }

// ── field → migration column ─────────────────────────────────────────────────

function migration_col(array $f): string {
    $n   = $f['name'];
    $nil = ($f['nullable'] ?? false) ? '->nullable()' : '';
    $def = isset($f['default']) ? '->default(' . var_export($f['default'], true) . ')' : '';
    $len = isset($f['length'])  ? ", {$f['length']}" : '';

    return match ($f['type']) {
        'string'     => "\$table->string('$n'$len)$nil$def;",
        'text'       => "\$table->text('$n')$nil;",
        'longtext'   => "\$table->longText('$n')$nil;",
        'integer'    => "\$table->integer('$n')$nil$def;",
        'decimal'    => "\$table->decimal('$n', 10, 2)$nil$def;",
        'boolean'    => "\$table->boolean('$n')$nil$def;",
        'date'       => "\$table->date('$n')$nil;",
        'datetime'   => "\$table->dateTime('$n')$nil;",
        'enum'       => (function () use ($f, $nil, $def) {
            $opts = "'" . implode("', '", $f['options']) . "'";
            return "\$table->enum('{$f['name']}', [$opts])$nil$def;";
        })(),
        'belongs_to' => "\$table->foreignId('{$n}_id'){$nil}->constrained()->cascadeOnDelete();",
        default      => "\$table->string('$n')$nil$def;",
    };
}

// ── field → Filament form component ──────────────────────────────────────────

function form_field(array $f, array $models): string {
    $n   = $f['name'];
    $req = ($f['nullable'] ?? false) ? '' : '->required()';

    return match ($f['type']) {
        'string'          => "TextInput::make('$n'){$req}->maxLength(255),",
        'text', 'longtext'=> "Textarea::make('$n'){$req}->rows(4)->columnSpanFull(),",
        'integer'         => "TextInput::make('$n')->numeric(){$req},",
        'decimal'         => "TextInput::make('$n')->numeric()->step('0.01'){$req},",
        'boolean'         => "Toggle::make('$n'),",
        'date'            => "DatePicker::make('$n'){$req},",
        'datetime'        => "DateTimePicker::make('$n'){$req},",
        'enum'            => (function () use ($f, $req) {
            $pairs = array_map(
                fn($o) => ind(5) . "'$o' => '" . ucfirst(str_replace('_', ' ', $o)) . "'",
                $f['options']
            );
            $opts = implode(",\n", $pairs);
            return "Select::make('{$f['name']}')->options([\n$opts,\n" . ind(4) . "]){$req},";
        })(),
        'belongs_to'      => (function () use ($f, $models, $req) {
            $rel    = camel(studly($f['name']));
            $title  = 'name';
            foreach ($models as $m) {
                if (strtolower($m['name']) === strtolower($f['name'])) {
                    $title = $m['fields'][0]['name'] ?? 'name';
                    break;
                }
            }
            return "Select::make('{$f['name']}_id')->relationship('$rel', '$title'){$req}->searchable()->preload(),";
        })(),
        default           => "TextInput::make('$n'){$req},",
    };
}

// ── field → Filament table column ────────────────────────────────────────────

function table_col(array $f): string {
    $n = $f['name'];
    return match ($f['type']) {
        'string'          => "TextColumn::make('$n')->searchable()->sortable(),",
        'text', 'longtext'=> "TextColumn::make('$n')->limit(60)->wrap(),",
        'integer'         => "TextColumn::make('$n')->sortable(),",
        'decimal'         => "TextColumn::make('$n')->money()->sortable(),",
        'boolean'         => "IconColumn::make('$n')->boolean(),",
        'date'            => "TextColumn::make('$n')->date('M d, Y')->sortable(),",
        'datetime'        => "TextColumn::make('$n')->dateTime('M d H:i')->sortable(),",
        'enum'            => "TextColumn::make('$n')->badge()->sortable(),",
        'belongs_to'      => "TextColumn::make('$n.name')->label('" . ucfirst($n) . "')->sortable(),",
        default           => "TextColumn::make('$n')->searchable(),",
    };
}

// ── field → Filament table filter ────────────────────────────────────────────

function table_filter(array $f): ?string {
    if ($f['type'] === 'enum') {
        $pairs = array_map(
            fn($o) => ind(5) . "'$o' => '" . ucfirst(str_replace('_', ' ', $o)) . "'",
            $f['options']
        );
        $opts = implode(",\n", $pairs);
        return "SelectFilter::make('{$f['name']}')->options([\n$opts,\n" . ind(4) . "]),";
    }
    if ($f['type'] === 'belongs_to') {
        $rel = camel(studly($f['name']));
        return "SelectFilter::make('{$f['name']}')->relationship('$rel', 'name')->searchable()->preload(),";
    }
    return null;
}

// ── generators ────────────────────────────────────────────────────────────────

function gen_migration(string $appDir, array $model, int $idx): void {
    $table   = plural(snake_case($model['name']));
    $date    = date('Y_m_d') . '_' . str_pad($idx, 6, '0', STR_PAD_LEFT);
    $class   = 'Create' . studly($table) . 'Table';
    $fields  = $model['fields'] ?? [];

    $cols = [];
    foreach ($fields as $f) {
        $cols[] = ind(3) . migration_col($f);
    }
    $colsStr = implode("\n", $cols);

    $content = <<<PHP
<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('$table', function (Blueprint \$table) {
            \$table->id();
$colsStr
            \$table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('$table');
    }
};
PHP;

    write_file("$appDir/database/migrations/{$date}_create_{$table}_table.php", $content);
}

function gen_model(string $appDir, array $model, array $allModels): void {
    $name    = studly($model['name']);
    $table   = plural(snake_case($model['name']));
    $fields  = $model['fields'] ?? [];

    // fillable: all field names (belongs_to → _id)
    $fillable = array_map(function ($f) {
        return $f['type'] === 'belongs_to' ? "{$f['name']}_id" : $f['name'];
    }, $fields);
    $fillableStr = implode(",\n", array_map(fn($f) => ind(2) . "'$f'", $fillable));

    // casts
    $casts = [];
    foreach ($fields as $f) {
        if ($f['type'] === 'date')     $casts[] = ind(2) . "'{$f['name']}' => 'date'";
        if ($f['type'] === 'datetime') $casts[] = ind(2) . "'{$f['name']}' => 'datetime'";
        if ($f['type'] === 'boolean')  $casts[] = ind(2) . "'{$f['name']}' => 'boolean'";
        if ($f['type'] === 'decimal')  $casts[] = ind(2) . "'{$f['name']}' => 'decimal:2'";
    }
    $castsStr = $casts ? "\n    protected \$casts = [\n" . implode(",\n", $casts) . ",\n    ];\n" : '';

    // relations: belongs_to
    $relations = [];
    foreach ($fields as $f) {
        if ($f['type'] === 'belongs_to') {
            $rel     = camel(studly($f['name']));
            $related = studly($f['name']);
            $relations[] = <<<PHP

    public function $rel(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return \$this->belongsTo($related::class);
    }
PHP;
        }
    }

    // has_many
    foreach (($model['has_many'] ?? []) as $child) {
        $rel     = camel(plural($child));
        $related = studly($child);
        $relations[] = <<<PHP

    public function $rel(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return \$this->hasMany($related::class);
    }
PHP;
    }

    $relStr = implode("\n", $relations);

    $content = <<<PHP
<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class $name extends Model
{
    protected \$table = '$table';

    protected \$fillable = [
$fillableStr,
    ];
$castsStr$relStr
}
PHP;

    write_file("$appDir/app/Models/{$name}.php", $content);
}

function gen_resource(string $appDir, array $model, array $allModels): void {
    $name      = studly($model['name']);
    $plural    = studly(plural($model['name']));
    $fields    = $model['fields'] ?? [];
    $nav_icon  = $model['nav_icon'] ?? 'heroicon-o-rectangle-stack';
    $nav_group = $model['nav_group'] ?? null;
    $nav_group_str = $nav_group ? "\n    protected static string|\UnitEnum|null \$navigationGroup = '$nav_group';\n" : '';

    // form fields
    $formFields = [];
    foreach ($fields as $f) {
        $formFields[] = ind(3) . form_field($f, $allModels);
    }
    $formStr = implode("\n", $formFields);

    // table columns — put belongs_to cols first, then string, then rest
    $tableCols = [];
    foreach ($fields as $f) {
        $tableCols[] = ind(3) . table_col($f);
    }
    // always add timestamps
    $tableCols[] = ind(3) . "TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),";
    $tableStr = implode("\n", $tableCols);

    // filters
    $filters = [];
    foreach ($fields as $f) {
        $flt = table_filter($f);
        if ($flt) $filters[] = ind(3) . $flt;
    }
    $filtersStr = $filters ? implode("\n", $filters) : ind(3) . '//';

    // relation managers
    $relManagers = [];
    foreach (($model['has_many'] ?? []) as $child) {
        $relManagers[] = ind(3) . studly($child) . 'RelationManager::class,';
    }
    $relStr = $relManagers ? implode("\n", $relManagers) : ind(3) . '//';

    $content = <<<PHP
<?php
namespace App\Filament\Resources;

use App\Filament\Resources\\{$name}Resource\Pages;
use App\Filament\Resources\\{$name}Resource\RelationManagers;
use App\Models\\$name;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Forms\Components\{DatePicker, DateTimePicker, Select, Textarea, TextInput, Toggle};
use Filament\Tables\Columns\{IconColumn, TextColumn};
use Filament\Tables\Filters\SelectFilter;

class {$name}Resource extends Resource
{
    protected static ?string \$model = $name::class;

    protected static string|\BackedEnum|null \$navigationIcon = '$nav_icon';
$nav_group_str
    public static function form(Schema \$schema): Schema
    {
        return \$schema->components([
$formStr
        ])->columns(2);
    }

    public static function table(Table \$table): Table
    {
        return \$table
            ->columns([
$tableStr
            ])
            ->filters([
$filtersStr
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
$relStr
        ];
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\List{$plural}::route('/'),
            'create' => Pages\Create{$name}::route('/create'),
            'view'   => Pages\View{$name}::route('/{record}'),
            'edit'   => Pages\Edit{$name}::route('/{record}/edit'),
        ];
    }
}
PHP;

    write_file("$appDir/app/Filament/Resources/{$name}Resource.php", $content);
}

function gen_pages(string $appDir, array $model): void {
    $name   = studly($model['name']);
    $plural = studly(plural($model['name']));
    $base   = "App\\Filament\\Resources\\{$name}Resource";

    $pages = [
        "List{$plural}" => ['ListRecords',   "List{$plural}"],
        "Create{$name}" => ['CreateRecord',  "Create{$name}"],
        "View{$name}"   => ['ViewRecord',    "View{$name}"],
        "Edit{$name}"   => ['EditRecord',    "Edit{$name}"],
    ];

    foreach ($pages as $class => [$parent, $_]) {
        $content = <<<PHP
<?php
namespace App\Filament\Resources\\{$name}Resource\Pages;

use $base;
use Filament\Resources\Pages\\$parent;

class $class extends $parent
{
    protected static string \$resource = {$name}Resource::class;
}
PHP;
        write_file("$appDir/app/Filament/Resources/{$name}Resource/Pages/{$class}.php", $content);
    }
}

function gen_relation_manager(string $appDir, string $parentName, string $childName): void {
    $parent     = studly($parentName);
    $child      = studly($childName);
    $childPlural = studly(plural($childName));
    $rel        = camel(plural($childName));

    $content = <<<PHP
<?php
namespace App\Filament\Resources\\{$parent}Resource\RelationManagers;

use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class {$child}RelationManager extends RelationManager
{
    protected static string \$relationship = '$rel';

    public function form(Schema \$schema): Schema
    {
        return \$schema->components([
            \Filament\Forms\Components\TextInput::make('name')->required(),
        ]);
    }

    public function table(Table \$table): Table
    {
        return \$table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
            ])
            ->actions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
PHP;

    write_file("$appDir/app/Filament/Resources/{$parent}Resource/RelationManagers/{$child}RelationManager.php", $content);
}

function gen_stats_widget(string $appDir, array $stats, array $models): void {
    if (empty($stats)) return;

    // Build use statements for models
    $uses = array_unique(array_map(fn($m) => 'use App\\Models\\' . studly($m['name']) . ';', $models));
    $usesStr = implode("\n", $uses);

    $statsItems = [];
    foreach ($stats as $s) {
        $label = $s['label'];
        $query = $s['query'];
        $icon  = $s['icon']  ?? 'heroicon-o-chart-bar';
        $color = $s['color'] ?? 'primary';
        $statsItems[] = ind(3) . "Stat::make('$label', $query)\n" .
                        ind(4) . "->icon('$icon')\n" .
                        ind(4) . "->color('$color'),";
    }
    $statsStr = implode("\n", $statsItems);

    $content = <<<PHP
<?php
namespace App\Filament\Widgets;

$usesStr
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected function getStats(): array
    {
        return [
$statsStr
        ];
    }
}
PHP;

    write_file("$appDir/app/Filament/Widgets/StatsOverviewWidget.php", $content);
}

function gen_seeder(string $appDir, array $def): void {
    $appName = $def['app']['name'] ?? 'App';
    $class   = studly(str_replace(' ', '', $appName)) . 'Seeder';
    $models  = $def['models'] ?? [];

    $stubs = [];
    foreach ($models as $m) {
        $name  = studly($m['name']);
        $table = plural(snake_case($m['name']));
        $stubs[] = <<<PHP
        // TODO: seed $name
        // DB::table('$table')->insert([...]);
PHP;
    }
    $stubsStr = implode("\n\n", $stubs);

    $content = <<<PHP
<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class $class extends Seeder
{
    public function run(): void
    {
$stubsStr
    }
}
PHP;

    write_file("$appDir/database/seeders/{$class}.php", $content);
    echo "\n  NOTE: edit {$class}.php to add real seed data.\n";
}

// ── main ──────────────────────────────────────────────────────────────────────

foreach ($models as $idx => $model) {
    $name = studly($model['name']);
    echo "\n  Model: $name\n";

    gen_migration($appDir, $model, $idx + 1);
    gen_model($appDir, $model, $models);
    gen_resource($appDir, $model, $models);
    gen_pages($appDir, $model);

    foreach (($model['has_many'] ?? []) as $child) {
        gen_relation_manager($appDir, $model['name'], $child);
    }
}

gen_stats_widget($appDir, $stats, $models);
gen_seeder($appDir, $def);

// ── server.php (static-file router for PHP built-in server) ──────────────────
$serverPhp = <<<'PHP'
<?php
// PHP built-in server router — serves Filament/Livewire static assets correctly.
// Start from project root: php -S 127.0.0.1:PORT -t public server.php
// OR from public/: php -S 127.0.0.1:PORT ../server.php
$uri  = urldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? '');
$file = getcwd() . $uri;
if ($uri !== '/' && file_exists($file) && !is_dir($file)) {
    return false; // serve static file directly (correct MIME, no Laravel overhead)
}
require getcwd() . '/index.php';
PHP;

$serverPhpPath = "$appDir/server.php";
if (!file_exists($serverPhpPath)) {
    file_put_contents($serverPhpPath, $serverPhp);
    echo "  Created: server.php\n";
}

echo "\n  Done. Next steps:\n";
echo "    1. Edit database/seeders/*Seeder.php — add real data\n";
echo "    2. php artisan filament:assets           (publish Filament JS/CSS)\n";
echo "    3. php artisan vendor:publish --tag=livewire:assets\n";
echo "    4. php artisan migrate:fresh --seed\n";
echo "    5. php artisan make:filament-user        (create admin user)\n";
echo "    6. npm run build\n";
echo "    7. Start server from public/ dir:\n";
echo "         cd public && php -S 127.0.0.1:PORT ../server.php\n";
echo "    8. Visit http://127.0.0.1:PORT/admin\n\n";
