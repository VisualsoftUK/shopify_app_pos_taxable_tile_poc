import React, { useState } from 'react';
import { Banner, BlockStack, Card, EmptyState, Layout, Page, Tabs } from '@shopify/polaris';
import { ExportIcon } from '@shopify/polaris-icons';
import { Deferred, router } from '@inertiajs/react';
import { useAppBridge } from '@shopify/app-bridge-react';
import { useTranslation } from 'react-i18next';
import AppLayout from '@/Layouts/AppLayout';
import { partialReload } from '@files/Utils';
import { downloadReportCsv } from '@/Utils/downloadReportCsv';
import PageFooter from '@admin/Components/Common/PageFooter';
import ReportFilters from '@components/Reports/ReportFilters';
import ReportSkeleton from '@components/Reports/ReportSkeleton';
import ReportSummaryCards from '@components/Reports/ReportSummaryCards';
import { CustomersTable, DailyTable, OrdersTable, VariantsTable } from '@components/Reports/ReportTables';

const REPORT_TABS = ['daily', 'variants', 'customers', 'orders'];

/**
 * The selected tab's table.
 *
 * @param {Object} props
 * @param {string} props.tab - Selected tab key.
 * @param {Object} props.report - The loaded report.
 * @returns {JSX.Element}
 */
function ReportTabContent({ tab, report }) {
    const tables = {
        daily: <DailyTable rows={report.daily} currencyCode={report.currencyCode} />,
        variants: <VariantsTable rows={report.variants} currencyCode={report.currencyCode} />,
        customers: <CustomersTable rows={report.customers} currencyCode={report.currencyCode} />,
        orders: <OrdersTable rows={report.orders} currencyCode={report.currencyCode} />,
    };

    return tables[tab] ?? tables.daily;
}

/**
 * Report body once the deferred report has loaded.
 *
 * @param {Object} props
 * @param {Object} props.report - The loaded report.
 * @param {number} props.selectedTabIndex - Selected tab index.
 * @param {Function} props.onSelectTab - Called with the new tab index.
 * @returns {JSX.Element}
 */
function ReportBody({ report, selectedTabIndex, onSelectTab }) {
    const { t } = useTranslation();

    if (report.orderCount === 0) {
        return (
            <Card>
                <EmptyState heading={t('reports.empty_title')} image="">
                    <p>{t('reports.empty_description')}</p>
                </EmptyState>
            </Card>
        );
    }

    const tabs = REPORT_TABS.map((key) => ({ id: key, content: t(`reports.tabs.${key}`) }));

    return (
        <BlockStack gap="400">
            {report.isTruncated === true && <Banner tone="warning">{t('reports.truncated')}</Banner>}
            <ReportSummaryCards summary={report.summary} currencyCode={report.currencyCode} />
            <Card padding="0">
                <Tabs tabs={tabs} selected={selectedTabIndex} onSelect={onSelectTab}>
                    <ReportTabContent tab={REPORT_TABS[selectedTabIndex]} report={report} />
                </Tabs>
            </Card>
        </BlockStack>
    );
}

/**
 * Eat In / Takeaway reports with filters and CSV export.
 *
 * @param {Object} props
 * @param {{from: string|null, to: string, order_type: string, channel: string}} props.filters - Applied filters.
 * @param {Object|undefined} props.report - Deferred report data.
 * @returns {JSX.Element}
 */
export default function Reports({ filters, report }) {
    const { t } = useTranslation();
    const shopify = useAppBridge();
    const [currentFilters, setCurrentFilters] = useState(filters);
    const [selectedTabIndex, setSelectedTabIndex] = useState(0);
    const [isLoading, setIsLoading] = useState(false);
    const [isExporting, setIsExporting] = useState(false);

    /**
     * Apply new filters and reload only the report.
     *
     * @param {Object} nextFilters - The updated filters.
     * @returns {void}
     */
    const handleFiltersChange = (nextFilters) => {
        setCurrentFilters(nextFilters);

        partialReload({
            only: ['report', 'filters'],
            params: nextFilters,
            visitOptions: {
                onStart: () => setIsLoading(true),
                onFinish: () => setIsLoading(false),
            },
        });
    };

    /**
     * Export the selected tab as CSV.
     *
     * @returns {Promise<void>}
     */
    const handleExport = async () => {
        setIsExporting(true);

        try {
            await downloadReportCsv(currentFilters, REPORT_TABS[selectedTabIndex]);
        } catch (error) {
            console.error('Unable to export the eat-in report CSV', error);
            shopify.toast.show(t('reports.export_error'), { isError: true });
        } finally {
            setIsExporting(false);
        }
    };

    return (
        <AppLayout>
            <ui-title-bar title={t('pages.reports')}>
                <button variant="breadcrumb" onClick={() => router.visit('/app')}>
                    {t('pages.dashboard')}
                </button>
            </ui-title-bar>
            <Page
                title={t('reports.title')}
                primaryAction={{
                    content: t('reports.export'),
                    icon: ExportIcon,
                    onAction: handleExport,
                    loading: isExporting,
                    disabled: isLoading || (report?.orderCount ?? 0) === 0,
                }}
            >
                <Layout>
                    <Layout.Section>
                        <BlockStack gap="400">
                            <ReportFilters filters={currentFilters} onChange={handleFiltersChange} isLoading={isLoading} />
                            <Deferred data="report" fallback={<ReportSkeleton />}>
                                {isLoading === true || report === undefined
                                    ? <ReportSkeleton />
                                    : <ReportBody report={report} selectedTabIndex={selectedTabIndex} onSelectTab={setSelectedTabIndex} />}
                            </Deferred>
                        </BlockStack>
                    </Layout.Section>
                    <PageFooter />
                </Layout>
            </Page>
        </AppLayout>
    );
}
