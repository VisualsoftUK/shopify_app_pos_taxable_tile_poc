import { useEffect, useState } from 'preact/hooks';

/**
 * Tracks the current POS cart, re-rendering whenever it changes (including changes made
 * outside this extension, e.g. properties set by another app or the cart being cleared).
 *
 * @returns {import('@shopify/ui-extensions/point-of-sale').Cart} The current cart.
 */
export function useCurrentCart() {
    const [currentCart, setCurrentCart] = useState(shopify.cart.current.value);

    useEffect(() => {
        const unsubscribe = shopify.cart.current.subscribe((updatedCart) => {
            setCurrentCart(updatedCart);
        });

        return unsubscribe;
    }, []);

    return currentCart;
}
