import { BlockStack, Card, InlineGrid, InlineStack, Text } from '@shopify/polaris';
import { useTranslation } from 'react-i18next';
import { formatMinorUnits } from '@/Utils/reportFormatters';
import OrderTypeBadge from '@components/Reports/OrderTypeBadge';

/**
 * A single label/value line within a summary card.
 *
 * @param {Object} props
 * @param {string} props.label - Metric label.
 * @param {string} props.value - Formatted metric value.
 * @returns {JSX.Element}
 */
function SummaryMetric({ label, value }) {
    return (
        <InlineStack align="space-between">
            <Text as="span" tone="subdued">{label}</Text>
            <Text as="span" fontWeight="semibold">{value}</Text>
        </InlineStack>
    );
}

/**
 * Eat In and Takeaway totals for the selected filters.
 *
 * @param {Object} props
 * @param {Object} props.summary - Report summary ({ eatIn, takeaway, eatInSharePercent }).
 * @param {string} props.currencyCode - Shop currency code.
 * @returns {JSX.Element}
 */
export default function ReportSummaryCards({ summary, currencyCode }) {
    const { t } = useTranslation();
    const { eatIn, takeaway } = summary;

    return (
        <InlineGrid columns={{ xs: 1, md: 2 }} gap="400">
            <Card>
                <BlockStack gap="300">
                    <InlineStack align="space-between">
                        <OrderTypeBadge orderType="eat_in" />
                        <Text as="span" tone="subdued">{`${summary.eatInSharePercent}%`}</Text>
                    </InlineStack>
                    <Text as="p" variant="headingXl">{formatMinorUnits(eatIn.eatInChargeMinor, currencyCode)}</Text>
                    <Text as="p" tone="subdued">{t('reports.summary.eat_in_charges')}</Text>
                    <SummaryMetric label={t('reports.summary.orders')} value={`${eatIn.orderCount}`} />
                    <SummaryMetric label={t('reports.summary.order_value')} value={formatMinorUnits(eatIn.totalMinor, currencyCode)} />
                    <SummaryMetric label={t('reports.summary.average_charge')} value={formatMinorUnits(eatIn.averageChargeMinor, currencyCode)} />
                </BlockStack>
            </Card>
            <Card>
                <BlockStack gap="300">
                    <InlineStack align="space-between">
                        <OrderTypeBadge orderType="takeaway" />
                    </InlineStack>
                    <Text as="p" variant="headingXl">{formatMinorUnits(takeaway.zeroRatedMinor, currencyCode)}</Text>
                    <Text as="p" tone="subdued">{t('reports.summary.zero_rated_value')}</Text>
                    <SummaryMetric label={t('reports.summary.orders')} value={`${takeaway.orderCount}`} />
                    <SummaryMetric label={t('reports.summary.order_value')} value={formatMinorUnits(takeaway.totalMinor, currencyCode)} />
                </BlockStack>
            </Card>
        </InlineGrid>
    );
}
