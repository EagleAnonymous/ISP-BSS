<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-900">Add Plan</h2>
        <p class="mt-1 text-sm text-gray-500">New plans are active by default and immediately selectable for subscribers.</p>
    </x-slot>

    <div class="max-w-2xl bg-white rounded-xl border border-gray-200 shadow-sm p-6 sm:p-8">
        <form method="POST" action="{{ route('admin.plans.store') }}" class="space-y-5">
            @csrf

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div class="sm:col-span-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Plan name</label>
                    <input id="name" type="text" name="name" value="{{ old('name') }}" required autofocus
                        placeholder="e.g. Home Fiber 100"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                </div>

                <div>
                    <label for="speed" class="block text-sm font-medium text-gray-700 mb-1.5">Speed</label>
                    <input id="speed" type="text" name="speed" value="{{ old('speed') }}" required
                        placeholder="e.g. 100 Mbps"
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    <x-input-error :messages="$errors->get('speed')" class="mt-1.5" />
                </div>

                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700 mb-1.5">Price</label>
                    <div class="relative">
                        <span class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5 text-sm text-gray-400">₱</span>
                        <input id="price" type="number" name="price" value="{{ old('price') }}" required step="0.01" min="0"
                            placeholder="0.00"
                            class="block w-full rounded-lg border border-gray-300 pl-7 pr-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    </div>
                    <x-input-error :messages="$errors->get('price')" class="mt-1.5" />
                </div>

                <div class="sm:col-span-2">
                    <label for="billing_cycle" class="block text-sm font-medium text-gray-700 mb-1.5">Billing cycle</label>
                    <select id="billing_cycle" name="billing_cycle" required
                        class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                        @foreach (['monthly' => 'Monthly', 'yearly' => 'Yearly'] as $value => $label)
                            <option value="{{ $value }}" {{ old('billing_cycle', 'monthly') === $value ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('billing_cycle')" class="mt-1.5" />
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2">
                <button type="submit"
                    class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                    Create plan
                </button>
                <a href="{{ route('admin.plans.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</x-admin-layout>
