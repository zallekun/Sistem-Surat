{{-- resources/views/admin/users/index.blade.php --}}
@extends('layouts.app')

@section('title', 'Kelola User')

@section('breadcrumb')
<li>
    <div class="flex items-center">
        <i class="fas fa-chevron-right text-gray-400 mx-2"></i>
        <span class="text-sm font-medium text-gray-500">Kelola User</span>
    </div>
</li>
@endsection

@section('content')
<div class="bg-white shadow-sm rounded-xl overflow-hidden">
            <!-- Header -->
            <div class="px-6 py-4 border-b border-gray-200">
                <div class="flex justify-between items-center">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Kelola User</h2>
                        <p class="text-sm text-gray-500 mt-0.5">Manajemen user sistem persuratan</p>
                    </div>
                    <a href="{{ route('admin.users.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                        <i class="fas fa-plus mr-2"></i>Tambah User
                    </a>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="px-6 py-4 bg-gray-50 border-b border-gray-200">
                <form method="GET" action="{{ route('admin.users.index') }}" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Search -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Cari</label>
                            <div class="relative">
                                <input type="text" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="Nama, Email, atau NIP..."
                                       class="w-full pl-10 pr-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <i class="fas fa-search text-gray-400 text-sm"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Role -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Role</label>
                            <select name="role" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Role</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}" {{ request('role') == $role->name ? 'selected' : '' }}>
                                        {{ ucfirst(str_replace('_', ' ', $role->name)) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Prodi -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Prodi</label>
                            <select name="prodi_id" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Prodi</option>
                                @foreach($prodis as $prodi)
                                    <option value="{{ $prodi->id }}" {{ request('prodi_id') == $prodi->id ? 'selected' : '' }}>
                                        {{ $prodi->nama_prodi }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Status -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1.5">Status</label>
                            <select name="is_active" class="w-full px-3 py-2 text-sm border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <option value="">Semua Status</option>
                                <option value="1" {{ request('is_active') === '1' ? 'selected' : '' }}>Aktif</option>
                                <option value="0" {{ request('is_active') === '0' ? 'selected' : '' }}>Nonaktif</option>
                            </select>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-2">
                        <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 text-sm font-medium transition">
                            <i class="fas fa-search mr-1"></i>Filter
                        </button>
                        <a href="{{ route('admin.users.index') }}" class="px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 text-sm font-medium transition">
                            <i class="fas fa-redo mr-1"></i>Reset
                        </a>
                    </div>
                </form>
            </div>

            <!-- Table -->
            @if($users->count() > 0)
                <div class="relative">
                    <div class="overflow-hidden">
                        <!-- Fixed Header -->
                        <div class="bg-gray-50 border-b-2 border-gray-200">
                            <table class="min-w-full">
                                <thead>
                                    <tr>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-16">No</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-48">Nama</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-40">Email</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-28">NIP</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Role</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase w-32">Prodi</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-24">Status</th>
                                        <th class="px-4 py-3 text-center text-xs font-semibold text-gray-600 uppercase w-40">Aksi</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                        
                        <!-- Scrollable Body -->
                        <div class="overflow-y-auto scroll-smooth" style="max-height: 500px; will-change: scroll-position;">
                            <table class="min-w-full">
                                <tbody class="bg-white divide-y divide-gray-100">
                                    @foreach($users as $index => $user)
                                        <tr class="hover:bg-blue-50">
                                            <td class="px-4 py-4 w-16 text-center">
                                                <span class="text-sm font-medium text-gray-700">{{ $users->firstItem() + $index }}</span>
                                            </td>
                                            <td class="px-4 py-4 w-48">
                                                <div class="flex items-center">
                                                    <div class="flex-shrink-0 h-10 w-10 bg-blue-100 rounded-full flex items-center justify-center">
                                                        <span class="text-blue-600 font-semibold text-sm">{{ substr($user->nama, 0, 2) }}</span>
                                                    </div>
                                                    <div class="ml-3">
                                                        <p class="text-sm font-medium text-gray-900">{{ $user->nama }}</p>
                                                        @if($user->jabatan)
                                                            <p class="text-xs text-gray-500">{{ $user->jabatan->nama_jabatan }}</p>
                                                        @endif
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-4 py-4 w-40">
                                                <p class="text-sm text-gray-900">{{ $user->email }}</p>
                                            </td>
                                            <td class="px-4 py-4 w-28">
                                                <p class="text-sm text-gray-900">{{ $user->nip ?? '-' }}</p>
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                @if($user->roles->isNotEmpty())
                                                    <span class="px-2 py-1 text-xs font-medium rounded-full bg-purple-100 text-purple-800">
                                                        {{ ucfirst(str_replace('_', ' ', $user->roles->first()->name)) }}
                                                    </span>
                                                @else
                                                    <span class="text-xs text-gray-400">No Role</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 w-32">
                                                @if($user->prodi)
                                                    <span class="text-sm text-gray-900">{{ $user->prodi->nama_prodi }}</span>
                                                @else
                                                    <span class="text-xs text-gray-400">-</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-4 w-24 text-center">
                                                <button onclick="toggleStatus({{ $user->id }}, {{ $user->is_active ? 'true' : 'false' }})" 
                                                        class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $user->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                                    <i class="fas fa-circle text-xs mr-1"></i>
                                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                                </button>
                                            </td>
                                            <td class="px-4 py-4 w-40">
                                                <div class="flex items-center justify-center gap-2">
                                                    <a href="{{ route('admin.users.edit', $user->id) }}" 
                                                       class="inline-flex items-center px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 text-xs font-medium"
                                                       title="Edit">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button onclick="resetPassword({{ $user->id }})" 
                                                            class="inline-flex items-center px-3 py-1.5 bg-yellow-100 text-yellow-700 rounded-lg hover:bg-yellow-200 text-xs font-medium"
                                                            title="Reset Password">
                                                        <i class="fas fa-key"></i>
                                                    </button>
                                                    <form action="{{ route('admin.users.destroy', $user->id) }}"
                                                          method="POST"
                                                          onsubmit="return handleDelete(event, 'Yakin ingin menghapus user ini?')"
                                                          class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit"
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-100 text-red-700 rounded-lg hover:bg-red-200 text-xs font-medium"
                                                                title="Hapus">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="px-6 py-4 border-t border-gray-200">
                    {{ $users->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-16">
                    <i class="fas fa-users fa-4x text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">Tidak Ada User</h3>
                    <p class="text-gray-500 text-sm">Belum ada user di sistem</p>
                </div>
            @endif
</div>

<!-- Reset Password Modal -->
<div id="resetPasswordModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
        <div class="p-6">
            <h3 class="text-lg font-bold text-gray-900 mb-4">Reset Password</h3>
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Password Baru</label>
                    <input type="password" id="newPassword" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Minimal 8 karakter">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Konfirmasi Password</label>
                    <input type="password" id="confirmPassword" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500" placeholder="Ketik ulang password">
                </div>
            </div>
            <div class="flex gap-3 mt-6">
                <button onclick="closeResetModal()" class="flex-1 px-4 py-2 bg-gray-200 text-gray-800 rounded-lg hover:bg-gray-300">
                    Batal
                </button>
                <button onclick="confirmResetPassword()" class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    Reset Password
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
let resetUserId = null;

async function toggleStatus(userId, currentStatus) {
    const action = currentStatus ? 'menonaktifkan' : 'mengaktifkan';
    const confirmed = await confirm(`Yakin ingin ${action} user ini?`);
    if (!confirmed) return;

    fetch(`/admin/users/${userId}/toggle-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess(data.message || 'Status berhasil diubah');
            setTimeout(() => location.reload(), 1500);
        } else {
            showError(data.message || 'Gagal mengubah status');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan');
    });
}

function resetPassword(userId) {
    resetUserId = userId;
    document.getElementById('resetPasswordModal').classList.remove('hidden');
}

function closeResetModal() {
    document.getElementById('resetPasswordModal').classList.add('hidden');
    document.getElementById('newPassword').value = '';
    document.getElementById('confirmPassword').value = '';
}

function confirmResetPassword() {
    const newPassword = document.getElementById('newPassword').value;
    const confirmPassword = document.getElementById('confirmPassword').value;

    if (newPassword.length < 8) {
        showError('Password minimal 8 karakter');
        return;
    }

    if (newPassword !== confirmPassword) {
        showError('Password tidak cocok');
        return;
    }

    fetch(`/admin/users/${resetUserId}/reset-password`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({
            new_password: newPassword,
            new_password_confirmation: confirmPassword
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showSuccess('Password berhasil direset');
            closeResetModal();
        } else {
            showError(data.message || 'Gagal reset password');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showError('Terjadi kesalahan');
    });
}
</script>
@endpush
@endsection