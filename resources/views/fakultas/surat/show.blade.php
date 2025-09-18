{{-- resources/views/fakultas/surat/show.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Surat Fakultas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="bg-white shadow-sm rounded-lg">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Detail Surat Fakultas</h5>
                    <a href="{{ route('fakultas.surat.index') }}" class="btn btn-secondary btn-sm">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </a>
                </div>

                <div class="px-4 py-5 sm:p-6">
                    @if($errors->any())
                        <div class="bg-red-50 border-l-4 border-red-400 p-4">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    {{-- Basic Surat Info --}}
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Nomor Surat:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $surat->nomor_surat ?? 'Belum ada nomor' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Perihal:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $surat->perihal }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Jenis Surat:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $surat->jenisSurat->nama_jenis ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Tanggal Dibuat:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $surat->created_at->format('d/m/Y H:i') }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="min-w-full divide-y divide-gray-200">
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Dibuat Oleh:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $surat->createdBy->nama ?? $surat->createdBy->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Prodi:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $surat->prodi->nama_prodi ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Fakultas:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $surat->prodi->fakultas->nama_fakultas ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><strong>Status Saat Ini:</strong></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        <span class="badge badge-{{ $surat->currentStatus->kode_status === 'disetujui_kaprodi' ? 'warning' : ($surat->currentStatus->kode_status === 'disetujui_fakultas' ? 'success' : 'secondary') }}">
                                            {{ $surat->currentStatus->nama_status ?? 'N/A' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    {{-- Content --}}
                    @if($surat->isi_surat)
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>Isi Surat:</h6>
                            <div class="border p-3 bg-light rounded">
                                {!! nl2br(e($surat->isi_surat)) !!}
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Actions for Faculty Staff --}}
                    @if($surat->currentStatus->kode_status === 'disetujui_kaprodi')
                    <div class="row mb-4">
                        <div class="col-12">
                            <h6>Tindakan Fakultas:</h6>
                            <div class="btn-group" role="group">
                                <form action="{{ route('fakultas.surat.approve', $surat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menyetujui surat ini?')">
                                    @csrf
                                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                        <i class="fas fa-check"></i> Setujui
                                    </button>
                                </form>
                                
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500" data-toggle="modal" data-target="#prosesModal">
                                    <i class="fas fa-hourglass-half"></i> Proses
                                </button>
                                
                                <button type="button" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500" data-toggle="modal" data-target="#tolakModal">
                                    <i class="fas fa-times"></i> Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                    @endif

                    {{-- Status History --}}
                    @if(isset($surat->statusHistories) && $surat->statusHistories->count() > 0)
                    <div class="row">
                        <div class="col-12">
                            <h6>Riwayat Status:</h6>
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Oleh</th>
                                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Keterangan</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($surat->statusHistories->sortByDesc('created_at') as $history)
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $history->created_at->format('d/m/Y H:i') }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $history->status->nama_status ?? 'N/A' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $history->user->nama ?? $history->user->name ?? 'System' }}</td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">{{ $history->keterangan ?? '-' }}</td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                    @endif

                </div>
            </div>
        </div>
    </div>
</div>

{{-- Modal Proses --}}
<div class="modal fade" id="prosesModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('fakultas.surat.updateStatus', $surat->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="status" value="diproses_fakultas">
                
                <div class="modal-header">
                    <h5 class="modal-title">Proses Surat</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="catatan">Catatan (opsional):</label>
                        <textarea class="form-control" id="catatan" name="catatan" rows="3" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-dismiss="modal">Batal</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-yellow-600 hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500">Proses Surat</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal Tolak --}}
<div class="modal fade" id="tolakModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('fakultas.surat.reject', $surat->id) }}" method="POST">
                @csrf
                
                <div class="modal-header">
                    <h5 class="modal-title">Tolak Surat</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                
                <div class="modal-body">
                    <div class="form-group">
                        <label for="keterangan">Alasan Penolakan: <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="keterangan" name="keterangan" rows="3" placeholder="Jelaskan alasan penolakan..." required></textarea>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-dismiss="modal">Batal</button>
                    <button type="submit" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">Tolak Surat</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection