# PragmaRX Tracker - API Contracts

**Date:** 2026-03-16

## Overview

The tracker exposes two API surfaces: (1) a PHP API via the `Tracker` facade for programmatic access within a Laravel application, and (2) HTTP endpoints for the optional stats dashboard.

## PHP Facade API

Access via `Tracker::` facade or dependency injection of `PragmaRX\Tracker\Tracker`.

### Session & User Queries

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `currentSession()` | none | `Session` model | Current visitor session with all relations |
| `sessions($minutes)` | `int\|Minutes` | Collection | Sessions within time range |
| `onlineUsers($minutes)` | `int\|Minutes` (default: 10) | Collection | Currently active authenticated users |
| `users($minutes)` | `int\|Minutes` | Collection | Authenticated users within time range |
| `userDevices($minutes, $user_id)` | `int\|Minutes`, `int` | Collection | Devices used by a specific user |

### Analytics Queries

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `pageViews($minutes)` | `int\|Minutes` | Collection | Page view statistics |
| `pageViewsByCountry($minutes)` | `int\|Minutes` | Collection | Page views grouped by country |
| `events($minutes)` | `int\|Minutes` | Collection | Application events |
| `errors($minutes)` | `int\|Minutes` | Collection | Logged errors/exceptions |

### Route-Based Queries

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `logByRouteName($name)` | `string` | Builder | Query builder for logs matching route name |
| `sessionLog($uuid, $results)` | `string`, `bool` | Collection\|Builder | Full session log by UUID |
| `allSessions()` | none | Collection | All sessions (no time filter) |

### Tracking Operations

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `boot()` | none | void | Initialize tracking for current request |
| `track()` | none | void | Log current page request |
| `trackEvent($event)` | `array` | void | Track a custom event (key: `event`) |
| `trackVisit($route, $request)` | `array`, `array` | void | Track a virtual page visit |
| `handleThrowable($throwable)` | `Throwable` | void | Log an exception/error |
| `logSqlQuery()` | none | void | Log a database query |

### Status Methods

| Method | Parameters | Returns | Description |
|--------|-----------|---------|-------------|
| `isEnabled()` | none | `bool` | Whether tracking is enabled |
| `isRobot()` | none | `bool` | Whether current visitor is a bot |
| `getSessionId()` | none | `int\|null` | Current session ID |
| `updateGeoIp()` | none | void | Update the GeoIP database |

### Parameters: Minutes Class

The `$minutes` parameter accepts either an integer (number of minutes) or a `PragmaRX\Tracker\Support\Minutes` object for custom ranges:

```php
// Simple: last 24 hours
Tracker::sessions(60 * 24);

// Custom range
$range = new \PragmaRX\Tracker\Support\Minutes();
$range->setStart(\Carbon\Carbon::now()->subDays(7));
$range->setEnd(\Carbon\Carbon::now()->subDays(1));
Tracker::sessions($range);
```

## HTTP Stats Panel Endpoints

Available when `stats_panel_enabled` is `true` in config. All routes are prefixed with the configured `stats_base_uri` (default: `stats`).

### Dashboard Pages

| Method | Path | Controller | Description |
|--------|------|-----------|-------------|
| GET | `/{base}/` | `Stats@index` | Main dashboard showing visits |
| GET | `/{base}/log/{uuid}` | `Stats@log` | Detailed session log view |

### Data API Endpoints

All return JSON responses suitable for DataTables consumption.

| Method | Path | Controller | Response |
|--------|------|-----------|----------|
| GET | `/{base}/api/visits` | `Stats@apiVisits` | Session list with device, agent, geoip data |
| GET | `/{base}/api/pageviews` | `Stats@apiPageviews` | Page view counts and paths |
| GET | `/{base}/api/pageviewsbycountry` | `Stats@apiPageviewsByCountry` | Page views grouped by country (for heatmap) |
| GET | `/{base}/api/log/{uuid}` | `Stats@apiLog` | Complete session log entries |
| GET | `/{base}/api/errors` | `Stats@apiErrors` | Error/exception list |
| GET | `/{base}/api/events` | `Stats@apiEvents` | Application event list |
| GET | `/{base}/api/users` | `Stats@apiUsers` | Authenticated user list |

### Authentication & Middleware

Stats routes use the middleware configured in `stats_routes_middleware` (default: `'web'`). There is no built-in authentication for the stats panel -- you should protect it via your application's middleware:

```php
// config/tracker.php
'stats_routes_middleware' => ['web', 'auth', 'admin'],
```

### Response Format

API endpoints return data structured for the `pragmarx/datatables` package, typically:

```json
{
    "draw": 1,
    "recordsTotal": 100,
    "recordsFiltered": 50,
    "data": [
        {
            "id": 1,
            "session_uuid": "abc-123",
            "client_ip": "192.168.1.1",
            ...
        }
    ]
}
```

---

_Generated using BMAD Method `document-project` workflow_
