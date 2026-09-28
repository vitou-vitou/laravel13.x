# Build an Immutable Audit Trail in Laravel: Track Every Sensitive Administrative Write

An administrative user modifies a customer's refund balance from $50 to $50,000, exports the funds, and alters the record back to $50 before the end of the day. A month later, during a financial reconciliation review, leadership discovers the discrepancy. You inspect your standard `updated_at` timestamps on the database row, but they only show the most recent change. Because your application lacked an immutable audit trail, you have zero evidence of who initiated the unauthorized change, what the balance was prior to the edit, or when it occurred.

Standard database tables record the *current* state of the world, not the *history* of how that state came to be. For sensitive business operations—including financial changes, permission updates, and customer deletions—compliance frameworks (such as SOC2, HIPAA, and GDPR) mandate an append-only, tamper-evident audit record. If you record model lifecycle changes inside database transactions using append-only audit tables and block update and delete operations at the database layer, you guarantee an indisputable audit trail.

## The Problem with In-Place Column Updates

When an operator updates a record in a standard CRUD application:

```php
$customer->update([
    'credit_limit' => 50000,
]);
```

The database executes an in-place overwrite:

```sql
UPDATE `customers` SET `credit_limit` = 50000, `updated_at` = NOW() WHERE `id` = 42;
```

The previous credit limit is permanently overwritten and lost to history. If an employee with administrative database privileges or compromised credentials alters data, standard database logging cannot prove who made the modification or what the values were before the update.

## Design an Append-Only Audit Schema

An audit trail must be structurally append-only: rows are inserted, but existing rows can never be updated or deleted.

Create an explicit `audit_logs` table:

database/migrations/2024_07_01_000001_create_audit_logs_table.php:
```php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();

            // Who performed the action
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('user_email')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();

            // What action took place
            $table->string('action'); // created, updated, deleted, viewed_sensitive_pii

            // Which entity was affected (Polymorphic)
            $table->morphs('auditable');

            // The state delta: before and after
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();

            // Immutable timestamp: no updated_at column!
            $table->timestamp('created_at')->useCurrent();

            // Indexes for fast compliance querying
            $table->index(['auditable_type', 'auditable_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_logs');
    }
};
```

Notice the schema design:
- There is **no `updated_at` column**. Audit logs represent historical facts that can never change.
- We snapshot `user_email` alongside `user_id`. Even if the user account is later removed, the historical email of the operator is permanently preserved in the log.
- `old_values` and `new_values` capture the exact JSON diff of modified attributes.

## Automatically Capture Changes via Eloquent Trait

Build a reusable model trait that hooks into Eloquent's `saved` and `deleted` lifecycle events:

app/Traits/Auditable.php:
```php
namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Request;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            $model->recordAudit('created', [], $model->getAuditableAttributes());
        });

        static::updating(function (Model $model) {
            $dirtyFields = array_keys($model->getDirty());

            // Ignore timestamp updates if no real attributes changed
            if (count($dirtyFields) === 1 && $dirtyFields[0] === 'updated_at') {
                return;
            }

            $oldValues = array_intersect_key($model->getOriginal(), $model->getDirty());
            $newValues = $model->getDirty();

            $model->recordAudit('updated', $oldValues, $newValues);
        });

        static::deleted(function (Model $model) {
            $model->recordAudit('deleted', $model->getAuditableAttributes(), []);
        });
    }

    protected function recordAudit(string $action, array $oldValues, array $newValues): void
    {
        $user = Auth::user();

        AuditLog::create([
            'user_id' => $user?->id,
            'user_email' => $user?->email ?? 'system',
            'ip_address' => Request::ip(),
            'user_agent' => Request::userAgent(),
            'action' => $action,
            'auditable_type' => static::class,
            'auditable_id' => $this->getKey(),
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'created_at' => now(),
        ]);
    }

    protected function getAuditableAttributes(): array
    {
        // Exclude passwords and sensitive tokens from audit logs
        return collect($this->attributesToArray())
            ->except(['password', 'remember_token', 'two_factor_secret'])
            ->toArray();
    }
}
```

Attach `use Auditable;` to your core financial and administrative models:

```php
class CustomerAccount extends Model
{
    use Auditable;
}
```

Every insert, update, and delete executes an accompanying `audit_logs` insert within the same database transaction.

## Enforce Immutability at the Model Layer

Prevent developers from accidentally updating or deleting audit logs in code:

app/Models/AuditLog.php:
```php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

class AuditLog extends Model
{
    public $timestamps = false;
    protected $guarded = [];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'created_at' => 'datetime',
    ];

    public static function booted(): void
    {
        // Strictly prohibit updating or deleting existing audit logs
        static::updating(function () {
            throw new RuntimeException('Audit log entries are immutable and cannot be updated.');
        });

        static::deleting(function () {
            throw new RuntimeException('Audit log entries cannot be deleted.');
        });
    }
}
```

If any code calls `$log->update()` or `$log->delete()`, Laravel immediately throws a runtime exception and aborts.

## What Can Go Wrong

A major operational problem with audit trails is high-volume tables generating millions of log rows, inflating your primary database storage.

Do not log high-frequency status changes (like a GPS tracking coordinate updating every five seconds or a session timestamp) to your primary audit table.

Filter auditable attributes strictly using `getAuditableAttributes()` and archive audit rows older than your compliance retention window (e.g. 7 years) to cheap, immutable cloud storage like AWS S3 Glacier with Object Lock enabled.

## Summary

In modern enterprise applications, being able to prove who made a change is just as important as the change itself.

Never rely solely on `updated_at` timestamps for critical business entities. Create an append-only `audit_logs` schema, capture field deltas automatically via an Eloquent `Auditable` trait, strip sensitive credentials from diffs, and enforce immutability at the model layer.

Your application establishes a trustworthy, tamper-evident history that satisfies compliance auditors and protects your team during financial reconciliations.

## Further Reading

- [SOC 2 Compliance: Audit Logging Requirements](https://www.iso.org/standard/70970.html)
- [Laravel Eloquent Model Events](https://laravel.com/docs/eloquent#events)
- [Owen-It Laravel Auditing Package](https://laravel-auditing.com/)
- [Amazon S3 Object Lock for Immutability](https://docs.aws.amazon.com/AmazonS3/latest/userguide/object-lock.html)

How does your team archive and retain historical audit logs for compliance audits? Share your storage strategies in the comments below.
