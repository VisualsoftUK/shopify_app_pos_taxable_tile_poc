<?php

use Illuminate\Container\Container;
use Illuminate\Foundation\Application;

/**
 * @param  array<string, ?string>  $environment
 * @return array<string, mixed>
 */
function databaseConfigurationWith(array $environment): array
{
    $variableNames = [
        'DEFAULT_REDIS_HOSTS',
        'CACHE_REDIS_HOSTS',
        'REDIS_HOST',
        'REDIS_PORT',
        'REDIS_CLUSTER',
    ];
    $originalEnvironment = [];
    $originalServer = [];
    $originalProcessEnvironment = [];
    $previousContainer = Container::getInstance();

    foreach ($variableNames as $variableName) {
        $originalEnvironment[$variableName] = $_ENV[$variableName] ?? null;
        $originalServer[$variableName] = $_SERVER[$variableName] ?? null;
        $originalProcessEnvironment[$variableName] = getenv($variableName);

        $value = $environment[$variableName] ?? null;

        if ($value === null) {
            unset($_ENV[$variableName], $_SERVER[$variableName]);
            putenv($variableName);

            continue;
        }

        $_ENV[$variableName] = $value;
        $_SERVER[$variableName] = $value;
        putenv("{$variableName}={$value}");
    }

    try {
        // config/database.php uses path helpers that require an Application instance.
        Container::setInstance(new Application(dirname(__DIR__, 2)));

        return require base_path('config/database.php');
    } finally {
        Container::setInstance($previousContainer);

        foreach ($variableNames as $variableName) {
            if ($originalEnvironment[$variableName] === null) {
                unset($_ENV[$variableName]);
            } else {
                $_ENV[$variableName] = $originalEnvironment[$variableName];
            }

            if ($originalServer[$variableName] === null) {
                unset($_SERVER[$variableName]);
            } else {
                $_SERVER[$variableName] = $originalServer[$variableName];
            }

            if ($originalProcessEnvironment[$variableName] === false) {
                putenv($variableName);
            } else {
                putenv("{$variableName}={$originalProcessEnvironment[$variableName]}");
            }
        }
    }
}

test('parses multiple default Redis cluster hosts', function (): void {
    $configuration = databaseConfigurationWith([
        'DEFAULT_REDIS_HOSTS' => 'redis-primary:6380,redis-replica:6381',
    ]);

    expect($configuration['redis']['clusters']['default'])->toMatchArray([
        [
            'host' => 'redis-primary',
            'username' => null,
            'password' => null,
            'port' => '6380',
            'database' => '0',
        ],
        [
            'host' => 'redis-replica',
            'username' => null,
            'password' => null,
            'port' => '6381',
            'database' => '0',
        ],
    ]);
});

test('parses multiple cache Redis cluster hosts', function (): void {
    $configuration = databaseConfigurationWith([
        'CACHE_REDIS_HOSTS' => 'cache-primary:6382,cache-replica:6383',
    ]);

    expect($configuration['redis']['clusters']['cache'])->toMatchArray([
        [
            'host' => 'cache-primary',
            'username' => null,
            'password' => null,
            'port' => '6382',
            'database' => '1',
        ],
        [
            'host' => 'cache-replica',
            'username' => null,
            'password' => null,
            'port' => '6383',
            'database' => '1',
        ],
    ]);
});

test('uses the configured default port for cluster hosts without ports', function (): void {
    $configuration = databaseConfigurationWith([
        'DEFAULT_REDIS_HOSTS' => 'redis-primary',
        'CACHE_REDIS_HOSTS' => 'cache-primary',
        'REDIS_PORT' => '6385',
    ]);

    expect($configuration['redis']['clusters']['default'][0]['port'])->toBe('6385')
        ->and($configuration['redis']['clusters']['cache'][0]['port'])->toBe('6385');
});

test('uses port 6379 for cluster hosts without a configured port', function (): void {
    $configuration = databaseConfigurationWith([
        'DEFAULT_REDIS_HOSTS' => 'redis-primary',
        'CACHE_REDIS_HOSTS' => 'cache-primary',
    ]);

    expect($configuration['redis']['clusters']['default'][0]['port'])->toBe('6379')
        ->and($configuration['redis']['clusters']['cache'][0]['port'])->toBe('6379');
});

test('falls back to single Redis connections when cluster hosts are empty', function (): void {
    $configuration = databaseConfigurationWith([
        'DEFAULT_REDIS_HOSTS' => '',
        'CACHE_REDIS_HOSTS' => '',
        'REDIS_HOST' => 'redis-fallback',
    ]);

    expect($configuration['redis'])->toHaveKeys(['default', 'cache'])
        ->and($configuration['redis'])->not->toHaveKey('clusters')
        ->and($configuration['redis']['default']['host'])->toBe('redis-fallback')
        ->and($configuration['redis']['cache']['host'])->toBe('redis-fallback');
});

test('uses the configured Redis cluster option', function (): void {
    $configuration = databaseConfigurationWith([
        'REDIS_CLUSTER' => 'predis',
    ]);

    expect($configuration['redis']['options']['cluster'])->toBe('predis');
});
