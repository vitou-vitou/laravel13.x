# Database Connection Pooling in Laravel: Prevent "Too Many Connections" Under Peak Load

Your application launches a marketing campaign and traffic surges by five times. Within three minutes, instead of handling orders smoothly, every page on your site crashes with: `SQLSTATE[08004] [1040] Too many connections`. You check your database CPU and memory, and both are under thirty percent. The database server was not overwhelmed by query workload; it simply ran out of available socket slots for PHP connection handles.

In standard PHP-FPM architectures, every worker process opens an independent TCP connection to your database. When you scale your web containers to handle traffic spikes or spin up dozens of queue workers, you can easily exhaust database connection limits. By implementing connection poolers like AWS RDS Proxy or PgBouncer and applying connection hygiene in Laravel, you can support thousands of concurrent requests without dropping database connections.

## Why PHP Exhausts Database Connections

Unlike long-running Node.js or Go applications that maintain a single internal connection pool shared across async threads, PHP-FPM relies on a multi-process model:

- 100 PHP-FPM workers = up to 100 concurrent database connections.
- 50 Queue worker processes = 50 persistent database connections.
- Auto-scaling to 4 web servers = 450+ concurrent connections.

Smaller cloud database instances (like an AWS db.t4g.small or DigitalOcean basic database) default to between 100 and 150 `max_connections`. As soon as your total active workers exceed that cap, every new incoming request throws a fatal connection error.

Increasing `max_connections` in your database configuration is rarely the answer. Every open connection in MySQL or PostgreSQL allocates memory for connection buffers, query caches, and sort buffers. Setting `max_connections = 2000` on a small server can cause an Out-Of-Memory (OOM) kernel crash under load.

## The Solution: External Connection Poolers

The most reliable architectural solution is a connection pooling proxy sitting between your application and your database:

- **For PostgreSQL:** PgBouncer
- **For MySQL:** ProxySQL or AWS RDS Proxy

A connection pooler maintains a small, warm pool of physical connections to the database (for example, 30 connections). When hundreds of PHP processes connect to the pooler, the pooler multiplexes transactions across those 30 connections. Once a request finishes its query, the connection is instantly recycled for another process.

Your Laravel configuration does not change significantly; you simply update the database host and port to point to the proxy:

.env:
```ini
# Connect through the connection pooling proxy
DB_HOST=127.0.0.1
DB_PORT=6432
DB_DATABASE=app_production
DB_USERNAME=app_user
DB_PASSWORD=secret
```

With PgBouncer or RDS Proxy in front of your database, 1,000 PHP processes can comfortably share 50 underlying database connections without connection rejection.

## Laravel-Side Connection Hygiene

Even without a dedicated proxy, applying disciplined connection practices inside your Laravel codebase dramatically reduces connection pressure.

### 1. Close Connections in Long-Running Queue Workers

If a queued job performs heavy data transformation, image manipulation, or waits on external APIs, tell Laravel to disconnect from the database while idle:

app/Jobs/ProcessVideoExportJob.php:
```php
namespace App\Jobs;

use App\Models\Video;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class ProcessVideoExportJob implements ShouldQueue
{
    use Queueable;

    public function __construct(public Video $video) {}

    public function handle(): void
    {
        // 1. Fetch required data
        $filePath = $this->video->source_file;

        // 2. Disconnect while performing a slow 60-second video encoding task
        DB::disconnect();

        $encodedOutput = (new VideoEncoder())->encode($filePath);

        // 3. Reconnect automatically when saving results
        $this->video->update([
            'encoded_path' => $encodedOutput,
            'status' => 'completed',
        ]);
    }
}
```

Calling `DB::disconnect()` releases the socket back to the database. When you call `$this->video->update()`, Laravel's database manager automatically re-establishes the connection.

### 2. Configure PDO Options and Timeouts

Ensure your database configuration sets reasonable connection and query timeouts so stuck queries do not hold connections indefinitely:

config/database.php:
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    // ...
    'options' => [
        PDO::ATTR_TIMEOUT => 3, // Timeout connection attempts after 3 seconds
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    ],
],
```

## What Can Go Wrong

When using PgBouncer in **Transaction Pooling** mode, certain session-level features behave unpredictably:

1. **Prepared Statements:** Older versions of PostgreSQL cannot share named prepared statements across different sessions. In Laravel, set `'modes' => ['prefer-null-on-empty' => true]` and disable prepared statement emulation if required.
2. **Session Variables:** Running `SET search_path` or setting session-level SQL flags will leak across requests if connections are returned to the pool without being reset.

Always test your application against the specific pooling mode (session, transaction, or statement) in a staging environment before routing production traffic.

## Summary

"Too many connections" errors are caused by architecture mismatch, not slow hardware. Stop scaling database RAM just to increase socket limits.

Deploy a connection pooler like RDS Proxy or PgBouncer to multiplex client connections. Apply `DB::disconnect()` during long non-database queue tasks, configure strict PDO timeouts, and audit your total worker concurrency.

Your database will remain responsive under heavy traffic spikes while keeping your infrastructure costs low.

## Further Reading

- [Laravel Database Configuration](https://laravel.com/docs/database#configuration)
- [AWS RDS Proxy Overview](https://aws.amazon.com/rds/proxy/)
- [PgBouncer Official Documentation](https://www.pgbouncer.org/)
- [High Performance MySQL: Connection Management](https://www.oreilly.com/library/view/high-performance-mysql/9781492080503/)

Have you configured RDS Proxy or PgBouncer with Laravel? Share your deployment architecture and findings in the comments below.
