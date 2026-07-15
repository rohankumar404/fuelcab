<div class="p-4 text-sm text-gray-800 dark:text-gray-200">
    @if($reason)
        <p>{{ $reason }}</p>
    @else
        <p class="text-gray-400 italic">No rejection reason provided.</p>
    @endif
</div>
