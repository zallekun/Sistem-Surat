# Buat script untuk generate components
cat > create_table_components.php << 'EOF'
<?php

$components = [
    'wrapper.blade.php' => '@props([\'title\' => null])
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
</div>',

    'table.blade.php' => '<table {{ $attributes->merge([\'class\' => \'min-w-full divide-y divide-gray-200\']) }}>
    {{ $slot }}
</table>',

    'thead.blade.php' => '<thead {{ $attributes->merge([\'class\' => \'bg-gray-50\']) }}>
    {{ $slot }}
</thead>',

    'tbody.blade.php' => '<tbody {{ $attributes->merge([\'class\' => \'bg-white divide-y divide-gray-200\']) }}>
    {{ $slot }}
</tbody>',

    'th.blade.php' => '@props([\'align\' => \'left\', \'width\' => null])
@php
    $alignClass = match($align) {
        \'center\' => \'text-center\',
        \'right\' => \'text-right\',
        default => \'text-left\'
    };
@endphp
<th 
    @if($width) style="width: {{ $width }}" @endif
    {{ $attributes->merge([\'class\' => "px-6 py-3 $alignClass text-xs font-medium text-gray-500 uppercase tracking-wider"]) }}
>
    {{ $slot }}
</th>',

    'td.blade.php' => '@props([\'align\' => \'left\', \'wrap\' => false])
@php
    $alignClass = match($align) {
        \'center\' => \'text-center\',
        \'right\' => \'text-right\',
        default => \'text-left\'
    };
    $wrapClass = $wrap ? \'\' : \'whitespace-nowrap\';
@endphp
<td {{ $attributes->merge([\'class\' => "px-6 py-4 $wrapClass $alignClass text-sm text-gray-900"]) }}>
    {{ $slot }}
</td>',

    'tr.blade.php' => '@props([\'hoverable\' => true])
<tr {{ $attributes->merge([\'class\' => $hoverable ? \'hover:bg-gray-50 transition-colors duration-200\' : \'\']) }}>
    {{ $slot }}
</tr>',

    'status.blade.php' => '@props([\'status\'])
@php
    $statusConfig = [
        \'draft\' => \'bg-gray-100 text-gray-800\',
        \'review_kaprodi\' => \'bg-yellow-100 text-yellow-800\',
        \'disetujui_kaprodi\' => \'bg-blue-100 text-blue-800\',
        \'ditolak_kaprodi\' => \'bg-red-100 text-red-800\',
        \'diproses_fakultas\' => \'bg-indigo-100 text-indigo-800\',
        \'disetujui_fakultas\' => \'bg-green-100 text-green-800\',
        \'ditolak_fakultas\' => \'bg-red-100 text-red-800\',
    ];
    $statusCode = $status->kode_status ?? \'draft\';
    $statusClass = $statusConfig[$statusCode] ?? \'bg-gray-100 text-gray-800\';
@endphp
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
    {{ $status->nama_status ?? \'Unknown\' }}
</span>',

    'action-button.blade.php' => '@props([\'type\' => \'view\', \'href\' => \'#\', \'onclick\' => null])
@php
    $configs = [
        \'view\' => \'text-indigo-600 hover:text-indigo-900\',
        \'edit\' => \'text-yellow-600 hover:text-yellow-900\',
        \'approve\' => \'text-green-600 hover:text-green-900\',
        \'reject\' => \'text-red-600 hover:text-red-900\',
    ];
    $color = $configs[$type] ?? $configs[\'view\'];
@endphp

@if($onclick)
    <button type="button" onclick="{{ $onclick }}" class="{{ $color }} p-1">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($type == \'view\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            @elseif($type == \'edit\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            @elseif($type == \'approve\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            @elseif($type == \'reject\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            @endif
        </svg>
    </button>
@else
    <a href="{{ $href }}" class="{{ $color }} p-1">
        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            @if($type == \'view\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            @elseif($type == \'edit\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            @elseif($type == \'approve\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            @elseif($type == \'reject\')
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            @endif
        </svg>
    </a>
@endif',

    'empty.blade.php' => '@props([\'colspan\' => 9, \'message\' => \'Tidak ada data\'])
<tr>
    <td colspan="{{ $colspan }}" class="px-6 py-12 text-center">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ $message }}</h3>
    </td>
</tr>'
];

$dir = 'resources/views/components/table/';
if (!is_dir($dir)) {
    mkdir($dir, 0777, true);
}

foreach ($components as $filename => $content) {
    $path = $dir . $filename;
    file_put_contents($path, $content);
    echo "Created: $path\n";
}

echo "\nAll components created successfully!\n";