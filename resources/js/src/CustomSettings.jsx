import { BlockStack, Card, Text } from '@shopify/polaris';

export default function CustomSettings({ children, t }) {
    return (
        <BlockStack gap="400">
            <Card>
                <BlockStack gap="200">
                    <Text as="h2" variant="headingMd">
                        {t('custom_settings')}
                    </Text>
                    <Text as="p" variant="bodyMd">
                        {t('custom_configuration_description')}
                    </Text>
                </BlockStack>
            </Card>
            {children}
        </BlockStack>
    );
}
