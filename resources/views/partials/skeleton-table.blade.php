{{-- resources/views/partials/skeleton-table.blade.php --}}
<div class="bg-white rounded-xl border border-gray-200 shadow-sm overflow-hidden animate-pulse">
    <div class="px-6 py-4 border-b border-gray-200">
        <div class="h-4 w-48 bg-gray-200 rounded"></div>
    </div>
    <div class="divide-y divide-gray-200">
        @for ($i = 0; $i < 5; $i++)
            <div class="px-6 py-4 flex items-center gap-4">
                <div class="h-4 flex-1 bg-gray-200 rounded"></div>
                <div class="h-4 w-24 bg-gray-200 rounded"></div>
                <div class="h-4 w-20 bg-gray-200 rounded"></div>
                <div class="h-4 w-20 bg-gray-200 rounded"></div>
                <div class="h-4 w-24 bg-gray-200 rounded"></div>
            </div>
        @endfor
    </div>
</div>
