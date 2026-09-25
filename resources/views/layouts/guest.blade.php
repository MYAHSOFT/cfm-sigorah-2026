<!DOCTYPE html>
<html lang="fr">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">

        <title>CFM</title>
        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/icons-font.css') }}">

    </head>
    <body class="font-sans antialiased text-gray-700">
        <div class="flex flex-1 items-center justify-center min-h-screen">
            @yield('content')
        </div>
        @yield('script')
    </body>
</html>
