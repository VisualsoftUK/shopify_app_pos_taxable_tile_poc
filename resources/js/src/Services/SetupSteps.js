import { CheckIcon } from "@shopify/polaris-icons";

import ActivateAppSVG from "@/Assets/ActivateAppSVG.jsx";

export const setupData = () => {
    const steps = [
        {
            stepKey: 'dismissed',
            completed: false,
            excluded: true
        },
        {
            stepKey: 'minimised',
            completed: false,
            excluded: true
        },
        {
            stepKey: 'welcome_card_dismissed',
            completed: false,
            excluded: true
        },
        {
            stepKey: 'cross_sell_dismissed',
            completed: false,
            excluded: true
        },
        {
            stepKey: 'cross_sell_minimised',
            completed: false,
            excluded: true
        },
        {
            stepKey: 'activate_template_app',
            title: 'setup_guide.steps.activate_template_app.title',
            description: 'setup_guide.steps.activate_template_app.description',
            completed: false,
            listItems: [
                'setup_guide.steps.activate_template_app.bullet1',
                'setup_guide.steps.activate_template_app.bullet2',
                'setup_guide.steps.activate_template_app.bullet3'
            ],
            primaryButton: {
                content: 'setup_guide.steps.activate_template_app.action',
                action: 'activateTemplateApp'
            },
            secondaryButton: {
                content: 'setup_guide.mark_as_done',
                contentCompleted: 'setup_guide.mark_as_not_done',
                props: {
                    icon: CheckIcon,
                    variant: 'secondary'
                },
                action: 'markStepCompleted'
            },
            svg: ActivateAppSVG
        },
    ];

    return { steps };
};
