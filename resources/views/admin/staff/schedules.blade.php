<x-admin-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between mb-6">
                <h1 class="text-2xl font-bold text-gray-900">Staff Schedules</h1>
                <a href="{{ route('admin.technical-staff.index') }}" class="text-sm font-medium text-gray-500 hover:text-gray-700">
                    &larr; Back to Technical Staff
                </a>
            </div>

            @if(session('status'))
            <div class="mb-4 rounded-md bg-green-50 p-4 text-sm text-green-700">
                {{ session('status') }}
            </div>
            @endif

            <div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-sm font-semibold text-gray-900">Weekly Shift Schedule</h2>
                    <p class="mt-1 text-xs text-gray-500">Set day or night shifts for each day. Leave staff empty for default schedule.</p>
                </div>

                <form method="POST" action="{{ route('admin.technical-staff.schedules.store') }}" class="divide-y divide-gray-200">
                    @csrf

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Day</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Shift</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Start Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">End Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Staff</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @php
                                    $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                @endphp

                                @for($i = 0; $i < 7; $i++)
                                    @php
                                        $existing = $schedules[$i]->first();
                                    @endphp
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            {{ $days[$i] }}
                                            <input type="hidden" name="day_of_week" value="{{ $i }}">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select name="shift_type" class="rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                                <option value="day" @selected(($existing?->shift_type ?? 'day') === 'day')>Day Shift</option>
                                                <option value="night" @selected(($existing?->shift_type ?? 'day') === 'night')>Night Shift</option>
                                            </select>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="time" name="start_time" value="{{ $existing?->start_time ? \Carbon\Carbon::parse($existing->start_time)->format('H:i') : '08:00' }}" class="rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <input type="time" name="end_time" value="{{ $existing?->end_time ? \Carbon\Carbon::parse($existing->end_time)->format('H:i') : '17:00' }}" class="rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <select name="technical_staff_id" class="rounded-md border-gray-300 text-sm focus:border-blue-500 focus:ring-1 focus:ring-blue-500">
                                                <option value="">Default (All Staff)</option>
                                                @foreach($staff as $member)
                                                    <option value="{{ $member->id }}" @selected($existing?->technical_staff_id == $member->id)>
                                                        {{ $member->user->name ?? 'Staff #'.$member->id }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    </div>

                    <div class="px-6 py-4 bg-gray-50 flex justify-end">
                        <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                            Save Schedule
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-admin-layout>
