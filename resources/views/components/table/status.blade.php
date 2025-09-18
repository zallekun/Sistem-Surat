@props(['status'])
@php
    $statusConfig = [
        'draft' => ['bg-gray-100 text-gray-800', 'Draft'],
        'review_kaprodi' => ['bg-yellow-100 text-yellow-800', 'Review'],
        'disetujui_kaprodi' => ['bg-blue-100 text-blue-800', 'Disetujui Kaprodi'],
        'ditolak_kaprodi' => ['bg-red-100 text-red-800', 'Ditolak'],
        'diproses_fakultas' => ['bg-indigo-100 text-indigo-800', 'Proses Fakultas'],
        'disetujui_fakultas' => ['bg-green-100 text-green-800', 'Selesai'],
        'ditolak_fakultas' => ['bg-red-100 text-red-800', 'Ditolak Fakultas'],
        'ditolak_umum' => ['bg-red-100 text-red-800', 'Ditolak'],
        'diajukan' => ['bg-yellow-100 text-yellow-800', 'Diajukan'],
    ];
    $statusCode = $status->kode_status ?? 'draft';
    $config = $statusConfig[$statusCode] ?? ['bg-gray-100 text-gray-800', 'Unknown'];
    $statusClass = $config[0];
    $statusLabel = $config[1];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusClass }}">
    {{ $statusLabel }}
</span>