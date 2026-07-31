<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900">Add Subscriber</h2>
        <p class="mt-1 text-sm text-gray-500">Create their profile, set up their login, and assign a plan.</p>
    </x-slot>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.subscribers.store') }}" class="space-y-8">
            @csrf

            {{-- 1. Personal Information --}}
            <div>
                <div class="flex items-center gap-2 mb-4">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">1</span>
                    <h3 class="text-sm font-semibold text-gray-900">Personal Information</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                        <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                    </div>

                    <div class="sm:col-span-2">
                        <label for="contact" class="block text-sm font-medium text-gray-700 mb-1.5">Contact number</label>
                        <input id="contact" type="text" name="contact" value="{{ old('contact') }}"
                            placeholder="+63 900 000 0000"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('contact')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            {{-- 2. Account --}}
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">2</span>
                    <h3 class="text-sm font-semibold text-gray-900">Account</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required
                            placeholder="you@example.com"
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                    </div>

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

            {{-- 3. Plan & Subscription --}}
            <div class="border-t border-gray-200 pt-6">
                <div class="flex items-center gap-2 mb-4">
                    <span class="flex items-center justify-center w-5 h-5 rounded-full bg-blue-50 text-blue-700 text-xs font-semibold">3</span>
                    <h3 class="text-sm font-semibold text-gray-900">Plan &amp; Subscription</h3>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div class="sm:col-span-2">
                        <label for="plan_id" class="block text-sm font-medium text-gray-700 mb-1.5">Plan</label>
                        <select id="plan_id" name="plan_id" required
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                            <option value="" disabled {{ old('plan_id') ? '' : 'selected' }}>Select a plan</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}" {{ (int) old('plan_id') === $plan->id ? 'selected' : '' }}>
                                    {{ $plan->name }} &middot; {{ $plan->speed }} &middot; ₱{{ number_format($plan->price, 2) }}/{{ $plan->billing_cycle === 'monthly' ? 'mo' : 'yr' }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('plan_id')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                        <select id="status" name="status" required
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'suspended' => 'Suspended'] as $value => $label)
                                <option value="{{ $value }}" {{ old('status', 'active') === $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('status')" class="mt-1.5" />
                    </div>

                    <div>
                        <label for="joined_date" class="block text-sm font-medium text-gray-700 mb-1.5">Joined date</label>
                        <input id="joined_date" type="date" name="joined_date" value="{{ old('joined_date', now()->toDateString()) }}" required
                            class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        <x-input-error :messages="$errors->get('joined_date')" class="mt-1.5" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    Create account
                </button>
                <a href="{{ route('admin.subscribers.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>