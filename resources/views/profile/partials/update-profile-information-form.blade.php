<section class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
    <div class="px-6 py-5 border-b border-gray-200">
        <h3 class="text-base font-semibold text-gray-900">Account Information</h3>
        <p class="mt-1 text-xs text-gray-500">Click the pencil to edit each field.</p>
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
            <dd class="flex-1 text-sm font-medium text-gray-900">
                <template x-if="editingField !== 'name'">
                    <div class="flex items-center gap-2">
                        <span>{{ $user->name }}</span>
                        <span x-show="saveStatus === 'success'" class="text-green-600 text-xs">✓ Saved</span>
                        <span x-show="saveStatus === 'error'" class="text-red-600 text-xs">✗ Failed</span>
                    </div>
                </template>
                <template x-if="editingField === 'name'">
                    <form @submit.prevent="submitField('name', $refs.nameInput.value, $refs.nameInput)" class="flex items-center gap-2">
                        @csrf
                        <input type="text" name="name" x-ref="nameInput" value="{{ $user->name }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <button type="submit" x-bind:disabled="saving" class="shrink-0 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">Save</button>
                        <button type="button" @click="editingField = null" class="shrink-0 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                    </form>
                </template>
            </dd>
            <button type="button" @click="startEdit('name', $refs.nameInput)" class="shrink-0 text-gray-400 hover:text-blue-600 transition" title="Edit Full Name">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </button>
        </div>

        {{-- Email Address --}}
        <div class="flex items-center gap-4 px-6 py-4">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                </svg>
            </span>
            <dt class="w-40 text-sm font-medium text-gray-500">Email Address</dt>
            <dd class="flex-1 text-sm font-medium text-gray-900">
                <template x-if="editingField !== 'email'">
                    <div class="flex items-center gap-2">
                        <span>{{ $user->email }}</span>
                        <span x-show="saveStatus === 'success'" class="text-green-600 text-xs">✓ Saved</span>
                        <span x-show="saveStatus === 'error'" class="text-red-600 text-xs">✗ Failed</span>
                    </div>
                </template>
                <template x-if="editingField === 'email'">
                    <form @submit.prevent="submitField('email', $refs.emailInput.value, $refs.emailInput)" class="flex items-center gap-2">
                        @csrf
                        <input type="email" name="email" x-ref="emailInput" value="{{ $user->email }}"
                               class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                        <button type="submit" x-bind:disabled="saving" class="shrink-0 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">Save</button>
                        <button type="button" @click="editingField = null" class="shrink-0 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                    </form>
                </template>
            </dd>
            <button type="button" @click="startEdit('email', $refs.emailInput)" class="shrink-0 text-gray-400 hover:text-blue-600 transition" title="Edit Email Address">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </button>
        </div>

        {{-- Phone Number --}}
        @if ($isSubscriber || $isStaff)
        <div class="flex items-center gap-4 px-6 py-4">
            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
                </svg>
            </span>
            <dt class="w-40 text-sm font-medium text-gray-500">Phone Number</dt>
            <dd class="flex-1 text-sm font-medium text-gray-900">
                @if ($isSubscriber)
                    <template x-if="editingField !== 'phone'">
                        <div class="flex items-center gap-2">
                            <span>{{ $subscriber?->contact ?? '—' }}</span>
                            <span x-show="saveStatus === 'success'" class="text-green-600 text-xs">✓ Saved</span>
                            <span x-show="saveStatus === 'error'" class="text-red-600 text-xs">✗ Failed</span>
                        </div>
                    </template>
                    <template x-if="editingField === 'phone'">
                        <form @submit.prevent="submitField('phone', $refs.phoneInput.value, $refs.phoneInput)" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="phone" x-ref="phoneInput" value="{{ $subscriber?->contact ?? '' }}"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <button type="submit" x-bind:disabled="saving" class="shrink-0 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">Save</button>
                            <button type="button" @click="editingField = null" class="shrink-0 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                        </form>
                    </template>
                @else
                    <template x-if="editingField !== 'phone'">
                        <div class="flex items-center gap-2">
                            <span>{{ $technicalStaff?->phone ?? '—' }}</span>
                            <span x-show="saveStatus === 'success'" class="text-green-600 text-xs">✓ Saved</span>
                            <span x-show="saveStatus === 'error'" class="text-red-600 text-xs">✗ Failed</span>
                        </div>
                    </template>
                    <template x-if="editingField === 'phone'">
                        <form @submit.prevent="submitField('phone', $refs.phoneInput.value, $refs.phoneInput)" class="flex items-center gap-2">
                            @csrf
                            <input type="text" name="phone" x-ref="phoneInput" value="{{ $technicalStaff?->phone ?? '' }}"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <button type="submit" x-bind:disabled="saving" class="shrink-0 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition disabled:opacity-50">Save</button>
                            <button type="button" @click="editingField = null" class="shrink-0 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                        </form>
                    </template>
                @endif
            </dd>
            <button type="button" @click="startEdit('phone', $refs.phoneInput)" class="shrink-0 text-gray-400 hover:text-blue-600 transition" title="Edit Phone Number">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                </svg>
            </button>
        </div>
        @endif

        {{-- Subscriber-only rows --}}
        @if ($isSubscriber)
            {{-- Account ID --}}
            <div class="flex items-center gap-4 px-6 py-4">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                    </svg>
                </span>
                <dt class="w-40 text-sm font-medium text-gray-500">Account ID</dt>
                <dd class="flex-1 text-sm font-medium text-gray-900">{{ $subscriber?->subscriber_id ?? '—' }}</dd>
            </div>

            {{-- Account Status --}}
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

            {{-- Member Since --}}
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
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                </span>
                <dt class="w-40 text-sm font-medium text-gray-500">Service Address</dt>
                <dd class="flex-1 text-sm font-medium text-gray-900">
                    <template x-if="editingField !== 'service_address'">
                        <span x-text="serviceAddress || '—'"></span>
                    </template>
                    <template x-if="editingField === 'service_address'">
                        <div class="flex items-center gap-2">
                            <input type="text" x-ref="addressInput" x-model="serviceAddressDraft"
                                   @keydown.enter="saveAddress()"
                                   class="block w-full rounded-lg border border-gray-300 px-3 py-1.5 text-sm text-gray-900 shadow-sm focus:border-blue-600 focus:ring-1 focus:ring-blue-600">
                            <button type="button" @click="saveAddress()" class="shrink-0 rounded-lg bg-blue-600 px-3 py-1.5 text-xs font-semibold text-white shadow-sm hover:bg-blue-700 transition">Save</button>
                            <button type="button" @click="editingField = null" class="shrink-0 rounded-lg border border-gray-300 px-3 py-1.5 text-xs font-semibold text-gray-600 hover:bg-gray-50 transition">Cancel</button>
                        </div>
                    </template>
                </dd>
                <button type="button" @click="startEdit('service_address', $refs.addressInput)" class="shrink-0 text-gray-400 hover:text-blue-600 transition" title="Edit Service Address">
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg>
                </button>
            </div>
        @endif

        {{-- Staff-only rows --}}
        @if ($isStaff)
            {{-- Employee ID --}}
            <div class="flex items-center gap-4 px-6 py-4">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 9h3.75M15 12h3.75M15 15h3.75M4.5 19.5h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5zm6-10.125a1.875 1.875 0 11-3.75 0 1.875 1.875 0 013.75 0zm1.294 6.336a6.721 6.721 0 01-3.17.789 6.721 6.721 0 01-3.168-.789 3.376 3.376 0 016.338 0z" />
                    </svg>
                </span>
                <dt class="w-40 text-sm font-medium text-gray-500">Employee ID</dt>
                <dd class="flex-1 text-sm font-medium text-gray-900">{{ $user->employee_id ?? '—' }}</dd>
            </div>

            {{-- Role --}}
            <div class="flex items-center gap-4 px-6 py-4">
                <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-blue-50 text-blue-600">
                    <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 14.15v4.25c0 1.094-.787 2.036-1.872 2.18-2.087.3-4.216.43-6.378.43a56.78 56.78 0 01-6.378 0c-1.085-.144-1.872-1.086-1.872-2.18v-4.25m16.5 0a2.25 2.25 0 00-2.25-2.25H6a2.25 2.25 0 00-2.25 2.25v4.25m16.5 0a2.25 2.25 0 01-2.25 2.25h-1.25a2.25 2.25 0 01-2.25-2.25v-4.25" />
                    </svg>
                </span>
                <dt class="w-40 text-sm font-medium text-gray-500">Role</dt>
                <dd class="flex-1 text-sm font-medium text-gray-900">{{ $user->getRoleNames()->first()?->name ?? '—' }}</dd>
            </div>
        @endif
    </dl>
</section>

