/**
 * Backend endpoint that returns the shop's POS configuration (`{"defaultOrderType": "takeaway"}`).
 *
 * This is deliberately a RELATIVE URL. POS resolves relative `fetch()` URLs against the app's
 * `application_url` in `shopify.app.toml` (requires POS 10.6.0+ and api_version 2025-07+), so the
 * same bundle automatically targets the right backend in every environment (local, testing,
 * acceptance, production) without a build-time variable.
 *
 * Only replace this with an absolute URL (e.g. `https://example.com/api/pos/config`) if the
 * Laravel backend is ever hosted on a different domain from `application_url`; in that case it
 * must be set per environment.
 *
 * @see https://shopify.dev/docs/api/pos-ui-extensions/latest (App authentication)
 */
export const POS_CONFIG_URL = '/api/pos/config';

/**
 * `shopify.storage` key under which the last known shop default order type is cached,
 * so the tile still shows the right default when the backend can't be reached.
 */
export const DEFAULT_ORDER_TYPE_STORAGE_KEY = 'defaultOrderType';
