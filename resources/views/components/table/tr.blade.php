@props(['hoverable' => true])
<tr {{ $attributes->merge(['class' => $hoverable ? 'hover:bg-gray-50 transition-colors duration-200' : '']) }}>
    {{ $slot }}
</tr>