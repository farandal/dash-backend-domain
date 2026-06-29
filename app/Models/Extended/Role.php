<?php

namespace Domain\App\Models\Extended;

use App\Models\Role as BaseRole;

/**
 * Domain extension point for the core Role model.
 *
 * Core defines LEVEL_SYSTEM_ADMIN (0), LEVEL_TENANCY_ADMIN (1),
 * LEVEL_TENANT_ADMIN (2) and LEVEL_NORMAL_USER (3). Add domain-specific role
 * levels/names above that, e.g.:
 *
 * public const LEVEL_STAFF = 10;
 * public const NAME_STAFF  = 'Staff';
 */
class Role extends BaseRole
{
}
