@extends('layouts.app')

@section('title', 'Pengaturan Profil')

@section('breadcrumb')
    <li aria-current="page">
        <div class="flex items-center">
            <i class="fas fa-angle-right text-gray-400"></i>
            <span class="ml-1 text-sm font-medium text-gray-500 md:ml-2">Pengaturan Profil</span>
        </div>
    </li>
@endsection

@section('content')
    <div class="space-y-6">
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
@endsection
