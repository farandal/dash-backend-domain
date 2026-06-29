<?php

namespace Domain\App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Providers\AuthServiceProvider as BaseAuthServiceProvider;

class AuthServiceProvider extends BaseAuthServiceProvider
{
    /**
     * Domain policy mappings, registered in addition to the core's.
     *
     * Don't redeclare the inherited $policies property here. This provider
     * replaces (not supplements) App\Providers\AuthServiceProvider once it
     * exists -- see AppServiceProvider::loadServiceProviders(), which
     * registers this class instead of the core one by class_exists()
     * convention. Redeclaring $policies would silently drop the core's
     * mappings (e.g. Log::class => LogPolicy::class) since registerPolicies()
     * just reads whatever $this->policies resolves to. Add domain policies
     * to $domainPolicies instead and they'll be merged in on boot().
     *
     * @var array<class-string, class-string>
     */
    protected $domainPolicies = [
        // \Domain\App\Models\Widget::class => \Domain\App\Policies\WidgetPolicy::class,
    ];

    public function boot()
    {
        // Must call parent::boot(): it sets up the password-reset and
        // email-verification URL builders and the Collection::paginate
        // macro. Skipping it (the original scaffold did) silently drops
        // that core auth wiring whenever a domain AuthServiceProvider exists.
        parent::boot();

        foreach ($this->domainPolicies as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }
}
