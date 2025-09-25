@extends('layouts.app')

@section('content')
<style>
/* CSS styles sama seperti sebelumnya */
.editable-field {
    background-color: #fef3c7;
    padding: 2px 4px;
    border-radius: 3px;
    cursor: pointer;
    border: 1px solid transparent;
    display: inline-block;
    min-width: 50px;
}

.editable-field:hover {
    background-color: #fde047;
    border: 1px solid #f59e0b;
}

.a4-container {
    width: 210mm;
    min-height: 270mm;
    margin: 0 auto;
    background: white;
    box-shadow: 0 0 20px rgba(0,0,0,0.1);
    padding: 15mm 20mm;
    font-family: 'Times New Roman', serif;
    font-size: 11pt;
    line-height: 1.3;
}
</style>

<div class="container mx-auto px-4 py-8">
    <div class="no-print bg-white rounded-lg shadow-sm p-6 mb-6">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h1 style="font-size: 24px; font-weight: bold;">Preview & Edit Surat FSI UNJANI</h1>
            <a href="{{ route('fakultas.surat.index') }}" 
               style="padding: 8px 16px; background-color: #6b7280; color: white; text-decoration: none; border-radius: 6px;">
                <i class="fas fa-arrow-left" style="margin-right: 8px;"></i>Kembali
            </a>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 24px;">
        <!-- LEFT PANEL: Edit All Fields -->
        <div class="no-print bg-white rounded-lg shadow-lg p-6">
            <h2 style="font-size: 18px; font-weight: bold; margin-bottom: 16px;">
                <i class="fas fa-edit"></i> Edit Data Surat
            </h2>
            
            <!-- Data Mahasiswa Edit -->
            <div style="margin-bottom: 24px;">
                <h3 style="font-weight: 600; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 12px;">
                    Data Mahasiswa
                </h3>
                <div style="font-size: 12px;">
                    <div style="margin-bottom: 8px;">
                        <label>Nama:</label>
                        <input type="text" id="edit_nama" value="{{ $pengajuan->nama_mahasiswa }}" 
                               onchange="updateField('nama', this.value)"
                               style="width: 100%; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 8px;">
                        <label>NIM:</label>
                        <input type="text" id="edit_nim" value="{{ $pengajuan->nim }}" 
                               onchange="updateField('nim', this.value)"
                               style="width: 100%; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 8px;">
                        <label>Program Studi:</label>
                        <input type="text" id="edit_prodi" value="{{ $pengajuan->prodi->nama_prodi }}" 
                               onchange="updateField('prodi', this.value)"
                               style="width: 100%; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 8px;">
                        <label>Semester:</label>
                        <input type="text" id="edit_semester" value="{{ $additionalData['semester'] ?? 'Ganjil' }}" 
                               onchange="updateField('semester', this.value)"
                               style="width: 100%; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px;">
                    </div>
                    <div style="margin-bottom: 8px;">
                        <label>Tahun Akademik:</label>
                        <input type="text" id="edit_tahun_akademik" value="{{ $additionalData['tahun_akademik'] ?? '2024/2025' }}" 
                               onchange="updateField('tahun_akademik', this.value)"
                               style="width: 100%; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px;">
                    </div>
                </div>
            </div>
            
            <!-- Data Orang Tua Edit -->
            @if(isset($additionalData['orang_tua']))
            <div style="margin-bottom: 24px;">
                <h3 style="font-weight: 600; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 12px;">
                    Data Orang Tua
                </h3>
                <div style="font-size: 12px;">
                    @foreach([
                        'nama' => 'Nama',
                        'tempat_lahir' => 'Tempat Lahir',
                        'tanggal_lahir' => 'Tanggal Lahir',
                        'pekerjaan' => 'Pekerjaan',
                        'nip' => 'NIP',
                        'pangkat_golongan' => 'Pangkat/Golongan',
                        'instansi' => 'Instansi',
                        'alamat_instansi' => 'Alamat Kantor',
                        'alamat_rumah' => 'Alamat Rumah'
                    ] as $key => $label)
                    <div style="margin-bottom: 8px;">
                        <label>{{ $label }}:</label>
                        <input type="text" id="edit_ortu_{{ $key }}" 
                               value="{{ $additionalData['orang_tua'][$key] ?? '' }}" 
                               onchange="updateField('ortu_{{ $key }}', this.value)"
                               style="width: 100%; padding: 6px; border: 1px solid #d1d5db; border-radius: 4px;">
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
            
            <!-- Barcode Selection -->
            <div style="margin-bottom: 24px;">
                <h3 style="font-weight: 600; border-bottom: 1px solid #e5e7eb; padding-bottom: 8px; margin-bottom: 12px;">
                    Pilih Tanda Tangan
                </h3>
                @foreach($barcodeSignatures as $barcode)
                <div onclick="selectBarcode(this, {{ $barcode->id }})"
                     style="border: 1px solid #e5e7eb; border-radius: 8px; padding: 12px; margin-bottom: 8px; cursor: pointer;">
                    <div style="display: flex; justify-content: space-between;">
                        <div>
                            <div style="font-weight: 600;">{{ $barcode->pejabat_nama }}</div>
                            <div style="font-size: 12px; color: #6b7280;">{{ $barcode->pejabat_jabatan }}</div>
                        </div>
                        <input type="radio" name="barcode_signature_id" value="{{ $barcode->id }}">
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        <!-- RIGHT PANEL: A4 Preview dengan semua field editable -->
        <div class="bg-white rounded-lg shadow-lg">
            <div class="no-print" style="padding: 16px; border-bottom: 1px solid #e5e7eb;">
                <h2 style="font-size: 18px; font-weight: bold;">
                    <i class="fas fa-file-alt"></i> Preview Surat (Klik field kuning untuk edit)
                </h2>
            </div>
            
            <div style="padding: 16px;">
                <div class="a4-container">
                    <!-- KOP SURAT dengan Logo -->
                    <table style="width: 100%; border-collapse: collapse; border-bottom: 3px double #000; padding-bottom: 8px; margin-bottom: 12px;">
                        <tr>
                            <td style="width: 15%; text-align: center;">
                                @if(file_exists(public_path('images/logo-ykep.png')))
                                    <img src="{{ asset('images/logo-ykep.png') }}" style="width: 50px; height: 50px;">
                                @else
                                    <div style="width: 50px; height: 50px; background: #ddd; margin: 0 auto; font-size: 8px; border: 1px solid #ccc;">
                                        LOGO YKEP
                                    </div>
                                @endif
                            </td>
                            <td style="width: 70%; text-align: center; font-weight: bold; line-height: 1.2;">
                                <div style="font-size: 11pt;">YAYASAN KARTIKA EKA PAKSI</div>
                                <div style="font-size: 11pt;">UNIVERSITAS JENDERAL ACHMAD YANI (UNJANI)</div>
                                <div style="font-size: 11pt;">FAKULTAS SAINS DAN INFORMATIKA</div>
                                <div style="font-size: 11pt; font-weight: bold;">(FSI)</div>
                                <div style="font-size: 9pt; font-weight: normal;">
                                    Kampus Cimahi : Jl. Terusan Jenderal Sudirman PO.BOX 148 Telp. (022) 6650646
                                </div>
                            </td>
                            <td style="width: 15%; text-align: center;">
                                @if(file_exists(public_path('images/logo-unjani.png')))
                                    <img src="{{ asset('images/logo-unjani.png') }}" style="width: 55px; height: 55px;">
                                @else
                                    <div style="width: 55px; height: 55px; background: #ddd; margin: 0 auto; font-size: 8px; border: 1px solid #ccc;">
                                        LOGO UNJANI
                                    </div>
                                @endif
                            </td>
                        </tr>
                    </table>
                    
                    <!-- Content Surat -->
                    <div style="text-align: center; margin: 12px 0;">
                        <h3 style="margin: 0; font-size: 11pt; font-weight: bold; text-decoration: underline;">
                            SURAT PERNYATAAN MASIH KULIAH
                        </h3>
                        <div style="margin-top: 6px; font-size: 11pt;">
                            NOMOR: {{ $nomorSurat }}
                        </div>
                    </div>
                    
                    <div style="text-align: justify; font-size: 11pt; line-height: 1.3;">
                        <p style="margin: 6px 0;">Yang bertanda tangan di bawah ini :</p>
                        
                        <table style="margin: 8px 0; width: 100%;">
                            <tr>
                                <td style="width: 160px;">Nama</td>
                                <td style="width: 15px; text-align: center;">:</td>
                                <td>AGUS KOMARUDIN, S.Kom., M.T.</td>
                            </tr>
                            <tr>
                                <td>Pangkat/Golongan</td>
                                <td style="text-align: center;">:</td>
                                <td>PENATA MUDA TK.I – III/B</td>
                            </tr>
                            <tr>
                                <td>Jabatan</td>
                                <td style="text-align: center;">:</td>
                                <td>WAKIL DEKAN III FAKULTAS SAINS DAN INFORMATIKA UNJANI</td>
                            </tr>
                        </table>
                        
                        <p style="margin: 6px 0;">Dengan ini menyatakan :</p>
                        
                        <table style="margin: 8px 0; width: 100%;">
                            <tr>
                                <td style="width: 160px;">Nama</td>
                                <td style="width: 15px; text-align: center;">:</td>
                                <td><span class="editable-field" data-field="nama">{{ strtoupper($pengajuan->nama_mahasiswa) }}</span></td>
                            </tr>
                            <tr>
                                <td>N I M</td>
                                <td style="text-align: center;">:</td>
                                <td><span class="editable-field" data-field="nim">{{ $pengajuan->nim }}</span></td>
                            </tr>
                            <tr>
                                <td>Program Studi</td>
                                <td style="text-align: center;">:</td>
                                <td><span class="editable-field" data-field="prodi">{{ $pengajuan->prodi->nama_prodi }}</span></td>
                            </tr>
                            <tr>
                                <td>Program</td>
                                <td style="text-align: center;">:</td>
                                <td>S1</td>
                            </tr>
                        </table>
                        
                        @if(isset($additionalData['orang_tua']))
                        <p style="margin: 6px 0;">Nama Orang Tua/Wali dari Mahasiswa tersebut adalah :</p>
                        
                        <table style="margin: 8px 0; width: 100%;">
                            @foreach([
                                'nama' => 'Nama',
                                'tempat_lahir' => 'Tempat/Tanggal Lahir',
                                'pekerjaan' => 'Pekerjaan',
                                'nip' => 'NIP',
                                'pangkat_golongan' => 'Pangkat/Golongan',
                                'instansi' => 'Instansi',
                                'alamat_instansi' => 'Alamat Kantor',
                                'alamat_rumah' => 'Alamat Rumah'
                            ] as $key => $label)
                                @if(isset($additionalData['orang_tua'][$key]))
                                <tr>
                                    <td style="width: 160px;">{{ $label }}</td>
                                    <td style="width: 15px; text-align: center;">:</td>
                                    <td>
                                        @if($key === 'tempat_lahir')
                                            <span class="editable-field" data-field="ortu_tempat_lahir">{{ $additionalData['orang_tua']['tempat_lahir'] ?? '' }}</span> / 
                                            <span class="editable-field" data-field="ortu_tanggal_lahir">{{ $additionalData['orang_tua']['tanggal_lahir'] ?? '' }}</span>
                                        @else
                                            <span class="editable-field" data-field="ortu_{{ $key }}">
                                                {{ $key === 'nama' ? strtoupper($additionalData['orang_tua'][$key]) : $additionalData['orang_tua'][$key] }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                @endif
                            @endforeach
                        </table>
                        @endif
                        
                        <p style="margin: 6px 0;">
                            Merupakan Mahasiswa Fakultas Sains dan Informatika Universitas Jenderal Achmad Yani dan 
                            <strong>Aktif</strong> pada Semester <span class="editable-field" data-field="semester">{{ $additionalData['semester'] ?? 'Ganjil' }}</span> 
                            Tahun Akademik <span class="editable-field" data-field="tahun_akademik">{{ $additionalData['tahun_akademik'] ?? '2024/2025' }}</span>.
                        </p>
                        
                        <p style="margin: 6px 0;">Demikian surat pernyataan ini dibuat agar dapat dipergunakan sebagaimana mestinya.</p>
                    </div>
                    
                    <!-- Tanda Tangan -->
                    <div style="margin-top: 25px; text-align: right;">
                        <div style="display: inline-block; text-align: center; width: 200px;">
                            <p style="margin: 4px 0;">Cimahi, {{ $tanggalSurat }}</p>
                            <p style="margin: 4px 0;">An. Dekan</p>
                            <p style="margin: 4px 0;">Wakil Dekan III – FSI</p>
                            <div style="height: 40px; margin: 8px 0; background: #f0f0f0; border: 1px dashed #ccc; display: flex; align-items: center; justify-content: center; font-size: 9px;" id="barcodePreview">
                                BARCODE TTD
                            </div>
                            <p style="text-decoration: underline; font-weight: bold; margin: 2px 0;">AGUS KOMARUDIN, S.Kom., M.T.</p>
                            <p style="margin: 2px 0;">NID. 4121 758 78</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Action Buttons dengan Reject -->
    <div class="no-print" style="margin-top: 24px; display: flex; justify-content: space-between;">
        <button onclick="rejectSurat()" 
                style="padding: 8px 16px; background-color: #dc2626; color: white; border: none; border-radius: 6px; cursor: pointer;">
            <i class="fas fa-times"></i> Tolak Surat
        </button>
        
        <div style="display: flex; gap: 12px;">
            <button onclick="window.print()" 
                    style="padding: 8px 16px; background-color: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer;">
                <i class="fas fa-print"></i> Print Preview
            </button>
            <button onclick="generatePDF()" id="generateBtn" disabled
                    style="padding: 12px 24px; background-color: #9ca3af; color: white; border: none; border-radius: 6px; cursor: not-allowed; font-weight: 600;">
                <i class="fas fa-file-pdf"></i> Generate PDF Final
            </button>
        </div>
    </div>
</div>

<script>
let selectedBarcodeId = null;
let editedData = {};

// Update field
function updateField(field, value) {
    const displayValue = field.includes('nama') ? value.toUpperCase() : value;
    
    // Update all matching fields in preview
    document.querySelectorAll(`[data-field="${field}"]`).forEach(el => {
        el.textContent = displayValue;
    });
    
    // Store edited data
    editedData[field] = value;
    console.log('Field updated:', field, value);
}

// Make editable fields clickable
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.editable-field').forEach(field => {
        field.addEventListener('click', function() {
            const fieldName = this.dataset.field;
            const currentValue = this.textContent.trim();
            const input = document.getElementById(`edit_${fieldName}`);
            
            if (input) {
                input.focus();
                input.select();
            }
        });
    });
});

// Select barcode
function selectBarcode(element, id) {
    // Remove previous selection
    document.querySelectorAll('[onclick^="selectBarcode"]').forEach(el => {
        el.style.borderColor = '#e5e7eb';
        el.style.backgroundColor = 'transparent';
    });
    
    // Select new
    element.style.borderColor = '#3b82f6';
    element.style.backgroundColor = '#eff6ff';
    element.querySelector('input[type="radio"]').checked = true;
    selectedBarcodeId = id;
    
    // Enable generate button
    const btn = document.getElementById('generateBtn');
    btn.disabled = false;
    btn.style.backgroundColor = '#16a34a';
    btn.style.cursor = 'pointer';
    
    document.getElementById('barcodePreview').innerHTML = 'BARCODE TERPILIH';
}

// Generate PDF
function generatePDF() {
    if (!selectedBarcodeId) {
        alert('Pilih barcode tanda tangan terlebih dahulu!');
        return;
    }
    
    // Collect all edited data from preview
    document.querySelectorAll('.editable-field').forEach(field => {
        editedData[field.dataset.field] = field.textContent.trim();
    });
    
    const btn = document.getElementById('generateBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating...';
    
    fetch(`/fakultas/surat/fsi/generate-pdf/{{ $pengajuan->id }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            barcode_signature_id: selectedBarcodeId,
            edited_data: editedData
        })
    })
    .then(response => {
        if (!response.ok) throw new Error(`HTTP ${response.status}`);
        return response.blob();
    })
    .then(blob => {
        const url = window.URL.createObjectURL(blob);
        const a = document.createElement('a');
        a.href = url;
        a.download = `Surat_FSI_{{ $pengajuan->nim }}_{{ date('YmdHis') }}.pdf`;
        a.click();
        window.URL.revokeObjectURL(url);
        
        alert('PDF berhasil didownload!');
        setTimeout(() => window.location.href = '{{ route("fakultas.surat.index") }}', 1500);
    })
    .catch(error => {
        alert(`Error: ${error.message}`);
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-file-pdf"></i> Generate PDF Final';
    });
}

// Reject surat
function rejectSurat() {
    const reason = prompt('Alasan penolakan:');
    if (!reason) return;
    
    if (confirm('Yakin tolak surat ini?')) {
        fetch(`/fakultas/surat/reject/{{ $pengajuan->id }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ rejection_reason: reason })
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || 'Surat ditolak');
            window.location.href = '{{ route("fakultas.surat.index") }}';
        })
        .catch(error => {
            alert('Error: ' + error.message);
        });
    }
}
</script>
@endsection