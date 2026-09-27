# Long-Running Queue Workers: Prevent Memory Leaks and Database Connection Timeouts

Your background queue workers run smoothly after deployment, but by Tuesday morning jobs start mysteriously disappearing without landing in `failed_jobs`. You inspect the Linux system logs and discover that the kernel's Out-Of-Memory (OOM) killer terminated your worker processes with `SIGKILL`. Alternatively, after an idle night with low traffic, the first morning job crashes with: `SQLSTATE[HY000]: General error: 2006 MySQL server has gone away`.

In standard web requests, PHP-FPM starts with a clean slate and terminates all memory upon request completion. In contrast, `php artisan queue:work` runs as a long-lived, continuous daemon process in memory for days. If you do not configure worker recycling thresholds and guard against static memory retention, your workers will leak RAM and drop idle database connections. With proper worker flags and process supervision, you can run rock-solid queue daemons that stay fast and leak-free.

## Why Long-Running PHP Daemons Leak Memory

PHP was historically architected around a share-nothing, ephemeral request lifecycle. When a worker process runs continuously for 48 hours, several standard Laravel patterns can accumulate memory:

1. **Static Property Caching:** Any class holding static arrays or registries keeps that data allocated in memory across thousands of jobs.
2. **Database Query Logging:** If query logging is enabled (intentionally or by an unconfigured debugging package), every single SQL query and its bound parameters are stored in an in-memory array.
3. **Event Listeners and Singletons:** Services resolved from the IoC container as singletons retain their internal state from the previous job.

Eventually, the worker's resident memory grows from 40 megabytes to 512 megabytes until the Linux kernel terminates the process without notice.

## The Three Flags That Prevent Memory Exhaustion

Never run `php artisan queue:work` without configuring lifecycle boundaries. Laravel provides three built-in flags designed to recycle worker processes gracefully:

```bash
php artisan queue:work redis \
    --max-time=3600 \
    --max-jobs=1000 \
    --memory=128
```

Here is what these flags enforce:

- **`--max-time=3600`:** Recycles the worker process after one hour of execution.
- **`--max-jobs=1000`:** Recycles the worker process after it completes 1,000 jobs.
- **`--memory=128`:** Instructs the worker to exit cleanly as soon as its memory footprint exceeds 128 megabytes.

When any of these limits is reached, the worker finishes its current job, exits with status 0, and allows your process supervisor (such as systemd or Supervisor) to start a fresh, clean worker in milliseconds.

## Supervisor Configuration for Automatic Restarts

Because workers exit intentionally when reaching their limits, configure a process manager like Supervisor to restart them instantly:

/etc/supervisor/conf.d/laravel-worker.conf:
```ini
[program:laravel-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/app/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600 --memory=128
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=www-data
numprocs=4
redirect_stderr=true
stdout_logfile=/var/www/app/storage/logs/worker.log
stopwaitsecs=3600
```

Notice `stopwaitsecs=3600`. This ensures that when you deploy new code or restart Supervisor, it waits for long-running jobs to complete gracefully instead of killing them mid-transaction.

## Fix "MySQL Server Has Gone Away" on Idle Workers

During slow overnight hours, a queue worker might sit completely idle with zero incoming jobs for several hours. By default, MySQL's `wait_timeout` closes idle client TCP connections after 8 hours (or sooner on managed cloud databases).

When a new job finally arrives, the worker attempts to query using the closed connection handle and crashes with error 2006.

To prevent dropped connections, ensure your MySQL configuration in `config/database.php` enables reconnect behavior or sets reasonable keep-alive timeouts:

config/database.php:
```php
'mysql' => [
    'driver' => 'mysql',
    'host' => env('DB_HOST', '127.0.0.1'),
    // ...
    'options' => [
        PDO::ATTR_PERSISTENT => false,
        PDO::ATTR_TIMEOUT => 5,
    ],
],
```

Because your workers recycle every hour via `--max-time=3600`, connections are renewed regularly before the database timeout can sever them.

## What Can Go Wrong

A common mistake made by developers trying to dodge memory leaks is switching to `php artisan queue:listen` or running `--max-jobs=1`.

While `--max-jobs=1` completely avoids memory leaks, it destroys queue throughput:

```bash
# Anti-pattern: forces Laravel to boot the entire framework from disk for every single job!
php artisan queue:work --max-jobs=1
```

Booting the full Laravel framework, resolving service providers, and establishing database connections takes between 50 and 150 milliseconds. Processing 10,000 jobs with `--max-jobs=1` wastes over 20 minutes solely on framework boot overhead.

Using `--max-jobs=1000` gives you maximum performance while ensuring that any small memory leak is purged before it impacts server stability.

## Summary

Long-running queue daemons require different operational discipline than ephemeral web requests.

Always configure `--max-time=3600`, `--max-jobs=1000`, and `--memory=128` on your worker commands. Run them under a reliable process supervisor like systemd or Supervisor, and never leave query logging enabled in production.

Your queue workers will run with flat, predictable memory usage and process millions of jobs without unexpected crashes.

## Further Reading

- [Laravel Queues: Supervisor Configuration](https://laravel.com/docs/queues#supervisor-configuration)
- [Managing Memory in Long-Running PHP CLI Scripts](https://www.php.net/manual/en/features.gc.php)
- [MySQL wait_timeout and Connection Management](https://dev.mysql.com/doc/refman/8.0/en/server-system-variables.html#sysvar_wait_timeout)
- [Supervisor Process Control System](http://supervisord.org/)

How do you supervise and monitor your queue worker processes in production? Share your configuration tips in the comments below.
