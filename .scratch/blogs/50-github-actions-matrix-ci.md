# Fast GitHub Actions for Laravel: Caching Composer, MySQL Services, and Parallel Tests

Your GitHub Actions CI pipeline takes twelve minutes to run on every pull request. Developers push a one-line typo fix, and context-switch to another task while waiting for CI to turn green. You inspect the runner timeline and discover that three minutes are spent downloading Composer packages from scratch, two minutes are spent setting up a Docker database service, and seven minutes are spent executing tests sequentially on a single virtual CPU core.

Slow CI pipelines destroy developer velocity and inflate your GitHub Actions billing minutes. A well-optimized Laravel CI workflow should complete in under ninety seconds. If you leverage GitHub Actions dependency caching, run MySQL inside native container services with tmpfs memory mounting, and split your test suite across parallel worker cores using Pest, you can turn a twelve-minute bottleneck into a lightning-fast feedback loop.

## The Three Bottlenecks in Standard Laravel CI

Standard unoptimized GitHub Actions workflows make three common mistakes:

1. **Cold Composer Installs:** Re-downloading 120 PHP packages from Packagist on every push instead of caching the `vendor/` directory.
2. **Sequential Test Execution:** Running 600 tests one-by-one on a single CPU thread when GitHub Actions runner VMs provide two or four virtual cores.
3. **Slow Database Disk I/O:** Initializing MySQL on virtual hard disks where table creation and index building hit physical disk write throttles.

## Step 1: Cache Composer and NPM Dependencies

Use GitHub's `actions/cache` or native action caching flags to cache `vendor/` and `node_modules/` based on your lockfile hashes:

```yaml
- name: Cache Composer dependencies
  uses: actions/cache@v4
  with:
    path: /tmp/composer-cache
    key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
    restore-keys: |
      ${{ runner.os }}-composer-
```

When `composer.lock` is unchanged, Composer reuses cached package archives, reducing `composer install` from 180 seconds to under 8 seconds.

## Step 2: Use Native Service Containers with tmpfs

Instead of installing MySQL via apt or booting complex multi-container Docker Compose stacks, declare MySQL as a native GitHub Actions service container.

Mount the database storage path to `tmpfs` (RAM disk) directly in the workflow YAML:

```yaml
services:
  mysql:
    image: mysql:8.0
    env:
      MYSQL_ALLOW_EMPTY_PASSWORD: 'yes'
      MYSQL_DATABASE: laravel_testing
    ports:
      - 3306:3306
    options: >-
      --tmpfs /var/lib/mysql:rw,noexec,nosuid,size=1024m
      --health-cmd="mysqladmin ping"
      --health-interval=10s
      --health-timeout=5s
      --health-retries=3
```

Because MySQL's `/var/lib/mysql` runs entirely in system memory, test database migrations and transaction rollbacks execute at native memory bus speeds.

## Step 3: Run Tests in Parallel with Pest

Standard GitHub Actions runners (e.g. `ubuntu-latest`) provide **2 vCPUs**. Larger enterprise runners provide 4 to 16 vCPUs.

Tell Pest to run tests in parallel across all available CPU cores:

```bash
./vendor/bin/pest --parallel --compact
```

Pest spins up isolated worker processes, assigns test files dynamically, and combines the results cleanly in the terminal output.

## The Complete Production GitHub Actions Workflow

Here is the complete, production-tuned `.github/workflows/ci.yml` that runs under 90 seconds:

.github/workflows/ci.yml:
```yaml
name: Continuous Integration

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  tests:
    runs-on: ubuntu-latest

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ALLOW_EMPTY_PASSWORD: 'yes'
          MYSQL_DATABASE: laravel_testing
        ports:
          - 3306:3306
        options: >-
          --tmpfs /var/lib/mysql:rw,noexec,nosuid,size=1024m
          --health-cmd="mysqladmin ping"
          --health-interval=5s
          --health-timeout=2s
          --health-retries=5

    steps:
      - name: Checkout Code
        uses: actions/checkout@v4

      - name: Setup PHP and Extensions
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          extensions: mbstring, pdo, pdo_mysql, pcntl, redis
          coverage: none

      - name: Get Composer Cache Directory
        id: composer-cache
        run: echo "dir=$(composer config cache-files-dir)" >> $GITHUB_OUTPUT

      - name: Cache Composer Dependencies
        uses: actions/cache@v4
        with:
          path: ${{ steps.composer-cache.outputs.dir }}
          key: ${{ runner.os }}-composer-${{ hashFiles('**/composer.lock') }}
          restore-keys: ${{ runner.os }}-composer-

      - name: Install Dependencies
        run: composer install --no-interaction --prefer-dist --optimize-autoloader --no-progress

      - name: Prepare Environment
        run: |
          cp .env.example .env.testing
          php artisan key:generate --env=testing

      - name: Run Schema Migrations
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: laravel_testing
          DB_USERNAME: root
          DB_PASSWORD: ''
        run: php artisan migrate --env=testing --force

      - name: Execute Tests in Parallel
        env:
          DB_CONNECTION: mysql
          DB_HOST: 127.0.0.1
          DB_PORT: 3306
          DB_DATABASE: laravel_testing
          DB_USERNAME: root
          DB_PASSWORD: ''
        run: ./vendor/bin/pest --parallel --compact
```

Notice `coverage: none` on `setup-php`. Enabling Xdebug or PCOV code coverage when you only need a pass/fail status can double test execution time. Only enable coverage on dedicated nightly audit workflows.

## What Can Go Wrong

A frequent failure with parallel testing in CI is database collision between parallel worker threads:

```
SQLSTATE[HY000]: General error: 1050 Table 'orders' already exists
```

When you run `pest --parallel`, Pest automatically provisions isolated databases for each parallel process (`laravel_testing_test_1`, `laravel_testing_test_2`).

Ensure your MySQL database user has permissions to create dynamic databases (`GRANT ALL PRIVILEGES ON *.* TO 'root'@'%'`), and use `php artisan test --parallel` or Pest's built-in parallel database handler so that each worker thread writes to an isolated database.

## Summary

Do not let slow CI pipelines drain developer productivity and exhaust your cloud budget.

Optimize your GitHub Actions workflow: cache Composer dependencies using lockfile hashes, run MySQL in a service container backed by `tmpfs` RAM storage, disable Xdebug in standard CI jobs, and execute tests across multiple cores with `pest --parallel`.

Your pull requests will turn green in under ninety seconds, giving developers rapid feedback and keeping deployments moving fast.

## Further Reading

- [GitHub Actions: Caching Dependencies](https://docs.github.com/en/actions/using-workflows/caching-dependencies-to-speed-up-workflows)
- [Pest PHP Parallel Testing Guide](https://pestphp.com/docs/parallel)
- [Shivam Mathur Setup-PHP GitHub Action](https://github.com/shivammathur/setup-php)
- [Database Performance in Continuous Integration](https://github.blog/2020-09-02-how-we-scaled-github-actions-for-speed/)

How long does your CI pipeline take to run from pull request to merge? Share your workflow timing in the comments below.
