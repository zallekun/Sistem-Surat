@props(['type' => 'view', 'href' => '#', 'onclick' => null, 'title' => null])
@php
    $configs = [
        'view' => [
            'color' => 'text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50',
            'icon' => 'M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z',
            'defaultTitle' => 'Lihat Detail'
        ],
        'edit' => [
            'color' => 'text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50',
            'icon' => 'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
            'defaultTitle' => 'Edit'
        ],
        'approve' => [
            'color' => 'text-green-600 hover:text-green-900 hover:bg-green-50',
            'icon' => 'M5 13l4 4L19 7',
            'defaultTitle' => 'Setujui'
        ],
        'reject' => [
            'color' => 'text-red-600 hover:text-red-900 hover:bg-red-50',
            'icon' => 'M6 18L18 6M6 6l12 12',
            'defaultTitle' => 'Tolak'
        ],
    ];
    $config = $configs[$type] ?? $configs['view'];
    $buttonTitle = $title ?? $config['defaultTitle'];
@endphp

@if($onclick)
    <button type="button" 
            onclick="{{ $onclick }}" 
            class="{{ $config['color'] }} p-1.5 rounded-lg transition-all duration-200 relative group"
            title="{{ $buttonTitle }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $config['icon'] }}"/>
        </svg>
        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none">
            {{ $buttonTitle }}
        </span>
    </button>
@else
    <a href="{{ $href }}" 
       class="{{ $config['color'] }} p-1.5 rounded-lg transition-all duration-200 relative group inline-block"
       title="{{ $buttonTitle }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $config['icon'] }}"/>
        </svg>
        <span class="absolute -top-8 left-1/2 transform -translate-x-1/2 px-2 py-1 bg-gray-900 text-white text-xs rounded opacity-0 group-hover:opacity-100 transition-opacity duration-200 whitespace-nowrap pointer-events-none z-10">
            {{ $buttonTitle }}
        </span>
    </a>
@endif