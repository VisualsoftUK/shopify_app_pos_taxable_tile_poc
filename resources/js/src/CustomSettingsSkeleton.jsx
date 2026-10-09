import { BlockStack, Card, SkeletonBodyText, SkeletonDisplayText } from '@shopify/polaris';

export default function CustomSettingsSkeleton() {
    return (
        <BlockStack gap="400">
            <Card>
                <BlockStack gap="200">
                    <SkeletonDisplayText size="small" />
                    <SkeletonBodyText lines={2} />
                </BlockStack>
            </Card>
        </BlockStack>
    );
}
