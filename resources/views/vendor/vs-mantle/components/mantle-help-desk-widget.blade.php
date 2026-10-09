@once
    <style id="vs-mantle-help-desk-widget-overrides">
        #mantle-help-desk-widget-toggle {
            background: rgb(0, 236, 133) !important;
            border: 1px solid rgb(0, 236, 133) !important;
        }

        #mantle-help-desk-widget-toggle svg * {
            stroke: #011127 !important;
        }
    </style>
@endonce
<script
    async
    src="https://cdn.heymantle.com/mantle-help-desk-widget.js"
    data-widget-id="{{ e(config('vs-mantle.help_desk.widget_id')) }}"
    data-jwt="{{ e((new \Visualsoft\Mantle\Actions\GenerateHelpDeskToken)->execute(Auth::user())) }}"
    data-api-url="https://app.heymantle.com"
></script>
