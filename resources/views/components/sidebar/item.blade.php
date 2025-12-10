@props(['route', 'icon' => null, 'label', 'badge' => null])

@php
$isActive = request()->routeIs($route) || request()->routeIs($route.'.*');
@endphp

<li>
    <a href="{{ route($route) }}"
        class="flex items-center px-4 py-2.5 text-sm font-medium rounded-lg transition-colors
            {{ $isActive
                ? 'bg-blue-100 text-blue-700 dark:bg-blue-900 dark:text-blue-200'
                : 'text-gray-700 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-700' }}">
        @if($icon)
            <x-icon :name="$icon" class="w-5 h-5 mr-3" />
        @endif
        <span class="flex-1">{{ __($label) }}</span>
        @if($badge)
            <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-red-600 rounded-full">
                {{ $badge }}
            </span>
        @endif
    </a>
</li>
