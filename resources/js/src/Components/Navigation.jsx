import { Link } from '@inertiajs/react';
import { useTranslation } from 'react-i18next';
import { useOpenAppPricingPlans } from '@app-pricing/Hooks/useOpenAppPricingPlans';

import { getHasActivePlan } from '@mantle/Services/MantleInertia';

/**
 * Embedded admin nav. Plans opens Shopify hosted pricing.
 *
 * @returns {JSX.Element}
 */
export default function Navigation() {
    const { t } = useTranslation();
    const hasActivePlan = getHasActivePlan();
    const { plansNavHref, canOpenPlans, openPlans } = useOpenAppPricingPlans();

    /**
     * Keep the relative href for Admin's copied sidebar link, and open hosted
     * Plans from the already-loaded App Bridge.
     *
     * @param {React.MouseEvent<HTMLAnchorElement>} event
     */
    const handleOpenPlans = (event) => {
        event.preventDefault();
        openPlans();
    };

    return (
        <ui-nav-menu>
            <Link href="/app" rel="home">{t('pages.dashboard')}</Link>

            {/* All other pages that require a plan should be in */}
            {hasActivePlan && (
                <>
                    <Link href="/app/reports">{t('pages.reports')}</Link>
                    <Link href="/app/how-it-works">{t('pages.how_it_works')}</Link>
                    <Link href="/app/custom-settings">{t('pages.settings')}</Link>
                </>
            )}

            {canOpenPlans === true && (
                <a
                    href={plansNavHref}
                    onClick={handleOpenPlans}
                >
                    {t('pages.plans')}
                </a>
            )}
        </ui-nav-menu>
    );
}
