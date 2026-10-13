import { Badge } from '@shopify/polaris';
import { useTranslation } from 'react-i18next';

/**
 * Badge showing whether a row relates to Eat In or Takeaway orders.
 *
 * @param {Object} props
 * @param {'eat_in'|'takeaway'} props.orderType - The order type.
 * @returns {JSX.Element}
 */
export default function OrderTypeBadge({ orderType }) {
    const { t } = useTranslation();

    return (
        <Badge tone={orderType === 'eat_in' ? 'info' : 'success'}>
            {t(`order_types.${orderType}`)}
        </Badge>
    );
}
