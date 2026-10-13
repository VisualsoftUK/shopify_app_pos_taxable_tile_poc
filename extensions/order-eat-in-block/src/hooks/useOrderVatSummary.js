import { useEffect, useState } from 'preact/hooks';
import { ORDER_VAT_METAFIELDS_QUERY, buildOrderVatSummary } from '../utils/orderVatSummary.js';

/**
 * @typedef {object} OrderVatSummaryState
 * @property {'loading' | 'not_processed' | 'loaded' | 'error'} status - The load status.
 * @property {import('../utils/orderVatSummary.js').OrderVatSummary | null} summary - The summary, when loaded.
 */

/**
 * Loads the eat-in VAT summary for an order through the extension's direct Admin API access.
 *
 * @param {string | undefined} orderId - The order GID (e.g. `gid://shopify/Order/1`).
 * @returns {OrderVatSummaryState} The current load state and summary.
 */
export function useOrderVatSummary(orderId) {
    const [state, setState] = useState(/** @type {OrderVatSummaryState} */ ({ status: 'loading', summary: null }));

    useEffect(() => {
        let isMounted = true;

        /**
         * Queries the order's `$app` metafields and maps them to a summary.
         *
         * @returns {Promise<void>}
         */
        async function loadOrderVatSummary() {
            try {
                if (orderId === undefined) {
                    throw new Error('No order ID was provided by the admin.order-details.block.render target');
                }

                const result = await shopify.query(ORDER_VAT_METAFIELDS_QUERY, {
                    variables: { orderId },
                });

                if ((result.errors?.length ?? 0) > 0) {
                    throw new Error(`Admin API returned errors: ${JSON.stringify(result.errors)}`);
                }

                const summary = buildOrderVatSummary(/** @type {any} */ (result.data)?.order);

                if (isMounted === true) {
                    setState({ status: summary === null ? 'not_processed' : 'loaded', summary });
                }
            } catch (error) {
                console.error(`Failed to load eat-in VAT metafields for order ${orderId}`, error);

                if (isMounted === true) {
                    setState({ status: 'error', summary: null });
                }
            }
        }

        loadOrderVatSummary();

        return () => {
            isMounted = false;
        };
    }, [orderId]);

    return state;
}
