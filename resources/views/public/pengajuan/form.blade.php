@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto" x-data="suratFormData()">

    <!-- Header -->
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-gray-900">Pengajuan Surat Online</h1>
        <p class="mt-2 text-gray-600">Ajukan surat untuk keperluan akademik Anda dengan mudah dan cepat</p>
    </div>

    <!-- Progress Steps -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div class="flex items-center" :class="step >= 1 ? 'text-blue-600' : 'text-gray-400'">
                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2"
                     :class="step >= 1 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 text-gray-400'">
                    <span x-show="step > 1">✓</span>
                    <span x-show="step === 1">1</span>
                    <span x-show="step < 1">1</span>
                </div>
                <span class="ml-2 font-medium">Pilih Jenis Surat</span>
            </div>
            
            <div class="flex-1 h-0.5 mx-4"
                 :class="step >= 2 ? 'bg-blue-600' : 'bg-gray-300'"></div>
            
            <div class="flex items-center" :class="step >= 2 ? 'text-blue-600' : 'text-gray-400'">
                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2"
                     :class="step >= 2 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 text-gray-400'">
                    <span x-show="step > 2">✓</span>
                    <span x-show="step === 2">2</span>
                    <span x-show="step < 2">2</span>
                </div>
                <span class="ml-2 font-medium">Isi Data</span>
            </div>
            
            <div class="flex-1 h-0.5 mx-4"
                 :class="step >= 3 ? 'bg-blue-600' : 'bg-gray-300'"></div>
            
            <div class="flex items-center" :class="step >= 3 ? 'text-blue-600' : 'text-gray-400'">
                <div class="flex items-center justify-center w-8 h-8 rounded-full border-2"
                     :class="step >= 3 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300 text-gray-400'">
                    <span>3</span>
                </div>
                <span class="ml-2 font-medium">Konfirmasi</span>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        
        <!-- Step 1: Pilih Jenis Surat -->
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
            <h2 class="text-xl font-semibold mb-4">Pilih Jenis Surat yang Diperlukan</h2>
            <div class="grid md:grid-cols-2 gap-4">
                @foreach($jenisSurat as $jenis)
                    <div class="border rounded-lg p-4 cursor-pointer transition-all" 
                         :class="form.jenis_surat_id == {{ $jenis->id }} ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                         @click="toggleJenisSurat({{ $jenis->id }})">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium">{{ $jenis->nama_jenis }}</h3>
                                <p class="text-sm text-gray-500">Kode: {{ $jenis->kode_surat }}</p>
                            </div>
                            <div class="ml-4">
                                <div class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                     :class="form.jenis_surat_id == {{ $jenis->id }} ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                    <div x-show="form.jenis_surat_id == {{ $jenis->id }}" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6 flex justify-end">
                <button @click="nextStep()" 
                        :disabled="!canProceed()"
                        :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="px-6 py-2 rounded-md font-medium transition-colors">
                    Selanjutnya
                </button>
            </div>
        </div>

        <!-- Step 2: Form Data -->
        <div x-show="step === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
            <h2 class="text-xl font-semibold mb-4">Data Pemohon</h2>
            
            <!-- Basic Information -->
            <div class="grid md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">NIM *</label>
                    <input type="text" x-model="form.nim" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap *</label>
                    <input type="text" x-model="form.nama_mahasiswa" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email *</label>
                    <input type="email" x-model="form.email" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">No. HP *</label>
                    <input type="tel" x-model="form.phone" 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Program Studi *</label>
                    <select x-model="form.prodi_id" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="">Pilih Program Studi</option>
                        @foreach($prodi as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan *</label>
                    <input type="text" x-model="form.keperluan" placeholder="Keperluan surat ini..." 
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                </div>
            </div>

            <!-- Dynamic Fields Based on Selected Jenis Surat -->
            <div x-show="getSelectedKode() === 'MA'">
                <h3 class="text-lg font-medium mb-4 text-gray-800">Data Tambahan - Surat Mahasiswa Aktif</h3>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Semester</label>
                        <select x-model="form.semester" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Semester</option>
                            @for ($i = 1; $i <= 14; $i++)
                                <option value="{{ $i }}">{{ $i }}</option>
                            @endfor
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik</label>
                        <input type="text" x-model="form.tahun_akademik" placeholder="2024/2025" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Orang Tua</label>
                        <input type="text" x-model="form.nama_orang_tua" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tempat Lahir Orang Tua</label>
                        <input type="text" x-model="form.tempat_lahir_ortu" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tanggal Lahir Orang Tua</label>
                        <input type="date" x-model="form.tanggal_lahir_ortu" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Orang Tua</label>
                        <select x-model="form.pekerjaan_ortu" @change="if (form.pekerjaan_ortu !== 'Lainnya') form.pekerjaan_ortu_lainnya = ''" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Pekerjaan</option>
                            <template x-for="pekerjaan in pekerjaanOptions" :key="pekerjaan">
                                <option :value="pekerjaan" x-text="pekerjaan"></option>
                            </template>
                        </select>
                    </div>
                    <div x-show="form.pekerjaan_ortu === 'Lainnya'" x-transition>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pekerjaan Lainnya</label>
                        <input type="text" x-model="form.pekerjaan_ortu_lainnya" placeholder="Sebutkan pekerjaan..."
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">NIP Orang Tua</label>
                        <input type="text" x-model="form.nip_ortu" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Jabatan Orang Tua</label>
                        <input type="text" x-model="form.jabatan_ortu" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pangkat/Golongan Orang Tua</label>
                        <input type="text" x-model="form.pangkat_golongan_ortu" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Pendidikan Terakhir Orang Tua</label>
                        <select x-model="form.pendidikan_terakhir_ortu" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            <option value="">Pilih Pendidikan</option>
                            <template x-for="pendidikan in pendidikanOptions" :key="pendidikan">
                                <option :value="pendidikan" x-text="pendidikan"></option>
                            </template>
                        </select>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Instansi Orang Tua</label>
                        <textarea x-model="form.alamat_instansi_ortu" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Rumah Orang Tua</label>
                        <textarea x-model="form.alamat_rumah_ortu" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>
            </div>

            <div x-show="getSelectedKode() === 'KP'">
                <h3 class="text-lg font-medium mb-4 text-gray-800">Data Tambahan - Surat Kerja Praktek</h3>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan</label>
                        <input type="text" x-model="form.nama_perusahaan" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bidang Kerja</label>
                        <input type="text" x-model="form.bidang_kerja" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Mulai</label>
                        <input type="date" x-model="form.periode_mulai" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Selesai</label>
                        <input type="date" x-model="form.periode_selesai" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Perusahaan</label>
                        <textarea x-model="form.alamat_perusahaan" rows="3" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>
            </div>

            <div x-show="getSelectedKode() === 'TA'">
                <h3 class="text-lg font-medium mb-4 text-gray-800">Data Tambahan - Surat Tugas Akhir</h3>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas Akhir</label>
                        <textarea x-model="form.judul_ta" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosen Pembimbing 1</label>
                        <input type="text" x-model="form.dosen_pembimbing1" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosen Pembimbing 2</label>
                        <input type="text" x-model="form.dosen_pembimbing2" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Lokasi Penelitian</label>
                        <textarea x-model="form.lokasi_penelitian" rows="2" 
                                  class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>
            </div>

            <div x-show="getSelectedKode() === 'SKM'">
                <h3 class="text-lg font-medium mb-4 text-gray-800">Data Tambahan - Surat Keterangan Mahasiswa</h3>
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Keterangan Khusus</label>
                    <textarea x-model="form.keterangan_khusus" rows="3" 
                              placeholder="Jelaskan keterangan khusus yang diperlukan..." 
                              class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="prevStep()" 
                        class="px-6 py-2 border border-gray-300 rounded-md font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Kembali
                </button>
                <button @click="nextStep()" 
                        :disabled="!canProceed()"
                        :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="px-6 py-2 rounded-md font-medium transition-colors">
                    Lanjutkan
                </button>
            </div>
        </div>

        <!-- Step 3: Konfirmasi -->
        <div x-show="step === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
            <h2 class="text-xl font-semibold mb-6">Konfirmasi Data</h2>
            
            <!-- Jenis Surat -->
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-blue-800 mb-2 flex items-center">
                    <i class="fas fa-file-alt mr-2"></i>
                    Jenis Surat
                </h3>
                <p class="text-blue-700 font-medium" x-text="getSelectedJenisName()"></p>
                <p class="text-blue-600 text-sm">Kode: <span x-text="getSelectedKode()"></span></p>
            </div>

            <!-- Data Pemohon -->
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-gray-800 mb-3 flex items-center">
                    <i class="fas fa-user mr-2"></i>
                    Data Pemohon
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-2">
                        <p><span class="font-medium text-gray-600">NIM:</span> <span class="text-gray-800" x-text="form.nim || '-'"></span></p>
                        <p><span class="font-medium text-gray-600">Nama:</span> <span class="text-gray-800" x-text="form.nama_mahasiswa || '-'"></span></p>
                        <p><span class="font-medium text-gray-600">Email:</span> <span class="text-gray-800" x-text="form.email || '-'"></span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium text-gray-600">No. HP:</span> <span class="text-gray-800" x-text="form.phone || '-'"></span></p>
                        <p><span class="font-medium text-gray-600">Program Studi:</span> <span class="text-gray-800" x-text="getSelectedProdiName() || '-'"></span></p>
                        <p><span class="font-medium text-gray-600">Keperluan:</span> <span class="text-gray-800" x-text="form.keperluan || '-'"></span></p>
                    </div>
                </div>
            </div>

            <!-- Data Tambahan Berdasarkan Jenis Surat -->
            
            <!-- Surat Mahasiswa Aktif -->
            <div x-show="getSelectedKode() === 'MA'" class="bg-green-50 border border-green-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-green-800 mb-3 flex items-center">
                    <i class="fas fa-graduation-cap mr-2"></i>
                    Data Mahasiswa Aktif
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-2">
                        <p><span class="font-medium text-green-600">Semester:</span> <span class="text-green-800" x-text="form.semester || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Tahun Akademik:</span> <span class="text-green-800" x-text="form.tahun_akademik || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Nama Orang Tua:</span> <span class="text-green-800" x-text="form.nama_orang_tua || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Tempat Lahir:</span> <span class="text-green-800" x-text="form.tempat_lahir_ortu || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Tanggal Lahir:</span> <span class="text-green-800" x-text="form.tanggal_lahir_ortu || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Pekerjaan:</span> <span class="text-green-800" x-text="(form.pekerjaan_ortu === 'Lainnya' ? form.pekerjaan_ortu_lainnya : form.pekerjaan_ortu) || '-'"></span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium text-green-600">NIP:</span> <span class="text-green-800" x-text="form.nip_ortu || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Jabatan:</span> <span class="text-green-800" x-text="form.jabatan_ortu || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Pangkat/Golongan:</span> <span class="text-green-800" x-text="form.pangkat_golongan_ortu || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Pendidikan:</span> <span class="text-green-800" x-text="form.pendidikan_terakhir_ortu || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Alamat Instansi:</span> <span class="text-green-800" x-text="form.alamat_instansi_ortu || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Alamat Rumah:</span> <span class="text-green-800" x-text="form.alamat_rumah_ortu || '-'"></span></p>
                    </div>
                </div>
            </div>

            <!-- Surat Kerja Praktek -->
            <div x-show="getSelectedKode() === 'KP'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                    <i class="fas fa-briefcase mr-2"></i>
                    Data Kerja Praktek
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div class="space-y-2">
                        <p><span class="font-medium text-yellow-600">Nama Perusahaan:</span> <span class="text-yellow-800" x-text="form.nama_perusahaan || '-'"></span></p>
                        <p><span class="font-medium text-yellow-600">Bidang Kerja:</span> <span class="text-yellow-800" x-text="form.bidang_kerja || '-'"></span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium text-yellow-600">Periode Mulai:</span> <span class="text-yellow-800" x-text="form.periode_mulai || '-'"></span></p>
                        <p><span class="font-medium text-yellow-600">Periode Selesai:</span> <span class="text-yellow-800" x-text="form.periode_selesai || '-'"></span></p>
                    </div>
                    <div class="md:col-span-2">
                        <p><span class="font-medium text-yellow-600">Alamat Perusahaan:</span> <span class="text-yellow-800" x-text="form.alamat_perusahaan || '-'"></span></p>
                    </div>
                </div>
            </div>

            <!-- Surat Tugas Akhir -->
            <div x-show="getSelectedKode() === 'TA'" class="bg-purple-50 border border-purple-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-purple-800 mb-3 flex items-center">
                    <i class="fas fa-book mr-2"></i>
                    Data Tugas Akhir
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div class="md:col-span-2">
                        <p><span class="font-medium text-purple-600">Judul Tugas Akhir:</span> <span class="text-purple-800" x-text="form.judul_ta || '-'"></span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium text-purple-600">Dosen Pembimbing 1:</span> <span class="text-purple-800" x-text="form.dosen_pembimbing1 || '-'"></span></p>
                        <p><span class="font-medium text-purple-600">Dosen Pembimbing 2:</span> <span class="text-purple-800" x-text="form.dosen_pembimbing2 || '-'"></span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium text-purple-600">Lokasi Penelitian:</span> <span class="text-purple-800" x-text="form.lokasi_penelitian || '-'"></span></p>
                    </div>
                </div>
            </div>

            <!-- Surat Keterangan Mahasiswa -->
            <div x-show="getSelectedKode() === 'SKM'" class="bg-indigo-50 border border-indigo-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-indigo-800 mb-3 flex items-center">
                    <i class="fas fa-certificate mr-2"></i>
                    Data Keterangan Mahasiswa
                </h3>
                <div class="text-sm">
                    <p><span class="font-medium text-indigo-600">Keterangan Khusus:</span> <span class="text-indigo-800" x-text="form.keterangan_khusus || '-'"></span></p>
                </div>
            </div>

            <!-- Pernyataan -->
            <div class="bg-orange-50 border border-orange-200 rounded-lg p-4 mb-6">
                <div class="flex items-start">
                    <i class="fas fa-exclamation-triangle text-orange-500 mt-1 mr-3"></i>
                    <div>
                        <h4 class="font-medium text-orange-800">Pernyataan Tanggung Jawab</h4>
                        <p class="text-orange-700 text-sm mt-1">
                            Saya menyatakan bahwa semua data yang telah diisi adalah benar dan dapat dipertanggungjawabkan. 
                            Apabila dikemudian hari terbukti data yang saya berikan tidak benar, maka saya bersedia 
                            menerima sanksi sesuai ketentuan yang berlaku.
                        </p>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-between">
                <button @click="prevStep()" 
                        class="px-6 py-2 border border-gray-300 rounded-md font-medium text-gray-700 hover:bg-gray-50 transition-colors">
                    Kembali
                </button>
                <button @click="submitForm()" 
                        :disabled="loading"
                        :class="loading ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700'"
                        class="px-6 py-2 rounded-md font-medium text-white transition-colors flex items-center">
                    <span x-show="loading" class="animate-spin mr-2">⏳</span>
                    <span x-text="loading ? 'Mengirim...' : 'Kirim Pengajuan'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div id="successModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md mx-4">
        <div class="text-center">
            <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-green-100 mb-4">
                <i class="fas fa-check text-green-600 text-xl"></i>
            </div>
            <h3 class="text-lg font-medium text-gray-900 mb-2">Pengajuan Berhasil!</h3>
            <p class="text-sm text-gray-500 mb-4">
                Surat Anda telah berhasil diajukan dengan nomor tracking:
            </p>
            <p id="trackingNumber" class="text-xl font-bold text-blue-600 mb-6"></p>
            <div class="flex flex-col sm:flex-row gap-3">
                <button onclick="copyTrackingNumber()" 
                        class="flex-1 bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-colors">
                    <i class="fas fa-copy mr-2"></i>Salin Nomor
                </button>
                <button onclick="closeSuccessModal()" 
                        class="flex-1 bg-gray-600 text-white px-4 py-2 rounded-md hover:bg-gray-700 transition-colors">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function suratFormData() {
        return {
            step: 1,
            loading: false,
            jenisSuratOptions: @json($jenisSurat),
            prodiOptions: @json($prodi),
            pekerjaanOptions: [
                'PNS', 'TNI/POLRI', 'Guru', 'Dosen', 'Dokter', 'Perawat', 'Bidan',
                'Karyawan Swasta', 'Wiraswasta', 'Petani', 'Nelayan', 'Pedagang',
                'Sopir', 'Buruh', 'Pensiunan', 'Ibu Rumah Tangga', 'Tidak Bekerja', 'Lainnya'
            ],
            pendidikanOptions: [
                'Tidak Sekolah', 'SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'
            ],
            form: {
                jenis_surat_id: '',
                nim: '',
                nama_mahasiswa: '',
                email: '',
                phone: '',
                prodi_id: '',
                keperluan: '',
                semester: '',
                tahun_akademik: '',
                nama_orang_tua: '',
                tempat_lahir_ortu: '',
                tanggal_lahir_ortu: '',
                pekerjaan_ortu: '',
                pekerjaan_ortu_lainnya: '',
                nip_ortu: '',
                jabatan_ortu: '',
                pangkat_golongan_ortu: '',
                pendidikan_terakhir_ortu: '',
                alamat_instansi_ortu: '',
                alamat_rumah_ortu: '',
                nama_perusahaan: '',
                alamat_perusahaan: '',
                periode_mulai: '',
                periode_selesai: '',
                bidang_kerja: '',
                judul_ta: '',
                dosen_pembimbing1: '',
                dosen_pembimbing2: '',
                lokasi_penelitian: '',
                keterangan_khusus: ''
            },
            
            toggleJenisSurat(id) {
                if (this.form.jenis_surat_id === id) {
                    this.form.jenis_surat_id = '';
                } else {
                    this.form.jenis_surat_id = id;
                }
            },
            
            getSelectedJenisName() {
                const selected = this.jenisSuratOptions.find(j => j.id == this.form.jenis_surat_id);
                return selected ? selected.nama_jenis : '';
            },
            
            getSelectedKode() {
                const selected = this.jenisSuratOptions.find(j => j.id == this.form.jenis_surat_id);
                return selected ? selected.kode_surat : '';
            },

            getSelectedProdiName() {
                const selected = this.prodiOptions.find(p => p.id == this.form.prodi_id);
                return selected ? selected.nama_prodi : '';
            },
            
            canProceed() {
                if (this.step === 1) {
                    return this.form.jenis_surat_id !== '';
                } else if (this.step === 2) {
                    return this.form.nim && this.form.nama_mahasiswa && 
                           this.form.email && this.form.phone && 
                           this.form.prodi_id && this.form.keperluan;
                }
                return true;
            },
            
            nextStep() {
                if (this.canProceed() && this.step < 3) {
                    this.step++;
                }
            },
            
            prevStep() {
                if (this.step > 1) {
                    this.step--;
                }
            },
            
            async submitForm() {
                this.loading = true;
                
                try {
                    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;
                    
                    if (!csrfToken) {
                        alert('Session expired. Halaman akan di-refresh.');
                        window.location.reload();
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    
                    formData.append('jenis_surat_id', this.form.jenis_surat_id);
                    formData.append('nim', this.form.nim);
                    formData.append('nama_mahasiswa', this.form.nama_mahasiswa);
                    formData.append('email', this.form.email);
                    formData.append('phone', this.form.phone);
                    formData.append('prodi_id', this.form.prodi_id);
                    formData.append('keperluan', this.form.keperluan);
                    
                    const kode = this.getSelectedKode();
                    
                    if (kode === 'MA') {
                        formData.append('semester', this.form.semester || '');
                        formData.append('tahun_akademik', this.form.tahun_akademik || '');
                        formData.append('nama_orang_tua', this.form.nama_orang_tua || '');
                        formData.append('tempat_lahir_ortu', this.form.tempat_lahir_ortu || '');
                        formData.append('tanggal_lahir_ortu', this.form.tanggal_lahir_ortu || '');
                        
                        // Handle pekerjaan "Lainnya"
                        let pekerjaanOrtu = this.form.pekerjaan_ortu;
                        if (pekerjaanOrtu === 'Lainnya') {
                            pekerjaanOrtu = this.form.pekerjaan_ortu_lainnya;
                        }
                        formData.append('pekerjaan_ortu', pekerjaanOrtu || '');

                        formData.append('nip_ortu', this.form.nip_ortu || '');
                        formData.append('jabatan_ortu', this.form.jabatan_ortu || '');
                        formData.append('pangkat_golongan_ortu', this.form.pangkat_golongan_ortu || '');
                        formData.append('pendidikan_terakhir_ortu', this.form.pendidikan_terakhir_ortu || '');
                        formData.append('alamat_instansi_ortu', this.form.alamat_instansi_ortu || '');
                        formData.append('alamat_rumah_ortu', this.form.alamat_rumah_ortu || '');
                    } else if (kode === 'KP') {
                        formData.append('nama_perusahaan', this.form.nama_perusahaan || '');
                        formData.append('alamat_perusahaan', this.form.alamat_perusahaan || '');
                        formData.append('periode_mulai', this.form.periode_mulai || '');
                        formData.append('periode_selesai', this.form.periode_selesai || '');
                        formData.append('bidang_kerja', this.form.bidang_kerja || '');
                    } else if (kode === 'TA') {
                        formData.append('judul_ta', this.form.judul_ta || '');
                        formData.append('dosen_pembimbing1', this.form.dosen_pembimbing1 || '');
                        formData.append('dosen_pembimbing2', this.form.dosen_pembimbing2 || '');
                        formData.append('lokasi_penelitian', this.form.lokasi_penelitian || '');
                    } else if (kode === 'SKM') {
                        formData.append('keterangan_khusus', this.form.keterangan_khusus || '');
                    }
                    
                    const response = await fetch('{{ route('public.pengajuan.store') }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.status === 419) {
                        alert('Session telah berakhir. Silakan refresh halaman.');
                        window.location.reload();
                        return;
                    }
                    
                    const responseText = await response.text();
                    console.log('Response:', responseText);
                    
                    if (response.ok) {
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (e) {
                            window.showSuccessModal('TRK-' + Date.now().toString(36).toUpperCase().substr(0, 8));
                            this.resetForm();
                            return;
                        }
                        
                        if (data.success) {
                            window.showSuccessModal(data.tracking_token);
                            this.resetForm();
                        } else {
                            alert(data.message || 'Terjadi kesalahan');
                        }
                    } else {
                        try {
                            const errorData = JSON.parse(responseText);
                            alert(errorData.message || 'Terjadi kesalahan server');
                        } catch (e) {
                            alert('Terjadi kesalahan. Silakan coba lagi.');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Terjadi kesalahan jaringan: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },
            
            resetForm() {
                this.step = 1;
                Object.keys(this.form).forEach(key => {
                    this.form[key] = '';
                });
            }
        }
    }

window.showSuccessModal = function(token) {
    document.getElementById('trackingNumber').textContent = token;
    document.getElementById('successModal').classList.remove('hidden');
    document.getElementById('successModal').classList.add('flex');
}

window.closeSuccessModal = function() {
    document.getElementById('successModal').classList.add('hidden');
    document.getElementById('successModal').classList.remove('flex');
}

window.copyTrackingNumber = function() {
    const trackingNumber = document.getElementById('trackingNumber').textContent;
    navigator.clipboard.writeText(trackingNumber).then(function() {
        alert('Nomor tracking berhasil disalin!');
    }).catch(function(err) {
        console.error('Gagal menyalin: ', err);
        prompt('Salin nomor tracking ini:', trackingNumber);
    });
}

window.addEventListener('error', function(event) {
    console.log('Global error:', event.error);
});
</script>
@endpush
@endsection