#!/usr/bin/env php
<?php

/**
 * CI Infrastructure Tests
 *
 * Usage: php .github/scripts/ci-tests.php <test-name>
 *
 * Available tests:
 * - coverage-driver
 * - sqlite
 * - mysql
 * - redis
 * - cache
 * - queue
 * - all
 */

define('BASE_PATH', dirname(__DIR__, 2));

class InfrastructureTester
{
    protected $app;

    const TESTS = [
//        'sqlite' => 'testSqlite',
        'mysql'  => 'testMysql',
        'redis'  => 'testRedis',
        'cache'  => 'testCache',
        'queue'  => 'testQueue',
        'coverage-driver' => 'testCoverageDriver',
    ];

    /**
     * The constructor handles the Laravel bootstrapping logic.
     */
    public function __construct()
    {
        $autoload = BASE_PATH . '/vendor/autoload.php';

        if (!file_exists($autoload)) {
            $this->output('Vendor autoload file not found. Run composer install first.', 'error');
            exit(1);
        }

        require $autoload;

        $appFile = BASE_PATH . '/bootstrap/app.php';
        if (!file_exists($appFile)) {
            $this->output('Laravel bootstrap file not found.', 'error');
            exit(1);
        }

        // Bootstrap the Laravel application
        $this->app = require $appFile;
        $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();
    }

    /**
     * Helper to output formatted messages.
     */
    public function output(string $message, string $type = 'info'): void
    {
        $icons = [
            'info'    => '[INFO]',
            'success' => '[OK]',
            'error'   => '[ERROR]',
            'warning' => '[WARN]',
        ];

        echo ($icons[$type] ?? '') . ' ' . $message . PHP_EOL;
    }

    public function testCoverageDriver(): bool
    {
        $this->output('Checking code coverage driver...');

        if (extension_loaded('xdebug')) {
            $version = phpversion('xdebug');
            $mode = ini_get('xdebug.mode');
            $this->output("Xdebug {$version} (mode: {$mode})", 'success');
            return true;
        }

        if (extension_loaded('pcov')) {
            $version = phpversion('pcov');
            $this->output("PCOV {$version}", 'success');
            return true;
        }

        $this->output('Xdebug or PCOV required for coverage', 'error');
        return false;
    }

    public function testSqlite(): bool
    {
        $this->output('Testing SQLite connection...');

        $dbPath = BASE_PATH . '/database/database.sqlite';
        $this->output("Config: path={$dbPath}");

        if (!extension_loaded('pdo_sqlite')) {
            $this->output('pdo_sqlite extension is not loaded', 'error');
            return false;
        }

        if (!file_exists($dbPath)) {
            $this->output("Database not found at: {$dbPath}", 'error');
            return false;
        }

        try {
            $pdo = new PDO("sqlite:{$dbPath}");
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $result = $pdo->query('SELECT 1')->fetchColumn();

            if ($result != 1) {
                $this->output('SQLite test query failed', 'error');
                return false;
            }

            $this->output('SQLite connection successful', 'success');
            return true;
        } catch (Exception $e) {
            $this->output('SQLite connection failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    public function testMysql(): bool
    {
        $this->output('Testing MySQL connection...');

        $host = config('database.connections.mysql.host');
        $port = config('database.connections.mysql.port');
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $this->output("Config: host={$host}, port={$port}, database={$database}, username={$username}");

        if (!extension_loaded('pdo_mysql')) {
            $this->output('pdo_mysql extension is not loaded', 'error');
            return false;
        }

        try {
            $connection = config('database.default');
            if ($connection !== 'mysql') {
                $this->output("Default connection is '{$connection}', expected 'mysql'", 'warning');
            }

            $result = \Illuminate\Support\Facades\DB::select('SELECT 1 as test');

            if (empty($result) || $result[0]->test != 1) {
                $this->output('MySQL test query returned unexpected result', 'error');
                return false;
            }

            $this->output('MySQL connection successful', 'success');
            return true;
        } catch (Exception $e) {
            $this->output('MySQL connection failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    public function testRedis(): bool
    {
        $this->output('Testing Redis connection...');

        $host = config('database.redis.default.host');
        $port = config('database.redis.default.port');
        $database = config('database.redis.default.database');
        $client = config('database.redis.client');
        $this->output("Config: client={$client}, host={$host}, port={$port}, database={$database}");

        try {
            $redis = $this->app->make('redis')->connection();
            $redis->ping();

            $this->output('Redis connection successful', 'success');
            return true;
        } catch (Exception $e) {
            $this->output('Redis connection failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    public function testCache(): bool
    {
        $this->output('Testing Laravel cache operations...');

        $store = config('cache.default');
        $prefix = config('cache.prefix');
        $this->output("Config: store={$store}, prefix={$prefix}");

        try {
            $cache = $this->app->make('cache');
            $testKey = 'ci_test_key_' . uniqid();
            $testValue = 'ci_test_value';

            $cache->put($testKey, $testValue, 60);

            if ($cache->get($testKey) !== $testValue) {
                $this->output('Cache read/write failed', 'error');
                return false;
            }

            $cache->forget($testKey);

            $this->output('Cache operations working', 'success');
            return true;
        } catch (Exception $e) {
            $this->output('Cache test failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    public function testQueue(): bool
    {
        $this->output('Testing queue connection...');

        $connection = config('queue.default');
        $queue = config("queue.connections.{$connection}.queue", 'default');
        $this->output("Config: connection={$connection}, queue={$queue}");

        try {
            $queueManager = $this->app->make('queue');

            // For Redis queue driver, verify via Redis ping (Laravel 12 safe)
            if ($connection === 'redis') {
                $queueManager->connection()->getRedis()->connection()->ping();
            } else {
                $queueManager->connection()->size($queue);
            }

            $this->output('Queue connection working', 'success');
            return true;
        } catch (Exception $e) {
            $this->output('Queue test failed: ' . $e->getMessage(), 'error');
            return false;
        }
    }

    public function runAllTests(): bool
    {
        $allPassed = true;
        foreach (self::TESTS as $name => $method) {
            echo PHP_EOL . "--- Running: {$name} ---" . PHP_EOL;
            if (!$this->$method()) {
                $allPassed = false;
            }
        }

        echo PHP_EOL;
        if ($allPassed) {
            $this->output('All tests passed!', 'success');
        } else {
            $this->output('Some tests failed!', 'error');
        }

        return $allPassed;
    }
}

// --- Main execution ---

$tester = new InfrastructureTester();
$testName = $argv[1] ?? 'all';

$mapping = array_merge(InfrastructureTester::TESTS, ['all' => 'runAllTests']);

if (!isset($mapping[$testName])) {
    $tester->output("Unknown test: {$testName}", 'error');
    echo PHP_EOL . 'Available tests:' . PHP_EOL;
    foreach (array_keys($mapping) as $name) {
        echo "  - {$name}" . PHP_EOL;
    }
    exit(1);
}

$method = $mapping[$testName];
$result = $tester->$method();

exit($result ? 0 : 1);
