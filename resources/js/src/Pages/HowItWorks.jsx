import React from "react";
import { BlockStack, Text, Layout } from "@shopify/polaris";
import AdminPage from '@components/AdminPage';

export default function HowItWorks() {
    return (
        <AdminPage titleKey="pages.how_it_works">
            <Layout.Section>
                <BlockStack gap="400">
                    <Text
                        as="h2"
                        variant="headingSm"
                    >
                        How It Works
                    </Text>
                </BlockStack>
            </Layout.Section>
        </AdminPage>
    );
}
