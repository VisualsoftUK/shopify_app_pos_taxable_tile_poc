<?php

namespace App\Application\Http;

use App\Application\Http\Middleware\HandleInertiaRequests;
use Illuminate\Auth\Middleware\AuthenticateWithBasicAuth;
use Illuminate\Auth\Middleware\Authorize;
use Illuminate\Auth\Middleware\EnsureEmailIsVerified;
use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidatePostSize;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Http\Middleware\SetCacheHeaders;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Laravel\Sanctum\Http\Middleware\CheckAbilities;
use Laravel\Sanctum\Http\Middleware\CheckForAnyAbility;
use Spatie\Csp\AddCspHeaders;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\Authenticate;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\EncryptCookies;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\HttpsProtocol;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\PreventRequestsDuringMaintenance;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\RedirectIfAuthenticated;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\SecurityHeaders;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\ShopifyGenericWebhooks;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\TrimStrings;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\TrustProxies;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\ValidateSignature;
use Visualsoft\LaravelSecurity\Framework\Http\Middleware\VerifyCsrfToken;
use Visualsoft\LaravelSecurity\Framework\Presets\WebCspPreset;
use Visualsoft\Shopify\Http\Middleware\ApplyShopScoping;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array<int, class-string|string>
     */
    protected $middleware = [
        // \App\Http\Middleware\TrustHosts::class,
        TrustProxies::class,
        HandleCors::class,
        PreventRequestsDuringMaintenance::class,
        ValidatePostSize::class,
        TrimStrings::class,
        ConvertEmptyStringsToNull::class,
        SecurityHeaders::class,
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array<string, array<int, class-string|string>>
     */
    protected $middlewareGroups = [
        'web' => [
            HttpsProtocol::class,
            EncryptCookies::class,
            AddQueuedCookiesToResponse::class,
            StartSession::class,
            ShareErrorsFromSession::class,
            VerifyCsrfToken::class,
            SubstituteBindings::class,
            AddCspHeaders::class . ':' . WebCspPreset::class,
        ],

        'api' => [
            HttpsProtocol::class,
            // \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
            ThrottleRequests::class . ':api',
            SubstituteBindings::class,
            AddCspHeaders::class,
        ],

        'admin' => [
            StartSession::class,
            'verify.shopify',
            ShareErrorsFromSession::class,
            ApplyShopScoping::class,
            SubstituteBindings::class,
            HandleInertiaRequests::class,
        ],
    ];

    /**
     * The application's middleware aliases.
     *
     * Aliases may be used to conveniently assign middleware to routes and groups.
     *
     * @var array<string, class-string|string>
     */
    protected $middlewareAliases = [
        'auth' => Authenticate::class,
        'auth.basic' => AuthenticateWithBasicAuth::class,
        'auth.session' => AuthenticateSession::class,
        'cache.headers' => SetCacheHeaders::class,
        'can' => Authorize::class,
        'guest' => RedirectIfAuthenticated::class,
        'password.confirm' => RequirePassword::class,
        'signed' => ValidateSignature::class,
        'throttle' => ThrottleRequests::class,
        'verified' => EnsureEmailIsVerified::class,
        'abilities' => CheckAbilities::class,
        'ability' => CheckForAnyAbility::class,
        'shopify_generic_webhooks' => ShopifyGenericWebhooks::class,
    ];
}
