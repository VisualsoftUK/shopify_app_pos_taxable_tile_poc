import React from 'react';
import { router } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { Page, Layout } from '@shopify/polaris';
import AppLayout from '@layouts/AppLayout';
import PageFooter from '@admin/Components/Common/PageFooter';

export default function AdminPage({ title, titleKey, primaryAction, children, ...pageProps }) {
    const { t } = useTranslation();
    const pageTitle = title || (titleKey ? t(titleKey) : '');

    return (
        <AppLayout>
            <ui-title-bar title={pageTitle}>
                <button 
                    variant="breadcrumb" 
                    onClick={() => router.visit('/app', { replace: true })}
                >
                    {t('pages.dashboard')}
                </button>
            </ui-title-bar>

            <Page 
                title={pageTitle}
                primaryAction={primaryAction}
                {...pageProps}
            >
                <Layout>
                    {children}
                    <PageFooter />
                </Layout>
            </Page>
        </AppLayout>
    );
}
