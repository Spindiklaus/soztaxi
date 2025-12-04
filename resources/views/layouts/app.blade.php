<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Самара. Соцтакси') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        <link href="{{ asset('css/app.css') }}" rel="stylesheet">       
<!--        <script src="https://cdn.tailwindcss.com"></script>-->
    </head>
    <body class="font-sans antialiased">
        <div class="min-h-screen bg-gray-100">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
            <header class="bg-white shadow">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
            @endisset

            <!-- Page Content -->
            <main>
                <!-- Сообщение об ошибке -->
                @if(session('error'))
                    <x-alert type="error" title="Доступ запрещён">
                        {{ session('error') }}
                    </x-alert>
                @endif
                @if(session('success'))
                    <x-alert type="success" title="Успех">
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if(session('warning'))
                    <x-alert type="warning" title="Предупреждение">
                        {{ session('warning') }}
                    </x-alert>
                @endif

                @if(session('info'))
                    <x-alert type="info" title="Информация">
                        {{ session('info') }}
                    </x-alert>
                @endif
                <!-- Ошибки валидации -->
                <x-validation-errors-alert />
                <!-- Содержимое страницы -->
                {{ $slot }}
            </main>
        </div>
        <!-- Footer -->
        @include('layouts.footer')
        <!-- Подключение Alpine.js с defer -->
        <script src="//unpkg.com/alpinejs" defer></script>    
        <!-- Подключение Feather Icons -->
        <script src="https://unpkg.com/feather-icons"></script> 
        <script>
            feather.replace();
        </script>

<!--        <script src="{{-- mix('js/app.js') --}}"></script>-->
        <script src="{{ asset('js/app.js') }}"></script>
    </body>
</html>
