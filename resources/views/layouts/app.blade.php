<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta http-equiv="refresh" content="{{ config('session.lifetime') * 60 }}">
        <title>CFM</title>
        <!-- Styles -->
        <link rel="stylesheet" href="{{ asset('css/style.css') }}">
        <link rel="stylesheet" href="{{ asset('css/icons-font.css') }}">
        <link rel="stylesheet" href="{{ asset('select2/css/select2.css') }}">
    </head>
    <body class="font-sans antialiased sm:text-2xl md:text-xl lg:text-xl">

        <div class="flex flex-col min-h-screen">

            <div class="flex items-center justify-between text-white text-opacity-50 bg-cyan-800 px-10 py-5">
                <x-header-groupement></x-header-groupement>
                <div class="flex space-x-10">
                    <a href="{{route('home')}}">
                        <i class="fad fa-home fa-2xl"></i>
                    </a>
                    <form method="post" action="{{route('logout')}}">
                        @csrf
                        <button class="">
                            <i class="fad fa-sign-out-alt fa-2xl"></i>
                        </button>
                    </form>
                </div>
            </div>

            <div class="flex flex-1 w-full pt-10 pb-14">
                @yield('content')
            </div>

        </div>

        <script src="{{asset('js/jquery.js')}}"></script>
        <script src="{{asset('js/jquery-ui.js')}}"></script>
        <script src="{{asset('js/app.js')}}"></script>
        @yield('script')

    </body>
</html>
