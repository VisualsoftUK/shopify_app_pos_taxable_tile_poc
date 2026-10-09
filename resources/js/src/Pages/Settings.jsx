import '@shopify/polaris/build/esm/styles.css';
import React, {useEffect} from "react";

import { BlockStack, Form, Layout, Page } from '@shopify/polaris';
import { fetchMetafields, updateMetafields } from '@/Metafields/Metafields.js';
import { useAppBridge, SaveBar } from '@shopify/app-bridge-react';
import { useImmer } from 'use-immer';

import { useTranslation } from 'react-i18next';
import { useLocale } from '@admin/Services/Common/I18n';
import PageFooter from '@admin/Components/Common/PageFooter';
import AppLayout from '@/Layouts/AppLayout';
import { router } from '@inertiajs/react';


export const Settings = () => {
    const shopify = useAppBridge();
    const { t } = useTranslation();
    const { polarisTranslations } = useLocale();

    // initial meta fields - there are better ways of getting this sorted out
    // but let's just keep it simple
    const [ initialMetafields, setInitialMetafields ] = useImmer();

    useEffect(() => {
        if (initialMetafields === undefined) {
            fetchMetafields((res) => {
                setInitialMetafields(res);
                setMetafields(res);
            });
        }
    });

    // current metafields
    const [ metafieldErrors, setMetafieldErrors ] = useImmer();
    const [ metafields, setMetafields ] = useImmer();

    // Handle recieving a request to change a particular meta field
    // Immer is used here to allow us to copy an object and ensure that
    // any modifications are applied to a new object
    const onMetafieldUpdate = (key, value) => {
        setMetafields((draft) => {
            draft[key] = value;
        });

        shopify.saveBar.show('save-bar');
    };

    // Handle saving our metafields
    const handleSave = async () => {
        shopify.loading(true);

        try {
            await updateMetafields(metafields);

            setInitialMetafields(() => metafields);
            setMetafieldErrors();

            shopify.saveBar.hide('save-bar');
        } catch (error) {
            setMetafieldErrors(error.metafieldErrors);

            console.log(error);
        }

        shopify.loading(false);
    };

    // Discard changes and revert back to last known good state
    const handleDiscard = () => {
        setMetafields(() => initialMetafields);
        setMetafieldErrors();

        shopify.saveBar.hide('save-bar');
    };

    return (
        <AppLayout>
            <ui-title-bar title={t('pages.settings')}>
                <button 
                    variant="breadcrumb" 
                    onClick={() => router.visit('/app')}
                >
                    {t('pages.dashboard')}
                </button>
            </ui-title-bar>
            <Page title={t('pages.settings')}>
                <SaveBar id="save-bar">
                    <button variant="primary" onClick={handleSave} />
                    <button onClick={handleDiscard} />
                </SaveBar>
                <Layout>
                    <Layout.Section>
                        <BlockStack gap="400">
                            <Form onSubmit={handleSave}>
                                
                            </Form>
                        </BlockStack>
                    </Layout.Section>

                    <PageFooter />
                </Layout>
            </Page>
        </AppLayout>
    )
}

export default Settings;
