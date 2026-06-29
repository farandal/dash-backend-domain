# Dash-backend domain layer

This repository is the **domain layer** that mounts on top of the `dash-backend`
core application. The core provides multi-tenancy, authentication, billing,
permissions and other cross-cutting infrastructure. Everything that is specific
to *your* business (products, catalogs, orders, lab management, delivery, etc.)
lives here.

This package is intentionally **minimal**: it is a clean starting point for a new
domain. None of the e-commerce/operational business logic from other domains
(e.g. `kitchntabs-backend-domain`, `fablabos-backend-domain`) is included here —
only the essential model and provider extension points every domain needs. Look
at those sibling repos for fuller, real-world examples once you outgrow this
scaffold.

## How it mounts

The core maps the `Domain\` PHP namespace to this folder's `app/` and
`database/` directories (see the core `composer.json` autoload section: this
repo's contents are checked out/copied to `domain/` inside the core). At boot
the core discovers and loads:

- **Service providers** in `app/Providers/` (e.g. `AppDomainServiceProvider`).
- **API routes** via `routes/api.php`, which `require`s every file in
  `routes/api/*.php`.
- **Migrations** in `database/migrations/`.
- **Seeders** in `database/seeders/`.
- **Factories** in `database/factories/`.
- **Translations** in `resources/lang/`.
- **Config** in `config/*.php`, merged into the matching core config key via
  `mergeConfigFrom()` (fills in missing keys only — see "Tenant settings"
  below for why that's not enough for array values you need to *append* to).

## Directory layout

```
app/
  Console/Commands/   Domain artisan commands
  Events/              Domain events
  Http/
    Controllers/       Domain API controllers (extend ReactAdminBaseController)
    Resources/         API resources / transformers
    Request/           Form requests / validators
  Jobs/                Queued jobs
  Models/
    Extended/          Core model extensions (User, Tenant, Role - see below)
  Notifications/       Notifications
  Policies/            Authorization policies
  Providers/           Service providers wired into the core
  Services/            Domain services / business logic
config/                Domain config files
database/
  factories/           Model factories
  migrations/          Domain migrations
  seeders/             Domain seeders
  data/                Permission & role-permission JSON used by core seeders
routes/
  api.php              Loads routes/api/*.php
  api/app.php           Example "app" route group (LogController)
  web.php              Web routes
  channels.php         Broadcast channels
resources/lang/        Translations (en, es)
tests/                 Domain test suite (Domain phpunit suite in core)
```

## Extending the core models (`app/Models/Extended/`)

Core ships `App\Models\{User,Tenant,Role}`. The domain layer doesn't replace
them system-wide — there's no global "use this class instead" switch for
Tenant or Role. Instead, **domain code (controllers, policies, services under
`Domain\App\*`) explicitly references the `Domain\App\Models\Extended\*`
subclasses**, while core code keeps using its own base classes directly. The
two stay in sync because the Extended classes simply `extends` the core ones.

This repo ships three minimal extension stubs as templates:

- **`Extended/User.php`** — overrides `getMorphClass()` to pin it back to
  `'App\Models\User'`. Without this, rows created through the Extended
  subclass get a different morph type (`model_type`/`notifiable_type`) than
  rows created through the base class, and Spatie Permission / notification
  lookups for the *same logical user* silently stop matching depending on
  which class happened to create/query them.
- **`Extended/Tenant.php`** — empty by default; core's `Tenant` already
  implements media, currencies, languages, settings, schedules and cascading
  deletes, so don't duplicate any of that here. The docblock shows the merge
  pattern needed if you add `$fillable`/`$casts` entries (a child class
  redeclaring those properties replaces the parent's, so merge them in
  `initializeTenant()` instead of just listing your own).
- **`Extended/Role.php`** — empty by default; add domain-specific
  `LEVEL_*`/`NAME_*` constants above core's `LEVEL_SYSTEM_ADMIN` (0) /
  `LEVEL_TENANCY_ADMIN` (1) / `LEVEL_TENANT_ADMIN` (2) / `LEVEL_NORMAL_USER`
  (3).

All three override `newFactory()`, pointing back at the core's own factory
(`Database\Factories\{User,Tenant}Factory`). `HasFactory`'s default factory
discovery guesses a factory class from the *model's own* namespace, which
fails once the model lives under `Domain\App\Models\Extended\*` — you'll hit
"Class ...Factory not found" the first time a test or seeder calls
`User::factory()` without this override.

## Service providers

### `AppDomainServiceProvider`

Extends core's `AppServiceProvider` and is always loaded directly by the
core's `domain/app/Providers` autodiscovery.

- **Register nested providers in `register()`, never in `boot()`.** A
  provider registered during another provider's `boot()` phase fires its
  `$this->commands()` call (`Artisan::starting` → `resolveCommands`) at a
  point where the console app's container reference is null on stable
  Laravel 11.44, crashing `artisan package:discover` during the image build
  with `Call to a member function make() on null`. This is why
  `CommandServiceProvider` is registered from `register()` here.
- `mergeDomainTenantSettings()` shows the pattern for *appending* to an array
  config value the core already defines (`tenants.setting_formats`).
  `mergeConfigFrom()` only fills in entirely-missing config keys — since core
  already defines `setting_formats`, a package merge would never add to it
  (and naively assigning over it would clobber the core's entries). Add your
  own `config/your_file.php` to the `$files` array to extend this.

### `AuthServiceProvider`

Core's `AppServiceProvider::loadServiceProviders()` registers
`Domain\App\Providers\AuthServiceProvider` **instead of** (not in addition
to) the core's `App\Providers\AuthServiceProvider`, purely because this class
exists (`class_exists("Domain\App\Providers\AuthServiceProvider")`). That
auto-override-by-convention applies to any core provider basename you choose
to shadow under `Domain\App\Providers\*`.

Two consequences to watch for, both already handled in this scaffold's
`AuthServiceProvider`:

- **Always call `parent::boot()`.** Core's `AuthServiceProvider::boot()` sets
  up the password-reset and email-verification URL builders (pointed at
  `FRONTEND_URL`) and a `Collection::paginate` macro. Skipping `parent::boot()`
  silently drops all of that the moment a domain `AuthServiceProvider` exists.
- **Don't redeclare the inherited `$policies` property.** It would replace —
  not merge with — the core's policy mappings, since `registerPolicies()`
  just reads whatever `$this->policies` resolves to on the final class. Add
  domain policies to `$domainPolicies` instead; they're registered via
  `Gate::policy()` in `boot()` without touching the inherited property.

### `CommandServiceProvider`

Auto-loads every class file under `domain/app/Console/Commands` as an Artisan
command. Guarded with `File::isDirectory()` so a domain with no custom
commands yet doesn't error.

## Getting started

1. **Add a model** under `app/Models/` using the `Domain\App\Models` namespace.
   Use the `HasUuids` trait with a UUIDv7 primary key to match core conventions:

   ```php
   namespace Domain\App\Models;

   use Illuminate\Database\Eloquent\Model;
   use Illuminate\Database\Eloquent\Concerns\HasUuids;
   use Illuminate\Support\Str;

   class Widget extends Model
   {
       use HasUuids;

       public function newUniqueId(): string
       {
           return (string) Str::uuid7();
       }
   }
   ```

2. **Add a migration** in `database/migrations/` to create the backing table.

3. **Add a controller** under `app/Http/Controllers/API/` (you can extend
   `ReactAdminBaseController` to get the standard list/get/create/update/delete
   behaviour the core front-end expects).

4. **Register routes** by creating a file in `routes/api/` (it is auto-required
   by `routes/api.php`).

5. **Run the domain test suite** from the core:

   ```bash
   php artisan test --testsuite=Domain
   ```

## Notes

- Keep infrastructure concerns (tenancy, auth, billing, payments, currencies,
  system marketplaces / points of sale) in the core. This layer should only
  contain business/domain logic plus the `Extended/*` model overrides needed
  to hook into it.
- Lookup/reference models that other domains add (`Country`, `Region`,
  `Commune`, `Currency`, `Language`, email-tracking models, etc.) are
  business-specific, not part of this minimal scaffold. Copy them from a
  sibling domain only if your business actually needs that geography/feature.
- The core remains fully functional with this minimal domain mounted; the
  `Core` test suite does not depend on any domain models existing.
