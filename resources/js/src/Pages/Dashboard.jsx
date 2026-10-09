import React, { useEffect, useState } from "react";
import {
    Page,
    Layout,
} from "@shopify/polaris";
import { CalendarIcon } from "@shopify/polaris-icons";
import { useTranslation } from "react-i18next";
import { Deferred } from '@inertiajs/react';
import AppLayout from "@/Layouts/AppLayout";
import { partialReload } from '@files/Utils';
import { createTimeFilters } from '@admin/Utils/Stats/TimeFilters';
import { setupData } from '@services/SetupSteps';
import { mergeSetupData, handleToggleCompletion } from '@admin/Services/Common/SetupGuide';
import StatsBar from '@admin/Components/Stats/StatsBar';
import CrossSellingCard from '@admin/Components/CrossSelling/CrossSellingCard';
import FeedbackCards from '@admin/Components/Common/FeedbackCards';
import PageFooter from '@admin/Components/Common/PageFooter';
import WelcomeCard from '@admin/Components/Common/WelcomeCard';
import SetupGuide from '@admin/Components/Common/SetupGuide';
import WelcomeIllustration from '@/Assets/WelcomeIllustration';
import { getHasActivePlan, navigateToPlans } from '@mantle/Services/MantleInertia';
import NoPlanBanner from '@mantle/Components/NoPlanBanner';
import { SubscriptionExpiryBanner } from '@app-pricing';

export default function Dashboard({ stats, setupStepsStatus }) {
    const { t } = useTranslation();
    const useMantleHelpDesk = window?.VS_SHOPIFY_APP_CONFIG?.mantleHelpDeskEnabled === true;
    const useGorgiasHelpDesk = window?.VS_SHOPIFY_APP_CONFIG?.gorgiasHelpDeskEnabled === true;
    const [filter, setFilter] = useState(1); // Default to daily_30
    const [statsLoading, setStatsLoading] = useState(false);
    const [showCrossSelling, setShowCrossSelling] = useState(false);
    const [crossSellingLoaded, setCrossSellingLoaded] = useState(false);
    const [stepData, setStepData] = useState(setupData());
    const [stepsDataLoaded, setStepsDataLoaded] = useState(false);

    const timeFilters = createTimeFilters(t, setFilter, filter);
    const activeFilter = timeFilters.at(filter) ?? timeFilters[1];

    const hasActivePlan = getHasActivePlan() ?? false;

    // Merge setup steps status with setup data
    useEffect(() => {
        if (!setupStepsStatus) return;
        setStepData(mergeSetupData(setupData(), setupStepsStatus));
        setStepsDataLoaded(true);
    }, [setupStepsStatus]);

    // Action map for setup guide
    const actionMap = {
        markStepCompleted: handleToggleCompletion
    };

    // Reload stats when filter changes
    useEffect(() => {
        if (!hasActivePlan) return;

        partialReload({
            only: ['stats'],
            params: { timeframe: activeFilter.timeframe },
            visitOptions: {
                onStart: () => setStatsLoading(true),
                onFinish: () => setStatsLoading(false)
            }
        });
    }, [activeFilter.timeframe]);

    return (
        <AppLayout>
            <ui-title-bar title={t('app_name')}></ui-title-bar>
            <Page
                title={t('pages.dashboard')}
                actionGroups={hasActivePlan ? [
                    {
                        icon: CalendarIcon,
                        title: activeFilter.content,
                        actions: timeFilters
                    },
                ] : []}
            >
                <Layout>
                    <Layout.Section>
                        <SubscriptionExpiryBanner />
                    </Layout.Section>

                    {!hasActivePlan && (
                        <NoPlanBanner action={navigateToPlans} />
                    )}

                    {/* Stats Cards */}
                    {hasActivePlan && (
                        <Layout.Section>
                            <Deferred
                                data="stats"
                                fallback={<StatsBar columns={4} asFallback={true} t={t} />}
                            >
                                <StatsBar stats={stats} loading={statsLoading} columns={4} t={t} />
                            </Deferred>
                        </Layout.Section>
                    )}

                    {/* Welcome Card */}
                    {!stepData?.steps?.find(step => step.stepKey === 'welcome_card_dismissed')?.completed && (
                        <Layout.Section>
                            <WelcomeCard
                                title={t('dashboard.welcome_panel.title', { app_name: t('app_name') })}
                                description={t('dashboard.welcome_panel.description')}
                                listItems={[
                                    t('dashboard.welcome_panel.features.0'),
                                    t('dashboard.welcome_panel.features.1'),
                                    t('dashboard.welcome_panel.features.2'),
                                    t('dashboard.welcome_panel.features.3'),
                                ]}
                                illustration={WelcomeIllustration}
                                onDismiss={() => handleToggleCompletion('welcome_card_dismissed', { completed: true })}
                            />
                        </Layout.Section>
                    )}

                    {/* Setup Guide */}
                    {hasActivePlan && stepsDataLoaded && !stepData?.steps?.find(step => step.stepKey === 'dismissed')?.completed && (
                        <Layout.Section>
                            <SetupGuide
                                stepData={stepData}
                                handleToggleCompletion={handleToggleCompletion}
                                actionMap={actionMap}
                            />
                        </Layout.Section>
                    )}

                    {/* Cross-Sell Card */}
                    {stepsDataLoaded && !stepData?.steps?.find(step => step.stepKey === 'cross_sell_dismissed')?.completed && (!crossSellingLoaded || showCrossSelling) && (
                        <Layout.Section>
                            <CrossSellingCard
                                dismissed={stepData?.steps?.find(step => step.stepKey === 'cross_sell_dismissed')?.completed || false}
                                minimized={stepData?.steps?.find(step => step.stepKey === 'cross_sell_minimised')?.completed || false}
                                onDismiss={handleToggleCompletion}
                                onToggleMinimize={handleToggleCompletion}
                                onContentAvailable={() => setShowCrossSelling(true)}
                                onLoadingComplete={() => setCrossSellingLoaded(true)}
                            />
                        </Layout.Section>
                    )}

                    {/* Feedback Cards */}
                    <Layout.Section>
                        <FeedbackCards
                            showFeedbackCard={hasActivePlan}
                            useMantleHelpDesk={useMantleHelpDesk}
                            useGorgiasHelpDesk={useGorgiasHelpDesk}
                        />
                    </Layout.Section>

                    <PageFooter />
                </Layout>
            </Page>
        </AppLayout>
    );
}
