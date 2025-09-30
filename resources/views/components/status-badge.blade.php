@props(['status', 'needsPengantar' => false])

@php
    $label = match($status) {
        'pending' => 'Menunggu Review',
        'approved_prodi' => $needsPengantar ? 'Perlu Surat Pengantar' : 'Menunggu Fakultas',
        'pengantar_generated' => 'Siap Diproses Fakultas',
        'approved_fakultas' => 'Siap Cetak',
        'sedang_ditandatangani' => 'Proses TTD',
        'completed' => 'Selesai',
        'rejected_prodi' => 'Ditolak Prodi',
        'rejected_fakultas' => 'Ditolak Fakultas',
        default => ucwords(str_replace('_', ' ', $status))
    };
    
    $color = match($status) {
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved_prodi' => 'bg-blue-100 text-blue-800',
        'pengantar_generated' => 'bg-indigo-100 text-indigo-800',
        'approved_fakultas' => 'bg-green-100 text-green-800',
        'sedang_ditandatangani' => 'bg-orange-100 text-orange-800',
        'completed' => 'bg-gray-100 text-gray-800',
        'rejected_prodi', 'rejected_fakultas' => 'bg-red-100 text-red-800',
        default => 'bg-gray-100 text-gray-600'
    };
@endphp

<span class="px-2 py-1 text-xs rounded-full {{ $color }}">
    {{ $label }}
</span>