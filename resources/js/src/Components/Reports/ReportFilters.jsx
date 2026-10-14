import { useCallback, useState } from 'react';
import { BlockStack, Button, Card, DatePicker, InlineGrid, InlineStack, Popover, Select, Text } from '@shopify/polaris';
import { CalendarIcon } from '@shopify/polaris-icons';
import { useTranslation } from 'react-i18next';
import { formatReportDate, fromDateParam, toDateParam } from '@/Utils/reportFormatters';

/**
 * Date range, order type and channel filters for the reports page.
 *
 * @param {Object} props
 * @param {{from: string|null, to: string, order_type: string, channel: string}} props.filters - Current filters.
 * @param {Function} props.onChange - Called with the full, updated filters object.
 * @param {boolean} props.isLoading - Whether a report is currently loading.
 * @returns {JSX.Element}
 */
export default function ReportFilters({ filters, onChange, isLoading }) {
    const { t } = useTranslation();
    const initialEnd = fromDateParam(filters.to) ?? new Date();
    const initialStart = fromDateParam(filters.from) ?? initialEnd;

    const [isDatePickerOpen, setIsDatePickerOpen] = useState(false);
    const [selectedRange, setSelectedRange] = useState({ start: initialStart, end: initialEnd });
    const [visibleMonth, setVisibleMonth] = useState({ month: initialEnd.getMonth(), year: initialEnd.getFullYear() });

    const dateRangeLabel = filters.from === null
        ? `… – ${formatReportDate(filters.to)}`
        : `${formatReportDate(filters.from)} – ${formatReportDate(filters.to)}`;

    /**
     * Toggle the date picker popover.
     *
     * @returns {void}
     */
    const handleToggleDatePicker = useCallback(() => setIsDatePickerOpen((isOpen) => !isOpen), []);

    /**
     * Track the visible month in the date picker.
     *
     * @param {number} month - Zero-based month.
     * @param {number} year - Full year.
     * @returns {void}
     */
    const handleMonthChange = useCallback((month, year) => setVisibleMonth({ month, year }), []);

    /**
     * Apply the selected date range.
     *
     * @returns {void}
     */
    const handleApplyDates = () => {
        setIsDatePickerOpen(false);
        onChange({ ...filters, from: toDateParam(selectedRange.start), to: toDateParam(selectedRange.end) });
    };

    /**
     * Update a single select-based filter.
     *
     * @param {string} key - Filter key.
     * @returns {Function} Change handler for a Polaris Select.
     */
    const handleSelectChange = (key) => (value) => onChange({ ...filters, [key]: value });

    return (
        <Card>
            <InlineGrid columns={{ xs: 1, md: 3 }} gap="400">
                <BlockStack gap="100">
                    <Text as="span">{t('reports.filters.date_range')}</Text>
                    <Popover
                        active={isDatePickerOpen}
                        onClose={handleToggleDatePicker}
                        preferredAlignment="left"
                        activator={(
                            <Button icon={CalendarIcon} onClick={handleToggleDatePicker} disabled={isLoading} fullWidth textAlign="left">
                                {dateRangeLabel}
                            </Button>
                        )}
                    >
                        <Popover.Pane fixed>
                            <Popover.Section>
                                <DatePicker
                                    month={visibleMonth.month}
                                    year={visibleMonth.year}
                                    selected={selectedRange}
                                    onChange={setSelectedRange}
                                    onMonthChange={handleMonthChange}
                                    disableDatesAfter={new Date()}
                                    allowRange
                                />
                            </Popover.Section>
                            <Popover.Section>
                                <InlineStack align="end" gap="200">
                                    <Button onClick={handleToggleDatePicker}>{t('reports.filters.cancel')}</Button>
                                    <Button variant="primary" onClick={handleApplyDates}>{t('reports.filters.apply')}</Button>
                                </InlineStack>
                            </Popover.Section>
                        </Popover.Pane>
                    </Popover>
                </BlockStack>
                <Select
                    label={t('reports.filters.order_type')}
                    value={filters.order_type}
                    onChange={handleSelectChange('order_type')}
                    disabled={isLoading}
                    options={[
                        { label: t('order_types.all'), value: 'all' },
                        { label: t('order_types.eat_in'), value: 'eat_in' },
                        { label: t('order_types.takeaway'), value: 'takeaway' },
                    ]}
                />
                <Select
                    label={t('reports.filters.channel')}
                    value={filters.channel}
                    onChange={handleSelectChange('channel')}
                    disabled={isLoading}
                    options={[
                        { label: t('reports.filters.channel_all'), value: 'all' },
                        { label: t('reports.filters.channel_pos'), value: 'pos' },
                        { label: t('reports.filters.channel_web'), value: 'web' },
                    ]}
                />
            </InlineGrid>
        </Card>
    );
}
