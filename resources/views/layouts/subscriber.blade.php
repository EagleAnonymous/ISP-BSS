<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ISP BSS &middot; Subscriber</title>

        {{-- Global app favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('image/icon-removebg-preview.png') }}">

        <!-- Load app fonts for display -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Bundle JS and CSS assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Instant page prefetching for instant navigation --}}
        <script src="https://instant.page/instantpage.js" defer></script>
    </head>
<body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen flex">
            <!-- Sidebar holds all main navigation -->
            <aside
                class="fixed inset-y-0 left-0 z-30 w-56 bg-gray-900 border-r border-blue-800 flex flex-col transform transition-transform duration-150 ease-in-out
                       lg:translate-x-0 lg:flex overflow-y-auto"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="h-16 flex items-center justify-center px-6 border-b border-gray-700">
                    <img src="{{ asset('image/wifi-svgrepo-com.svg') }}" class="w-6 h-6" alt="WiFi">
                    <span class="text-lg font-semibold text-white ml-2">ISP BSS</span>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1">
                    {{-- Navigation with distinct feature icons --}}
                    <a href="{{ route('subscriber.dashboard') }}"
                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                              {{ request()->routeIs('subscriber.dashboard') ? 'bg-blue-900 text-blue-300' : 'text-white hover:bg-gray-800 hover:text-blue-300' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </span>
                    </a>

                    <a href="{{ route('subscriber.account') }}"
                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white hover:bg-gray-800 hover:text-blue-300 transition
                              {{ request()->routeIs('subscriber.account') ? 'bg-blue-900 text-blue-300' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            My Account
                        </span>
                    </a>

                    <a href="{{ route('subscriber.billing') }}"
                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white hover:bg-gray-800 hover:text-blue-300 transition
                              {{ request()->routeIs('subscriber.billing') ? 'bg-blue-900 text-blue-300' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 12a2.25 2.25 0 00-2.25-2.25H15a3 3 0 11-6 0H5.25A2.25 2.25 0 003 12m18 0v6a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 18v-6m18 0V9M3 12V9m18 0a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 9m18 0V6a2.25 2.25 0 00-2.25-2.25H5.25A2.25 2.25 0 003 6v3" />
                            </svg>
                            Billing
                        </span>
                    </a>

                    <a href="{{ route('subscriber.chatbot') }}"
                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium text-white hover:bg-gray-800 hover:text-blue-300 transition
                              {{ request()->routeIs('subscriber.chatbot') ? 'bg-blue-900 text-blue-300' : '' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 5V20.7929C3 21.2383 3.53857 21.4614 3.85355 21.1464L7.70711 17.2929C7.89464 17.1054 8.149 17 8.41421 17H19C20.1046 17 21 16.1046 21 15V5C21 3.89543 20.1046 3 19 3H5C3.89543 3 3 3.89543 3 5Z" />
                                <path d="M15 12C14.2005 12.6224 13.1502 13 12 13C10.8498 13 9.79952 12.6224 9 12" />
                                <path d="M9 8.01953V8" />
                                <path d="M15 8.01953V8" />
                            </svg>
                            Ai Chatbot
                        </span>
                    </a>
                </nav>

                {{-- Logout button at sidebar bottom --}}
                <form method="POST" action="{{ route('logout') }}" class="px-3 pb-4">
                    @csrf

                    <button type="submit"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-300 hover:bg-gray-800 hover:text-red-300 transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Logout
                    </button>
                </form>
            </aside>

            <!-- Mobile menu overlay backdrop layer -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-gray-900/70 lg:hidden"></div>

            <!-- Main content column layout wrapper -->
            <div class="flex-1 flex flex-col min-w-0 lg:pl-56">
                <!-- Mobile top bar toggle menu -->
                <div class="lg:hidden h-16 flex items-center justify-between px-4 bg-gray-900 border-b border-gray-700">
                    <span class="text-lg font-semibold text-white">Smart ISP</span>
                    <button @click="sidebarOpen = ! sidebarOpen" class="p-2 rounded-md text-gray-400 hover:bg-gray-800">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5M3.75 17.25h16.5" />
                        </svg>
                    </button>
                </div>

                @isset($header)
                    <header class="bg-white border-b border-gray-200">
                        <div class="px-4 sm:px-6 lg:px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
                    {{ $slot }}
                </main>
            </div>
</div>
    </body>
</html>

