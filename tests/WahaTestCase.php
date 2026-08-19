<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use DenLopes\Waha\WahaServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * Testbench-backed base case for tests that need a booted Laravel application.
 *
 * Registering {@see WahaServiceProvider} here gives those tests the container,
 * `config()`, facades and HTTP layer they need, while keeping the package
 * self-contained: `composer test` works without a host application.
 */
abstract class WahaTestCase extends BaseTestCase
{
    protected function getPackageProviders($app): array
    {
        return [WahaServiceProvider::class];
    }
}
