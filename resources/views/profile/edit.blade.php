@extends('layouts.app')

@section('title', 'Pengaturan Profil')

@section('breadcrumb')
    <li>
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
            <a href="#" class="text-sm font-medium text-gray-700 hover:text-blue-600">Profil</a>
        </div>
    </li>
    <li aria-current="page">
        <div class="flex items-center">
            <i class="fas fa-chevron-right text-gray-400 mx-1"></i>
            <span class="text-sm font-medium text-gray-500">Pengaturan</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>
        </div>
    </div>
@endsection
