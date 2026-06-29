<?php

namespace Domain\App\Models\Extended;

use App\Models\User as BaseUser;
use Database\Factories\UserFactory;

/**
 * Domain extension point for the core User model.
 *
 * Add domain-specific relations, attributes or methods here. Core code keeps
 * using App\Models\User directly; domain code (controllers, policies,
 * services under Domain\App\*) should reference this class instead.
 */
class User extends BaseUser
{
    /**
     * Keep the polymorphic type stable as 'App\Models\User'.
     *
     * Spatie Permission, notifications and any other morph-mapped relation
     * write the resolving class's FQCN into the DB (e.g. model_type /
     * notifiable_type). Without this override, rows created via this Extended
     * class would be stamped 'Domain\App\Models\Extended\User' while rows
     * created via the base class are stamped 'App\Models\User' -- the same
     * logical user ends up with inconsistent morph types and permission /
     * notification lookups silently stop matching.
     */
    public function getMorphClass()
    {
        return 'App\Models\User';
    }

    /**
     * HasFactory's default discovery guesses the factory from this class's
     * own namespace (Domain\Database\Factories\Models\Extended\UserFactory),
     * which doesn't exist. Point it back at the core factory explicitly.
     */
    protected static function newFactory()
    {
        return UserFactory::new();
    }
}
