<?php

it('loads the dashboard for a shop with an active mantle plan', function () {
    createTemplateShop();

    $page = adminVisit($this->shopifyAdminAppUrl());

    $page->assertPresent('#app')
        ->assertSee(adminLocale('pages.dashboard'))
        ->assertSee(adminLocale('pages.how_it_works'))
        ->assertSee(adminLocale('pages.plans'))
        ->assertPresent('a[href*="/app-pricing/plans-redirect"]')
        ->assertNoJavaScriptErrors();
});
