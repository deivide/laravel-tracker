<?php

namespace PragmaRX\Tracker\Tests\Feature;

use PragmaRX\Tracker\Tests\TestCase;
use PragmaRX\Tracker\Vendor\Laravel\ServiceProvider;

class ServiceProviderTest extends TestCase
{
    /** @test */
    public function it_registers_the_service_provider()
    {
        $providers = $this->app->getLoadedProviders();

        $this->assertArrayHasKey(ServiceProvider::class, $providers);
    }

    /** @test */
    public function it_loads_the_config()
    {
        $config = $this->app['config']->get('tracker');

        $this->assertIsArray($config);
        $this->assertArrayHasKey('enabled', $config);
        $this->assertArrayHasKey('log_enabled', $config);
        $this->assertArrayHasKey('use_middleware', $config);
    }

    /** @test */
    public function it_has_default_config_values()
    {
        $this->assertFalse($this->app['config']->get('tracker.log_enabled'));
        $this->assertFalse($this->app['config']->get('tracker.use_middleware'));
        $this->assertFalse($this->app['config']->get('tracker.log_sql_queries'));
        $this->assertFalse($this->app['config']->get('tracker.log_events'));
        $this->assertFalse($this->app['config']->get('tracker.log_geoip'));
        $this->assertFalse($this->app['config']->get('tracker.stats_panel_enabled'));
    }

    /** @test */
    public function it_has_all_model_configs()
    {
        $modelKeys = [
            'session_model',
            'log_model',
            'agent_model',
            'device_model',
            'cookie_model',
            'path_model',
            'query_model',
            'query_argument_model',
            'domain_model',
            'referer_model',
            'route_model',
            'route_path_model',
            'route_path_parameter_model',
            'error_model',
            'geoip_model',
            'sql_query_model',
            'sql_query_binding_model',
            'sql_query_binding_parameter_model',
            'sql_query_log_model',
            'connection_model',
            'event_model',
            'event_log_model',
            'system_class_model',
            'language_model',
        ];

        foreach ($modelKeys as $key) {
            $this->assertNotNull(
                $this->app['config']->get('tracker.' . $key),
                "Config key 'tracker.{$key}' should not be null"
            );
        }
    }

    /** @test */
    public function it_has_do_not_track_defaults()
    {
        $ips = $this->app['config']->get('tracker.do_not_track_ips');
        $this->assertIsArray($ips);
        $this->assertContains('127.0.0.0/24', $ips);

        $connections = $this->app['config']->get('tracker.do_not_log_sql_queries_connections');
        $this->assertIsArray($connections);
        $this->assertContains('tracker', $connections);
    }

    /** @test */
    public function it_does_not_register_tracker_when_disabled()
    {
        $this->app['config']->set('tracker.enabled', false);

        $this->assertFalse($this->app->bound('tracker'));
    }

    /** @test */
    public function it_registers_tracker_when_enabled()
    {
        $app = $this->createApplication();
        $app['config']->set('tracker.enabled', true);
        $app['config']->set('database.connections.tracker', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);

        $provider = new ServiceProvider($app);
        $provider->register();

        $this->assertTrue($app->bound('tracker'));
    }
}
