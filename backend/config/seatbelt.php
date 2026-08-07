<?php

declare(strict_types=1);

use Judehashane\Seatbelt\Configurations\AutomaticEagerLoading;
use Judehashane\Seatbelt\Configurations\DatabaseMonitoring;
use Judehashane\Seatbelt\Configurations\DefaultPasswordRules;
use Judehashane\Seatbelt\Configurations\ForceHttpsScheme;
use Judehashane\Seatbelt\Configurations\ImmutableDates;
use Judehashane\Seatbelt\Configurations\PreventStrayProcesses;
use Judehashane\Seatbelt\Configurations\PreventStrayRequests;
use Judehashane\Seatbelt\Configurations\ProhibitDestructiveCommands;
use Judehashane\Seatbelt\Configurations\QueueFailedJobLogging;
use Judehashane\Seatbelt\Configurations\StrictModels;
use Judehashane\Seatbelt\Configurations\ViteAggressivePrefetching;

return [

    /*
    |--------------------------------------------------------------------------
    | Configurations
    |--------------------------------------------------------------------------
    |
    | Configuration classes to run on boot. Each must implement
    | Judehashane\Seatbelt\Contracts\Configuration. A configuration only
    | takes effect when its own enabled() check passes. Remove an entry to
    | disable that behavior entirely, or leave it in and use the settings
    | below to toggle it instead.
    |
    */

    'configurations' => [
        ProhibitDestructiveCommands::class,
        DefaultPasswordRules::class,
        StrictModels::class,
        ForceHttpsScheme::class,
        DatabaseMonitoring::class,
        ViteAggressivePrefetching::class,
        AutomaticEagerLoading::class,
        PreventStrayRequests::class,
        PreventStrayProcesses::class,
        QueueFailedJobLogging::class,
        ImmutableDates::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Prohibit Destructive Commands
    |--------------------------------------------------------------------------
    |
    | When true, ProhibitDestructiveCommands blocks db:wipe,
    | migrate:fresh, and migrate:reset while the app environment is
    | "production" — even if invoked with --force. Edit this value directly
    | to opt out; it's read once at boot, not from the environment.
    |
    */

    'prohibit_destructive_commands' => true,

    /*
    |--------------------------------------------------------------------------
    | Password Rule
    |--------------------------------------------------------------------------
    |
    | When enforce_rule is true, DefaultPasswordRules registers a
    | default password validation rule (minimum length + "uncompromised"
    | breach check) via Password::defaults(), applied everywhere validation
    | runs — including production, since weak passwords are a production
    | security concern, not a development one.
    |
    */

    'password' => [
        'enforce_rule' => true,
        'min_length' => 12,
        'max_length' => 255,
        'require_letter' => true,
        'require_numbers' => true,
        'require_symbols' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Enforce Strict Models
    |--------------------------------------------------------------------------
    |
    | When true, StrictModels calls Model::shouldBeStrict()
    | outside of production — surfacing lazy-loading violations, silently
    | discarded attributes, and missing-attribute access as exceptions
    | during development instead of silent bugs in production.
    |
    */

    'enforce_strict_models' => true,

    /*
    |--------------------------------------------------------------------------
    | Force HTTPS Scheme
    |--------------------------------------------------------------------------
    |
    | When true, ForceHttpsScheme calls URL::forceScheme('https') in
    | production, so generated URLs (routes, assets) always use https://
    | — including behind a proxy that terminates TLS before the request
    | reaches Laravel. Turn this off if your production environment
    | intentionally serves plain HTTP, or already handles scheme detection
    | via TrustProxies.
    |
    */

    'force_https_scheme' => true,

    /*
    |--------------------------------------------------------------------------
    | Database Query Monitoring
    |--------------------------------------------------------------------------
    |
    | When true, DatabaseMonitoring logs a warning whenever a
    | query exceeds query_budget_ms, outside of production — surfacing slow
    | queries and N+1s while you're building the feature, instead of
    | discovering them from production latency later.
    |
    */

    'database' => [
        'enforce_monitoring' => true,
        'query_budget_ms' => 500,
    ],

    /*
    |--------------------------------------------------------------------------
    | Vite Aggressive Prefetching
    |--------------------------------------------------------------------------
    |
    | When true, optimize front-end performance by prefetching all
    | JavaScript and CSS chunks in the background on initial load.
    |
    */
    'vite_aggressive_prefetching' => true,

    /*
    |--------------------------------------------------------------------------
    | Automatically Eager Load Relationships
    |--------------------------------------------------------------------------
    |
    | When true, AutomaticEagerLoading calls
    | Model::automaticallyEagerLoadRelationships() in production, so an N+1
    | that slips past StrictModels in development gets silently upgraded to
    | an eager load instead of hitting real users with per-row queries.
    | StrictModels (dev-only) still throws on the same access so you catch
    | and fix it before shipping — this is the production safety net, not a
    | replacement for fixing the N+1.
    |
    */

    'automatically_eager_load_relationships' => true,

    /*
    |--------------------------------------------------------------------------
    | Prevent Stray Requests
    |--------------------------------------------------------------------------
    |
    | When true, PreventStrayRequests calls Http::preventStrayRequests()
    | while running the test suite, so an un-faked Http:: call fails loudly
    | instead of hitting the real network. Gated on the test environment
    | specifically (not just "outside of production"), since applying this
    | during ordinary local development would break every real outbound
    | request your app makes.
    |
    */

    'prevent_stray_requests' => true,

    /*
    |--------------------------------------------------------------------------
    | Prevent Stray Processes
    |--------------------------------------------------------------------------
    |
    | When true, PreventStrayProcesses calls Process::preventStrayProcesses()
    | while running the test suite, so an un-faked Process:: call fails loudly
    | instead of shelling out for real. Same test-only gating as
    | prevent_stray_requests, for the same reason.
    |
    */

    'prevent_stray_processes' => true,

    /*
    |--------------------------------------------------------------------------
    | Queue Failed Job Logging
    |--------------------------------------------------------------------------
    |
    | When true, QueueFailedJobLogging calls Queue::failing() in production,
    | logging the connection, queue, job, class and exception whenever a
    | queue job exhausts its retries and fails - surfacing failures via
    | your normal log channel instead of only via - `failed_jobs` table rows
    | that tend to go overlooked.
    |
    */

    'queue_failed_job_logging' => true,

    /*
    |--------------------------------------------------------------------------
    | Immutable Dates
    |--------------------------------------------------------------------------
    |
    | When true, ImmutableDates calls Date::use(CarbonImmutable::class) —
    | applied everywhere, not just production, so code relying on Carbon's
    | mutable in-place behavior (e.g. discarding addDay()'s return value)
    | fails the same way in development as it would in production, instead
    | of only surfacing once it ships.
    |
    */

    'immutable_dates' => true,

];
