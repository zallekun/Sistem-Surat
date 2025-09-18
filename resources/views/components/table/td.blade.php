@props(['align' => 'left', 'wrap' => false])
@php
    $alignClass = match($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left'
    };
    $wrapClass = $wrap ? 'text-wrap break-words' : 'whitespace-nowrap';
@endphp
<td {{ $attributes->merge(['class' => "px-6 py-4 $wrapClass $alignClass text-sm text-gray-900"]) }}>
    {{ $slot }}
</td>