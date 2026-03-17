<?php

namespace PragmaRX\Tracker\Tests\Unit;

use PHPUnit\Framework\TestCase;

class ConfigTest extends TestCase
{
    protected $config;

    protected function setUp(): void
    {
        parent::setUp();

        $this->config = require __DIR__ . '/../../src/config/config.php';
    }

    /** @test */
    public function config_file_returns_array()
    {
        $this->assertIsArray($this->config);
    }

    /** @test */
    public function config_has_required_keys()
    {
        $requiredKeys = [
            'enabled',
            'cache_enabled',
            'use_middleware',
            'log_enabled',
            'log_sql_queries',
            'log_events',
            'log_geoip',
            'log_user_agents',
            'log_users',
            'log_devices',
            'log_languages',
            'log_referers',
            'log_paths',
            'log_queries',
            'log_routes',
            'log_exceptions',
            'do_not_track_ips',
            'do_not_track_environments',
            'do_not_track_routes',
            'do_not_track_paths',
            'do_not_track_robots',
            'do_not_log_sql_queries_connections',
            'connection',
            'stats_panel_enabled',
            'stats_base_uri',
        ];

        foreach ($requiredKeys as $key) {
            $this->assertArrayHasKey($key, $this->config, "Config should have key '{$key}'");
        }
    }

    /** @test */
    public function config_defaults_are_safe()
    {
        $this->assertFalse($this->config['enabled'], 'Tracker should be disabled by default');
        $this->assertFalse($this->config['log_enabled'], 'Logging should be disabled by default');
        $this->assertFalse($this->config['stats_panel_enabled'], 'Stats panel should be disabled by default');
    }

    /** @test */
    public function config_prevents_tracker_sql_recursion()
    {
        $this->assertContains(
            'tracker',
            $this->config['do_not_log_sql_queries_connections'],
            'Tracker connection must be excluded from SQL query logging to prevent recursion'
        );
    }

    /** @test */
    public function config_excludes_localhost_by_default()
    {
        $this->assertContains('127.0.0.0/24', $this->config['do_not_track_ips']);
    }

    /** @test */
    public function config_has_tracker_connection()
    {
        $this->assertEquals('tracker', $this->config['connection']);
    }

    /** @test */
    public function all_model_configs_point_to_existing_classes()
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
            $class = $this->config[$key];
            $this->assertTrue(
                class_exists($class),
                "Model class '{$class}' for config '{$key}' does not exist"
            );
        }
    }
}
