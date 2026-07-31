<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900">Add Technical Staff</h2>
        <p class="mt-1 text-sm text-gray-500">Create their profile and set up the credentials they'll log in with.</p>
    </x-slot>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.technical-staff.store') }}" class="space-y-8">
            @csrf

            <div>
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Information</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            placeholder="you@example.com"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone number</label>
                        <input id="phone" type="text" name="phone" value="{{ old('phone') }}"
                            placeholder="+63 900 000 0000"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="position" class="block text-sm font-medium text-gray-700 mb-1.5">Position</label>
                        <input id="position" type="text" name="position" value="{{ old('position') }}"
                            placeholder="Network Technician"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('position')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-semibold text-gray-900 mb-4">Login credentials</h3>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input id="password" type="password" name="password" required
                            placeholder="••••••••"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('password')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1.5">Confirm password</label>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            placeholder="••••••••"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    Create account
                </button>
                <a href="{{ route('admin.technical-staff.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
