{{-- resources/views/components/table/wrapper.blade.php --}}
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

{{-- resources/views/components/table/table.blade.php --}}
<table {{ $attributes->merge(['class' => 'min-w-full divide-y divide-gray-200']) }}>
    {{ $slot }}
</table>

{{-- resources/views/components/table/thead.blade.php --}}
<thead {{ $attributes->merge(['class' => 'bg-gray-50']) }}>
    {{ $slot }}
</thead>

{{-- resources/views/components/table/tbody.blade.php --}}
<tbody {{ $attributes->merge(['class' => 'bg-white divide-y divide-gray-200']) }}>
    {{ $slot }}
</tbody>

{{-- resources/views/components/table/th.blade.php --}}
@props(['align' => 'left', 'sortable' => false, 'width' => null])
@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',
        'right' => 'text-right'
    ];
    $alignClass = $alignClasses[$align] ?? 'text-left';
@endphp
<th 
    @if($width) style="width: {{ $width }}" @endif
    {{ $attributes->merge(['class' => "px-6 py-3 $alignClass text-xs font-medium text-gray-500 uppercase tracking-wider"]) }}
>
    @if($sortable)
        <button class="group inline-flex items-center">
            {{ $slot }}
            <span class="ml-2 flex-none rounded text-gray-400 group-hover:text-gray-500">
                <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M7 7l3-3 3 3m0 6l-3 3-3-3"/>
                </svg>
            </span>
        </button>
    @else
        {{ $slot }}
    @endif
</th>

{{-- resources/views/components/table/td.blade.php --}}
@props(['align' => 'left', 'wrap' => false])
@php
    $alignClasses = [
        'left' => 'text-left',
        'center' => 'text-center',  
        'right' => 'text-right'
    ];
    $alignClass = $alignClasses[$align] ?? 'text-left';
    $wrapClass = $wrap ? '' : 'whitespace-nowrap';
@endphp
<td {{ $attributes->merge(['class' => "px-6 py-4 $wrapClass $alignClass text-sm text-gray-900"]) }}>
    {{ $slot }}
</td>

{{-- resources/views/components/table/tr.blade.php --}}
@props(['hoverable' => true])
<tr {{ $attributes->merge(['class' => $hoverable ? 'hover:bg-gray-50 transition-colors duration-200' : '']) }}>
    {{ $slot }}
</tr>

{{-- resources/views/components/table/status.blade.php --}}
@props(['status'])
@php
    $statusConfig = [
        'draft' => ['bg-gray-100 text-gray-800', 'Draft'],
        'review_kaprodi' => ['bg-yellow-100 text-yellow-800', 'Review'],
        'disetujui_kaprodi' => ['bg-blue-100 text-blue-800', 'Disetujui'],
        'ditolak_kaprodi' => ['bg-red-100 text-red-800', 'Ditolak'],
        'diproses_fakultas' => ['bg-indigo-100 text-indigo-800', 'Proses'],
        'disetujui_fakultas' => ['bg-green-100 text-green-800', 'Selesai'],
        'ditolak_fakultas' => ['bg-red-100 text-red-800', 'Ditolak'],
        'ditolak_umum' => ['bg-red-100 text-red-800', 'Ditolak'],
        'revisi_opsional' => ['bg-orange-100 text-orange-800', 'Revisi'],
    ];
    $statusCode = $status->kode_status ?? 'draft';
    $config = $statusConfig[$statusCode] ?? ['bg-gray-100 text-gray-800', 'Unknown'];
    $statusClass = $config[0];
    $statusLabel = $config[1];
@endphp
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
    {{ $statusLabel }}
</span>

{{-- resources/views/components/table/action-button.blade.php --}}
@props(['type' => 'view', 'href' => '#', 'onclick' => null])
@php
    $configs = [
        'view' => [
            'color' => 'text-indigo-600 hover:text-indigo-900 hover:bg-indigo-50',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>',
            'title' => 'Lihat'
        ],
        'edit' => [
            'color' => 'text-yellow-600 hover:text-yellow-900 hover:bg-yellow-50',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>',
            'title' => 'Edit'
        ],
        'approve' => [
            'color' => 'text-green-600 hover:text-green-900 hover:bg-green-50',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>',
            'title' => 'Setujui'
        ],
        'reject' => [
            'color' => 'text-red-600 hover:text-red-900 hover:bg-red-50',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>',
            'title' => 'Tolak'
        ],
        'delete' => [
            'color' => 'text-red-600 hover:text-red-900 hover:bg-red-50',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>',
            'title' => 'Hapus'
        ],
    ];
    $config = $configs[$type] ?? $configs['view'];
@endphp

@if($onclick)
    <button type="button" 
            onclick="{{ $onclick }}"
            class="{{ $config['color'] }} p-1 rounded transition-colors duration-200"
            title="{{ $config['title'] }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $config['icon'] !!}
        </svg>
    </button>
@else
    <a href="{{ $href }}" 
       class="{{ $config['color'] }} p-1 rounded transition-colors duration-200"
       title="{{ $config['title'] }}">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            {!! $config['icon'] !!}
        </svg>
    </a>
@endif

{{-- resources/views/components/table/empty.blade.php --}}
@props(['colspan' => 9, 'message' => 'Tidak ada data'])
<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">Tidak ada data</h3>
        <p class="mt-1 text-sm text-gray-500">{{ $message }}</p>
    </td>
</tr>