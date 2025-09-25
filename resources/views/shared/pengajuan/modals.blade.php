<!-- Modal Konfirmasi Approve -->
<div id="approveModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-bold mb-4 text-gray-900">Konfirmasi Persetujuan</h3>
        <div class="mb-6">
            <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menyetujui pengajuan surat ini?</p>
            <div class="bg-gray-50 p-3 rounded-lg">
                <p class="text-sm"><strong>Mahasiswa:</strong> {{ $pengajuan->nama_mahasiswa ?? 'N/A' }} ({{ $pengajuan->nim ?? 'N/A' }})</p>
                <p class="text-sm"><strong>Jenis Surat:</strong> {{ isset($pengajuan->jenisSurat) ? $pengajuan->jenisSurat->nama_jenis : 'N/A' }}</p>
            </div>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeApproveModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                Batal
            </button>
            <button onclick="processPengajuan('approve')" 
                    class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 transition-colors">
                Ya, Setujui
            </button>
        </div>
    </div>
</div>

<!-- Modal Reject dengan Alasan -->
<div id="rejectModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4 shadow-xl">
        <h3 class="text-lg font-bold mb-4 text-red-600">Tolak Pengajuan</h3>
        <div class="mb-4">
            <p class="text-gray-600 mb-4">Berikan alasan penolakan untuk pengajuan ini:</p>
            <div class="bg-gray-50 p-3 rounded-lg mb-4">
                <p class="text-sm"><strong>Mahasiswa:</strong> {{ $pengajuan->nama_mahasiswa ?? 'N/A' }} ({{ $pengajuan->nim ?? 'N/A' }})</p>
                <p class="text-sm"><strong>Jenis Surat:</strong> {{ isset($pengajuan->jenisSurat) ? $pengajuan->jenisSurat->nama_jenis : 'N/A' }}</p>
            </div>
            <textarea id="rejectionReason" 
                      class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-red-500"
                      rows="4"
                      placeholder="Contoh: Dokumen pendukung tidak lengkap, data tidak sesuai dengan persyaratan, dll..."
                      required></textarea>
            <p class="text-xs text-gray-500 mt-1">Minimal 10 karakter diperlukan</p>
        </div>
        <div class="flex justify-end space-x-3">
            <button onclick="closeRejectModal()" 
                    class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-colors">
                Batal
            </button>
            <button onclick="processPengajuan('reject')" 
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-colors">
                Tolak Pengajuan
            </button>
        </div>
    </div>
</div>