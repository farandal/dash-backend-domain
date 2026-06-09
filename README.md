# Dash-backend domain layer

This repository is the **domain layer** that mounts on top of the `dash-backend`
core application. The core provides multi-tenancy, authentication, billing,
permissions and other cross-cutting infrastructure. Everything that is specific
to *your* business (products, catalogs, orders, lab management, delivery, etc.)
lives here.

This package is intentionally **minimal**: it is a clean starting point for a new
domain. None of the e-commerce/operational logic from other domains (e.g.
`kitchntabs-domain`) is included here.

## How it mounts

The core maps the `Domain\` PHP namespace to this folder (see the core
`composer.json` autoload section). At boot the core discovers and loads:

- **Service providers** in `app/Providers/` (e.g. `AppDomainServiceProvider`).
- **API routes** via `routes/api.php`, which `require`s every file in
  `routes/api/*.php`.
- **Migrations** in `database/migrations/`.
- **Seeders** in `database/seeders/`.
- **Factories** in `database/factories/`.
- **Translations** in `resources/lang/`.

## Directory layout

```
app/
  Console/Commands/   Domain artisan commands
  Events/             Domain events
  Http/
    Controllers/      Domain API controllers (extend ReactAdminBaseController)
    Resources/        API resources / transformers
    Request/          Form requests / validators
  Jobs/               Queued jobs
  Models/             Eloquent models (namespace Domain\App\Models\*)
  Notifications/      Notifications
  Policies/           Authorization policies
  Providers/          Service providers wired into the core
  Services/           Domain services / business logic
config/               Domain config files
database/
  factories/          Model factories
  migrations/         Domain migrations
  seeders/            Domain seeders
  data/               Permission & role-permission JSON used by core seeders
routes/
  api.php             Loads routes/api/*.php
  api/app.php         Example "app" route group (LogController)
  web.php             Web routes
  channels.php        Broadcast channels
resources/lang/       Translations (en, es)
tests/                Domain test suite (Domain phpunit suite in core)
```

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
  contain business/domain logic.
- The core remains fully functional with this minimal domain mounted; the
  `Core` test suite does not depend on any domain models existing.