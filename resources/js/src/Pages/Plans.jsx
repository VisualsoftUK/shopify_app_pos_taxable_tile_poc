import React from "react";
import { BlockStack, Text, Layout } from "@shopify/polaris";
import AdminPage from '@components/AdminPage';

export default function Plans() {
    return (
        <AdminPage titleKey="pages.plans">
            <Layout.Section>
                <BlockStack gap="400">
                    <Text
                        as="h2"
                        variant="headingSm"
                    >
                        Plans
                    </Text>
                </BlockStack>
            </Layout.Section>
        </AdminPage>
    );
}
