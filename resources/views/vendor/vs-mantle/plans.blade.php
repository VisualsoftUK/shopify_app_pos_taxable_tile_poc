@extends('shopify-app::layouts.default')

@section('scripts')
    <script>
        window.VS_SHOPIFY_APP_CONFIG = {
            appHandle: @json($appHandle),
            activePlan: @json($activePlan),
            shopifyPlan: @json($shopifyPlan),
            mantleApiToken: @json($mantleApiToken ?? null),
            mantleAppId: @json($mantleAppId ?? null),
        };
    </script>
    @viteReactRefresh
    @vite(['resources/js/app.jsx'])
    @mantleCustomerBehaviourTracking
@endsection

@section('content')
    <div id="app"></div>
@endsection
