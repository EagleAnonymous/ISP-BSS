<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>ISP BSS &middot; Staff</title>

        {{-- Global app favicon --}}
        <link rel="icon" type="image/png" href="{{ asset('image/icon-removebg-preview.png') }}">

        <!-- Load app fonts for display -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Bundle JS and CSS assets -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {{-- Instant page prefetching for instant navigation --}}
        <script src="https://instant.page/instantpage.js" defer></script>

        {{-- Alpine.js dropdown helpers --}}
        <style>
            [x-cloak] { display: none !important; }
        </style>
    </head>
<body class="font-sans antialiased bg-gray-50"
      x-data="{ sidebarOpen: false, profileDropdown: false, notificationDropdown: false, notificationCount: {{ $notificationCount ?? 0 }}, lastNotificationCount: {{ $notificationCount ?? 0 }}, toast: { show: false, title: '', message: '', url: '' } }"
      @click.outside="profileDropdown = false; notificationDropdown = false"
      x-init="startPolling()">
    <script>
        function markRead(notificationId) {
            fetch('{{ route('notifications.read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: JSON.stringify({ notification_id: notificationId }),
            }).then(() => {
                notificationCount = Math.max(0, notificationCount - 1);
                lastNotificationCount = notificationCount;
                notificationDropdown = false;
            });
        }

        function markAllRead() {
            fetch('{{ route('notifications.read') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
            }).then(() => {
                notificationCount = 0;
                lastNotificationCount = 0;
            });
        }

        function startPolling() {
            setInterval(() => {
                fetch('{{ route('notifications.check') }}', {
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    },
                })
                .then(response => response.json())
                .then(data => {
                    if (data.count > notificationCount && !notificationDropdown) {
                        const latest = data.notifications[0];
                        if (latest) {
                            toast = {
                                show: true,
                                title: latest.title,
                                message: latest.message,
                                url: latest.url || '#',
                            };
                            setTimeout(() => {
                                toast.show = false;
                            }, 5000);
                        }
                    }
                    notificationCount = data.count;
                    lastNotificationCount = data.count;
                })
                .catch(() => {});
            }, 3000);
        }

        function goToNotification(url) {
            toast.show = false;
            window.location.href = url;
        }
    </script>

    {{-- Toast Notification Popup --}}
    <div x-show="toast.show"
         x-cloak
         x-transition:enter="transform ease-out duration-300 transition"
         x-transition:enter-start="translate-y-2 opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed top-4 right-4 z-50 max-w-sm w-full bg-white rounded-lg shadow-lg border border-gray-200 overflow-hidden">
        <div class="p-4">
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0">
                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100 text-blue-600">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                        </svg>
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-gray-900" x-text="toast.title"></p>
                    <p class="mt-1 text-xs text-gray-500 line-clamp-2" x-text="toast.message"></p>
                </div>
                <button @click="toast.show = false" class="flex-shrink-0 text-gray-400 hover:text-gray-500">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="mt-3 flex gap-2">
                <button @click="goToNotification(toast.url)" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-md hover:bg-blue-700">
                    View Ticket
                </button>
                <button @click="toast.show = false" class="inline-flex items-center px-3 py-1.5 text-xs font-medium text-gray-700 bg-gray-100 rounded-md hover:bg-gray-200">
                    Dismiss
                </button>
            </div>
        </div>
    </div>

        <div class="min-h-screen flex">
<!-- Sidebar (w-56 bg-slate-900) -->
            <aside
                class="fixed inset-y-0 left-0 z-30 w-56 bg-slate-900 border-r border-slate-800 flex flex-col transform transition-transform duration-150 ease-in-out
                       lg:translate-x-0 lg:flex overflow-y-auto"
                :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
            >
{{-- Branding header: ISP BSS logo (clickable to close sidebar on mobile) --}}
<div class="relative h-16 flex items-center justify-center px-6 border-b border-slate-800">
                    <a href="{{ route('staff.dashboard') }}" class="flex items-center">
                        <img src="{{ asset('image/wifi-svgrepo-com.svg') }}" class="w-6 h-6" alt="WiFi">
                        <span class="text-lg font-semibold text-white ml-2">ISP BSS</span>
                    </a>
                    {{-- Close button (mobile only), positioned at the right edge --}}
                    <button @click="sidebarOpen = false"
                            class="lg:hidden absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-md text-slate-400 hover:bg-slate-800 hover:text-white focus:outline-none"
                            aria-label="Close sidebar">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

{{-- Main navigation links (always visible, no Alpine dependency) --}}
                <nav class="flex-1 px-3 py-4 space-y-1">
                    {{-- Dashboard --}}
                    @if(Route::has('staff.dashboard'))
                    <a href="{{ route('staff.dashboard') }}"
                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                              {{ request()->routeIs('staff.dashboard') ? 'bg-blue-900 text-blue-300' : 'text-white hover:bg-slate-800 hover:text-blue-300' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
                            </svg>
                            Dashboard
                        </span>
                    </a>
                    @endif

                    {{-- My Account --}}
                    @if(Route::has('staff.my-account'))
                    <a href="{{ route('staff.my-account') }}"
                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                              {{ request()->routeIs('staff.my-account') ? 'bg-blue-900 text-blue-300' : 'text-white hover:bg-slate-800 hover:text-blue-300' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                            </svg>
                            My Account
                        </span>
                    </a>
                    @endif

{{-- Assignments --}}
                    @if(Route::has('staff.assignments'))
                    <a href="{{ route('staff.assignments') }}"
                       class="flex items-center justify-between gap-3 rounded-lg px-3 py-2 text-sm font-medium transition
                              {{ request()->routeIs('staff.assignments') ? 'bg-blue-900 text-blue-300' : 'text-white hover:bg-slate-800 hover:text-blue-300' }}">
                        <span class="flex items-center gap-3">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25z" />
                            </svg>
                            Assignments
                        </span>
                    </a>
                    @endif
                </nav>

                {{-- Logout button fixed at the bottom (always visible) --}}
                <form method="POST" action="{{ route('logout') }}" class="px-3 pb-4">
                    @csrf
                    <button type="submit"
                            class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-gray-300 hover:bg-slate-800 hover:text-red-300 transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                        </svg>
                        Logout
                    </button>
                </form>
            </aside>

            <!-- Mobile menu overlay backdrop layer -->
            <div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false" class="fixed inset-0 z-20 bg-slate-900/70 lg:hidden"></div>

            <!-- Main content column wrapper -->
            <div class="flex-1 flex flex-col min-w-0 lg:pl-56">
                {{-- ============================================================ --}}
                {{-- TOP NAVIGATION HEADER (persistent, always visible on desktop) --}}
                {{-- ============================================================ --}}
                <header class="sticky top-0 z-20 bg-white border-b border-gray-200 shadow-sm">
                    <div class="flex items-center justify-between h-16 px-4 sm:px-6 lg:px-8">
{{-- Left: Brand toggle for mobile (replaces hamburger) --}}
                        <button @click="sidebarOpen = ! sidebarOpen"
                                class="lg:hidden flex items-center gap-2 p-2 rounded-md text-gray-700 hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                aria-label="Toggle sidebar">
                            <img src="{{ asset('image/wifi-svgrepo-com.svg') }}" class="w-6 h-6" alt="WiFi">
                            <span class="text-sm font-semibold text-gray-900">ISP BSS</span>
                        </button>

{{-- Right: Notification bell + Profile avatar (anchored to the right) --}}
                        <div class="ml-auto flex items-center gap-4">
                            {{-- Notification Bell --}}
                            <div class="relative">
                                <button type="button"
                                        @click="notificationDropdown = !notificationDropdown"
                                        class="relative inline-flex items-center rounded-full p-2 text-gray-500 hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500"
                                        aria-label="Notifications">
                                    {{-- Bell icon (inline SVG) --}}
                                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                                    </svg>

                                    {{-- Unread count badge --}}
                                    <span x-show="notificationCount > 0"
                                          x-text="notificationCount > 99 ? '99+' : notificationCount"
                                          class="absolute -top-0.5 -right-0.5 flex h-5 w-5 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white">
                                    </span>
                                </button>

                                {{-- Notification Dropdown Panel --}}
                                <div x-show="notificationDropdown"
                                     x-cloak
                                     @click.outside="notificationDropdown = false; markAllRead()"
                                     class="absolute right-0 mt-2 w-80 origin-top-right rounded-lg bg-white shadow-lg ring-1 ring-black ring-opacity-5 z-50">
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <h3 class="text-sm font-semibold text-gray-900">Notifications</h3>
                                    </div>
                                    <div class="max-h-80 overflow-y-auto">
                                        @if(isset($notifications) && $notifications->count() > 0)
                                            <div class="divide-y divide-gray-100">
                                                @foreach($notifications as $notification)
                                                    <a href="{{ route('staff.tickets.show', $notification->data['ticket_id']) }}"
                                                       @click="markRead('{{ $notification->id }}')"
                                                       class="block px-4 py-3 hover:bg-gray-50 transition">
                                                        <p class="text-sm font-medium text-gray-900">{{ $notification->data['title'] ?? 'Notification' }}</p>
                                                        <p class="mt-1 text-xs text-gray-500 line-clamp-2">{{ $notification->data['message'] ?? '' }}</p>
                                                        <p class="mt-1 text-xs text-gray-400">{{ $notification->created_at->diffForHumans() }}</p>
                                                    </a>
                                                @endforeach
                                            </div>
                                        @else
                                            <div class="px-4 py-6 text-center text-sm text-gray-500">
                                                No new notifications
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>

{{-- Profile Avatar → redirects to My Account --}}
                            @php
                                $avatarUser = Auth::user();
                                $initial = $avatarUser ? strtoupper(substr($avatarUser->name, 0, 1)) : 'S';
                            @endphp
<a href="{{ route('staff.my-account') }}"
                               class="flex h-8 w-8 items-center justify-center overflow-hidden rounded-full bg-blue-600 text-sm font-semibold text-white shadow-sm border-2 border-white hover:ring-2 hover:ring-blue-300 transition">
                                @if($avatarUser && $avatarUser->avatar_path)
                                    <img src="{{ asset('storage/'.$avatarUser->avatar_path) }}" alt="Avatar" class="h-full w-full object-cover">
                                @else
                                    <img src="{{ asset('image/icon.png') }}" alt="Default Avatar" class="h-full w-full object-cover">
                                @endif
                            </a>
                        </div>
                    </div>
                </header>

                {{-- Page header (slot from child views) --}}
                @isset($header)
                    <header class="bg-white border-b border-gray-200">
                        <div class="px-4 sm:px-6 lg:px-8 py-5">
                            {{ $header }}
                        </div>
                    </header>
                @endisset

                {{-- Main content area --}}
                <main class="flex-1 px-4 sm:px-6 lg:px-8 py-8">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>

