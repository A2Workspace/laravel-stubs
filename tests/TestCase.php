<?php

namespace Tests;

use A2Workspace\Stubs\ServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    // protected function setUp(): void
    // {
    //     parent::setUp();

    //     // ...
    // }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('stubs.paths', [
            __DIR__ . '/fixtures/stubs',
        ]);
    }

    protected function getPackageProviders($app)
    {
        return [ServiceProvider::class];
    }
}
