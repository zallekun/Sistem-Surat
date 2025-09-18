@props(['status'])
@php
    $statusConfig = [
        'draft' => 'bg-gray-100 text-gray-800',
        'review_kaprodi' => 'bg-yellow-100 text-yellow-800',
        'disetujui_kaprodi' => 'bg-blue-100 text-blue-800',
        'ditolak_kaprodi' => 'bg-red-100 text-red-800',
        'diproses_fakultas' => 'bg-indigo-100 text-indigo-800',
        'disetujui_fakultas' => 'bg-green-100 text-green-800',
        'ditolak_fakultas' => 'bg-red-100 text-red-800',
    ];
    $statusCode = $status->kode_status ?? 'draft';
    $statusClass = $statusConfig[$statusCode] ?? 'bg-gray-100 text-gray-800';
@endphp
<span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $statusClass }}">
    {{ $status->nama_status ?? 'Unknown' }}
</span>