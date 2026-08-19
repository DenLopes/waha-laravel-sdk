<?php

declare(strict_types=1);

namespace DenLopes\Waha\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Lightweight Laravel test base for WAHA tests that need the container (e.g.
 * Http::fake() and config()), without the database seeding used by the main
 * application test suite.
 *
 * The framework base class already boots the host application via
 * `Application::inferBasePath()`, so no `createApplication()` override is
 * required here.
 */
abstract class WahaTestCase extends BaseTestCase
{
    //
}
