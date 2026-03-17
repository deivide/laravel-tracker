<?php

namespace PragmaRX\Tracker\Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use PragmaRX\Tracker\Vendor\Laravel\Facade;
use PragmaRX\Tracker\Vendor\Laravel\ServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            ServiceProvider::class,
        ];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Tracker' => Facade::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        $app['config']->set('database.connections.tracker', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $app['config']->set('tracker.enabled', false);
    }
}
