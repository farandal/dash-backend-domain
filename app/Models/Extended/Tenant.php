<?php

namespace Domain\App\Models\Extended;

use App\Models\Tenant as BaseTenant;
use Database\Factories\TenantFactory;

/**
 * Domain extension point for the core Tenant model.
 *
 * Core's Tenant already implements tenancy infrastructure (media, currencies,
 * languages, settings, cascading deletes, schedules, open/close logic, etc.).
 * Don't reimplement any of that here -- only add domain-specific relations,
 * fillable/casts additions, or business attributes.
 */
class Tenant extends BaseTenant
{
    // Add domain-specific fillable/cast entries here. Eloquent's $fillable
    // and $casts are plain property declarations, so a child class can't
    // just redeclare them without losing the parent's -- merge them instead:
    //
    // protected $fillable = ['my_domain_field'];
    // protected $casts = ['my_domain_field' => 'boolean'];
    //
    // protected function initializeTenant()
    // {
    //     $this->fillable = array_merge((new BaseTenant)->getFillable(), $this->fillable);
    //     $this->casts = array_merge((new BaseTenant)->getCasts(), $this->casts);
    // }

    /**
     * Same cross-namespace factory-discovery gotcha as Extended\User.
     */
    protected static function newFactory()
    {
        return TenantFactory::new();
    }
}
