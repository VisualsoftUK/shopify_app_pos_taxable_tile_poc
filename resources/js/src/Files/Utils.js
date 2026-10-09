import { router } from '@inertiajs/react';

/**
 * Shopify OAuth parameters that should be excluded from Inertia requests
 * These are only needed for the initial OAuth flow, not for subsequent API calls
 */
const SHOPIFY_OAUTH_PARAMS = [
    'hmac',
    'host',
    'id_token',
    'session',
    'shop',
    'timestamp',
    'token',
    'embedded'
];

/**
 * Parse a query parameter key to extract the base key
 * e.g., "filter[is_active][]" -> "filter"
 * e.g., "page" -> "page"
 */
function getBaseKey(key) {
    const match = key.match(/^([^[]+)/);
    return match ? match[1] : key;
}

/**
 * Serialize params object to query string, handling nested objects and arrays
 * This mimics how Inertia serializes data
 */
function serializeParams(params, prefix = '') {
    const parts = [];
    
    for (const [key, value] of Object.entries(params)) {
        if (value === null || value === undefined) continue;
        
        const paramKey = prefix ? `${prefix}[${key}]` : key;
        
        if (Array.isArray(value)) {
            // Handle arrays: key[]=value1&key[]=value2
            value.forEach(item => {
                parts.push(`${encodeURIComponent(paramKey + '[]')}=${encodeURIComponent(item)}`);
            });
        } else if (typeof value === 'object' && value !== null) {
            // Handle nested objects recursively
            parts.push(serializeParams(value, paramKey));
        } else {
            // Handle primitives
            parts.push(`${encodeURIComponent(paramKey)}=${encodeURIComponent(value)}`);
        }
    }
    
    return parts.filter(Boolean).join('&');
}

/**
 * Perform a partial reload of Inertia props with clean URL parameters
 * 
 * @param {Object} options - Reload options
 * @param {Array<string>} options.only - Array of prop names to reload
 * @param {Object} options.params - Object of query parameters to add to the URL
 * @param {Object} options.visitOptions - Additional Inertia visit options (onStart, onFinish, etc.)
 * @returns {void}
 */
export function partialReload({ only = [], params = {}, visitOptions = {} }) {
    // Get current search params, excluding Shopify OAuth params
    const currentParams = new URLSearchParams(window.location.search);
    const preservedParams = {};
    
    // Get the base keys from new params that will override existing ones
    const newParamBaseKeys = Object.keys(params).map(key => getBaseKey(key));
    
    currentParams.forEach((value, key) => {
        const baseKey = getBaseKey(key);
        
        // Skip if it's a Shopify OAuth param or if it's being overridden by new params
        if (SHOPIFY_OAUTH_PARAMS.includes(key) === false && newParamBaseKeys.includes(baseKey) === false) {
            // Keep the original flat key structure for preserved params
            if (!preservedParams[key]) {
                preservedParams[key] = [];
            }
            preservedParams[key].push(value);
        }
    });
    
    // Build clean URL
    const url = new URL(window.location.pathname, window.location.origin);
    
    // Add preserved params (flat structure)
    Object.entries(preservedParams).forEach(([key, values]) => {
        values.forEach(value => {
            url.searchParams.append(key, value);
        });
    });
    
    // Add new params (serialize nested objects/arrays properly)
    const serializedNewParams = serializeParams(params);
    if (serializedNewParams) {
        url.search += (url.search ? '&' : '?') + serializedNewParams;
    }

    // Use router.visit with clean URL
    // preserveUrl keeps the browser URL unchanged while making the request
    router.visit(url.href, {
        only,
        preserveState: true,
        preserveScroll: true,
        preserveUrl: true,
        async: true,
        ...visitOptions
    });
}

/**
 *  Upload file
 */
export async function uploadStagedFile(stagedFile, stagedTarget) {
    const { url, parameters, resourceUrl } = stagedTarget;
    const stagedFormData = new FormData();

    parameters.forEach(({ name, value }) => {
        stagedFormData.append(name, value);
    });

    stagedFormData.append('file', stagedFile.file);

    const res = await fetch(url, {
        method: 'POST',
        body: stagedFormData,
    });

    if (!res.ok) {
        throw new Error('Staged file failed to upload');
    }

    stagedFile.resourceUrl = resourceUrl;

    return true;
}
