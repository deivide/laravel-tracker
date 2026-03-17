# PragmaRX Tracker - Development Guide

**Date:** 2026-03-16

## Prerequisites

- **PHP:** >= 7.0
- **Composer:** Latest stable
- **Laravel Application:** 5.x through 11.x
- **Database:** MySQL, PostgreSQL, or SQLite (for the tracker connection)

## Installation

### 1. Install via Composer

```bash
composer require deivide/tracker
```

### 2. Register Service Provider

Add to your `config/app.php` providers array:

```php
PragmaRX\Tracker\Vendor\Laravel\ServiceProvider::class,
```

### 3. Register Facade (optional)

Add to your `config/app.php` aliases array:

```php
'Tracker' => PragmaRX\Tracker\Vendor\Laravel\Facade::class,
```

### 4. Publish Configuration

```bash
php artisan vendor:publish --provider="PragmaRX\Tracker\Vendor\Laravel\ServiceProvider"
```

This publishes `config/tracker.php` to your application.

### 5. Configure Database Connection

Add a `tracker` connection to your `config/database.php`:

```php
'tracker' => [
    'driver'   => 'mysql',
    'host'     => env('DB_HOST', '127.0.0.1'),
    'database' => env('DB_DATABASE', 'tracker'),
    'username' => env('DB_USERNAME', 'root'),
    'password' => env('DB_PASSWORD', ''),
    // ... other connection options
],
```

You can use the same database as your application or a separate one.

### 6. Run Migrations

```bash
php artisan tracker:tables
php artisan migrate
```

### 7. Enable Tracking

In `config/tracker.php`, set:

```php
'enabled' => true,
```

Then enable individual features as needed (`log_enabled`, `log_sql_queries`, `log_geoip`, etc.).

## Configuration Reference

The configuration file (`config/tracker.php`) contains 50+ options organized into categories:

### Activation
| Option | Default | Description |
|--------|---------|-------------|
| `enabled` | `false` | Master on/off switch |
| `cache_enabled` | `true` | Cache query results |
| `use_middleware` | `false` | Defer boot to middleware (vs auto-boot) |

### Feature Toggles
| Option | Default | Description |
|--------|---------|-------------|
| `log_enabled` | `false` | Track page views |
| `log_sql_queries` | `false` | Log database queries |
| `log_sql_queries_bindings` | `false` | Log query parameters |
| `log_events` | `false` | Track application events |
| `log_geoip` | `false` | Store geographic location |
| `log_user_agents` | `false` | Store user-agent info |
| `log_users` | `false` | Track authenticated users |
| `log_devices` | `false` | Track device types |
| `log_languages` | `false` | Track browser languages |
| `log_referers` | `false` | Track HTTP referers |
| `log_paths` | `false` | Track URL paths |
| `log_queries` | `false` | Track query strings |
| `log_routes` | `false` | Track named routes |
| `log_exceptions` | `false` | Track errors/exceptions |

### Exclusion Rules
| Option | Default | Description |
|--------|---------|-------------|
| `do_not_track_ips` | `['127.0.0.0/24']` | IP ranges to skip |
| `do_not_track_environments` | `[]` | Environments to skip |
| `do_not_track_routes` | `[]` | Route names to skip |
| `do_not_track_paths` | `[]` | URL paths to skip |
| `do_not_track_robots` | `false` | Skip bot/crawler tracking |

### Stats Dashboard
| Option | Default | Description |
|--------|---------|-------------|
| `stats_panel_enabled` | `false` | Enable analytics UI |
| `stats_base_uri` | `'stats'` | URL prefix for dashboard |
| `stats_routes_middleware` | `'web'` | Middleware for stats routes |

## Using the Middleware Approach

If you prefer deferred tracking (not on every request), set `use_middleware` to `true` in config, then add the middleware to your routes:

```php
// In app/Http/Kernel.php
protected $routeMiddleware = [
    'tracker' => \PragmaRX\Tracker\Vendor\Laravel\Middlewares\Tracker::class,
];

// In routes
Route::middleware('tracker')->group(function () {
    // Routes to track
});
```

## Using the Tracker API

### Query Active Sessions

```php
use Tracker;

// Sessions from the last 24 hours
$sessions = Tracker::sessions(60 * 24);

// Online users (last 10 minutes by default)
$users = Tracker::onlineUsers();

// Users from the last week
$users = Tracker::users(60 * 24 * 7);
```

### Get Current Session Info

```php
$session = Tracker::currentSession();
$session->client_ip;
$session->device->kind;        // Computer, Phone, Tablet
$session->device->platform;    // Windows, macOS, Linux, iOS, Android
$session->agent->browser;      // Chrome, Firefox, Safari
$session->geoIp->country_name;
$session->geoIp->city;
$session->language->preference;
$session->user->email;         // If authenticated
```

### Analytics Queries

```php
// Page views (last 30 days)
$views = Tracker::pageViews(60 * 24 * 30);

// Page views by country (last 24 hours)
$byCountry = Tracker::pageViewsByCountry(60 * 24);

// Errors
$errors = Tracker::errors(60 * 24);

// Events
$events = Tracker::events(60 * 24);

// User devices
$devices = Tracker::userDevices(60 * 24, $userId);
```

### Custom Time Ranges

```php
use PragmaRX\Tracker\Support\Minutes;
use Carbon\Carbon;

$range = new Minutes();
$range->setStart(Carbon::now()->subDays(7));
$range->setEnd(Carbon::now()->subDays(1));

$sessions = Tracker::sessions($range);
```

### Manual Tracking

```php
// Track a custom event
Tracker::trackEvent(['event' => 'user.signup']);

// Track a virtual page visit
Tracker::trackVisit(
    ['name' => 'my.route', 'action' => 'MyClass@method'],
    ['path' => '/virtual/page']
);
```

### Query by Route

```php
// Count visits to a specific route with parameter
Tracker::logByRouteName('user.profile')
    ->where('parameter', 'id')
    ->where('value', 1)
    ->count();
```

## GeoIP Setup

### Using MaxMind GeoIP2 (recommended)

1. Install the GeoIP2 package:
```bash
composer require geoip2/geoip2
```

2. Get a MaxMind license key from [maxmind.com](https://www.maxmind.com)

3. Configure in `config/tracker.php`:
```php
'geoip_database_path' => '/path/to/GeoLite2-City.mmdb',
'geoip_database_license' => 'YOUR_LICENSE_KEY',
```

4. Update the database:
```bash
php artisan tracker:updategeoip
```

## Artisan Commands

| Command | Description |
|---------|-------------|
| `tracker:tables` | Publish migration files to your application |
| `tracker:updategeoip` | Download/update the GeoIP database |

## Extending Models

All models can be replaced with custom implementations:

```php
// config/tracker.php
'session_model' => App\Models\Tracker\CustomSession::class,
```

Your custom model should extend the original:

```php
namespace App\Models\Tracker;

use PragmaRX\Tracker\Vendor\Laravel\Models\Session as BaseSession;

class CustomSession extends BaseSession
{
    // Add custom relationships, scopes, etc.
}
```

## Testing

```bash
# Run all tests
./vendor/bin/phpunit

# Run with coverage
./vendor/bin/phpunit --coverage-html coverage/
```

Note: The test suite is currently empty. When adding tests, consider using an in-memory SQLite database for the tracker connection.

## Troubleshooting

### Common Issues

1. **Infinite loop / stack overflow**: Ensure `do_not_log_sql_queries_connections` includes `'tracker'` (default). The tracker must not log its own queries.

2. **Missing tables**: Run `php artisan tracker:tables` then `php artisan migrate`. If using a separate database, specify `--database=tracker`.

3. **No data appearing**: Check that `enabled` is `true` and individual feature toggles (`log_enabled`, etc.) are enabled for what you want to track.

4. **GeoIP not working**: Ensure you have either `geoip/geoip` or `geoip2/geoip2` installed and the database file exists at the configured path.

5. **Stats panel not showing**: Set `stats_panel_enabled` to `true` and ensure the stats routes aren't blocked by middleware.

---

_Generated using BMAD Method `document-project` workflow_
