{{-- resources/views/admin/users/create.blade.php --}}
@extends('layouts.app')

@section('title', 'Tambah User')

@section('content')
<div class="py-6 px-4 sm:px-6 lg:px-8">
    <div class="max-w-3xl mx-auto">
        <!-- Back Button -->
        <div class="mb-4">
            <a href="{{ route('admin.users.index') }}" class="inline-flex items-center text-sm text-gray-600 hover:text-gray-900">
                <i class="fas fa-arrow-left mr-2"></i>
                Kembali ke Daftar User
            </a>
        </div>

        <div class="bg-white/95 backdrop-blur-sm shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200 bg-gradient-to-r from-blue-500 to-blue-600">
                <h2 class="text-xl font-bold text-white">Tambah User Baru</h2>
                <p class="text-sm text-white/90 mt-1">Lengkapi form di bawah untuk menambahkan user</p>
            </div>

            <!-- Form -->
            <form action="{{ route('admin.users.store') }}" method="POST" class="p-6">
                @csrf

                <div class="space-y-6">
                    <!-- Nama -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Nama Lengkap <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               name="nama" 
                               value="{{ old('nama') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nama') border-red-500 @enderror"
                               placeholder="Masukkan nama lengkap"
                               required>
                        @error('nama')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror"
                               placeholder="user@example.com"
                               required>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- NIP -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            NIP <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <input type="text" 
                               name="nip" 
                               value="{{ old('nip') }}" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('nip') border-red-500 @enderror"
                               placeholder="Masukkan NIP">
                        @error('nip')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Role <span class="text-red-500">*</span>
                        </label>
                        <select name="role" 
                                id="roleSelect"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('role') border-red-500 @enderror"
                                required
                                onchange="handleRoleChange()">
                            <option value="">Pilih Role</option>
                            @foreach($roles as $role)
                                <option value="{{ $role->name }}" {{ old('role') == $role->name ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                </option>
                            @endforeach
                        </select>
                        @error('role')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Prodi -->
                    <div id="prodiField">
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Program Studi <span class="text-gray-400">(Untuk Staff Prodi/Fakultas)</span>
                        </label>
                        <select name="prodi_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('prodi_id') border-red-500 @enderror">
                            <option value="">Pilih Prodi</option>
                            @foreach($prodis as $prodi)
                                <option value="{{ $prodi->id }}" {{ old('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                    {{ $prodi->nama_prodi }}
                                </option>
                            @endforeach
                        </select>
                        @error('prodi_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Jabatan -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Jabatan <span class="text-gray-400">(Opsional)</span>
                        </label>
                        <select name="jabatan_id" 
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('jabatan_id') border-red-500 @enderror">
                            <option value="">Pilih Jabatan</option>
                            @foreach($jabatans as $jabatan)
                                <option value="{{ $jabatan->id }}" {{ old('jabatan_id') == $jabatan->id ? 'selected' : '' }}>
                                    {{ $jabatan->nama_jabatan }}
                                </option>
                            @endforeach
                        </select>
                        @error('jabatan_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror"
                               placeholder="Minimal 8 karakter"
                               required>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Konfirmasi Password <span class="text-red-500">*</span>
                        </label>
                        <input type="password" 
                               name="password_confirmation" 
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                               placeholder="Ketik ulang password"
                               required>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex gap-3">
                    <button type="submit" 
                            class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition">
                        <i class="fas fa-save mr-2"></i>Simpan User
                    </button>
                    <a href="{{ route('admin.users.index') }}" 
                       class="flex-1 px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 font-medium transition text-center">
                        <i class="fas fa-times mr-2"></i>Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function handleRoleChange() {
    const role = document.getElementById('roleSelect').value;
    const prodiField = document.getElementById('prodiField');
    
    // Show prodi field for staff_prodi, staff_fakultas, kaprodi
    if (['staff_prodi', 'staff_fakultas', 'kaprodi'].includes(role)) {
        prodiField.style.display = 'block';
    } else {
        prodiField.style.display = 'none';
    }
}

// Run on page load
handleRoleChange();
</script>
@endpush
@endsection