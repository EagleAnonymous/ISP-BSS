@php $initial = strtoupper(substr($user->name, 0, 1)); @endphp

<x-subscriber-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('My Account') }}
        </h2>
    </x-slot>

    {{-- Script Definition placed first to prevent Alpine initialization errors --}}
    <script>
        function subscriberProfileEditor() {
            return {
                isEditing: false,
                saving: false,
                toast: { show: false, type: 'success', message: '' },

                nameDraft: @json($user->name ?? ''),
                emailDraft: @json($user->email ?? ''),
                phoneDraft: @json($subscriber?->contact ?? ''),
                addressDraft: @json($subscriber?->service_address ?? ''),
                avatarUrl: @json($user->avatar_path ? asset('storage/' . $user->avatar_path) : null),

                enterEdit() {
                    this.isEditing = true;
                },

                cancelEdit() {
                    this.isEditing = false;
                    this.nameDraft = @json($user->name ?? '');
                    this.emailDraft = @json($user->email ?? '');
                    this.phoneDraft = @json($subscriber?->contact ?? '');
                    this.addressDraft = @json($subscriber?->service_address ?? '');
                },

                async saveAll() {
                    this.saving = true;
                    const fields = ['name', 'email', 'phone', 'service_address'];
                    let ok = true;

                    for (const field of fields) {
                        const saved = await this.submitField(field);
                        if (!saved) ok = false;
                    }

                    if (ok) {
                        this.isEditing = false;
                        this.showToast('success', 'Your profile has been updated successfully.');
                    }

                    this.saving = false;
                },

                async submitField(field) {
                    let value, key;

                    if (field === 'name') { value = this.nameDraft; key = 'name'; }
                    else if (field === 'email') { value = this.emailDraft; key = 'email'; }
                    else if (field === 'phone') { value = this.phoneDraft; key = 'phone'; }
                    else if (field === 'service_address') { value = this.addressDraft ?? ''; key = 'service_address'; }
                    else { return true; }

                    try {
                        const res = await fetch('{{ url('/profile/field') }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            },
                            body: JSON.stringify({ field: key, [key]: value }),
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            this.showToast('error', data.message || 'Update failed.');
                            return false;
                        }

                        return true;
                    } catch (err) {
                        this.showToast('error', 'Could not save changes. Please try again.');
                        return false;
                    }
                },

showToast(type, message) {
                    this.toast = { show: true, type, message };
                    setTimeout(() => {
                        this.toast.show = false;
                    }, 3000);
                },
            };
        }
    </script>

    <div class="py-2" x-data="subscriberProfileEditor()">
        <div class="max-w-7xl mx-auto">
            @if (session('status') === 'account-updated')
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    Your account information has been updated.
                </div>
            @endif

            @if (session('status') === 'avatar-updated')
                <div class="mb-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
                    Your profile picture has been updated.
                </div>
            @endif

            {{-- Fixed Toast Notification --}}
            <div x-show="toast.show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform translate-y-[-10px]"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 transform translate-y-0"
                 x-transition:leave-end="opacity-0 transform translate-y-[-10px]"
                 class="fixed top-4 right-4 z-50 rounded-lg px-4 py-3 text-sm font-medium shadow-lg flex items-center gap-2 border bg-white"
                 :class="toast.type === 'success' ? 'border-green-200 text-green-800' : 'border-red-200 text-red-800'">
                <template x-if="toast.type === 'success'">
                    <svg class="h-4 w-4 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 013 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="h-4 w-4 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </template>
                <span x-text="toast.message"></span>
            </div>

            {{-- My Account page header --}}
            <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between mb-6">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">My Account</h1>
                    <p class="mt-1 text-sm text-gray-500">View your personal information and account settings.</p>
                </div>
                <div class="flex items-center gap-3">
                    <template x-if="!isEditing">
                        <button type="button"
                                @click="enterEdit()"
                                class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                            </svg>
                            Edit Profile
                        </button>
                    </template>
                    <template x-if="isEditing">
                        <div class="flex items-center gap-2">
                            <button type="button"
                                    @click="cancelEdit()"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-blue-300 transition">
                                Cancel
                            </button>
                            <button type="button"
                                    @click="saveAll()"
                                    :disabled="saving"
                                    class="inline-flex items-center gap-1.5 rounded-lg border border-transparent bg-blue-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">
                                <template x-if="!saving"><span>Save Changes</span></template>
                                <template x-if="saving">
                                    <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" d="M12 3a9 9 0 109 9" />
                                    </svg>
                                </template>
                            </button>
                        </div>
                    </template>
                </div>
            </div>

            {{-- Two-column responsive account grid --}}
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                {{-- Left column: account information list --}}
                {{-- On mobile: appears second; on desktop: appears first --}}
                <div class="order-2 lg:col-span-2 lg:order-1 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-5 border-b border-gray-200">
                        <h3 class="text-base font-semibold text-gray-900">Account Information</h3>
                    </div>

                    <dl class="divide-y divide-gray-100">
                        {{-- Full Name --}}
                        <div class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                </svg>
                            </span>
                            <dt class="w-40 text-sm font-medium text-gray-500">Full Name</dt>
                            <template x-if="!isEditing">
                                <dd class="flex-1 text-sm font-medium text-gray-900" x-text="nameDraft"></dd>
                            </template>
                            <template x-if="isEditing">
                                <dd class="flex-1">
                                    <input type="text" x-model="nameDraft"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                </dd>
                            </template>
                        </div>

                        {{-- Email Address --}}
                        <div class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                            </span>
                            <dt class="w-40 text-sm font-medium text-gray-500">Email Address</dt>
                            <template x-if="!isEditing">
                                <dd class="flex-1 text-sm font-medium text-gray-900" x-text="emailDraft"></dd>
                            </template>
                            <template x-if="isEditing">
                                <dd class="flex-1">
                                    <input type="email" x-model="emailDraft"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                </dd>
                            </template>
                        </div>

                        {{-- Phone Number --}}
                        <div class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                </svg>
                            </span>
                            <dt class="w-40 text-sm font-medium text-gray-500">Phone Number</dt>
                            <template x-if="!isEditing">
                                <dd class="flex-1 text-sm font-medium text-gray-900" x-text="phoneDraft || '—'"></dd>
                            </template>
                            <template x-if="isEditing">
                                <dd class="flex-1">
                                    <input type="text" x-model="phoneDraft"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                </dd>
                            </template>
                        </div>

                        {{-- Account ID (read-only) --}}
                        <div class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                                </svg>
                            </span>
                            <dt class="w-40 text-sm font-medium text-gray-500">Account ID</dt>
                            <dd class="flex-1 text-sm font-medium text-gray-900">{{ $subscriber?->subscriber_id ?? '—' }}</dd>
                        </div>

                        {{-- Account Status (read-only) --}}
                        <div class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                                </svg>
                            </span>
                            <dt class="w-40 text-sm font-medium text-gray-500">Account Status</dt>
                            <dd>
                                <span @class([
                                    'inline-flex items-center rounded-md px-2 py-1 text-xs font-medium',
                                    'bg-green-50 text-green-700' => ($subscriber?->status ?? 'inactive') === 'active',
                                    'bg-amber-50 text-amber-700' => ($subscriber?->status ?? 'inactive') === 'suspended',
                                    'bg-red-50 text-red-700' => ($subscriber?->status ?? 'inactive') === 'terminated',
                                    'bg-gray-100 text-gray-600' => !in_array($subscriber?->status ?? 'inactive', ['active', 'suspended', 'terminated']),
                                ])>
                                    {{ ucfirst($subscriber?->status ?? 'inactive') }}
                                </span>
                            </dd>
                        </div>

                        {{-- Member Since (read-only) --}}
                        <div class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                            </span>
                            <dt class="w-40 text-sm font-medium text-gray-500">Member Since</dt>
                            <dd class="flex-1 text-sm font-medium text-gray-900">{{ $subscriber?->joined_date?->format('F d, Y') ?? '—' }}</dd>
                        </div>

                        {{-- Service Address --}}
                        <div class="flex items-center gap-4 px-6 py-4">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </span>
                            <dt class="w-40 text-sm font-medium text-gray-500">Service Address</dt>
                            <template x-if="!isEditing">
                                <dd class="flex-1 text-sm font-medium text-gray-900" x-text="addressDraft || '—'"></dd>
                            </template>
                            <template x-if="isEditing">
                                <dd class="flex-1">
                                    <input type="text" x-model="addressDraft"
                                           class="w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                </dd>
                            </template>
                        </div>
                    </dl>
                </div>

                {{-- Right column: profile summary card --}}
                {{-- On mobile: appears first; on desktop: appears second --}}
                <div class="order-1 lg:order-2 bg-blue-50/60 rounded-xl border border-blue-100 shadow-sm overflow-hidden">
                    <div class="flex flex-col items-center px-6 py-8 text-center">
{{-- Profile avatar circle (read-only, no upload for non-admin) --}}
                        <div class="relative">
                            <div class="relative flex h-20 w-20 items-center justify-center rounded-full bg-blue-600 text-2xl font-bold text-white shadow-lg overflow-hidden">
                                <template x-if="avatarUrl">
                                    <img :src="avatarUrl" alt="Avatar" class="h-full w-full object-cover">
                                </template>
                                <template x-if="!avatarUrl">
                                    <img src="{{ asset('image/icon.png') }}" alt="Default Avatar" class="h-full w-full object-cover">
                                </template>
                            </div>
                        </div>
                        <h2 class="mt-4 text-xl font-bold text-gray-900" x-text="nameDraft"></h2>
                        <span class="inline-flex items-center gap-1.5 rounded-full bg-blue-100 px-3 py-1 text-xs font-semibold text-blue-700">
                            <svg class="h-3.5 w-3.5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                            </svg>
                            Subscriber
                        </span>
                    </div>

                    {{-- Divider separating summary and address --}}
                    <div class="border-t border-blue-100 px-6 py-5">
                        <div class="flex items-start gap-3">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-100 text-blue-600">
                                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-semibold text-gray-900">Service Address</p>
                                <p class="mt-1 text-sm text-gray-600" x-text="addressDraft || '—'"></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Display current plan details card --}}
            <div class="mt-6 bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Current Plan</h3>
                </div>

                @if ($subscriber->plan)
                    <dl class="divide-y divide-gray-200">
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Plan Name</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $subscriber->plan->name }}</dd>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Speed</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">{{ $subscriber->plan->speed }}</dd>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Monthly Price</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">₱{{ number_format((float) $subscriber->plan->price, 2) }}</dd>
                        </div>
                        <div class="px-6 py-4 grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <dt class="text-sm font-medium text-gray-500">Billing Cycle</dt>
                            <dd class="text-sm text-gray-900 sm:col-span-2">{{ ucfirst($subscriber->plan->billing_cycle) }}</dd>
                        </div>
                    </dl>
                @else
                    <div class="p-10 text-center">
                        <p class="text-sm text-gray-500">No plan assigned yet.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-subscriber-layout>
