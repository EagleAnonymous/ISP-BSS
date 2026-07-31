<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ISP BSS &middot; Staff</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans antialiased bg-gray-50" x-data="{ sidebarOpen: false }">
        <div class="min-h-screen flex">
            <!-- Sidebar -->
            <aside
                class="fixed inset-y-0 left-0 z-30 w-64 bg-white border-r border-gray-200 flex flex-col transform transition-transform duration-150 ease-in-out
                       lg:translate-x-0 lg:static lg:flex"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
                <div class="h-16 flex items-center px-6 border-b border-gray-200">
                    <span class="text-lg font-semibold text-gray-900">Smart ISP</span>
                    <span class="ml-2 text-xs font-medium text-blue-600 bg-blue-50 rounded-md px-1.5 py-0.5">Staff</span>
                </div>

                <nav class="flex-1 px-3 py-4 space-y-1">
                    <a href="{{ route('staff.dashboard') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                              {{ request()->routeIs('staff.dashboard') ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                        </svg>
                        Dashboard
                    </a>

                    <p class="pt-4 pb-1 px-3 text-xs font-semibold text-gray-400 uppercase tracking-wider">Support</p>

                    <a href="{{ route('staff.tickets.index', ['tab' => 'queue']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                              {{ request()->routeIs('staff.tickets.*') && request('tab', 'queue') === 'queue' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                        </svg>
                        Open Queue
                    </a>

                    <a href="{{ route('staff.tickets.index', ['tab' => 'mine']) }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                              {{ request()->routeIs('staff.tickets.*') && request('tab') === 'mine' ? 'bg-blue-50 text-blue-700' : 'text-gray-600 hover:bg-gray-100 hover:text-gray-900' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 7.5a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM3.75 20.25a8.25 8.25 0 0116.5 0" />
                        </svg>
                        My Tickets
                    </a>
                </nav>

                <div class="border-t border-gray-200 p-3">
                    <div class="flex items-center gap-3 px-3 py-2">
                        <div class="w-8 h-8 rounded-full bg-blue-600 text-white flex items-center justify-center text-sm font-semibold shrink-0">
                            {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email }}</p>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit"
                            class="mt-1 w-full flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-600 hover:bg-gray-100 hover:text-gray-900 transition">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 9V5.25A2.25 2.25 0 0110.5 3h6a2.25 2.25 0 012.25 2.25v13.5A2.25 2.25 0 0116.5 21h-6a2.25 2.25 0 01-2.25-2.25V15m-3 0l-3-3m0 0l3-3m-3 3H15" />
                            </svg>
                            Log out
                        </button>
                    </form>
                </div>
            </aside>

            <!-- Mobile overlay -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-gray-900/40 lg:hidden"></div>

            <!-- Main column -->
            <div class="flex-1 flex flex-col min-w-0">
                <!-- Mobile top bar -->
                <div class="lg:hidden h-16 flex items-center justify-between px-4 bg-white border-b border-gray-200">
                    <span class="text-lg font-semibold text-gray-900">Smart ISP</span>
                    <button @click="sidebarOpen = ! sidebarOpen" class="p-2 rounded-md text-gray-500 hover:bg-gray-100">
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
