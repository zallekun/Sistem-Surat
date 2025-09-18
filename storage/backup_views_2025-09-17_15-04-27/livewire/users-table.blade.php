<div>
    <div class="flex flex-col md:flex-row justify-between mb-4 space-y-4 md:space-y-0 md:space-x-4">
        <input wire:model.live.debounce.300ms="search" type="text" placeholder="Cari user..." class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline flex-grow">

        <select wire:model.live="filterRole" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Semua Role</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}">{{ $role->nama_role }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterFakultas" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Semua Fakultas</option>
            @foreach($fakultas as $fakultasItem)
                <option value="{{ $fakultasItem->id }}">{{ $fakultasItem->nama_fakultas }}</option>
            @endforeach
        </select>

        <select wire:model.live="filterProdi" class="shadow appearance-none border rounded py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
            <option value="">Semua Prodi</option>
            @foreach($prodis as $prodi)
                <option value="{{ $prodi->id }}">{{ $prodi->nama_prodi }}</option>
            @endforeach
        </select>

        <button wire:click="exportUsers" class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
            Export Excel
        </button>
    </div>

    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200 table-fixed">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer w-[120px]" wire:click="sortBy('nama')">
                        Nama
                        @if($sortField === 'nama')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer w-[200px]" wire:click="sortBy('email')">
                        Email
                        @if($sortField === 'email')
                            <span class="ml-1">{{ $sortDirection === 'asc' ? '▲' : '▼' }}</span>
                        @endif
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[100px]">
                        Role
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[150px]">
                        Jabatan
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider w-[100px]">
                        Prodi
                    </th>
                    <th scope="col" class="relative px-6 py-3 text-right w-[150px]">
                        Aksi
                    </th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach ($users as $user)
                    <tr class="odd:bg-white even:bg-gray-50 hover:bg-gray-100">
                        <td class="px-6 py-4 text-sm font-medium text-gray-900">
                            {{ $user->nama }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->email }}
                        </td>
                        <td class="px-6 py-4">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                {{ $user->role?->nama_role ?? 'N/A' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->jabatan?->nama_jabatan ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-500">
                            {{ $user->prodi?->nama_prodi ?? 'N/A' }}
                        </td>
                        <td class="px-6 py-4 text-right text-sm font-medium whitespace-nowrap">
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="text-indigo-600 hover:text-indigo-900 px-2 py-1 rounded-md" title="Edit User"><i class="fa-solid fa-pen-to-square"></i></a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900 ml-2 px-2 py-1 rounded-md" title="Hapus User"><i class="fa-solid fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</div>