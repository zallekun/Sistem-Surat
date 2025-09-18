@props(['title' => null])
<div class="bg-white shadow-sm rounded-lg overflow-hidden">
    @if($title)
    <div class="px-4 py-5 border-b border-gray-200 sm:px-6">
        <h3 class="text-lg leading-6 font-medium text-gray-900">
            {{ $title }}
        </h3>
    </div>
    @endif
    <div class="overflow-x-auto">
        {{ $slot }}
    </div>
</div>