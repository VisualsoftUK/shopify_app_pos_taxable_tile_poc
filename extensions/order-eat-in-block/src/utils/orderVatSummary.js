/**
 * Supported order types, as stored in the `$app.order_type` order metafield.
 *
 * @type {{EatIn: 'eat_in', Takeaway: 'takeaway'}}
 */
export const ORDER_TYPES = Object.freeze({
    EatIn: 'eat_in',
    Takeaway: 'takeaway',
});

/**
 * Shopify's placeholder variant title for single-variant products.
 */
const DEFAULT_VARIANT_TITLE = 'Default Title';

/**
 * GraphQL query reading the app-owned eat-in VAT metafields for an order.
 */
export const ORDER_VAT_METAFIELDS_QUERY = `query OrderEatInVatMetafields($orderId: ID!) {
    order(id: $orderId) {
        id
        currencyCode
        orderType: metafield(namespace: "$app", key: "order_type") {
            value
        }
        eatInTotal: metafield(namespace: "$app", key: "eat_in_total") {
            jsonValue
        }
        zeroRatedTotal: metafield(namespace: "$app", key: "zero_rated_total") {
            jsonValue
        }
        vatBreakdown: metafield(namespace: "$app", key: "vat_breakdown") {
            jsonValue
        }
    }
}`;

/**
 * @typedef {object} Money
 * @property {number} amount - The decimal amount.
 * @property {string} currencyCode - ISO 4217 currency code.
 */

/**
 * @typedef {object} BreakdownRow
 * @property {string} key - Stable key for rendering.
 * @property {string} productName - Product title, with the variant title when meaningful.
 * @property {string} sku - Variant SKU (may be empty).
 * @property {number} quantity - Units sold.
 * @property {number} lineTotal - Line total, excluding the eat-in charge.
 * @property {number} eatInCharge - Eat-in charge applied to the line (0 for takeaway).
 * @property {string} upliftPercent - Uplift percentage applied (e.g. "20.00").
 */

/**
 * @typedef {object} OrderVatSummary
 * @property {string} orderType - `eat_in`, `takeaway`, or the raw stored value.
 * @property {string} currencyCode - Currency used to format every amount in the summary.
 * @property {number | null} eatInTotal - Total eat-in charges, or null when not recorded.
 * @property {number | null} zeroRatedTotal - Total of zero-rated dynamic lines, or null when not recorded.
 * @property {BreakdownRow[]} breakdownRows - Per-line breakdown.
 */

/**
 * Converts a value to a finite number.
 *
 * @param {unknown} value - A number or numeric string.
 * @returns {number | null} The number, or null when the value isn't numeric.
 */
function toFiniteNumber(value) {
    if (value === null || value === undefined || value === '') {
        return null;
    }

    const numericValue = Number(value);

    return Number.isFinite(numericValue) === true ? numericValue : null;
}

/**
 * Parses the `jsonValue` of a `money` metafield.
 *
 * @param {unknown} jsonValue - The metafield's jsonValue, e.g. `{"amount":"1.38","currency_code":"GBP"}`.
 * @returns {Money | null} The parsed money, or null when absent or malformed.
 * @example
 * parseMoneyMetafield({ amount: '1.38', currency_code: 'GBP' }) // { amount: 1.38, currencyCode: 'GBP' }
 */
export function parseMoneyMetafield(jsonValue) {
    if (jsonValue === null || typeof jsonValue !== 'object') {
        return null;
    }

    const moneyValue = /** @type {Record<string, unknown>} */ (jsonValue);
    const amount = toFiniteNumber(moneyValue.amount);
    const currencyCode = moneyValue.currency_code ?? moneyValue.currencyCode;

    if (amount === null || typeof currencyCode !== 'string' || currencyCode === '') {
        return null;
    }

    return { amount, currencyCode };
}

/**
 * Builds a display name from product and variant titles, omitting Shopify's "Default Title".
 *
 * @param {unknown} productTitle - The product title.
 * @param {unknown} variantTitle - The variant title.
 * @returns {string} e.g. "Latte - Large" or "Cheese Sandwich".
 */
export function formatProductName(productTitle, variantTitle) {
    const product = typeof productTitle === 'string' ? productTitle.trim() : '';
    const variant = typeof variantTitle === 'string' ? variantTitle.trim() : '';

    if (variant === '' || variant === DEFAULT_VARIANT_TITLE) {
        return product;
    }

    if (product === '') {
        return variant;
    }

    return `${product} - ${variant}`;
}

/**
 * Parses the `jsonValue` of the `$app.vat_breakdown` metafield into display rows.
 * Malformed entries are skipped.
 *
 * @param {unknown} jsonValue - The metafield's jsonValue (an array of breakdown entries).
 * @returns {BreakdownRow[]} The breakdown rows.
 */
export function parseBreakdown(jsonValue) {
    if (Array.isArray(jsonValue) === false) {
        return [];
    }

    return /** @type {unknown[]} */ (jsonValue)
        .filter((entry) => entry !== null && typeof entry === 'object')
        .map((entry, index) => {
            const breakdownEntry = /** @type {Record<string, unknown>} */ (entry);

            return {
                key: `${breakdownEntry.variantId ?? 'line'}-${index}`,
                productName: formatProductName(breakdownEntry.productTitle, breakdownEntry.variantTitle),
                sku: typeof breakdownEntry.sku === 'string' ? breakdownEntry.sku : '',
                quantity: toFiniteNumber(breakdownEntry.quantity) ?? 0,
                lineTotal: toFiniteNumber(breakdownEntry.lineTotal) ?? 0,
                eatInCharge: toFiniteNumber(breakdownEntry.eatInCharge) ?? 0,
                upliftPercent: typeof breakdownEntry.upliftPercent === 'string' ? breakdownEntry.upliftPercent : '',
            };
        });
}

/**
 * Formats an amount as currency with Intl.NumberFormat.
 *
 * @param {number} amount - The decimal amount.
 * @param {string} currencyCode - ISO 4217 currency code.
 * @param {string | undefined} [locale] - BCP 47 locale; defaults to the runtime locale.
 * @returns {string} The formatted amount, e.g. "£1.38".
 * @example
 * formatMoney(1.38, 'GBP', 'en-GB') // "£1.38"
 */
export function formatMoney(amount, currencyCode, locale = undefined) {
    try {
        return new Intl.NumberFormat(locale, { style: 'currency', currency: currencyCode }).format(amount);
    } catch (error) {
        console.error(`Failed to format ${amount} as currency "${currencyCode}"`, error);

        return `${amount.toFixed(2)} ${currencyCode}`;
    }
}

/**
 * Builds the eat-in VAT summary for an order from its app-owned metafields.
 *
 * @param {any} order - The `order` object returned by ORDER_VAT_METAFIELDS_QUERY.
 * @returns {OrderVatSummary | null} The summary, or null when the order hasn't been processed yet
 *     (no `$app.order_type` metafield).
 */
export function buildOrderVatSummary(order) {
    const orderType = order?.orderType?.value;

    if (typeof orderType !== 'string' || orderType === '') {
        return null;
    }

    const eatInTotal = parseMoneyMetafield(order?.eatInTotal?.jsonValue);
    const zeroRatedTotal = parseMoneyMetafield(order?.zeroRatedTotal?.jsonValue);

    return {
        orderType,
        currencyCode: eatInTotal?.currencyCode ?? zeroRatedTotal?.currencyCode ?? order?.currencyCode ?? 'GBP',
        eatInTotal: eatInTotal?.amount ?? null,
        zeroRatedTotal: zeroRatedTotal?.amount ?? null,
        breakdownRows: parseBreakdown(order?.vatBreakdown?.jsonValue),
    };
}
