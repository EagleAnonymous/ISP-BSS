{{-- Standalone error page for 500s --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>500 - Internal Server Error</title>
        {{-- Load tailwind for self-contained styling --}}
        <script src="https://cdn.tailwindcss.com"></script>
    </head>
    <body class="flex min-h-screen items-center justify-center bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-6 text-white antialiased">
        {{-- Center the error card nicely --}}
        <div class="w-full max-w-lg">
            <div class="rounded-2xl border border-white/10 bg-white/5 p-8 text-center shadow-2xl backdrop-blur sm:p-10">
                {{-- Show animated broken chain icon --}}
                <div class="mx-auto h-28 w-28 overflow-hidden rounded-full bg-red-500/10 p-2">
                    <img src="{{ asset('image/broken-chain.gif') }}" alt="Broken chain icon"
                        class="h-full w-full rounded-full object-cover">
                </div>

                {{-- Display the error heading text --}}
                <h1 class="mt-8 text-3xl font-extrabold tracking-tight sm:text-4xl">500 - Internal Server Error</h1>

                {{-- Display main error callout message --}}
                <p class="mt-4 text-lg text-slate-300">System Error: Please contact your system developer.</p>

                @if (config('app.debug'))
                    {{-- Show error details when debugging --}}
                    <details class="mt-6 text-left">
                        {{-- Toggle debug details on click --}}
                        <summary class="cursor-pointer text-sm font-medium text-slate-400 transition hover:text-white">
                            View technical details
                        </summary>
                        <div class="mt-3 rounded-lg bg-slate-900/80 p-4 text-left text-xs">
                            @if (isset($exception))
                                <p class="font-mono text-red-400">{{ $exception->getMessage() ?: get_class($exception) }}</p>
                                <p class="mt-2 font-mono text-slate-400">{{ basename($exception->getFile()) }}:{{ $exception->getLine() }}</p>
                            @else
                                <p class="font-mono text-slate-400">No exception details available.</p>
                            @endif
                        </div>
                    </details>
                @endif

                {{-- Refresh page to retry request --}}
                <div class="mt-8 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <button type="button" onclick="window.location.reload()"
                        class="w-full rounded-lg bg-red-500 px-6 py-3 text-sm font-semibold text-white shadow-lg shadow-red-500/30 transition hover:bg-red-400 sm:w-auto">
                        Try Again / Refresh
                    </button>
                    {{-- Link back to application home --}}
                    <a href="{{ url('/') }}"
                        class="w-full rounded-lg border border-white/20 px-6 py-3 text-sm font-semibold text-slate-200 transition hover:bg-white/10 sm:w-auto">
                        Go Back to Home
                    </a>
                </div>
            </div>
        </div>
    </body>
</html>

