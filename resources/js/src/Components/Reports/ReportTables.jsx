import { IndexTable, Text } from '@shopify/polaris';
import { useTranslation } from 'react-i18next';
import { formatMinorUnits, formatReportDate } from '@/Utils/reportFormatters';
import OrderTypeBadge from '@components/Reports/OrderTypeBadge';

/**
 * Shared read-only IndexTable wrapper.
 *
 * @param {Object} props
 * @param {Array<{title: string, alignment?: string}>} props.headings - Column headings.
 * @param {number} props.itemCount - Number of rows.
 * @param {React.ReactNode} props.children - Table rows.
 * @returns {JSX.Element}
 */
function ReportTable({ headings, itemCount, children }) {
    return (
        <IndexTable
            resourceName={{ singular: 'row', plural: 'rows' }}
            itemCount={itemCount}
            headings={headings}
            selectable={false}
        >
            {children}
        </IndexTable>
    );
}

/**
 * Daily Eat In / Takeaway totals.
 *
 * @param {Object} props
 * @param {Array<Object>} props.rows - Daily report rows.
 * @param {string} props.currencyCode - Shop currency code.
 * @returns {JSX.Element}
 */
export function DailyTable({ rows, currencyCode }) {
    const { t } = useTranslation();

    return (
        <ReportTable
            itemCount={rows.length}
            headings={[
                { title: t('reports.columns.date') },
                { title: t('reports.columns.eat_in_orders'), alignment: 'end' },
                { title: t('reports.columns.eat_in_value'), alignment: 'end' },
                { title: t('reports.columns.eat_in_charge'), alignment: 'end' },
                { title: t('reports.columns.takeaway_orders'), alignment: 'end' },
                { title: t('reports.columns.takeaway_value'), alignment: 'end' },
                { title: t('reports.columns.zero_rated_value'), alignment: 'end' },
            ]}
        >
            {rows.map((row, index) => (
                <IndexTable.Row id={row.date} key={row.date} position={index}>
                    <IndexTable.Cell>{formatReportDate(row.date)}</IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{row.eatInCount}</Text></IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.eatInTotalMinor, currencyCode)}</Text></IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.eatInChargeMinor, currencyCode)}</Text></IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{row.takeawayCount}</Text></IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.takeawayTotalMinor, currencyCode)}</Text></IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.zeroRatedMinor, currencyCode)}</Text></IndexTable.Cell>
                </IndexTable.Row>
            ))}
        </ReportTable>
    );
}

/**
 * Dynamic-product variants sold under each order type.
 *
 * @param {Object} props
 * @param {Array<Object>} props.rows - Variant report rows.
 * @param {string} props.currencyCode - Shop currency code.
 * @returns {JSX.Element}
 */
export function VariantsTable({ rows, currencyCode }) {
    const { t } = useTranslation();

    return (
        <ReportTable
            itemCount={rows.length}
            headings={[
                { title: t('reports.columns.product') },
                { title: t('reports.columns.sku') },
                { title: t('reports.columns.order_type') },
                { title: t('reports.columns.orders'), alignment: 'end' },
                { title: t('reports.columns.quantity'), alignment: 'end' },
                { title: t('reports.columns.line_value'), alignment: 'end' },
                { title: t('reports.columns.eat_in_charge'), alignment: 'end' },
            ]}
        >
            {rows.map((row, index) => {
                const rowId = `${row.variantId}-${row.orderType}`;
                const hasVariantTitle = row.variantTitle !== null && row.variantTitle !== 'Default Title';

                return (
                    <IndexTable.Row id={rowId} key={rowId} position={index}>
                        <IndexTable.Cell>
                            <Text as="span" fontWeight="semibold">{row.productTitle}</Text>
                            {hasVariantTitle === true && <Text as="span" tone="subdued">{` — ${row.variantTitle}`}</Text>}
                        </IndexTable.Cell>
                        <IndexTable.Cell>{row.sku ?? '—'}</IndexTable.Cell>
                        <IndexTable.Cell><OrderTypeBadge orderType={row.orderType} /></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{row.orderCount}</Text></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{row.quantity}</Text></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.lineTotalMinor, currencyCode)}</Text></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.eatInChargeMinor, currencyCode)}</Text></IndexTable.Cell>
                    </IndexTable.Row>
                );
            })}
        </ReportTable>
    );
}

/**
 * Customers (or guests) and their Eat In / Takeaway orders.
 *
 * @param {Object} props
 * @param {Array<Object>} props.rows - Customer report rows.
 * @param {string} props.currencyCode - Shop currency code.
 * @returns {JSX.Element}
 */
export function CustomersTable({ rows, currencyCode }) {
    const { t } = useTranslation();

    return (
        <ReportTable
            itemCount={rows.length}
            headings={[
                { title: t('reports.columns.customer') },
                { title: t('reports.columns.order_type') },
                { title: t('reports.columns.orders'), alignment: 'end' },
                { title: t('reports.columns.order_value'), alignment: 'end' },
                { title: t('reports.columns.eat_in_charge'), alignment: 'end' },
                { title: t('reports.columns.zero_rated_value'), alignment: 'end' },
            ]}
        >
            {rows.map((row, index) => {
                const rowId = `${row.customerId ?? 'guest'}-${row.orderType}`;

                return (
                    <IndexTable.Row id={rowId} key={rowId} position={index}>
                        <IndexTable.Cell>{row.customerName ?? t('reports.guest')}</IndexTable.Cell>
                        <IndexTable.Cell><OrderTypeBadge orderType={row.orderType} /></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{row.orderCount}</Text></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.totalMinor, currencyCode)}</Text></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.eatInChargeMinor, currencyCode)}</Text></IndexTable.Cell>
                        <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.zeroRatedMinor, currencyCode)}</Text></IndexTable.Cell>
                    </IndexTable.Row>
                );
            })}
        </ReportTable>
    );
}

/**
 * Individual classified orders.
 *
 * @param {Object} props
 * @param {Array<Object>} props.rows - Order report rows.
 * @param {string} props.currencyCode - Shop currency code.
 * @returns {JSX.Element}
 */
export function OrdersTable({ rows, currencyCode }) {
    const { t } = useTranslation();

    return (
        <ReportTable
            itemCount={rows.length}
            headings={[
                { title: t('reports.columns.order') },
                { title: t('reports.columns.created_at') },
                { title: t('reports.columns.channel') },
                { title: t('reports.columns.order_type') },
                { title: t('reports.columns.customer') },
                { title: t('reports.columns.total'), alignment: 'end' },
                { title: t('reports.columns.eat_in_charge'), alignment: 'end' },
                { title: t('reports.columns.zero_rated_value'), alignment: 'end' },
            ]}
        >
            {rows.map((row, index) => (
                <IndexTable.Row id={row.orderId} key={row.orderId} position={index}>
                    <IndexTable.Cell><Text as="span" fontWeight="semibold">{row.orderName}</Text></IndexTable.Cell>
                    <IndexTable.Cell>{formatReportDate(row.createdAt, true)}</IndexTable.Cell>
                    <IndexTable.Cell>{row.channel ?? '—'}</IndexTable.Cell>
                    <IndexTable.Cell><OrderTypeBadge orderType={row.orderType} /></IndexTable.Cell>
                    <IndexTable.Cell>{row.customerName ?? t('reports.guest')}</IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.totalMinor, currencyCode)}</Text></IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.eatInTotalMinor, currencyCode)}</Text></IndexTable.Cell>
                    <IndexTable.Cell><Text as="span" alignment="end" numeric>{formatMinorUnits(row.zeroRatedTotalMinor, currencyCode)}</Text></IndexTable.Cell>
                </IndexTable.Row>
            ))}
        </ReportTable>
    );
}
