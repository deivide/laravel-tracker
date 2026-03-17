# PragmaRX Tracker - Source Tree Analysis

**Date:** 2026-03-16

## Overview

This is a single-part Laravel package (monolith) with all source code under `src/`. The package follows a clean separation of concerns: core tracking logic, data access via repositories, Eloquent models, support utilities, and Laravel-specific integrations.

## Complete Directory Structure

```
tracker/
├── src/                              # All package source code
│   ├── Tracker.php                   # Core Tracker class (534 lines) - main orchestrator
│   ├── config/
│   │   └── config.php               # Package configuration (386 lines, 50+ options)
│   ├── migrations/                   # Database migrations (34 files)
│   │   ├── 2014_02_10_000000-000034  # Creates 25+ tracker_* tables
│   │   └── ...
│   ├── Data/
│   │   ├── RepositoryManager.php     # Central repository orchestrator
│   │   ├── RepositoryManagerInterface.php
│   │   └── Repositories/            # Data access layer (25+ repositories)
│   │       ├── Repository.php        # Base repository class
│   │       ├── RepositoryInterface.php
│   │       ├── Session.php           # Visitor session management
│   │       ├── Log.php               # Page view logging
│   │       ├── Agent.php             # User-agent storage
│   │       ├── Device.php            # Device info storage
│   │       ├── Cookie.php            # Device cookie tracking
│   │       ├── Path.php              # URL path storage
│   │       ├── Query.php             # Query string storage
│   │       ├── QueryArgument.php     # Query parameter storage
│   │       ├── Domain.php            # Domain name storage
│   │       ├── Referer.php           # HTTP referer storage
│   │       ├── Route.php             # Named route storage
│   │       ├── RoutePath.php         # Route path storage
│   │       ├── RoutePathParameter.php # Route parameter storage
│   │       ├── Error.php             # Exception/error storage
│   │       ├── GeoIp.php             # Geographic location storage
│   │       ├── SqlQuery.php          # SQL statement storage
│   │       ├── SqlQueryBinding.php   # Query binding storage
│   │       ├── SqlQueryBindingParameter.php
│   │       ├── SqlQueryLog.php       # Query execution log storage
│   │       ├── Connection.php        # DB connection storage
│   │       ├── Event.php             # Event name storage
│   │       ├── EventLog.php          # Event log storage
│   │       ├── SystemClass.php       # System class storage
│   │       ├── Language.php          # Language preference storage
│   │       └── User.php              # User tracking
│   ├── Services/
│   │   └── Authentication.php        # Multi-guard auth service
│   ├── Support/
│   │   ├── Cache.php                 # Caching layer for query results
│   │   ├── CrawlerDetector.php       # Bot/crawler detection wrapper
│   │   ├── LanguageDetect.php        # Browser language extraction
│   │   ├── MobileDetect.php          # Device detection (mobile/tablet/desktop)
│   │   ├── UserAgentParser.php       # UA string parsing
│   │   ├── RefererParser.php         # HTTP referer parsing
│   │   ├── Minutes.php               # Time range helper for queries
│   │   ├── Migration.php             # Migration base class
│   │   ├── Filesystem.php            # File system utilities
│   │   ├── Exceptions/              # Exception hierarchy (14 types)
│   │   │   ├── ExceptionFactory.php  # Creates typed exceptions
│   │   │   ├── Handler.php           # Exception/error handler
│   │   │   ├── Error.php, Warning.php, Notice.php, etc.
│   │   │   └── ...
│   │   └── GeoIp/                   # Geolocation services
│   │       ├── GeoIp.php             # GeoIP facade
│   │       ├── GeoIpAbstract.php     # Abstract base
│   │       ├── GeoIpContract.php     # Interface contract
│   │       ├── GeoIp1.php            # Legacy GeoIP support
│   │       ├── GeoIp2.php            # MaxMind GeoIP2 support
│   │       ├── GeoLiteCity.dat       # Legacy GeoIP database
│   │       └── GeoLite2-City.mmdb    # GeoIP2 database
│   ├── Eventing/
│   │   └── EventStorage.php          # Event queue management
│   ├── Repositories/
│   │   └── Message.php               # Message aggregation utility
│   └── Vendor/Laravel/              # Laravel-specific integrations
│       ├── Facade.php                # Tracker facade
│       ├── ServiceProvider.php       # Service provider (645 lines) - bootstrap
│       ├── Middlewares/
│       │   └── Tracker.php           # HTTP middleware
│       ├── Controllers/
│       │   └── Stats.php             # Stats dashboard controller
│       ├── Artisan/
│       │   ├── Base.php              # Command base class
│       │   ├── Tables.php            # Migration publish command
│       │   └── UpdateGeoIp.php       # GeoIP database update command
│       ├── Models/                   # 29 Eloquent models
│       │   ├── Base.php              # Base model (config, caching, scopePeriod)
│       │   ├── Session.php, Log.php, Agent.php, Device.php,
│       │   ├── Cookie.php, Path.php, Query.php, QueryArgument.php,
│       │   ├── Domain.php, Referer.php, RefererSearchTerm.php,
│       │   ├── Route.php, RoutePath.php, RoutePathParameter.php,
│       │   ├── Error.php, GeoIp.php, SqlQuery.php, SqlQueryBinding.php,
│       │   ├── SqlQueryBindingParameter.php, SqlQueryLog.php,
│       │   ├── Connection.php, Event.php, EventLog.php,
│       │   ├── SystemClass.php, Language.php, User.php
│       │   └── ...
│       ├── Support/
│       │   └── Session.php           # Session helper
│       └── Views/                    # Blade view references
├── src/views/                        # Blade templates
│   ├── layout.blade.php             # Main layout (SB Admin 2)
│   ├── index.blade.php              # Visits dashboard
│   ├── log.blade.php                # Session detail view
│   ├── summary.blade.php            # Charts and summaries
│   ├── users.blade.php              # Users list
│   ├── events.blade.php             # Events tracking
│   ├── errors.blade.php             # Errors list
│   ├── message.blade.php            # Message display
│   ├── html.blade.php               # HTML helper
│   ├── _dataTable.blade.php         # DataTable partial
│   ├── _datatables.blade.php        # DataTables init partial
│   └── _summaryPiechart.blade.php   # Pie chart partial
├── src/lang/
│   └── en/tracker.php               # English translations
├── tests/                            # Test directory (currently empty)
├── vendor/                           # Composer dependencies
├── composer.json                     # Package manifest
├── phpunit.xml                       # PHPUnit configuration
├── readme.md                         # Full documentation (735 lines)
├── upgrading.md                      # Version upgrade guide
├── changelog.md                      # Changelog
└── LICENSE                           # MIT License
```

## Critical Directories

### `src/`

The root source directory containing all package code. Entry point for the package.

**Purpose:** Contains the entire package implementation
**Contains:** Core tracker class, configuration, migrations, data layer, support utilities, Laravel integrations

### `src/Data/Repositories/`

The data access layer implementing the Repository pattern.

**Purpose:** Provides abstracted CRUD operations for all tracking entities
**Contains:** 25+ repository classes, each handling a specific tracking entity (sessions, logs, agents, devices, etc.)

### `src/Vendor/Laravel/Models/`

Eloquent model definitions for all tracking database tables.

**Purpose:** ORM layer mapping to tracker_* database tables
**Contains:** 29 Eloquent models with relationships, scopes, and configurable table/connection settings

### `src/Vendor/Laravel/`

Laravel framework integration layer.

**Purpose:** Bridges the core tracker with Laravel's service container, routing, middleware, and artisan
**Contains:** ServiceProvider, Facade, Middleware, Controllers, Commands, Models, Views
**Entry Points:** `ServiceProvider.php` (bootstrap), `Facade.php` (static access)

### `src/Support/`

Utility and helper classes.

**Purpose:** Provides detection, parsing, caching, and exception handling utilities
**Contains:** Device detection, user-agent parsing, bot detection, GeoIP lookups, referer parsing, caching, exception factory

### `src/migrations/`

Database migration files (34 total).

**Purpose:** Creates and modifies the tracker_* database tables
**Contains:** Sequential migrations creating 25+ tables with foreign keys and indexes

### `src/config/`

Package configuration.

**Purpose:** Defines all configurable behavior (50+ options)
**Contains:** Single config.php with tracking toggles, exclusion rules, model mappings, auth settings, GeoIP config

## Entry Points

- **Main Entry:** `src/Vendor/Laravel/ServiceProvider.php` - Bootstraps the package, registers bindings, attaches event listeners
- **Additional:**
  - `src/Vendor/Laravel/Facade.php` - Static access via `Tracker::` calls
  - `src/Vendor/Laravel/Middlewares/Tracker.php` - HTTP middleware (alternative boot method)
  - `src/Tracker.php` - Core class instantiated by the service provider

## File Organization Patterns

- **Namespace:** `PragmaRX\Tracker\` maps to `src/`
- **Laravel conventions:** Models, Controllers, Middleware, Commands follow Laravel package conventions
- **Repository per entity:** Each tracked entity (session, log, agent, device, etc.) has its own repository class and model
- **Support utilities:** Detection and parsing utilities are grouped under `Support/`
- **GeoIP providers:** Pluggable via abstract contract pattern under `Support/GeoIp/`
- **Exception hierarchy:** PHP error types mapped to exception classes under `Support/Exceptions/`

## Key File Types

### PHP Source Files
- **Pattern:** `*.php`
- **Purpose:** All application logic, models, repositories, controllers
- **Examples:** `Tracker.php`, `ServiceProvider.php`, `RepositoryManager.php`

### Blade Templates
- **Pattern:** `*.blade.php`
- **Purpose:** Dashboard UI views
- **Examples:** `index.blade.php`, `log.blade.php`, `summary.blade.php`

### Migration Files
- **Pattern:** `YYYY_MM_DD_NNNNNN_*.php`
- **Purpose:** Database schema creation and modification
- **Examples:** `2014_02_10_000000_create_tracker_sessions_table.php`

## Asset Locations

- **GeoIP Databases**: `src/Support/GeoIp/GeoLiteCity.dat`, `src/Support/GeoIp/GeoLite2-City.mmdb`
- **Screenshots**: `src/views/screenshots/` (dashboard screenshots for documentation)

## Configuration Files

- **`composer.json`**: Package manifest, dependencies, autoloading (PSR-4)
- **`src/config/config.php`**: Main package configuration (386 lines, 50+ options)
- **`phpunit.xml`**: PHPUnit test configuration
- **`.scrutinizer.yml`**: Scrutinizer CI code quality configuration
- **`.travis.yml`**: Travis CI configuration

## Notes for Development

- The package uses a dedicated database connection (`tracker`) to isolate tracking data from the main application database
- SQL query logging has recursion prevention to avoid infinite loops when the tracker itself executes queries
- All models are configurable via the config file, allowing users to extend or replace any model class
- The `scopePeriod` scope on the base model enables time-range queries across all tracking entities
- GeoIP databases are bundled but should be updated periodically via `tracker:updategeoip`

---

_Generated using BMAD Method `document-project` workflow_
