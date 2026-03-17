# PragmaRX Tracker - Project Overview

**Date:** 2026-03-16
**Type:** Library (Laravel Package)
**Architecture:** Repository Pattern with Service Provider Bootstrap

## Executive Summary

PragmaRX Tracker is a comprehensive Laravel visitor tracking package that gathers detailed analytics about application requests, users, devices, and site visits. It provides automatic tracking of sessions, page views, user agents, devices, geographic locations, database queries, application events, errors, and more. The package includes an optional web-based analytics dashboard.

Originally authored by Antonio Carlos Ribeiro, this fork (deivide/laravel-tracker) extends support through Laravel 11.

## Project Classification

- **Repository Type:** Monolith
- **Project Type(s):** Library (Laravel Package with Backend characteristics)
- **Primary Language(s):** PHP (>=7.0)
- **Architecture Pattern:** Repository Pattern with Service Provider Bootstrap, Facade access

## Technology Stack Summary

| Category | Technology | Version | Notes |
|----------|-----------|---------|-------|
| Language | PHP | >=7.0 | |
| Framework | Laravel | 5-11 | Wide version compatibility |
| ORM | Eloquent | (via Laravel) | 29 models |
| Database Abstraction | Doctrine DBAL | ^2.6 or ^3.1 | Schema introspection |
| UUID | ramsey/uuid | ^3 or ^4 | Session identification |
| Device Detection | jenssegers/agent | ~2.1 | Mobile/tablet/desktop |
| User-Agent Parsing | ua-parser/uap-php | ~3.4 | Browser/OS extraction |
| Bot Detection | jaybizzle/crawler-detect | ~1.0 | Crawler filtering |
| Referer Parsing | snowplow/referer-parser | ~0.1 | Search term extraction |
| DataTables | pragmarx/datatables | dev-master | Dashboard tables |
| GeoIP (optional) | geoip2/geoip2 | ~2.0 | MaxMind GeoIP2 |
| GeoIP Legacy (optional) | geoip/geoip | ~1.14 | Legacy GeoIP |
| Logging | psr/log | ^1.1-^3.0 | PSR-3 logging |
| Testing | mockery/mockery | ~0.8 | Mocking (dev) |

## Key Features

1. **Comprehensive Visitor Tracking** - Sessions (UUID-based), page views, authenticated users, device/browser/OS detection, geographic location, language preferences, HTTP referers with search term extraction
2. **Database Query Logging** - Captures SQL statements, bindings, execution time with recursion prevention
3. **Application Event Logging** - Hooks into Laravel's event system to track custom and system events
4. **Exception/Error Logging** - Captures and categorizes all PHP error types and exceptions
5. **Route Tracking** - Named routes, route paths, and route parameters
6. **Smart Filtering** - Skip tracking by IP range, environment, route name, path, or bot detection
7. **Analytics Dashboard** - Optional web-based stats panel with DataTables, geographic heatmaps, and pie charts
8. **Separate Database Connection** - Isolates tracking data on its own database connection
9. **Caching Layer** - Query result caching for performance
10. **Multi-Guard Authentication** - Supports multiple Laravel auth guards

## Architecture Highlights

- **Repository Pattern** - 25+ repository classes provide a clean data access layer, orchestrated by a central `RepositoryManager`
- **Service Provider Bootstrap** - The `ServiceProvider` (645 lines) wires everything together: dependency injection, event listeners, route registration, view composers
- **Facade Access** - Simple `Tracker::` static API for common operations
- **Configurable Models** - All 29 Eloquent models are swappable via configuration
- **Extensible GeoIP** - Pluggable GeoIP providers (GeoIP1, GeoIP2) via abstract contract

## Development Overview

### Prerequisites

- PHP >= 7.0
- Composer
- A Laravel application (5.x through 11.x)

### Getting Started

```bash
composer require deivide/tracker
```

Add the service provider and facade to your Laravel app config, publish config and migrations, then run migrations.

### Key Commands

- **Install:** `composer require deivide/laravel-tracker`
- **Publish Config:** `php artisan vendor:publish --provider="PragmaRX\Tracker\Vendor\Laravel\ServiceProvider"`
- **Run Migrations:** `php artisan tracker:tables && php artisan migrate`
- **Update GeoIP:** `php artisan tracker:updategeoip`
- **Test:** `phpunit`

## Repository Structure

The package follows standard Laravel package conventions with all source code under `src/`, organized by responsibility: core tracker logic, data repositories, Eloquent models, support utilities, Laravel-specific integrations (service provider, facade, middleware, controllers, views, commands), and database migrations.

## Documentation Map

For detailed information, see:

- [index.md](./index.md) - Master documentation index
- [architecture.md](./architecture.md) - Detailed architecture
- [source-tree-analysis.md](./source-tree-analysis.md) - Directory structure
- [data-models.md](./data-models.md) - Database schema and models
- [development-guide.md](./development-guide.md) - Development workflow

---

_Generated using BMAD Method `document-project` workflow_
