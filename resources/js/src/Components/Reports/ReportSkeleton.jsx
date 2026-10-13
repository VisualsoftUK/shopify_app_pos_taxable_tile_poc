import { BlockStack, Card, InlineGrid, SkeletonBodyText, SkeletonDisplayText } from '@shopify/polaris';

/**
 * Loading placeholder matching the summary cards and table layout of the reports page.
 *
 * @returns {JSX.Element}
 */
export default function ReportSkeleton() {
    return (
        <BlockStack gap="400">
            <InlineGrid columns={{ xs: 1, md: 2 }} gap="400">
                {['eat_in', 'takeaway'].map((key) => (
                    <Card key={key}>
                        <BlockStack gap="300">
                            <SkeletonDisplayText size="small" />
                            <SkeletonBodyText lines={3} />
                        </BlockStack>
                    </Card>
                ))}
            </InlineGrid>
            <Card>
                <SkeletonBodyText lines={8} />
            </Card>
        </BlockStack>
    );
}
