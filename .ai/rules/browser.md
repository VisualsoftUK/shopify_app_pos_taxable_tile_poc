---
paths:
  - 'tests/Browser/**'
---

# Browser

Admin browser tests use `visualsoft/vs-shopify-browser-testing` ^1.26 with Pest 5 + Playwright. Boot via `ShopifyBrowser::admin()->in('Browser/Admin')` in `tests/Pest.php`.

## Local runs

```bash
npm install && npx playwright install chromium
npm run prod   # or npm run dev — admin assets must exist
./vendor/bin/pest tests/Browser/Admin --compact --no-tia
# or
npm run test:browser
```

Test Impact Analysis is enabled locally (`pest()->tia()->locally()->filtered()`). Use `--no-tia` for a full run. CI sets `CI=true` and `--no-tia` on the Pest+pcov job.

## CI

Semaphore runs `vendor/visualsoft/vs-shopify-browser-testing/scripts/semaphore-admin-browser.sh` in a parallel job after `composer-install-dev` + `npm-run-prod`. The main Pest+pcov job excludes `--testsuite=Browser`.

Failure screenshots stay on Semaphore artifacts via `semaphore-epilogue-browser-failures.sh`. Never `gh release create` for CI dumps.

## Playwright version

Pin `"playwright": "1.62.1"` exactly in `package.json` — must match browsers in the `php-cli-8.5-playwright` Docker image.

## GraphQL

Stub app-specific Admin GraphQL in `tests/Concerns/InteractsWithGraphQlBrowser.php` only. Do not add browser GraphQL traits to `tests/TestCase.php`. Package defaults cover connector theme/shop lookups.

## Extending the suite

Copy additional baseline tests from blueprint or seasonly when ready (`SmokeTest`, `AuthenticateTest`, `NavSmokeTest`, etc.). Rename `createTemplateShop()` usage and adjust locale keys per page.
