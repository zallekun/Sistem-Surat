@extends('layouts.app')

@section('title', 'Kelola User')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold mb-6">Daftar User</h1>

                <div class="mb-4">
                    <a href="{{ route('admin.users.create') }}" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                        Tambah User
                    </a>
                </div>

                @livewire('users-table')

            </div>
        </div>
    </div>
</div>
@endsection