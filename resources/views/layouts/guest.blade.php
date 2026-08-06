<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>ISP BSS</title>
        {{-- Global app favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('image/icon-removebg-preview.png') }}">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased" style="background-image: url('{{ asset('image/Background-image.png') }}'); background-size: cover; background-position: center; background-attachment: fixed;">
        <div class="flex min-h-screen flex-col items-center justify-center p-4 sm:p-6 relative">
            <div class="mb-12 flex flex-row items-center gap-2">
                <div class="h-16 w-16">
                    <svg viewBox="0 0 20 20" fill="var(--primary-color)" xmlns="http://www.w3.org/2000/svg" class="h-full w-full"><path d="M8.763 13.58a1.75 1.75 0 1 1 2.473 2.477 1.75 1.75 0 0 1-2.473-2.478v.001zM3.4 10.825c3.64-3.64 9.56-3.64 13.2 0l-1.65 1.65a7.007 7.007 0 0 0-9.9 0l-1.65-1.65zm-3.3-3.3c5.46-5.459 14.34-5.459 19.8 0l-1.65 1.65c-4.55-4.55-11.95-4.55-16.5 0L.1 7.526v-.001z"/></svg>
                </div>
                <span class="text-4xl font-bold text-gray-600">ISP BSS</span>
            </div>
            {{ $slot }}
            <div class="absolute bottom-0 w-full py-4 text-center text-[13px] text-gray-500">&copy; 2026 ISP BSS, All rights reserved.</div>
        </div>
    </body>
</html>