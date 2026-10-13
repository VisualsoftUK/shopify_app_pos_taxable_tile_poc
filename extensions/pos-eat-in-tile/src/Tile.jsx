import '@shopify/ui-extensions/preact';
import { render } from 'preact';
import { useState } from 'preact/hooks';
import { useCurrentCart } from './hooks/useCurrentCart.js';
import { useDefaultOrderType } from './hooks/useDefaultOrderType.js';
import {
    ORDER_TYPES,
    buildOrderTypeCartProperties,
    getCartOrderType,
    resolveOrderType,
    toggleOrderType,
} from './utils/orderType.js';

/**
 * Translation keys for the staff-facing label of each order type.
 *
 * @type {Readonly<Record<string, string>>}
 */
const ORDER_TYPE_TRANSLATION_KEYS = Object.freeze({
    [ORDER_TYPES.EatIn]: 'order_type_eat_in',
    [ORDER_TYPES.Takeaway]: 'order_type_takeaway',
});

/**
 * Extension entry point for the `pos.home.tile.render` target.
 *
 * @returns {Promise<void>}
 */
export default async () => {
    render(<EatInTile />, document.body);
};

/**
 * Smart grid tile that shows the cart's current order type and toggles it between
 * Eat In and Takeaway when tapped.
 *
 * @returns {import('preact').JSX.Element} The tile.
 */
function EatInTile() {
    const { i18n } = shopify;
    const currentCart = useCurrentCart();
    const defaultOrderType = useDefaultOrderType();
    const [isUpdating, setIsUpdating] = useState(false);

    const cartOrderType = getCartOrderType(currentCart.properties);
    const currentOrderType = resolveOrderType(currentCart.properties, defaultOrderType);
    const currentOrderTypeLabel = i18n.translate(ORDER_TYPE_TRANSLATION_KEYS[currentOrderType]);
    const isCartEditable = currentCart.editable ?? true;

    const subheading = cartOrderType === null
        ? i18n.translate('subheading_default', { orderType: currentOrderTypeLabel })
        : currentOrderTypeLabel;

    /**
     * Toggles the cart's order type by writing the `_order_type` and `Order type` cart properties.
     *
     * @returns {Promise<void>}
     */
    async function handleToggleOrderType() {
        if (isUpdating === true) {
            return;
        }

        const nextOrderType = toggleOrderType(currentOrderType);
        setIsUpdating(true);

        try {
            await shopify.cart.addCartProperties(buildOrderTypeCartProperties(nextOrderType));
            shopify.toast.show(i18n.translate('toast_updated', {
                orderType: i18n.translate(ORDER_TYPE_TRANSLATION_KEYS[nextOrderType]),
            }));
        } catch (error) {
            console.error(`Failed to set the cart order type from "${currentOrderType}" to "${nextOrderType}"`, error);
            shopify.toast.show(i18n.translate('toast_update_failed'));
        } finally {
            setIsUpdating(false);
        }
    }

    return (
        <s-tile
            heading={i18n.translate('tile_heading')}
            subheading={subheading}
            tone={currentOrderType === ORDER_TYPES.EatIn ? 'accent' : 'auto'}
            disabled={isCartEditable === false || isUpdating === true}
            onClick={handleToggleOrderType}
        />
    );
}
