<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Campus Space Admin') }}</title>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        
        <script src="https://cdn.tailwindcss.com"></script>
        <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    </head>
    <body class="font-sans antialiased bg-gray-100">
        <div class="min-h-screen">
            
            <nav class="bg-indigo-900 border-b border-indigo-800 text-white shadow-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between h-16">
                        <div class="flex items-center">
                            <div class="shrink-0 flex items-center">
                                <span class="text-xl font-bold tracking-wider text-white">🚀 CAMPUS SPACE</span>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <span class="text-sm font-medium bg-indigo-800 px-3 py-1 rounded-full text-indigo-200">
                                Mode Developer (Bypass Auth)
                            </span>
                        </div>
                    </div>
                </div>
            </nav>

            @if (isset($header))
                <header class="bg-white shadow">
                    <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        {{ $header }}
                    </div>
                </header>
            @endif

            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>