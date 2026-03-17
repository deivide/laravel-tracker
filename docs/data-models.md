# PragmaRX Tracker - Data Models

**Date:** 2026-03-16

## Overview

The tracker uses 25+ database tables (all prefixed with `tracker_`) managed via 34 migration files. Tables are designed for normalized storage of visitor analytics data with extensive foreign key relationships. All tables use a dedicated `tracker` database connection.

## Entity Relationship Diagram

```
                          ┌──────────────┐
                          │   Sessions   │
                          │──────────────│
                     ┌───►│ id           │◄───┐
                     │    │ uuid         │    │
                     │    │ user_id ─────┼────┼──► Users (app)
                     │    │ device_id ───┼────┼──► Devices
                     │    │ agent_id ────┼────┼──► Agents
                     │    │ client_ip    │    │
                     │    │ cookie_id ───┼────┼──► Cookies
                     │    │ geoip_id ────┼────┼──► GeoIp
                     │    │ language_id ─┼────┼──► Languages
                     │    │ referer_id ──┼────┼──► Referers
                     │    │ is_robot     │    │
                     │    └──────────────┘    │
                     │                        │
              ┌──────┴───────┐                │
              │     Log      │                │
              │──────────────│                │
              │ id           │                │
              │ session_id ──┼────────────────┘
              │ path_id ─────┼──► Paths
              │ query_id ────┼──► Queries ──► QueryArguments
              │ method       │
              │ route_path_id┼──► RoutePaths ──► Routes
              │ is_ajax      │            └──► RoutePathParameters
              │ is_secure    │
              │ is_json      │
              │ wants_json   │
              │ error_id ────┼──► Errors
              │ referer_id ──┼──► Referers ──► RefererSearchTerms
              └──────────────┘

    ┌──────────────┐     ┌──────────────────┐
    │   Domains    │     │  SqlQueries      │
    │──────────────│     │──────────────────│
    │ id           │     │ id               │
    │ name         │     │ sha1             │
    └──────────────┘     │ statement        │
                         │ time             │
    ┌──────────────┐     │ connection_id ───┼──► Connections
    │   Events     │     └────────┬─────────┘
    │──────────────│              │
    │ id           │     ┌────────┴─────────┐
    │ name         │     │  SqlQueryLog     │
    └──────┬───────┘     │──────────────────│
           │             │ id               │
    ┌──────┴───────┐     │ log_id           │
    │  EventLog    │     │ sql_query_id     │
    │──────────────│     └──────────────────┘
    │ id           │
    │ event_id     │     ┌──────────────────┐
    │ class_id ────┼──►  │  SystemClasses   │
    │ log_id       │     │──────────────────│
    └──────────────┘     │ id               │
                         │ name             │
                         └──────────────────┘
```

## Table Definitions

### tracker_sessions

Central table tracking visitor sessions.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | Auto-increment ID |
| uuid | string (unique) | Session UUID for cross-request identification |
| user_id | bigint (nullable, FK) | Authenticated user reference |
| device_id | bigint (nullable, FK) | Device reference |
| agent_id | bigint (nullable, FK) | User-agent reference |
| client_ip | string | Visitor IP address |
| cookie_id | bigint (nullable, FK) | Device cookie reference |
| geoip_id | bigint (nullable, FK) | Geographic location reference |
| language_id | bigint (nullable, FK) | Language preference reference |
| referer_id | bigint (nullable, FK) | Referring URL reference |
| is_robot | boolean | Whether session is a bot/crawler |
| created_at | timestamp | Session start time |
| updated_at | timestamp | Last activity time |

**Relationships:** Has many `Log`, belongs to `Device`, `Agent`, `Cookie`, `GeoIp`, `Language`, `Referer`

### tracker_log

Page view/request log entries.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | Auto-increment ID |
| session_id | bigint (FK) | Session reference |
| path_id | bigint (nullable, FK) | URL path reference |
| query_id | bigint (nullable, FK) | Query string reference |
| method | string | HTTP method (GET, POST, etc.) |
| route_path_id | bigint (nullable, FK) | Route path reference |
| is_ajax | boolean | AJAX request flag |
| is_secure | boolean | HTTPS flag |
| is_json | boolean | JSON request flag |
| wants_json | boolean | Accepts JSON flag |
| error_id | bigint (nullable, FK) | Error reference (if error occurred) |
| referer_id | bigint (nullable, FK) | HTTP referer reference |
| created_at | timestamp | Request timestamp |
| updated_at | timestamp | |

**Relationships:** Belongs to `Session`, `Path`, `Query`, `RoutePath`, `Error`, `Referer`

### tracker_agents

Parsed user-agent information.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| name | string (unique) | Full user-agent string |
| browser | string | Browser name |
| browser_version | string | Browser version |
| name_hash | string | SHA hash for quick lookup |
| created_at / updated_at | timestamp | |

### tracker_devices

Device information extracted from user-agent.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| kind | string | Device type: Computer, Phone, Tablet |
| model | string | Device model name |
| platform | string | OS name |
| platform_version | string | OS version |
| is_mobile | boolean | Mobile device flag |
| created_at / updated_at | timestamp | |

### tracker_cookies

Device identification cookies for returning visitor tracking.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| uuid | string (unique) | Cookie UUID value |
| created_at / updated_at | timestamp | |

### tracker_paths

Distinct URL paths accessed.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| path | string | URL path (e.g., `/users/profile`) |
| created_at / updated_at | timestamp | |

### tracker_queries

Distinct URL query strings.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| query | string | Full query string |
| created_at / updated_at | timestamp | |

### tracker_queries_arguments

Individual query parameters parsed from query strings.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| query_id | bigint (FK) | Query string reference |
| argument | string | Parameter name |
| value | string (nullable) | Parameter value |
| created_at / updated_at | timestamp | |

### tracker_domains

Distinct domain names.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| name | string | Domain name |
| created_at / updated_at | timestamp | |

### tracker_referers

HTTP referer URLs.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| domain_id | bigint (FK) | Domain reference |
| url | string | Full referer URL |
| host | string | Referer hostname |
| medium | string (nullable) | Traffic medium (search, social, etc.) |
| source | string (nullable) | Traffic source |
| search_terms_hash | string (nullable) | Hash of search terms |
| created_at / updated_at | timestamp | |

### tracker_referers_search_terms

Search terms extracted from referer URLs.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| referer_id | bigint (FK) | Referer reference |
| search_term | string | Extracted search term |
| created_at / updated_at | timestamp | |

### tracker_routes

Named Laravel routes.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| name | string | Route name |
| action | string | Controller@method action |
| created_at / updated_at | timestamp | |

### tracker_route_paths

Route URL patterns.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| route_id | bigint (FK) | Route reference |
| path | string | Route URL pattern |
| created_at / updated_at | timestamp | |

### tracker_route_path_parameters

Route parameter values for parameterized routes.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| route_path_id | bigint (FK) | Route path reference |
| parameter | string | Parameter name |
| value | string | Parameter value |
| created_at / updated_at | timestamp | |

### tracker_geoip

Geographic location data from IP lookups.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| latitude | double | Latitude coordinate |
| longitude | double | Longitude coordinate |
| country_code | string (2) | ISO country code |
| country_name | string | Full country name |
| region | string (nullable) | Region/state |
| city | string (nullable) | City name |
| area_code | string (nullable) | Area/phone code |
| postal_code | string (nullable) | Postal/ZIP code |
| created_at / updated_at | timestamp | |

### tracker_errors

Exceptions and errors captured during requests.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| code | string (nullable) | Error code |
| message | text | Error message |
| created_at / updated_at | timestamp | |

### tracker_languages

Browser language preferences.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| preference | string | Language code (e.g., `en-US`) |
| language_range | string | Full Accept-Language value |
| created_at / updated_at | timestamp | |

### tracker_events

Tracked application event types.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| name | string | Event class name |
| created_at / updated_at | timestamp | |

### tracker_events_log

Event occurrence log entries.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| event_id | bigint (FK) | Event type reference |
| class_id | bigint (nullable, FK) | System class reference |
| log_id | bigint (nullable, FK) | Request log reference |
| created_at / updated_at | timestamp | |

### tracker_sql_queries

Distinct SQL query statements.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| sha1 | string (unique) | SHA1 hash of statement |
| site | string | Application identifier |
| statement | text | Full SQL statement |
| time | double | Execution time (ms) |
| connection_id | bigint (FK) | Connection reference |
| created_at / updated_at | timestamp | |

### tracker_sql_queries_log

SQL query execution log (links queries to request logs).

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| log_id | bigint (nullable, FK) | Request log reference |
| sql_query_id | bigint (FK) | SQL query reference |
| created_at / updated_at | timestamp | |

### tracker_sql_query_bindings

Query binding sets for parameterized queries.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| sha1 | string (unique) | SHA1 hash of bindings |
| serialized | text | Serialized binding values |
| created_at / updated_at | timestamp | |

### tracker_sql_query_bindings_parameters

Individual binding parameters.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| sql_query_binding_id | bigint (FK) | Binding set reference |
| name | string (nullable) | Parameter name |
| value | text (nullable) | Parameter value |
| created_at / updated_at | timestamp | |

### tracker_connections

Database connection identifiers.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| name | string | Connection name |
| created_at / updated_at | timestamp | |

### tracker_system_classes

System class names referenced in events.

| Column | Type | Description |
|--------|------|-------------|
| id | bigint (PK) | |
| name | string | Fully qualified class name |
| created_at / updated_at | timestamp | |

## Model Relationships Summary

| Model | Belongs To | Has Many |
|-------|-----------|----------|
| Session | Device, Agent, Cookie, GeoIp, Language, Referer | Log |
| Log | Session, Path, Query, RoutePath, Error, Referer | - |
| Query | - | QueryArgument |
| RoutePath | Route | RoutePathParameter |
| Referer | Domain | RefererSearchTerm |
| SqlQuery | Connection | SqlQueryLog, SqlQueryBinding |
| SqlQueryBinding | - | SqlQueryBindingParameter |
| Event | - | EventLog |
| EventLog | Event, SystemClass | - |

## Migration Strategy

- All 34 migrations are sequential, starting from `2014_02_10_000000`
- Published to the application via `php artisan tracker:tables`
- Run with `php artisan migrate --database=tracker`
- Tables use the dedicated `tracker` database connection
- Foreign keys reference within the tracker schema only (no cross-database FKs to the app)
- The user_id column in sessions references the application's users table by convention but has no database-level FK

## Data Deduplication

The package uses first-or-create patterns to minimize storage:
- **Agents** are deduplicated by name hash
- **Devices** are deduplicated by kind+model+platform combination
- **Paths**, **Queries**, **Domains** are deduplicated by value
- **SQL Queries** are deduplicated by SHA1 hash of the statement
- **Events** are deduplicated by class name
- **Routes** are deduplicated by name+action

---

_Generated using BMAD Method `document-project` workflow_
