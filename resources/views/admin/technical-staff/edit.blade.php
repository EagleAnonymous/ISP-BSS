<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-900">Edit Technical Staff</h2>
                <p class="mt-1 text-sm text-gray-500">Update their profile and work details.</p>
            </div>
            <a href="{{ route('admin.technical-staff.index') }}"
               class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:border-blue-300 transition">
                &larr; Back to list
            </a>
        </div>
    </x-slot>

    @if (session('status') === 'technical-staff-updated')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Staff profile updated successfully.
        </div>
    @elseif (session('status') === 'avatar-updated')
        <div class="mb-6 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-700">
            Profile photo updated successfully.
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Avatar upload card --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h3 class="text-sm font-semibold text-gray-900">Profile Photo</h3>
                </div>

                <div class="px-6 py-6 flex flex-col items-center text-center">
<div class="flex h-28 w-28 items-center justify-center rounded-full bg-gray-100 text-4xl font-bold text-gray-400 shadow-inner overflow-hidden ring-4 ring-gray-100">
                        @if ($technicalStaff->user->avatar_path)
                            <img src="{{ asset('storage/' . $technicalStaff->user->avatar_path) }}" alt="Avatar" class="h-full w-full object-cover">
                        @else
                            <img src="{{ asset('image/icon.png') }}" alt="Default Avatar" class="h-full w-full object-cover">
                        @endif
                    </div>

                    <h4 class="mt-4 text-lg font-bold text-gray-900">{{ $technicalStaff->user->name }}</h4>
                    <p class="text-sm text-gray-500">{{ $technicalStaff->position ?? 'Technical Staff' }}</p>

                    <form method="POST" action="{{ route('admin.technical-staff.avatar', $technicalStaff) }}"
                          class="mt-6 w-full" enctype="multipart/form-data">
                        @csrf
                        <label for="avatar" class="block w-full cursor-pointer rounded-lg border-2 border-dashed border-gray-300 px-4 py-4 text-center hover:border-blue-400 hover:bg-blue-50/40 transition">
                            <svg class="mx-auto h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 12.75a.75.75 0 100-1.5.75.75 0 000 1.5z" />
                            </svg>
                            <span class="mt-2 block text-sm font-medium text-gray-700">Click to upload a photo</span>
                            <span class="mt-0.5 block text-xs text-gray-400">JPG, PNG, GIF or WebP up to 10MB</span>
                        </label>
                        <input id="avatar" type="file" name="avatar" accept=".jpg,.jpeg,.png,.gif,.webp" class="hidden">
                        <x-input-error :messages="$errors->get('avatar')" class="mt-2" />

                        <button type="submit"
                                class="mt-4 w-full rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            Upload Photo
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Right: Profile details form --}}
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-6">
                <form method="POST" action="{{ route('admin.technical-staff.update', $technicalStaff) }}" class="space-y-6">
                    @csrf
                    @method('PATCH')

                    <div>
                        <h3 class="text-sm font-semibold text-gray-900 mb-4">Account Information</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                            <div class="sm:col-span-2">
                                <label for="name" class="block text-sm font-medium text-gray-700 mb-1.5">Full name</label>
                                <input id="name" type="text" name="name" value="{{ old('name', $technicalStaff->user->name) }}" required
                                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <x-input-error :messages="$errors->get('name')" class="mt-1.5" />
                            </div>

                            <div class="sm:col-span-2">
                                <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email address</label>
                                <input id="email" type="email" name="email" value="{{ old('email', $technicalStaff->user->email) }}" required
                                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <x-input-error :messages="$errors->get('email')" class="mt-1.5" />
                            </div>

                            <div>
                                <label for="phone" class="block text-sm font-medium text-gray-700 mb-1.5">Phone number</label>
                                <input id="phone" type="text" name="phone" value="{{ old('phone', $technicalStaff->phone ?? '') }}"
                                    placeholder="+63 900 000 0000"
                                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <x-input-error :messages="$errors->get('phone')" class="mt-1.5" />
                            </div>

                            <div>
                                <label for="position" class="block text-sm font-medium text-gray-700 mb-1.5">Position</label>
                                <input id="position" type="text" name="position" value="{{ old('position', $technicalStaff->position ?? '') }}"
                                    placeholder="Network Technician"
                                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <x-input-error :messages="$errors->get('position')" class="mt-1.5" />
                            </div>

                            <div>
                                <label for="department" class="block text-sm font-medium text-gray-700 mb-1.5">Department</label>
                                <input id="department" type="text" name="department" value="{{ old('department', $technicalStaff->department ?? '') }}"
                                    placeholder="e.g. Field Operations"
                                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <x-input-error :messages="$errors->get('department')" class="mt-1.5" />
                            </div>

                            <div>
                                <label for="supervisor" class="block text-sm font-medium text-gray-700 mb-1.5">Supervisor</label>
                                <input id="supervisor" type="text" name="supervisor" value="{{ old('supervisor', $technicalStaff->supervisor ?? '') }}"
                                    placeholder="Supervisor name"
                                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <x-input-error :messages="$errors->get('supervisor')" class="mt-1.5" />
                            </div>

                            <div class="sm:col-span-2">
                                <label for="location" class="block text-sm font-medium text-gray-700 mb-1.5">Location</label>
                                <input id="location" type="text" name="location" value="{{ old('location', $technicalStaff->location ?? '') }}"
                                    placeholder="e.g. Brgy. San Isidro, Puerto Princesa"
                                    class="block w-full rounded-lg border border-gray-300 px-3.5 py-2.5 text-sm text-gray-900 placeholder:text-gray-400 shadow-sm transition focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                <x-input-error :messages="$errors->get('location')" class="mt-1.5" />
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2 border-t border-gray-100">
                        <button type="submit"
                            class="rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 transition">
                            Save changes
                        </button>
                        <a href="{{ route('admin.technical-staff.index') }}" class="text-sm font-medium text-gray-600 hover:text-gray-900">
                            Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
