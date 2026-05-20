<?php

namespace Domain\App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Providers\AuthServiceProvider as BaseAuthServiceProvider;

class AuthServiceProvider extends BaseAuthServiceProvider
{
    /**
     * Domain  policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [

    ];


    public function boot()
    {
            $this->registerPolicies();

    }
}
