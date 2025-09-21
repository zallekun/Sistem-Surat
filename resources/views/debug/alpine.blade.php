@extends('layouts.public')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-center mb-8">Pengajuan Surat Online</h1>
    
    <!-- Debug Data dari PHP -->
    <div class="bg-yellow-100 p-4 mb-6 rounded">
        <h3 class="font-bold">PHP Data Check:</h3>
        <p>Jenis Surat: {{ count($jenisSurat) }} items</p>
        <p>Prodi: {{ count($prodi) }} items</p>
    </div>

    <!-- Form dengan Alpine -->
    <div class="bg-white rounded-lg shadow p-6" x-data="{
        step: 1,
        form: {
            jenis_surat_id: '',
            nim: '',
            nama_mahasiswa: '',
            email: '',
            phone: '',
            prodi_id: '',
            keperluan: ''
        },
        
        toggleJenisSurat(id) {
            if (this.form.jenis_surat_id === id) {
                this.form.jenis_surat_id = '';
            } else {
                this.form.jenis_surat_id = id;
            }
        },
        
        canProceed() {
            if (this.step === 1) {
                return this.form.jenis_surat_id !== '';
            }
            return this.form.nim && this.form.nama_mahasiswa && this.form.email;
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
        }
    }">
        
        <!-- Progress -->
        <div class="mb-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center" :class="step >= 1 ? 'text-blue-600' : 'text-gray-400'">
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center"
                         :class="step >= 1 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300'">
                        1
                    </div>
                    <span class="ml-2">Pilih Surat</span>
                </div>
                <div class="flex-1 h-0.5 mx-4" :class="step >= 2 ? 'bg-blue-600' : 'bg-gray-300'"></div>
                <div class="flex items-center" :class="step >= 2 ? 'text-blue-600' : 'text-gray-400'">
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center"
                         :class="step >= 2 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300'">
                        2
                    </div>
                    <span class="ml-2">Isi Data</span>
                </div>
                <div class="flex-1 h-0.5 mx-4" :class="step >= 3 ? 'bg-blue-600' : 'bg-gray-300'"></div>
                <div class="flex items-center" :class="step >= 3 ? 'text-blue-600' : 'text-gray-400'">
                    <div class="w-8 h-8 rounded-full border-2 flex items-center justify-center"
                         :class="step >= 3 ? 'bg-blue-600 border-blue-600 text-white' : 'border-gray-300'">
                        3
                    </div>
                    <span class="ml-2">Selesai</span>
                </div>
            </div>
        </div>

        <!-- Step 1: Pilih Jenis Surat -->
        <div x-show="step === 1">
            <h2 class="text-xl font-semibold mb-4">Pilih Jenis Surat</h2>
            <div class="grid md:grid-cols-2 gap-4">
                @foreach($jenisSurat as $jenis)
                    <div class="border rounded-lg p-4 cursor-pointer transition-all" 
                         :class="form.jenis_surat_id == '{{ $jenis->id }}' ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'"
                         @click="toggleJenisSurat('{{ $jenis->id }}')">
                        <div class="flex items-center justify-between">
                            <div>
                                <h3 class="font-medium">{{ $jenis->nama_jenis }}</h3>
                                <p class="text-sm text-gray-500">Kode: {{ $jenis->kode_surat }}</p>
                            </div>
                            <div class="w-4 h-4 border-2 rounded-full flex items-center justify-center"
                                 :class="form.jenis_surat_id == '{{ $jenis->id }}' ? 'border-blue-500 bg-blue-500' : 'border-gray-300'">
                                <div x-show="form.jenis_surat_id == '{{ $jenis->id }}'" class="w-2 h-2 bg-white rounded-full"></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <div class="mt-6 flex justify-end">
                <button @click="nextStep()" 
                        :disabled="!canProceed()"
                        :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="px-6 py-2 rounded-md font-medium">
                    Selanjutnya
                </button>
            </div>
        </div>

        <!-- Step 2: Isi Data -->
        <div x-show="step === 2">
            <h2 class="text-xl font-semibold mb-4">Isi Data Pemohon</h2>
            <div class="grid md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">NIM</label>
                    <input type="text" x-model="form.nim" 
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Nama Lengkap</label>
                    <input type="text" x-model="form.nama_mahasiswa" 
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" x-model="form.email" 
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">No. HP</label>
                    <input type="tel" x-model="form.phone" 
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Program Studi</label>
                    <select x-model="form.prodi_id" 
                            class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                        <option value="">Pilih Prodi</option>
                        @foreach($prodi as $p)
                            <option value="{{ $p->id }}">{{ $p->nama_prodi }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Keperluan</label>
                    <input type="text" x-model="form.keperluan" 
                           class="w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500">
                </div>
            </div>
            
            <div class="mt-6 flex justify-between">
                <button @click="prevStep()" 
                        class="px-6 py-2 border rounded-md hover:bg-gray-50">
                    Kembali
                </button>
                <button @click="nextStep()" 
                        :disabled="!canProceed()"
                        :class="canProceed() ? 'bg-blue-600 hover:bg-blue-700 text-white' : 'bg-gray-300 text-gray-500 cursor-not-allowed'"
                        class="px-6 py-2 rounded-md font-medium">
                    Lanjutkan
                </button>
            </div>
        </div>

        <!-- Step 3: Konfirmasi -->
        <div x-show="step === 3">
            <h2 class="text-xl font-semibold mb-4">Konfirmasi Data</h2>
            <div class="bg-gray-50 rounded p-4">
                <p><strong>Jenis Surat ID:</strong> <span x-text="form.jenis_surat_id"></span></p>
                <p><strong>NIM:</strong> <span x-text="form.nim"></span></p>
                <p><strong>Nama:</strong> <span x-text="form.nama_mahasiswa"></span></p>
                <p><strong>Email:</strong> <span x-text="form.email"></span></p>
                <p><strong>HP:</strong> <span x-text="form.phone"></span></p>
                <p><strong>Prodi ID:</strong> <span x-text="form.prodi_id"></span></p>
                <p><strong>Keperluan:</strong> <span x-text="form.keperluan"></span></p>
            </div>
            
            <div class="mt-6 flex justify-between">
                <button @click="prevStep()" 
                        class="px-6 py-2 border rounded-md hover:bg-gray-50">
                    Kembali
                </button>
                <button @click="alert('Form akan disubmit!')" 
                        class="px-6 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                    Kirim Pengajuan
                </button>
            </div>
        </div>

        <!-- Debug Info -->
        <div class="mt-6 bg-gray-100 p-3 rounded text-sm">
            <p>Current Step: <span x-text="step"></span></p>
            <p>Selected Jenis: <span x-text="form.jenis_surat_id || 'none'"></span></p>
            <p>Can Proceed: <span x-text="canProceed() ? 'YES' : 'NO'"></span></p>
        </div>
    </div>
</div>
@endsection