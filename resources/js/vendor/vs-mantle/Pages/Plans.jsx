import React, {useEffect, useState} from "react";
import {Page, Card, SkeletonBodyText, Banner} from "@shopify/polaris";
import {useTranslation} from "react-i18next";
import { createHostedSession } from "../Services/Mantle";


export const Plans = () => {
    const { t } = useTranslation();
    const [error, setError] = useState(null);

    useEffect(() => {
        const loadHostedSession = async () => {
            try {
                const hostedSession = await createHostedSession();
                if (hostedSession && !hostedSession.error) {
                    if (typeof hostedSession.url !== 'string' || hostedSession.url === '') {
                        setError(true);
                        return;
                    }

                    window.open(hostedSession.url, "_self");
                }
            } catch (error) {
                setError(true);
            }
        }

        loadHostedSession();
    }, []);

    return (
        <>
            <ui-title-bar title={t('pages.plans')}>
                <button 
                    variant="breadcrumb" 
                    onClick={() => navigate('/app')}
                >
                    {t('pages.dashboard')}
                </button>
            </ui-title-bar>

            <Page title={ t('pages.plans') }>
                {error ? (
                        <Banner
                            title="Unable to load pricing plans"
                            tone="critical"
                        >
                            <p>Unfortunately, we were unable to load the pricing plans at this time, please try again later.</p>
                        </Banner>
                ) : (
                    <Card>
                        <SkeletonBodyText />
                    </Card>
                )}
            </Page>
        </>
    )
}

export default Plans;
