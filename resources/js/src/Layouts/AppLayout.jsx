import { AppProvider } from '@shopify/polaris';
import { useLocale } from '@admin/Services/Common/I18n';
import Navigation from '@components/Navigation';

export default ({ children }) => {
    const { polarisTranslations } = useLocale();
    
    return (
        <AppProvider i18n={polarisTranslations}>
            <Navigation />

            {children}
        </AppProvider>
    );
};