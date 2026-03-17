<?php

namespace PragmaRX\Tracker\Tests;

use PragmaRX\Tracker\Tracker;
use PragmaRX\Tracker\Data\RepositoryManager;
use PragmaRX\Tracker\Repositories\Message as MessageRepository;
use PragmaRX\Support\Config;

abstract class EnabledTestCase extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('tracker.enabled', true);
        $app['config']->set('tracker.log_enabled', false);
        $app['config']->set('tracker.use_middleware', true);
    }

    protected function setUp(): void
    {
        parent::setUp();

        // The ServiceProvider's register() fails because registerGlobalViewComposers()
        // calls app('view') during register phase. We manually bind the tracker.
        if (!$this->app->bound('tracker')) {
            $this->registerTrackerManually();
        }
    }

    protected function registerTrackerManually()
    {
        $app = $this->app;

        // Register config singleton
        if (!$app->bound('tracker.config')) {
            $app->singleton('tracker.config', function ($app) {
                return new Config($app['config'], 'tracker.');
            });
        }

        // Register messages
        if (!$app->bound('tracker.messages')) {
            $app->singleton('tracker.messages', function () {
                return new MessageRepository();
            });
        }

        // Register tracker with mocked repository manager
        $app->singleton('tracker', function ($app) {
            $repositoryManager = \Mockery::mock(RepositoryManager::class);

            return new Tracker(
                $app['tracker.config'],
                $repositoryManager,
                $app['request'],
                $app['router'],
                $app['log'],
                $app,
                $app['tracker.messages']
            );
        });
    }
}
