import { BlockStack, Card, Select, Text } from '@shopify/polaris';
import { useTranslation } from 'react-i18next';

/**
 * Shop default for carts and orders that have not been marked as Eat In or Takeaway.
 *
 * @param {Object} props
 * @param {string} props.defaultOrderType - Current default ('eat_in' | 'takeaway').
 * @param {Function} props.onChange - Called with (key, value) when the default changes.
 * @returns {JSX.Element}
 */
export default function EatInSettingsCard({ defaultOrderType, onChange }) {
    const { t } = useTranslation();

    return (
        <Card>
            <BlockStack gap="300">
                <Text as="h2" variant="headingMd">{t('eat_in_settings.title')}</Text>
                <Select
                    label={t('eat_in_settings.default_order_type')}
                    helpText={t('eat_in_settings.description')}
                    value={defaultOrderType}
                    onChange={(value) => onChange('defaultOrderType', value)}
                    options={[
                        { label: t('order_types.takeaway'), value: 'takeaway' },
                        { label: t('order_types.eat_in'), value: 'eat_in' },
                    ]}
                />
            </BlockStack>
        </Card>
    );
}
