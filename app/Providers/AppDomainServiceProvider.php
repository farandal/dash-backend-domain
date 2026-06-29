<?php

namespace Domain\App\Providers;

use Laravel\Horizon\Horizon;
use Illuminate\Support\Facades\Auth;
use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\Response;

class AppDomainServiceProvider extends AppServiceProvider
{
    /**
     * Register any application services.
     *
     * Register additional domain singletons/bindings here, e.g.:
     *   $this->app->singleton(MyDomainService::class, fn ($app) => new MyDomainService());
     *
     * @return void
     */
    public function register()
    {
        // Must be registered here, not in boot(). A provider registered during
        // another provider's boot() phase fires its $this->commands() call
        // (Artisan::starting -> resolveCommands) at a point where the console
        // app's container reference is null on stable Laravel 11.44, which
        // crashes `artisan package:discover` during the image build with
        // "Call to a member function make() on null".
        $this->app->register(CommandServiceProvider::class);
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Horizon::auth(function () {
            return \in_array(1, Auth::user()->role_ids) ? true : false;
        });

        Response::macro('downloadFile', function ($content, $filename, $contentType) {
            $headers = [
                'Content-type'        => $contentType,
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            return Response::make($content, 200, $headers);
        });

        $this->mergeDomainTenantSettings();
    }

    /**
     * Append domain-defined tenant setting formats onto the core's
     * `tenants.setting_formats` array.
     *
     * mergeConfigFrom() only fills in config keys that are entirely missing;
     * it can't safely append onto an array key the core already defines
     * (one side would just clobber the other). This does an explicit
     * array_merge instead. Add files to $files as the domain grows.
     *
     * @return void
     */
    private function mergeDomainTenantSettings(): void
    {
        $files = [
            base_path('domain/config/tenant_settings.php'),
        ];

        foreach ($files as $path) {
            if (!file_exists($path)) {
                continue;
            }

            $domain = require $path;

            config(['tenants.setting_formats' => array_merge(
                config('tenants.setting_formats', []),
                $domain['setting_formats'] ?? []
            )]);
        }
    }
}
