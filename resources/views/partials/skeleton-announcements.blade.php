{{-- resources/views/partials/skeleton-announcements.blade.php --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden animate-pulse">
    <div class="px-6 py-4 border-b border-gray-200 flex items-center justify-between">
        <div class="h-4 w-40 bg-gray-200 rounded"></div>
        <div class="h-4 w-16 bg-gray-200 rounded"></div>
    </div>
    <div class="divide-y divide-gray-100">
        @for ($i = 0; $i < 4; $i++)
            <div class="px-6 py-4">
                <div class="flex items-start gap-4">
                    <div class="h-10 w-10 shrink-0 rounded-full bg-gray-200"></div>
                    <div class="flex-1 min-w-0">
                        <div class="h-4 w-3/4 bg-gray-200 rounded"></div>
                        <div class="mt-2 h-3 w-full bg-gray-200 rounded"></div>
                        <div class="mt-1 h-3 w-2/3 bg-gray-200 rounded"></div>
                        <div class="mt-3 h-3 w-24 bg-gray-200 rounded"></div>
                    </div>
                </div>
            </div>
        @endfor
    </div>
</div>
