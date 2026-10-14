import '@shopify/ui-extensions/preact';
import { render } from 'preact';
import { useOrderVatSummary } from './hooks/useOrderVatSummary.js';
import { ORDER_TYPES, formatMoney } from './utils/orderVatSummary.js';

/**
 * Translation keys for each known order type.
 *
 * @type {Readonly<Record<string, string>>}
 */
const ORDER_TYPE_TRANSLATION_KEYS = Object.freeze({
    [ORDER_TYPES.EatIn]: 'order_type_eat_in',
    [ORDER_TYPES.Takeaway]: 'order_type_takeaway',
});

/**
 * Extension entry point for the `admin.order-details.block.render` target.
 *
 * @returns {Promise<void>}
 */
export default async () => {
    render(<OrderEatInBlock />, document.body);
};

/**
 * Returns the translated label for an order type, or the raw value for unknown types.
 *
 * @param {string} orderType - The stored order type.
 * @returns {string} The display label.
 */
function getOrderTypeLabel(orderType) {
    const translationKey = ORDER_TYPE_TRANSLATION_KEYS[orderType];

    return translationKey === undefined ? orderType : shopify.i18n.translate(translationKey);
}

/**
 * Order details block showing the order type, eat-in charge total, zero-rated total and
 * the per-line eat-in VAT breakdown written by the app after `orders/create`.
 *
 * @returns {import('preact').JSX.Element} The admin block.
 */
function OrderEatInBlock() {
    const { i18n, data } = shopify;
    const orderId = data.selected[0]?.id;
    const { status, summary } = useOrderVatSummary(orderId);

    return (
        <s-admin-block
            heading={i18n.translate('heading')}
            collapsedSummary={summary === null ? undefined : getOrderTypeLabel(summary.orderType)}
        >
            <OrderEatInContent status={status} summary={summary} />
        </s-admin-block>
    );
}

/**
 * Renders the block body for the current load state.
 *
 * @param {object} props - Component props.
 * @param {import('./hooks/useOrderVatSummary.js').OrderVatSummaryState['status']} props.status - The load status.
 * @param {import('./utils/orderVatSummary.js').OrderVatSummary | null} props.summary - The loaded summary.
 * @returns {import('preact').JSX.Element} The block body.
 */
function OrderEatInContent({ status, summary }) {
    const { i18n } = shopify;

    if (status === 'loading') {
        return (
            <s-stack direction="inline" alignItems="center" justifyContent="center">
                <s-spinner accessibilityLabel={i18n.translate('loading')} />
            </s-stack>
        );
    }

    if (status === 'error') {
        return <s-banner tone="critical">{i18n.translate('load_failed')}</s-banner>;
    }

    if (status === 'not_processed' || summary === null) {
        return (
            <s-stack direction="block" gap="small-200">
                <s-text type="strong">{i18n.translate('not_processed')}</s-text>
                <s-text color="subdued">{i18n.translate('not_processed_description')}</s-text>
            </s-stack>
        );
    }

    return <OrderVatSummaryDetails summary={summary} />;
}

/**
 * Renders the totals and breakdown table for a processed order.
 *
 * @param {object} props - Component props.
 * @param {import('./utils/orderVatSummary.js').OrderVatSummary} props.summary - The loaded summary.
 * @returns {import('preact').JSX.Element} The summary details.
 */
function OrderVatSummaryDetails({ summary }) {
    const { i18n } = shopify;
    const { currencyCode } = summary;

    /**
     * Formats an optional amount in the summary currency.
     *
     * @param {number | null} amount - The amount, or null when not recorded.
     * @returns {string} The formatted amount, or an em dash when not recorded.
     */
    function formatAmount(amount) {
        return amount === null ? '—' : formatMoney(amount, currencyCode);
    }

    return (
        <s-stack direction="block" gap="base">
            <s-stack direction="inline" gap="small-200" alignItems="center" justifyContent="space-between">
                <s-text>{i18n.translate('order_type')}</s-text>
                <s-badge tone={summary.orderType === ORDER_TYPES.EatIn ? 'info' : 'neutral'}>
                    {getOrderTypeLabel(summary.orderType)}
                </s-badge>
            </s-stack>
            <s-stack direction="inline" gap="small-200" justifyContent="space-between">
                <s-text>{i18n.translate('eat_in_total')}</s-text>
                <s-text type="strong">{formatAmount(summary.eatInTotal)}</s-text>
            </s-stack>
            <s-stack direction="inline" gap="small-200" justifyContent="space-between">
                <s-text>{i18n.translate('zero_rated_total')}</s-text>
                <s-text type="strong">{formatAmount(summary.zeroRatedTotal)}</s-text>
            </s-stack>
            <s-divider />
            <s-heading>{i18n.translate('breakdown_heading')}</s-heading>
            {summary.breakdownRows.length === 0 ? (
                <s-text color="subdued">{i18n.translate('breakdown_empty')}</s-text>
            ) : (
                <s-table>
                    <s-table-header-row>
                        <s-table-header listSlot="primary">{i18n.translate('column_product')}</s-table-header>
                        <s-table-header format="numeric">{i18n.translate('column_quantity')}</s-table-header>
                        <s-table-header format="currency">{i18n.translate('column_line_total')}</s-table-header>
                        <s-table-header format="currency">{i18n.translate('column_eat_in_charge')}</s-table-header>
                    </s-table-header-row>
                    <s-table-body>
                        {summary.breakdownRows.map((breakdownRow) => (
                            <s-table-row key={breakdownRow.key}>
                                <s-table-cell>
                                    <s-stack direction="block">
                                        <s-text>{breakdownRow.productName}</s-text>
                                        {breakdownRow.sku === '' ? null : <s-text color="subdued">{breakdownRow.sku}</s-text>}
                                    </s-stack>
                                </s-table-cell>
                                <s-table-cell>{breakdownRow.quantity}</s-table-cell>
                                <s-table-cell>{formatMoney(breakdownRow.lineTotal, currencyCode)}</s-table-cell>
                                <s-table-cell>
                                    <s-stack direction="block">
                                        <s-text>{formatMoney(breakdownRow.eatInCharge, currencyCode)}</s-text>
                                        {breakdownRow.upliftPercent === '' || breakdownRow.eatInCharge === 0 ? null : (
                                            <s-text color="subdued">
                                                {i18n.translate('uplift_percent', { percent: breakdownRow.upliftPercent })}
                                            </s-text>
                                        )}
                                    </s-stack>
                                </s-table-cell>
                            </s-table-row>
                        ))}
                    </s-table-body>
                </s-table>
            )}
        </s-stack>
    );
}
