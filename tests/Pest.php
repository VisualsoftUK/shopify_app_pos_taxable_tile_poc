<?php

use App\Domain\Models\User;
use Illuminate\Foundation\Testing\LazilyRefreshDatabase;
use Tests\TestCase;
use Visualsoft\ShopifyBrowserTesting\Pest as ShopifyBrowser;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(
    TestCase::class,
    LazilyRefreshDatabase::class,
)->in('Feature');

ShopifyBrowser::admin()->in('Browser/Admin');

pest()->browser()->timeout(15000);

/*
|--------------------------------------------------------------------------
| Test Impact Analysis
|--------------------------------------------------------------------------
|
| Local runs only, and the guard has to be an environment variable rather
| than "--no-tia". Pest strips the TIA flags before paratest spawns its
| workers, so a worker re-reads this file, sees "locally()", and re-enables
| itself even when the parent stood down; it then aborts if git is missing.
| Pest's own CI detection keys off the "--ci" argument and is stripped the
| same way. Environment variables are inherited by the workers, so gating
| on CI is the only signal every process agrees on.
|
| "filtered" narrows PHPUnit to the affected files, and self-disables for
| explicit paths and "--coverage". Locally, use "--no-tia" for a guaranteed
| full run, or "--tia --fresh" to rebuild a stale graph after a refactor.
|
*/

if (filter_var(getenv('CI'), FILTER_VALIDATE_BOOL) === false) {
    pest()->tia()
        ->locally()
        ->filtered();
}

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/**
 * Create an embedded-admin shop user for browser tests.
 *
 * @param  array<string, mixed>  $features
 */
function createTemplateShop(array $features = []): User
{
    return test()->createShopifyAdminUser($features);
}

/**
 * Named admin route URL with Shopify session-token query params for authenticated requests.
 *
 * @param  array<string, mixed>  $parameters
 */
function shopifyRoute(string $route, array $parameters = []): string
{
    return test()->routeWithShopifyToken($route, $parameters);
}
