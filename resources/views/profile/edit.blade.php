@php
    $initial = strtoupper(substr($user->name ?? 'A', 0, 1));
    $isSubscriber = $user->hasRole('subscriber');
    $isAdmin = $user->hasRole('admin');
    $isStaff = $user->hasRole('technical_staff');

$layout = $isSubscriber ? 'subscriber' : ($isAdmin ? 'admin' : ($isStaff ? 'staff' : 'subscriber'));

    $roleLabel = $isSubscriber ? 'Network Technician' : ($isAdmin ? 'Administrator' : ($isStaff ? 'Technical Staff' : 'Member'));

    $weekStart = \Illuminate\Support\Carbon::now()->startOfWeek(\Carbon\Carbon::MONDAY);
    $scheduleDays = collect();
    for ($i = 0; $i < 5; $i++) {
        $scheduleDays[] = $weekStart->copy()->addDays($i);
    }
@endphp

@component("layouts.{$layout}")
    @slot('header')
        <div class="flex items-center justify-between gap-4">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    My Account
                </h2>
                <p class="text-sm text-gray-500 mt-0.5">Manage your profile, personal information, and schedule.</p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('dashboard') }}"
                   class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-blue-300 hover:text-blue-700 transition">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 15L15 9m0 0H9m6 0v6" />
                    </svg>
                    Back to Dashboard
                </a>
            </div>
        </div>
    @endslot

    <div class="py-6 bg-slate-50 min-h-screen">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="profileEditor()" x-cloak>
            {{-- Toast notification --}}
<div x-show="toast.show" x-transition.duration.300ms
                 class="fixed top-4 right-4 z-50 rounded-lg px-4 py-3 text-sm font-medium shadow-lg flex items-center gap-2 border"
                 :class="toast.type === 'success' ? 'bg-white border-green-200 text-green-800' : 'bg-white border-red-200 text-red-800'">
                <template x-if="toast.type === 'success'">
                    <svg class="h-4 w-4 shrink-0 text-green-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 013 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03-9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
                    </svg>
                </template>
                <template x-if="toast.type === 'error'">
                    <svg class="h-4 w-4 shrink-0 text-red-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                    </svg>
                </template>
                <span x-text="toast.message"></span>
            </div>

            {{-- Top Row: Profile Sidebar + Personal Information --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">

                {{-- Left Column: Profile Card (read-only info list) --}}
                <div class="lg:col-span-1">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
{{-- Avatar (read-only for non-admin; upload for admin only) --}}
                        <div class="px-6 pt-6 flex flex-col items-center text-center">
                            <div class="relative inline-block">
                                <div class="flex h-20 w-20 items-center justify-center rounded-full bg-blue-600 text-2xl font-bold text-white shadow-lg ring-4 ring-white overflow-hidden">
                                    <template x-if="avatarUrl">
                                        <img :src="avatarUrl" alt="Avatar" class="h-full w-full object-cover">
                                    </template>
                                    <template x-if="!avatarUrl">
                                        <img src="{{ asset('image/icon.png') }}" alt="Default Avatar" class="h-full w-full object-cover">
                                    </template>
                                </div>
                                @if ($isAdmin)
                                <label for="profile-avatar-upload" title="Upload Profile Picture"
                                       class="absolute bottom-0 right-0 flex h-7 w-7 cursor-pointer items-center justify-center rounded-full bg-white text-blue-600 shadow-md border border-gray-200 hover:bg-blue-50 transition">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 12.75a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                                    </svg>
                                </label>
                                <input id="profile-avatar-upload" type="file" name="avatar" accept=".jpg,.jpeg,.png,.webp" class="hidden" @change="uploadAvatar($event)">
                                @endif
                            </div>
                            <h2 class="mt-4 text-lg font-bold text-gray-900">{{ $user->name }}</h2>
                            <p class="text-sm text-gray-500 mt-0.5">{{ $roleLabel }}</p>
                        </div>

                        {{-- Contact info list (read-only) --}}
                        <div class="mt-4 px-6 pb-4 space-y-3 text-left">
                            <div class="flex items-center gap-3 text-sm text-gray-600">
                                <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                    <path stroke-linecap="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                                </svg>
                                <span class="flex-1 truncate text-gray-900">{{ $user->email }}</span>
                            </div>

                            @if ($isSubscriber || $isStaff)
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                                    </svg>
                                    <span class="flex-1 truncate text-gray-900">
                                        @if ($isSubscriber)
                                            {{ $subscriber?->contact ?? '—' }}
                                        @elseif ($isStaff)
                                            {{ $technicalStaff?->phone ?? '—' }}
                                        @endif
                                    </span>
                                </div>
                            @endif

                            @if ($isSubscriber)
                                <div class="flex items-center gap-3 text-sm text-gray-600">
                                    <svg class="h-4 w-4 shrink-0 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0zM15 10.5c0 .898-.751 1.626-1.5 1.626S12 12.399 12 11.5s.751-1.626 1.5-1.626 1.5.727 1.5 1.626z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                    </svg>
                                    <span class="flex-1 truncate text-gray-900">{{ $subscriber?->service_address ?? '—' }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- Member since footer --}}
                        <div class="border-t border-gray-100 px-6 py-3 text-center">
                            <p class="text-xs text-gray-400">
                                Member since {{ ($subscriber?->joined_date ?? $user->created_at)?->format('F d, Y') ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Personal Information Card --}}
                <div class="lg:col-span-2">
                    <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">Personal Information</h3>
                                <p class="mt-0.5 text-xs text-gray-500">Update your personal details and contact information.</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <template x-if="!isEditing">
                                    <button type="button"
                                            @click="enterEdit()"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:border-blue-300 transition">
                                        Edit Information
                                    </button>
                                </template>
                                <template x-if="isEditing">
                                    <button type="button"
                                            @click="cancelEdit()"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:border-blue-300 transition">
                                        Cancel
                                    </button>
                                    <button type="button"
                                            @click="saveAll()"
                                            :disabled="saving"
                                            class="inline-flex items-center gap-1.5 rounded-lg border border-transparent bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                                        <template x-if="!saving">Save Changes</template>
                                        <template x-if="saving">
                                            <svg class="h-3.5 w-3.5 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" d="M12 3a9 9 0 109 9" />
                                            </svg>
                                        </template>
                                    </button>
                                </template>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-6">
                            {{-- Full Name (editable: all roles) --}}
                            <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                <span class="text-sm font-medium text-gray-500 w-36">Full Name</span>
                                <template x-if="!isEditing">
                                    <span class="flex-1 text-sm text-gray-900" x-text="nameDraft"></span>
                                </template>
                                <template x-if="isEditing">
                                    <input type="text" x-model="nameDraft"
                                           class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                </template>
                            </div>

                            {{-- Email Address (editable: all roles) --}}
                            <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                <span class="text-sm font-medium text-gray-500 w-36">Email Address</span>
                                <template x-if="!isEditing">
                                    <span class="flex-1 text-sm text-gray-900" x-text="emailDraft"></span>
                                </template>
                                <template x-if="isEditing">
                                    <input type="email" x-model="emailDraft"
                                           class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                </template>
                            </div>

                            {{-- Phone Number (editable: subscriber & staff) --}}
                            @if ($isSubscriber || $isStaff)
                                <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                    <span class="text-sm font-medium text-gray-500 w-36">Phone Number</span>
                                    <template x-if="!isEditing">
                                        <span class="flex-1 text-sm text-gray-900" x-text="phoneDraft"></span>
                                    </template>
                                    <template x-if="isEditing">
                                        <input type="text" x-model="phoneDraft"
                                               class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                    </template>
                                </div>
                            @endif

                            {{-- Location (editable: subscriber only) --}}
                            @if ($isSubscriber)
                                <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                    <span class="text-sm font-medium text-gray-500 w-36">Location</span>
                                    <template x-if="!isEditing">
                                        <span class="flex-1 text-sm text-gray-900" x-text="locationDraft"></span>
                                    </template>
                                    <template x-if="isEditing">
                                        <input type="text" x-model="locationDraft"
                                               class="flex-1 rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600" />
                                    </template>
                                </div>
                            @endif

                            {{-- Employee ID (READ-ONLY: staff) --}}
                            @if ($isStaff)
                                <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                    <span class="text-sm font-medium text-gray-500 w-36">Employee ID</span>
                                    <span class="flex-1 text-sm text-gray-900">{{ $user->employee_id ?? '—' }}</span>
                                </div>
                            @endif

                            {{-- Role (READ-ONLY: all roles) --}}
                            <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                <span class="text-sm font-medium text-gray-500 w-36">Role</span>
                                <span class="flex-1 text-sm text-gray-900">{{ $roleLabel }}</span>
                            </div>

                            {{-- Department (READ-ONLY: staff) --}}
                            @if ($isStaff)
                                <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                    <span class="text-sm font-medium text-gray-500 w-36">Department</span>
                                    <span class="flex-1 text-sm text-gray-900">{{ $technicalStaff?->department ?? '—' }}</span>
                                </div>
                            @endif

                            {{-- Supervisor (READ-ONLY: staff) --}}
                            @if ($isStaff)
                                <div class="flex items-baseline justify-between py-3 sm:py-2 border-b border-gray-100 sm:border-b-0">
                                    <span class="text-sm font-medium text-gray-500 w-36">Supervisor</span>
                                    <span class="flex-1 text-sm text-gray-900">{{ $technicalStaff?->supervisor ?? '—' }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bottom Row: My Schedule (technical staff only) --}}
            @if ($isStaff)
                <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            <div>
                                <h3 class="text-base font-semibold text-gray-900">My Schedule</h3>
                                <p class="mt-0.5 text-xs text-gray-500">View your upcoming work shifts.</p>
                            </div>
                        </div>
                        <button type="button"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm hover:border-blue-300 transition">
                            View Full Schedule
                        </button>
                    </div>

<div class="divide-y divide-gray-100">
                         @foreach ($scheduleDays as $day)
                             <div class="px-6 py-4 flex flex-row items-center gap-4">
                                 <span class="font-medium text-gray-500">{{ $day->format('D, M j') }},</span>
                                 <span class="text-gray-900">8:00 AM - 5:00 PM,</span>
                                 <span class="inline-flex items-center rounded-full bg-green-50 px-2.5 py-0.5 text-xs font-semibold text-green-700">Day Shift</span>
                             </div>
                         @endforeach
                     </div>

                    <div class="px-6 py-3 border-t border-gray-100 flex items-center gap-2 text-xs text-gray-500">
                        <svg class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>All times are in (GMT+8) Asia/Manila</span>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        function profileEditor() {
            return {
                isEditing: false,
                saving: false,
                toast: { show: false, type: 'success', message: '' },

                nameDraft: @json($user->name),
                emailDraft: @json($user->email),
                phoneDraft: @json($isStaff ? ($technicalStaff?->phone ?? '') : ($subscriber?->contact ?? '')),
                @if ($isSubscriber)
                locationDraft: @json($subscriber?->service_address ?? ''),
                @endif
avatarUrl: @json($user->avatar_path ? asset('storage/' . $user->avatar_path) : asset('image/icon.png')),

                enterEdit() {
                    this.isEditing = true;
                },

                cancelEdit() {
                    this.isEditing = false;
                    this.nameDraft = @json($user->name);
                    this.emailDraft = @json($user->email);
                    this.phoneDraft = @json($isStaff ? ($technicalStaff?->phone ?? '') : ($subscriber?->contact ?? ''));
                    @if ($isSubscriber)
                    this.locationDraft = @json($subscriber?->service_address ?? '');
                    @endif
                },

                async saveAll() {
                    this.saving = true;
                    const fields = ['name', 'email', 'phone'@if ($isSubscriber) , 'service_address'@endif];
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
                    else if (field === 'service_address') { value = this.locationDraft ?? ''; key = 'service_address'; }
                    else { return true; }

                    try {
                        const res = await fetch('{{ route('profile.update-field') }}', {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
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

async uploadAvatar(event) {
                    const file = event.target.files[0];
                    if (!file) return;

                    // Client-side size guard (mirrors server limit of 2MB).
                    if (file.size > 2 * 1024 * 1024) {
                        this.showToast('error', 'Image is too large. Please choose a photo under 2MB.');
                        event.target.value = '';
                        return;
                    }

                    this.saving = true;
                    const formData = new FormData();
                    formData.append('avatar', file);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    try {
                        const res = await fetch('{{ url('/profile/avatar') }}', {
                            method: 'PATCH',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: formData,
                        });

                        const data = await res.json();

                        if (!res.ok || !data.success) {
                            throw new Error(data.message || 'Avatar upload failed.');
                        }

                        this.avatarUrl = data.avatar_url || @json(asset('image/icon.png'));
                        this.showToast('success', 'Your profile picture has been updated.');
                    } catch (err) {
                        this.showToast('error', err.message || 'Avatar upload failed. Please try again.');
                    } finally {
                        this.saving = false;
                        event.target.value = '';
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
@endcomponent
