<?php

namespace App\Framework\Providers;

use Visualsoft\LaravelSecurity\Framework\Providers\AuthServiceProvider as BaseAuthServiceProvider;

class AuthServiceProvider extends BaseAuthServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        parent::boot();
    }
}
