<x-guest-layout>
    <div class="mx-auto flex w-full max-w-sm flex-col justify-center bg-white p-10 shadow-xl" style="border-radius: 10px;"> 
        <div class="mb-1 text-center"> 
            <h1 class="text-[38px] font-bold text-[var(--primary-color)]">{{ __('LOGIN') }}</h1>
        </div>
 
        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf

            <!-- Email Address -->
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M3 4a2 2 0 00-2 2v1.161l8.441 4.221a1.25 1.25 0 001.118 0L19 7.162V6a2 2 0 00-2-2H3z" />
                        <path d="M19 8.839l-7.77 3.885a2.75 2.75 0 01-2.46 0L1 8.839V14a2 2 0 002 2h14a2 2 0 002-2V8.839z" />
                    </svg>
                </div>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                    placeholder="Email"
                    class="block w-full rounded-lg border border-gray-300 ps-10 pe-3.5 py-1.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
            </div>

            <!-- Password -->
            <div class="relative">
                <div class="pointer-events-none absolute inset-y-0 start-0 flex items-center ps-3.5">
                    <svg class="h-5 w-5 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd" />
                    </svg>
                </div>
                <input id="password" type="password" name="password" required autocomplete="current-password"
                    placeholder="Password"
                    class="block w-full rounded-lg border border-gray-300 ps-10 pe-3.5 py-1.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
            </div>

            <button type="submit" class="w-full mt-6 rounded-lg bg-[var(--primary-color)] px-4 py-1.5 text-md font-semibold text-white shadow-lg shadow-[var(--primary-color)]/50 transition hover:bg-[var(--primary-hover-color)] hover:shadow-md focus:outline-none focus:ring-2 focus:ring-[var(--primary-color)] focus:ring-offset-2 mb-2">
                {{ __('Login') }}
            </button>
        </form>
    </div>
</x-guest-layout>
