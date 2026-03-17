# PragmaRX Tracker - Architecture

**Date:** 2026-03-16
**Type:** Library (Laravel Package)
**Architecture Pattern:** Repository Pattern with Service Provider Bootstrap

## Executive Summary

PragmaRX Tracker is a Laravel package that provides comprehensive visitor analytics by intercepting HTTP requests, database queries, application events, and exceptions. It uses a Repository Pattern for data access, Eloquent ORM for persistence, and Laravel's Service Provider for dependency injection and lifecycle management.

## High-Level Architecture

```
┌─────────────────────────────────────────────────────────┐
│                    Laravel Application                    │
│                                                          │
│  ┌──────────────┐    ┌──────────────┐                   │
│  │  Middleware   │    │    Facade    │                   │
│  │  (optional)   │    │  Tracker::   │                   │
│  └──────┬───────┘    └──────┬───────┘                   │
│         │                    │                            │
│         ▼                    ▼                            │
│  ┌──────────────────────────────────┐                   │
│  │        ServiceProvider            │                   │
│  │  - Registers bindings            │                   │
│  │  - Attaches event listeners      │                   │
│  │  - Configures routes             │                   │
│  └──────────────┬───────────────────┘                   │
│                 │                                        │
│                 ▼                                        │
│  ┌──────────────────────────────────┐                   │
│  │          Tracker (Core)           │                   │
│  │  - Coordinates all tracking      │                   │
│  │  - Manages session lifecycle     │                   │
│  │  - Provides query API            │                   │
│  └──────────────┬───────────────────┘                   │
│                 │                                        │
│                 ▼                                        │
│  ┌──────────────────────────────────┐                   │
│  │      RepositoryManager            │                   │
│  │  - Orchestrates 25+ repositories │                   │
│  │  - Manages entity creation       │                   │
│  │  - Coordinates cross-entity ops  │                   │
│  └──────────────┬───────────────────┘                   │
│                 │                                        │
│    ┌────────────┼────────────┐                           │
│    ▼            ▼            ▼                           │
│ ┌──────┐  ┌──────────┐  ┌────────┐                     │
│ │Repos │  │  Models   │  │Support │                     │
│ │(25+) │  │  (29)     │  │Classes │                     │
│ └──┬───┘  └────┬─────┘  └───┬────┘                     │
│    │           │             │                           │
│    ▼           ▼             ▼                           │
│ ┌──────────────────────────────────┐                    │
│ │     tracker_* Database Tables     │                    │
│ │     (Dedicated Connection)        │                    │
│ └──────────────────────────────────┘                    │
└─────────────────────────────────────────────────────────┘
```

## Core Components

### 1. Tracker (`src/Tracker.php` - 534 lines)

The central orchestrator that coordinates all tracking operations. Key responsibilities:

- **Session Management:** Creates and manages visitor sessions with UUID identification
- **Request Tracking:** Logs page views with full HTTP context (method, path, query, referrer, etc.)
- **Trackability Logic:** Determines whether to track based on IP, environment, route, path, and bot filters
- **Query API:** Provides methods for retrieving analytics data (sessions, users, page views, events, errors)
- **Event/Error Hooks:** Delegates tracking of events, errors, and SQL queries

**Key Public API:**
- `boot()` / `track()` - Initialize and log current request
- `currentSession()` - Get current visitor session with relations
- `sessions($minutes)` / `onlineUsers($minutes)` - Active session queries
- `pageViews($minutes)` / `pageViewsByCountry($minutes)` - Analytics queries
- `trackEvent($event)` / `trackVisit($route, $request)` - Manual tracking
- `handleThrowable($throwable)` - Exception tracking
- `logSqlQuery()` - SQL query tracking

### 2. ServiceProvider (`src/Vendor/Laravel/ServiceProvider.php` - 645 lines)

The bootstrap component that integrates the package with Laravel:

- **Dependency Injection:** Registers all repositories, support classes, and the Tracker instance
- **Event Listeners:** Attaches to router matching, SQL query logging, global events, auth checks
- **Configuration:** Publishes and merges config, loads views and translations
- **Route Registration:** Registers stats panel routes when enabled
- **Artisan Commands:** Registers `tracker:tables` and `tracker:updategeoip` commands

### 3. RepositoryManager (`src/Data/RepositoryManager.php`)

Central data orchestrator that injects and coordinates all 25+ repository classes:

- Creates/retrieves tracking entities (sessions, logs, agents, devices, etc.)
- Manages first-or-create patterns to avoid duplicates
- Coordinates multi-entity operations (e.g., creating a session requires agent, device, cookie, geoip)
- Provides the data query layer used by the Tracker class

### 4. Repository Classes (`src/Data/Repositories/`)

Each repository handles CRUD for a specific tracking entity. All extend `Repository` base class which provides:

- Model access via dependency injection
- Common query methods
- Interface compliance via `RepositoryInterface`

### 5. Eloquent Models (`src/Vendor/Laravel/Models/`)

29 models mapping to `tracker_*` database tables. All extend `Base` model which provides:

- **Configurable connection:** Uses dedicated `tracker` database connection
- **Config access:** Singleton pattern for package config
- **Cache integration:** Triggers cache updates on save
- **`scopePeriod()`:** Shared scope for time-range queries across all entities

### 6. Support Classes (`src/Support/`)

Utility layer providing detection and parsing:

| Class | Purpose | External Dependency |
|-------|---------|-------------------|
| `MobileDetect` | Device type detection (mobile/tablet/desktop) | jenssegers/agent |
| `UserAgentParser` | Browser/OS extraction from UA string | ua-parser/uap-php |
| `CrawlerDetector` | Bot/crawler identification | jaybizzle/crawler-detect |
| `RefererParser` | HTTP referer analysis, search term extraction | snowplow/referer-parser |
| `LanguageDetect` | Browser language preference extraction | Built-in |
| `GeoIp` / `GeoIp2` | Geographic location from IP | geoip2/geoip2 |
| `Cache` | Query result caching | Built-in |
| `Minutes` | Time range helper (numeric or Carbon-based) | Built-in |
| `Authentication` | Multi-guard auth checking | Built-in |
| `ExceptionFactory` | Creates typed PHP exception objects | Built-in |

## Data Flow

### Request Tracking Flow

```
HTTP Request
    │
    ▼
ServiceProvider::boot() OR Middleware::handle()
    │
    ▼
Tracker::boot()
    ├── Check: Is tracking enabled?
    ├── Check: Is this IP excluded?
    ├── Check: Is this environment excluded?
    ├── Check: Is this a robot? (if do_not_track_robots)
    ├── Check: Is this route/path excluded?
    │
    ▼ (if trackable)
Tracker::track()
    │
    ▼
RepositoryManager::createLog()
    ├── getOrCreateSession()
    │   ├── Detect/create Agent (UA parsing)
    │   ├── Detect/create Device (mobile detection)
    │   ├── Get/create Cookie (device identification)
    │   ├── Lookup/create GeoIp (IP geolocation)
    │   ├── Detect/create Language (Accept-Language header)
    │   └── Check authenticated user
    ├── getOrCreatePath() + getOrCreateDomain()
    ├── getOrCreateQuery() + QueryArguments
    ├── getOrCreateRoute() + RoutePath + RoutePathParameters
    └── Create Log entry with all foreign keys
```

### SQL Query Logging Flow

```
Database Query Executed
    │
    ▼
ServiceProvider event listener (DB::listen)
    │
    ▼
Tracker::logSqlQuery()
    ├── Check: Is this on the tracker connection? (skip to prevent recursion)
    ├── Check: Is SQL logging enabled?
    │
    ▼
RepositoryManager::logSqlQuery()
    ├── Create/find SqlQuery (by SHA1 hash)
    ├── Create SqlQueryLog (execution record)
    └── Create SqlQueryBindings + Parameters
```

### Event Logging Flow

```
Laravel Event Fired
    │
    ▼
ServiceProvider event listener (Event::listen('*'))
    │
    ▼
EventStorage queue
    │
    ▼ (on next request)
RepositoryManager::logEvents()
    ├── Find/create Event by class name
    └── Create EventLog entries
```

## Configuration Architecture

The package uses a single config file (`src/config/config.php`, 386 lines) with hierarchical organization:

### Toggle Categories
- **Activation:** `enabled`, `cache_enabled`, `use_middleware`
- **Feature toggles:** `log_enabled`, `log_sql_queries`, `log_events`, `log_geoip`, `log_user_agents`, `log_users`, `log_devices`, `log_languages`, `log_referers`, `log_paths`, `log_queries`, `log_routes`, `log_exceptions`
- **Exclusions:** `do_not_track_ips`, `do_not_track_environments`, `do_not_track_routes`, `do_not_track_paths`, `do_not_track_robots`
- **Database:** `connection` name, model class mappings (26 models)
- **Authentication:** `authentication_ioc_binding`, `authentication_guards`, check/user methods, user model
- **GeoIP:** Database path, license key
- **Stats Panel:** `stats_panel_enabled`, `stats_base_uri`, middleware, layout

### Model Configurability

All 29 Eloquent models are swappable via config:
```php
'session_model' => 'PragmaRX\Tracker\Vendor\Laravel\Models\Session',
'log_model' => 'PragmaRX\Tracker\Vendor\Laravel\Models\Log',
// ... 26 more model class references
```

This allows consumers to extend or replace any model while maintaining the package's relationships.

## Stats Panel Architecture

When `stats_panel_enabled` is true, the package registers routes under the configured `stats_base_uri`:

| Route | Method | Controller Method | Description |
|-------|--------|------------------|-------------|
| `/{base}/` | GET | `index()` | Dashboard (visits view) |
| `/{base}/log/{uuid}` | GET | `log()` | Session detail |
| `/{base}/api/pageviews` | GET | `apiPageviews()` | Page views data |
| `/{base}/api/pageviewsbycountry` | GET | `apiPageviewsByCountry()` | Geographic data |
| `/{base}/api/log/{uuid}` | GET | `apiLog()` | Session log API |
| `/{base}/api/errors` | GET | `apiErrors()` | Errors data |
| `/{base}/api/events` | GET | `apiEvents()` | Events data |
| `/{base}/api/users` | GET | `apiUsers()` | Users data |
| `/{base}/api/visits` | GET | `apiVisits()` | Visits data |

Views use Blade templates with DataTables for tabular data and JavaScript charts for summaries.

## Error Handling Architecture

The package includes a comprehensive exception hierarchy in `src/Support/Exceptions/`:

- `ExceptionFactory` creates typed exceptions matching PHP's error categories
- `Handler` wraps Laravel's exception handler to intercept and log errors
- Exception types: `Error`, `Warning`, `Notice`, `Parse`, `Fatal`, `Strict`, `Deprecated`, `CoreError`, `CoreWarning`, `CompileError`, `CompileWarning`, `UserError`, `UserWarning`, `UserNotice`, `UserDeprecated`

## Testing Strategy

- PHPUnit is configured via `phpunit.xml`
- Mockery is available for mocking (`~0.8`)
- The `tests/` directory exists but is currently empty
- CI was configured via Travis CI (`.travis.yml`) and Scrutinizer (`.scrutinizer.yml`)

## Performance Considerations

1. **Dedicated DB Connection:** Tracking uses a separate database connection to avoid impacting application query performance
2. **SQL Recursion Prevention:** The tracker skips logging queries made on its own connection
3. **Caching:** Query results are cached when `cache_enabled` is true
4. **Event Queueing:** Events are queued via `EventStorage` and flushed in batches
5. **Feature Toggles:** Each tracking feature can be individually disabled to reduce overhead
6. **First-or-Create Pattern:** Entities like agents, devices, and paths are deduplicated to minimize storage

## Key Design Decisions

1. **Repository Pattern over Active Record:** Provides a clean separation between business logic and data access, enabling testability and model swapping
2. **Single Config File:** All 50+ options in one file rather than scattered configs, with sensible defaults (most features off by default)
3. **Optional Middleware:** Can boot via service provider (automatic) or middleware (deferred), giving consumers control over when tracking occurs
4. **UUID Sessions:** Sessions identified by UUID rather than database auto-increment, enabling cross-request tracking without database dependency
5. **Pluggable GeoIP:** Abstract contract allows switching between GeoIP providers without code changes

---

_Generated using BMAD Method `document-project` workflow_
