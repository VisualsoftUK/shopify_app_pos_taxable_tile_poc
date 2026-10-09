<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <link rel="icon" type="image/png" href="/favicon.png">
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta property="csp-nonce" content="{{ app('csp-nonce') }}">

    <title>{{ config('app.name') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script @cspNonce>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy:  '#040138',
                        black: '#000000',
                        cream: '#efebe7',
                    }
                }
            }
        }
    </script>
</head>
<body class="antialiased bg-navy text-navy">
<div class="min-h-screen flex items-center">
    <div class="max-w-6xl mx-auto">
        <div class="mb-8 m-5">
            <img src="https://visualsoft-assets.s3.eu-west-2.amazonaws.com/2023/logos/white/logo-full.png"
                 alt="Visualsoft Logo"
                 class="w-1/2 mx-auto"
                 loading="lazy">
        </div>

        <div class="px-12 py-8 rounded-lg shadow-2xl bg-cream text-center text-sm mt-10 m-5">
            <h1>Move along, people. There's nothing to see here.</h1>
        </div>

        <div class="px-12 py-8 rounded-lg text-center text-sm text-gray-400 m-5">
            <p><strong>Release</strong> {{ config('app.build') }} (Version {{ config('app.version') }} {{ config('app.build_version') }})</p>
            <p><strong>Environment</strong> {{ config('app.env')}}</p>
        </div>

    </div>
</div>
</body>
</html>
