@extends('layouts.app')

@section('title', 'Daftar Surat Divisi')

@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
            <div class="p-6 bg-white border-b border-gray-200">
                <h1 class="text-2xl font-semibold mb-6">Daftar Surat Divisi</h1>

                @livewire('divisi-surat-table')

            </div>
        </div>
    </div>
</div>
@endsection