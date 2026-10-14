/**
 * Supported order types, as stored in the hidden `_order_type` cart property.
 *
 * @type {{EatIn: 'eat_in', Takeaway: 'takeaway'}}
 */
export const ORDER_TYPES = Object.freeze({
    EatIn: 'eat_in',
    Takeaway: 'takeaway',
});

/**
 * Order type used when neither the cart nor the backend provide a valid value.
 */
export const FALLBACK_ORDER_TYPE = ORDER_TYPES.Takeaway;

/**
 * Hidden, machine-readable cart property key (source of truth for the Cart Transform function).
 */
export const ORDER_TYPE_PROPERTY_KEY = '_order_type';

/**
 * Visible cart property key, printed on receipts.
 */
export const ORDER_TYPE_LABEL_PROPERTY_KEY = 'Order type';

/**
 * Fixed receipt labels written to the visible `Order type` cart property.
 * These are part of the data contract and must not be translated.
 *
 * @type {Readonly<Record<string, string>>}
 */
const RECEIPT_LABELS = Object.freeze({
    [ORDER_TYPES.EatIn]: 'Eat In',
    [ORDER_TYPES.Takeaway]: 'Takeaway',
});

/**
 * Checks whether a value is one of the supported order types.
 *
 * @param {unknown} value - The value to check.
 * @returns {boolean} True when the value is `eat_in` or `takeaway`.
 * @example
 * isValidOrderType('eat_in') // true
 * isValidOrderType('dine_in') // false
 */
export function isValidOrderType(value) {
    return Object.values(ORDER_TYPES).includes(/** @type {any} */ (value));
}

/**
 * Reads the order type explicitly set on the cart, if any.
 *
 * @param {Record<string, string> | undefined | null} cartProperties - The cart-level properties.
 * @returns {string | null} The cart's order type, or null when absent or invalid.
 */
export function getCartOrderType(cartProperties) {
    const cartOrderType = cartProperties?.[ORDER_TYPE_PROPERTY_KEY];

    if (isValidOrderType(cartOrderType) === true) {
        return cartOrderType;
    }

    return null;
}

/**
 * Resolves the effective order type: the cart's own value first, then the shop default,
 * then the hard fallback (`takeaway`).
 *
 * @param {Record<string, string> | undefined | null} cartProperties - The cart-level properties.
 * @param {unknown} defaultOrderType - The shop's configured default order type.
 * @returns {string} The effective order type.
 * @example
 * resolveOrderType({ _order_type: 'eat_in' }, 'takeaway') // 'eat_in'
 * resolveOrderType({}, 'eat_in') // 'eat_in'
 * resolveOrderType({}, undefined) // 'takeaway'
 */
export function resolveOrderType(cartProperties, defaultOrderType) {
    const cartOrderType = getCartOrderType(cartProperties);

    if (cartOrderType !== null) {
        return cartOrderType;
    }

    if (isValidOrderType(defaultOrderType) === true) {
        return /** @type {string} */ (defaultOrderType);
    }

    return FALLBACK_ORDER_TYPE;
}

/**
 * Returns the opposite order type.
 *
 * @param {string} orderType - The current order type.
 * @returns {string} `takeaway` for `eat_in`, otherwise `eat_in`.
 */
export function toggleOrderType(orderType) {
    if (orderType === ORDER_TYPES.EatIn) {
        return ORDER_TYPES.Takeaway;
    }

    return ORDER_TYPES.EatIn;
}

/**
 * Returns the fixed receipt label for an order type.
 *
 * @param {string} orderType - The order type.
 * @returns {string} `Eat In` or `Takeaway`.
 */
export function getReceiptLabel(orderType) {
    return RECEIPT_LABELS[orderType] ?? RECEIPT_LABELS[FALLBACK_ORDER_TYPE];
}

/**
 * Builds the cart properties that record an order type on the cart.
 *
 * @param {string} orderType - The order type to record.
 * @returns {Record<string, string>} The hidden and visible order type properties.
 * @example
 * buildOrderTypeCartProperties('eat_in')
 * // { _order_type: 'eat_in', 'Order type': 'Eat In' }
 */
export function buildOrderTypeCartProperties(orderType) {
    return {
        [ORDER_TYPE_PROPERTY_KEY]: orderType,
        [ORDER_TYPE_LABEL_PROPERTY_KEY]: getReceiptLabel(orderType),
    };
}

/**
 * Extracts a valid default order type from the backend `/api/pos/config` response body.
 *
 * @param {unknown} responseBody - The parsed JSON response body.
 * @returns {string | null} The default order type, or null when missing or invalid.
 */
export function parseDefaultOrderType(responseBody) {
    const defaultOrderType = /** @type {{defaultOrderType?: unknown} | null | undefined} */ (responseBody)?.defaultOrderType;

    if (isValidOrderType(defaultOrderType) === true) {
        return /** @type {string} */ (defaultOrderType);
    }

    return null;
}
