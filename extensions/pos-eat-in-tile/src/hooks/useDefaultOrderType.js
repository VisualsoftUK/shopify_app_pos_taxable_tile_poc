import { useEffect, useState } from 'preact/hooks';
import { DEFAULT_ORDER_TYPE_STORAGE_KEY, POS_CONFIG_URL } from '../config.js';
import { FALLBACK_ORDER_TYPE, isValidOrderType, parseDefaultOrderType } from '../utils/orderType.js';

/**
 * Reads the cached shop default order type from POS extension storage.
 *
 * @returns {Promise<string | null>} The cached default, or null when nothing valid is cached.
 */
async function readCachedDefaultOrderType() {
    try {
        const cachedOrderType = await shopify.storage.get(DEFAULT_ORDER_TYPE_STORAGE_KEY);

        return isValidOrderType(cachedOrderType) === true ? /** @type {string} */ (cachedOrderType) : null;
    } catch (error) {
        console.error(`Failed to read "${DEFAULT_ORDER_TYPE_STORAGE_KEY}" from POS extension storage`, error);

        return null;
    }
}

/**
 * Caches the shop default order type in POS extension storage for offline use.
 *
 * @param {string} defaultOrderType - The default order type to cache.
 * @returns {Promise<void>}
 */
async function cacheDefaultOrderType(defaultOrderType) {
    try {
        await shopify.storage.set(DEFAULT_ORDER_TYPE_STORAGE_KEY, defaultOrderType);
    } catch (error) {
        console.error(`Failed to cache "${DEFAULT_ORDER_TYPE_STORAGE_KEY}" in POS extension storage`, error);
    }
}

/**
 * Fetches the shop default order type from the app backend, authenticated with the POS session token.
 *
 * @returns {Promise<string | null>} The backend default, or null when the request fails or returns no valid value.
 */
async function fetchDefaultOrderType() {
    try {
        const sessionToken = await shopify.session.getSessionToken();
        /** @type {Record<string, string>} */
        const headers = { Accept: 'application/json' };

        if (typeof sessionToken === 'string' && sessionToken !== '') {
            headers.Authorization = `Bearer ${sessionToken}`;
        }

        const response = await fetch(POS_CONFIG_URL, { headers });

        if (response.ok === false) {
            throw new Error(`Unexpected HTTP ${response.status} from ${POS_CONFIG_URL}`);
        }

        return parseDefaultOrderType(await response.json());
    } catch (error) {
        console.error(`Failed to fetch the default order type from ${POS_CONFIG_URL}`, error);

        return null;
    }
}

/**
 * Provides the shop's default order type for carts without an `_order_type` property.
 * Uses the cached value immediately, then refreshes it from the backend; falls back to `takeaway`.
 *
 * @returns {string} The shop default order type.
 */
export function useDefaultOrderType() {
    const [defaultOrderType, setDefaultOrderType] = useState(/** @type {string} */ (FALLBACK_ORDER_TYPE));

    useEffect(() => {
        let isMounted = true;

        /**
         * Loads the cached default first, then replaces it with the backend value when available.
         *
         * @returns {Promise<void>}
         */
        async function loadDefaultOrderType() {
            const cachedOrderType = await readCachedDefaultOrderType();

            if (isMounted === true && cachedOrderType !== null) {
                setDefaultOrderType(cachedOrderType);
            }

            const backendOrderType = await fetchDefaultOrderType();

            if (backendOrderType === null) {
                return;
            }

            await cacheDefaultOrderType(backendOrderType);

            if (isMounted === true) {
                setDefaultOrderType(backendOrderType);
            }
        }

        loadDefaultOrderType();

        return () => {
            isMounted = false;
        };
    }, []);

    return defaultOrderType;
}
