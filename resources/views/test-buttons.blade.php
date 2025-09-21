@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Test Button Visibility</h1>
    <div class="flex gap-3">
        <button class="px-4 py-2 bg-gray-600 text-white rounded">
            Test Button 1 (No special classes)
        </button>
        <button class="px-4 py-2 bg-blue-600 text-white rounded opacity-100">
            Test Button 2 (opacity-100)
        </button>
        <button style="opacity: 1 !important;" class="px-4 py-2 bg-green-600 text-white rounded">
            Test Button 3 (inline style)
        </button>
    </div>
</div>
@endsection