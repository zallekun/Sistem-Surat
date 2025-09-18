@props(['align' => 'left', 'width' => null])
@php
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left'
    };
@endphp
<th 
    @if($width) style="width: {{ $width }}" @endif
    {{ $attributes->merge(['class' => "px-6 py-3 $alignClass text-xs font-medium text-gray-500 uppercase tracking-wider"]) }}
>
    {{ $slot }}
</th>