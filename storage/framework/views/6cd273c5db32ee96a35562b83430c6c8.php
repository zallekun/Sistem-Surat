<!-- public.pengajuan.form -->



<?php $__env->startSection('content'); ?>
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
                <span class="ml-2 font-medium">Validasi</span>
            </div>
        </div>
    </div>

    <!-- Form Content -->
    <div class="bg-white rounded-lg shadow-lg p-6">
        
        <!-- Step 1: Pilih Jenis Surat -->
        <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 transform translate-x-4" x-transition:enter-end="opacity-100 transform translate-x-0">
            <h2 class="text-xl font-semibold mb-4">Pilih Jenis Surat yang Diperlukan</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <?php $__currentLoopData = $jenisSurat; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $jenis): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="border rounded-lg p-4 cursor-pointer transition-all" 
                         :class="form.jenis_surat_id == <?php echo e($jenis->id); ?> ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                         @click="toggleJenisSurat(<?php echo e($jenis->id); ?>)">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium"><?php echo e($jenis->nama_jenis); ?></h3>
                                <p class="text-sm text-gray-500">Kode: <?php echo e($jenis->kode_surat); ?></p>
                            </div>
                            <div class="ml-4">
                                <div class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                     :class="form.jenis_surat_id == <?php echo e($jenis->id); ?> ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                    <div x-show="form.jenis_surat_id == <?php echo e($jenis->id); ?>" class="w-2 h-2 bg-white rounded-full"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
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
                <?php $__currentLoopData = $prodi; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($p->id); ?>"><?php echo e($p->nama_prodi); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Keperluan *</label>
            <input type="text" x-model="form.keperluan" placeholder="Keperluan surat ini..." 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        
        <!-- MOVED: Semester & Tahun Akademik (Universal fields) -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Semester *</label>
            <select x-model="form.semester" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Pilih Semester</option>
                <?php for($i = 1; $i <= 14; $i++): ?>
                    <option value="<?php echo e($i); ?>"><?php echo e($i); ?></option>
                <?php endfor; ?>
            </select>
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Tahun Akademik *</label>
            <input type="text" x-model="form.tahun_akademik" placeholder="2024/2025" 
                   class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
        </div>
        
        <!-- NEW: Dosen Wali (Universal fields) with Dropdown -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                Dosen Wali * 
                <span class="text-xs text-gray-500">(Pilih dari daftar)</span>
            </label>
            <select x-model="form.dosen_wali_id" 
                    @change="selectDosenWali()"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <option value="">Pilih Dosen Wali</option>
                <template x-for="dosen in dosenWaliList" :key="dosen.id">
                    <option :value="dosen.id" x-text="dosen.nama"></option>
                </template>
                <option value="other">Dosen Lain (Input Manual)</option>
            </select>
        </div>

        <!-- Manual Input - shown only if "Dosen Lain" selected -->
        <div x-show="form.dosen_wali_id === 'other'" x-transition>
            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Dosen Wali (Manual)</label>
            <input type="text" 
                x-model="form.dosen_wali_nama_manual" 
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="Masukkan nama dosen wali">
        </div>

                <!-- NID Display/Input -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
                NID Dosen Wali
                <span class="text-xs text-gray-500" x-show="form.dosen_wali_id && form.dosen_wali_id !== 'other'">(Otomatis)</span>
            </label>
            <input type="text" 
                x-model="form.dosen_wali_nid" 
                :readonly="form.dosen_wali_id && form.dosen_wali_id !== 'other'"
                :class="form.dosen_wali_id && form.dosen_wali_id !== 'other' ? 'bg-gray-100 cursor-not-allowed' : ''"
                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                placeholder="NID akan terisi otomatis">
        </div>

                <!-- Hidden field for dosen wali nama (final value) -->
        <input type="hidden" x-model="form.dosen_wali_nama">
    </div>

            <!-- Dynamic Fields Based on Selected Jenis Surat -->
            <div x-show="getSelectedKode() === 'MA'">
                <h3 class="text-lg font-medium mb-4 text-gray-800">Data Tambahan - Surat Mahasiswa Aktif</h3>
                <div class="grid md:grid-cols-2 gap-4 mb-4">
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
                
                <!-- Jumlah Mahasiswa -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Mahasiswa KP *</label>
                    <select x-model="form.jumlah_mahasiswa_kp" 
                            @change="updateMahasiswaKPList()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1">1 Orang</option>
                        <option value="2">2 Orang</option>
                        <option value="3">3 Orang</option>
                        <option value="4">4 Orang</option>
                        <option value="5">5 Orang</option>
                    </select>
                </div>

                <!-- Dynamic Mahasiswa List -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-700 mb-3">Data Mahasiswa</h4>
                    <div class="space-y-4">
                        <template x-for="(mahasiswa, index) in form.mahasiswa_kp" :key="index">
                            <div class="grid md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="`Nama Mahasiswa ${index + 1} *`"></label>
                                    <input type="text" x-model="mahasiswa.nama" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :placeholder="`Nama mahasiswa ${index + 1}`">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="`NIM ${index + 1} *`"></label>
                                    <input type="text" x-model="mahasiswa.nim" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :placeholder="`NIM mahasiswa ${index + 1}`">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="`Program Studi ${index + 1} *`"></label>
                                    <select x-model="mahasiswa.prodi" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Program Studi</option>
                                        <template x-for="prodi in prodiOptions" :key="prodi.id">
                                            <option :value="prodi.nama_prodi" x-text="prodi.nama_prodi"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                <!-- Data Perusahaan -->
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Perusahaan/Instansi *</label>
                        <input type="text" x-model="form.nama_perusahaan" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Mulai *</label>
                        <input type="date" x-model="form.periode_mulai" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Periode Selesai *</label>
                        <input type="date" x-model="form.periode_selesai" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bidang Kerja</label>
                        <input type="text" x-model="form.bidang_kerja" 
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Alamat Lengkap Perusahaan/Instansi *</label>
                        <textarea x-model="form.alamat_perusahaan" rows="3" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                </div>
            </div>
            <div x-show="getSelectedKode() === 'TA'">
                <h3 class="text-lg font-medium mb-4 text-gray-800">Data Tambahan - Surat Tugas Akhir</h3>
                
                <!-- Jumlah Mahasiswa TA -->
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Jumlah Mahasiswa TA *</label>
                    <select x-model="form.jumlah_mahasiswa_ta" 
                            @change="updateMahasiswaTAList()"
                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option value="1">1 Orang (TA Individu)</option>
                        <option value="2">2 Orang (TA Kelompok)</option>
                    </select>
                </div>

                <!-- Dynamic Mahasiswa List -->
                <div class="mb-6">
                    <h4 class="font-medium text-gray-700 mb-3">Data Mahasiswa TA</h4>
                    <div class="space-y-4">
                        <template x-for="(mahasiswa, index) in form.mahasiswa_ta" :key="index">
                            <div class="grid md:grid-cols-3 gap-4 p-4 border border-gray-200 rounded-lg bg-gray-50">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="`Nama Mahasiswa ${index + 1} *`"></label>
                                    <input type="text" x-model="mahasiswa.nama" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :placeholder="`Nama mahasiswa ${index + 1}`">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="`NIM ${index + 1} *`"></label>
                                    <input type="text" x-model="mahasiswa.nim" 
                                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                                        :placeholder="`NIM mahasiswa ${index + 1}`">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-1" x-text="`Program Studi ${index + 1} *`"></label>
                                    <select x-model="mahasiswa.prodi" 
                                            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                        <option value="">Pilih Program Studi</option>
                                        <template x-for="prodi in prodiOptions" :key="prodi.id">
                                            <option :value="prodi.nama_prodi" x-text="prodi.nama_prodi"></option>
                                        </template>
                                    </select>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
                
                <!-- Data TA -->
                <div class="grid md:grid-cols-2 gap-4 mb-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Judul Tugas Akhir *</label>
                        <textarea x-model="form.judul_ta" rows="2" 
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"></textarea>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Dosen Pembimbing 1 *</label>
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
                        <p><span class="font-medium text-green-600">Semester:</span> <span class="text-green-800" x-text="form.semester || '-'"></span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium text-gray-600">No. HP:</span> <span class="text-gray-800" x-text="form.phone || '-'"></span></p>
                        <p><span class="font-medium text-gray-600">Program Studi:</span> <span class="text-gray-800" x-text="getSelectedProdiName() || '-'"></span></p>
                        <p><span class="font-medium text-gray-600">Keperluan:</span> <span class="text-gray-800" x-text="form.keperluan || '-'"></span></p>
                        <p><span class="font-medium text-green-600">Tahun Akademik:</span> <span class="text-green-800" x-text="form.tahun_akademik || '-'"></span></p>
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
            <div x-show="getSelectedKode() === 'KP'" class="bg-yellow-50 border border-yellow-200 rounded-lg p-4 mb-6">
                <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                    <i class="fas fa-briefcase mr-2"></i>
                    Data Kerja Praktek
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm mb-4">
                    <div class="space-y-2">
                        <p><span class="font-medium text-yellow-600">Nama Perusahaan:</span> <span class="text-yellow-800" x-text="form.nama_perusahaan || '-'"></span></p>
                        <p><span class="font-medium text-yellow-600">Periode:</span> <span class="text-yellow-800" x-text="(form.periode_mulai && form.periode_selesai) ? form.periode_mulai + ' s.d. ' + form.periode_selesai : '-'"></span></p>
                        <p><span class="font-medium text-yellow-600">Bidang Kerja:</span> <span class="text-yellow-800" x-text="form.bidang_kerja || '-'"></span></p>
                    </div>
                    <div class="space-y-2">
                        <p><span class="font-medium text-yellow-600">Jumlah Mahasiswa:</span> <span class="text-yellow-800" x-text="form.jumlah_mahasiswa_kp + ' orang'"></span></p>
                        <p><span class="font-medium text-yellow-600">Alamat:</span> <span class="text-yellow-800" x-text="form.alamat_perusahaan || '-'"></span></p>
                    </div>
                </div>
                
                <!-- List Mahasiswa KP -->
                <div class="mt-4">
                    <h4 class="font-medium text-yellow-700 mb-2">Daftar Mahasiswa:</h4>
                    <div class="bg-yellow-100 rounded p-3">
                        <template x-for="(mahasiswa, index) in form.mahasiswa_kp" :key="index">
                            <div class="grid grid-cols-4 gap-2 py-1 border-b border-yellow-200 last:border-b-0 text-sm">
                                <span class="font-medium" x-text="`${index + 1}.`"></span>
                                <span x-text="mahasiswa.nama || 'Belum diisi'"></span>
                                <span class="text-yellow-700" x-text="mahasiswa.nim || 'NIM belum diisi'"></span>
                                <span class="text-yellow-600" x-text="mahasiswa.prodi || 'Prodi belum dipilih'"></span>
                            </div>
                        </template>
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

<?php $__env->startPush('scripts'); ?>
<script>
    function suratFormData() {
        return {
            step: 1,
            loading: false,
            jenisSuratOptions: <?php echo json_encode($jenisSurat, 15, 512) ?>,
            prodiOptions: <?php echo json_encode($prodi, 15, 512) ?>,
            dosenWaliList: [],
            pekerjaanOptions: [
                'PNS', 'TNI/POLRI', 'Guru', 'Dosen', 'Dokter', 'Perawat', 'Bidan',
                'Karyawan Swasta', 'Wiraswasta', 'Petani', 'Nelayan', 'Pedagang',
                'Sopir', 'Buruh', 'Pensiunan', 'Ibu Rumah Tangga', 'Tidak Bekerja', 'Lainnya'
            ],
            pendidikanOptions: [
                'Tidak Sekolah', 'SD', 'SMP', 'SMA/SMK', 'D1', 'D2', 'D3', 'S1', 'S2', 'S3'
            ],
            form: {
                // Basic fields
                jenis_surat_id: '',
                nim: '',
                nama_mahasiswa: '',
                email: '',
                phone: '',
                prodi_id: '',
                keperluan: '',

                // Universal fields - for all jenis surat
                semester: '',
                tahun_akademik: '',
                dosen_wali_id: '',
                dosen_wali_nama: '',
                dosen_wali_nama_manual: '',
                dosen_wali_nid: '',

                // MA fields
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

                // KP fields - COMPLETE SET
                jumlah_mahasiswa_kp: '1',
                mahasiswa_kp: [{ nama: '', nim: '', prodi: '' }],
                nama_perusahaan: '',
                alamat_perusahaan: '',
                periode_mulai: '',
                periode_selesai: '',
                bidang_kerja: '',

                // TA fields
                jumlah_mahasiswa_ta: '1',  // TAMBAHKAN
                mahasiswa_ta: [{ nama: '', nim: '', prodi: '' }],  // TAMBAHKAN
                judul_ta: '',
                dosen_pembimbing1: '',
                dosen_pembimbing2: '',
                lokasi_penelitian: '',
                
                // SKM fields
                keterangan_khusus: ''
            },
            
            // Initialize component
            init() {
                // Watch prodi_id changes to load dosen wali
                this.$watch('form.prodi_id', (value) => {
                    if (value) {
                        this.loadDosenWali();
                    } else {
                        this.dosenWaliList = [];
                        this.form.dosen_wali_id = '';
                        this.form.dosen_wali_nama = '';
                        this.form.dosen_wali_nid = '';
                    }
                });
            },

            // Fetch dosen wali when prodi selected
            async loadDosenWali() {
                if (!this.form.prodi_id) {
                    this.dosenWaliList = [];
                    return;
                }
                
                try {
                    const response = await fetch(`/api/dosen-wali/${this.form.prodi_id}`);
                    const result = await response.json();
                    
                    if (result.success) {
                        this.dosenWaliList = result.data;
                        console.log('Loaded dosen wali:', this.dosenWaliList);
                    } else {
                        console.error('Failed to load dosen wali:', result.message);
                        this.dosenWaliList = [];
                    }
                } catch (error) {
                    console.error('Error loading dosen wali:', error);
                    this.dosenWaliList = [];
                }
            },

            // Select dosen wali and auto-populate NID
            selectDosenWali() {
                const selectedId = this.form.dosen_wali_id;
                
                if (selectedId === 'other') {
                    // Manual input mode
                    this.form.dosen_wali_nama = '';
                    this.form.dosen_wali_nid = '';
                    return;
                }
                
                if (!selectedId) {
                    // Clear selection
                    this.form.dosen_wali_nama = '';
                    this.form.dosen_wali_nid = '';
                    return;
                }
                
                // Find selected dosen from list
                const selectedDosen = this.dosenWaliList.find(d => d.id == selectedId);
                
                if (selectedDosen) {
                    this.form.dosen_wali_nama = selectedDosen.nama;
                    this.form.dosen_wali_nid = selectedDosen.nid;
                    console.log('Selected dosen:', selectedDosen);
                }
            },

            // CRITICAL: Update mahasiswa KP list when count changes
            updateMahasiswaKPList() {
                const count = parseInt(this.form.jumlah_mahasiswa_kp);
                
                // Create array with specified count
                this.form.mahasiswa_kp = Array(count).fill().map(() => ({ 
                    nama: '', 
                    nim: '', 
                    prodi: '' 
                }));
                
                console.log('Updated mahasiswa KP list to', count, 'students:', this.form.mahasiswa_kp);
            },

            // Fungsi untuk update list mahasiswa TA
            updateMahasiswaTAList() {
                const count = parseInt(this.form.jumlah_mahasiswa_ta);
                
                // Create array with specified count
                this.form.mahasiswa_ta = Array(count).fill().map(() => ({ 
                    nama: '', 
                    nim: '', 
                    prodi: '' 
                }));
                
                console.log('Updated mahasiswa TA list to', count, 'students:', this.form.mahasiswa_ta);
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
            
            // UPDATED: Complete validation including KP multiple students
            canProceed() {
                if (this.step === 1) {
                    return this.form.jenis_surat_id !== '';
                } else if (this.step === 2) {
                    // Check dosen wali based on mode
                    let dosenWaliValid = false;
                    
                    if (this.form.dosen_wali_id === 'other') {
                        // Manual mode - nama manual required
                        dosenWaliValid = this.form.dosen_wali_nama_manual !== '';
                    } else if (this.form.dosen_wali_id) {
                        // Dropdown mode - ID selected
                        dosenWaliValid = true;
                    }
                    
                    const basicValid = this.form.nim && this.form.nama_mahasiswa && 
                                      this.form.email && this.form.phone && 
                                      this.form.prodi_id && this.form.keperluan &&
                                      this.form.semester && this.form.tahun_akademik &&
                                      dosenWaliValid;
                    
                    const kode = this.getSelectedKode();
                    
                    // KP specific validation
                    if (kode === 'KP') {
                        const kpBasicValid = this.form.nama_perusahaan && 
                                            this.form.alamat_perusahaan &&
                                            this.form.periode_mulai && 
                                            this.form.periode_selesai;
                        
                        // Validate all mahasiswa entries
                        const mahasiswaValid = this.form.mahasiswa_kp.every(m => 
                            m.nama.trim() !== '' && m.nim.trim() !== '' && m.prodi.trim() !== ''
                        );
                        
                        console.log('KP Validation:', {
                            basicValid,
                            kpBasicValid,
                            mahasiswaValid,
                            mahasiswaData: this.form.mahasiswa_kp
                        });
                        
                        return basicValid && kpBasicValid && mahasiswaValid;
                    }
                    
                    // MA specific validation
                    if (kode === 'MA') {
                        const maValid = this.form.nama_orang_tua && 
                                       this.form.tempat_lahir_ortu && 
                                       this.form.tanggal_lahir_ortu &&
                                       this.form.pekerjaan_ortu &&
                                       this.form.pendidikan_terakhir_ortu &&
                                       this.form.alamat_rumah_ortu;
                        
                        return basicValid && maValid;
                    }
                    
                    // TA specific validation
                    if (kode === 'TA') {
                        const taBasicValid = this.form.judul_ta && 
                                            this.form.dosen_pembimbing1;
                        
                        // Validate all mahasiswa entries
                        const mahasiswaValid = this.form.mahasiswa_ta.every(m => 
                            m.nama.trim() !== '' && m.nim.trim() !== '' && m.prodi.trim() !== ''
                        );
                        
                        console.log('TA Validation:', {
                            basicValid,
                            taBasicValid,
                            mahasiswaValid,
                            mahasiswaData: this.form.mahasiswa_ta
                        });
                        
                        return basicValid && taBasicValid && mahasiswaValid;
                    }
                    
                    // SKM specific validation
                    if (kode === 'SKM') {
                        const skmValid = this.form.keterangan_khusus;
                        
                        return basicValid && skmValid;
                    }
                    
                    return basicValid;
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
            
            // UPDATED: Complete form submission with KP multiple students
            async submitForm() {
                this.loading = true;
                
                try {
                    const csrfToken = document.querySelector('meta[name=csrf-token]')?.content;

                    if (!csrfToken) {
                        showError('Session expired. Halaman akan di-refresh.');
                        setTimeout(() => window.location.reload(), 2000);
                        return;
                    }
                    
                    const formData = new FormData();
                    formData.append('_token', csrfToken);
                    
                    // Basic data
                    formData.append('jenis_surat_id', this.form.jenis_surat_id);
                    formData.append('nim', this.form.nim);
                    formData.append('nama_mahasiswa', this.form.nama_mahasiswa);
                    formData.append('email', this.form.email);
                    formData.append('phone', this.form.phone);
                    formData.append('prodi_id', this.form.prodi_id);
                    formData.append('keperluan', this.form.keperluan);
                    formData.append('semester', this.form.semester || '');
                    formData.append('tahun_akademik', this.form.tahun_akademik || '');
                    
                    // Dosen Wali - handle both dropdown and manual
                    if (this.form.dosen_wali_id === 'other') {
                        // Manual input
                        formData.append('dosen_wali_nama', this.form.dosen_wali_nama_manual || '');
                        formData.append('dosen_wali_nid', this.form.dosen_wali_nid || '');
                    } else {
                        // From dropdown
                        formData.append('dosen_wali_nama', this.form.dosen_wali_nama || '');
                        formData.append('dosen_wali_nid', this.form.dosen_wali_nid || '');
                    }
                    
                    const kode = this.getSelectedKode();
                    console.log('Submitting form for jenis surat:', kode);
                    
                    // Handle specific jenis surat data
                    if (kode === 'MA') {
                        formData.append('nama_orang_tua', this.form.nama_orang_tua || '');
                        formData.append('tempat_lahir_ortu', this.form.tempat_lahir_ortu || '');
                        formData.append('tanggal_lahir_ortu', this.form.tanggal_lahir_ortu || '');
                        
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
                        // Basic KP data
                        formData.append('nama_perusahaan', this.form.nama_perusahaan || '');
                        formData.append('alamat_perusahaan', this.form.alamat_perusahaan || '');
                        formData.append('periode_mulai', this.form.periode_mulai || '');
                        formData.append('periode_selesai', this.form.periode_selesai || '');
                        formData.append('bidang_kerja', this.form.bidang_kerja || '');
                        
                        // CRITICAL: Multiple mahasiswa data
                        formData.append('jumlah_mahasiswa_kp', this.form.jumlah_mahasiswa_kp);
                        formData.append('mahasiswa_kp', JSON.stringify(this.form.mahasiswa_kp));
                        
                        console.log('KP Form Data:', {
                            jumlah: this.form.jumlah_mahasiswa_kp,
                            mahasiswa: this.form.mahasiswa_kp,
                            mahasiswaJSON: JSON.stringify(this.form.mahasiswa_kp)
                        });
                        
                    } else if (kode === 'TA') {
                        formData.append('judul_ta', this.form.judul_ta || '');
                        formData.append('dosen_pembimbing1', this.form.dosen_pembimbing1 || '');
                        formData.append('dosen_pembimbing2', this.form.dosen_pembimbing2 || '');
                        formData.append('lokasi_penelitian', this.form.lokasi_penelitian || '');
                        formData.append('jumlah_mahasiswa_ta', this.form.jumlah_mahasiswa_ta);
                        formData.append('mahasiswa_ta', JSON.stringify(this.form.mahasiswa_ta));
                        
                        console.log('TA Form Data:', {
                            jumlah: this.form.jumlah_mahasiswa_ta,
                            mahasiswa: this.form.mahasiswa_ta,
                            mahasiswaJSON: JSON.stringify(this.form.mahasiswa_ta)
                        });
                        
                        
                    } else if (kode === 'SKM') {
                        formData.append('keterangan_khusus', this.form.keterangan_khusus || '');
                    }
                    
                    console.log('Submitting to server...');
                    
                    const response = await fetch('<?php echo e(route('public.pengajuan.store')); ?>', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin'
                    });
                    
                    if (response.status === 419) {
                        showError('Session telah berakhir. Silakan refresh halaman.');
                        setTimeout(() => window.location.reload(), 2000);
                        return;
                    }
                    
                    const responseText = await response.text();
                    console.log('Server Response:', responseText);
                    
                    if (response.ok) {
                        let data;
                        try {
                            data = JSON.parse(responseText);
                        } catch (e) {
                            console.error('Failed to parse response as JSON:', e);
                            window.showSuccessModal('TRK-' + Date.now().toString(36).toUpperCase().substr(0, 8));
                            this.resetForm();
                            return;
                        }
                        
                        if (data.success) {
                            console.log('Form submitted successfully:', data);
                            window.showSuccessModal(data.tracking_token);
                            this.resetForm();
                        } else {
                            console.error('Server returned error:', data);
                            showError(data.message || 'Terjadi kesalahan saat mengirim data');
                        }
                    } else {
                        try {
                            const errorData = JSON.parse(responseText);
                            console.error('HTTP Error:', errorData);
                            showError(errorData.message || 'Terjadi kesalahan server (HTTP ' + response.status + ')');
                        } catch (e) {
                            console.error('HTTP Error - unable to parse:', responseText);
                            showError('Terjadi kesalahan server. Silakan coba lagi.');
                        }
                    }
                } catch (error) {
                    console.error('Network/JS Error:', error);
                    showError('Terjadi kesalahan jaringan: ' + error.message);
                } finally {
                    this.loading = false;
                }
            },
            
            // UPDATED: Reset form with proper KP defaults
            resetForm() {
                this.step = 1;
                this.dosenWaliList = [];
                
                // Reset all form fields with proper defaults
                Object.keys(this.form).forEach(key => {
                    if (key === 'mahasiswa_kp' || key === 'mahasiswa_ta') {
                        // Reset to single student
                        this.form[key] = [{ nama: '', nim: '', prodi: '' }];
                    } else if (key === 'jumlah_mahasiswa_kp' || key === 'jumlah_mahasiswa_ta') {
                        // Reset to 1 student
                        this.form[key] = '1';
                    } else {
                        // Reset to empty string
                        this.form[key] = '';
                    }
                });
                
                console.log('Form reset completed');
            }
        }
    }

    // Success modal functions
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
            showSuccess('Nomor tracking berhasil disalin!');
        }).catch(function(err) {
            console.error('Gagal menyalin: ', err);
            // Fallback untuk browser lama
            const el = document.createElement('textarea');
            el.value = trackingNumber;
            el.style.position = 'absolute';
            el.style.left = '-9999px';
            document.body.appendChild(el);
            el.select();
            document.execCommand('copy');
            document.body.removeChild(el);
            showSuccess('Nomor tracking berhasil disalin!');
        });
    }

    // Global error handling
    window.addEventListener('error', function(event) {
        console.log('Global error:', event.error);
    });

    // Debug helper for development
    window.debugFormData = function() {
        const app = document.querySelector('[x-data]').__x.$data;
        console.log('Current form state:', app.form);
        console.log('Current step:', app.step);
        console.log('Can proceed:', app.canProceed());
        console.log('Selected jenis:', app.getSelectedKode());
    }
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\laragon\www\sistem-surat\resources\views/public/pengajuan/form.blade.php ENDPATH**/ ?>