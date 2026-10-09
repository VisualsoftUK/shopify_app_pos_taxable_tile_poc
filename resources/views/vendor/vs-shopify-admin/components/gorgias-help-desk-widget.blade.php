@once
    <style id="vs-gorgias-help-desk-widget-overrides">
    </style>
    <script id="gorgias-chat-widget-install-v3"
        src="https://config.gorgias.chat/bundle-loader/{{ e(config('vs-admin-gorgias.help_desk.widget_id')) }}" async>
    </script>
    <script src="https://static.9gtb.com/loader.js?g_cvt_id={{ e(config('vs-admin-gorgias.help_desk.bundle_id')) }}" async></script>
@endonce
