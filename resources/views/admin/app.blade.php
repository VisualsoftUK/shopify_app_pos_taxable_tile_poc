@extends('shopify-app::layouts.default')

@section('scripts')
    <script>
        window.VS_SHOPIFY_APP_CONFIG = {
            mantleHelpDeskEnabled: @json((bool) config('vs-mantle.help_desk.enabled')),
            gorgiasHelpDeskEnabled: @json((bool) config('vs-admin-gorgias.help_desk.enabled')),
        };
    </script>
    @viteReactRefresh
    @vite('resources/js/app.jsx')
    @inertiaHead
    @appPricingConfig
    @mantleCustomerBehaviourTracking
    @mantleHelpDeskWidget
    @gorgiasHelpDeskWidget
@endsection

@section('content')
    @inertia
@endsection
