@extends('layouts.app')

@section('content')
<div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-xl shadow p-6">
        <h1 class="text-xl font-semibold mb-4">Masuk</h1>

        @if ($errors->any())
            <div class="mb-4 p-3 bg-red-100 text-red-700 rounded">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block mb-1 text-sm">Username atau Email</label>
                <input type="text" name="login" value="{{ old('login') }}" required
                       class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:border-gray-700" autofocus>
            </div>

            <div>
                <label class="block mb-1 text-sm">Password</label>
                <input type="password" name="password" required
                       class="w-full border rounded px-3 py-2 dark:bg-gray-900 dark:border-gray-700">
            </div>

            <div class="flex items-center justify-between">
                <label class="inline-flex items-center gap-2 text-sm">
                    <input type="checkbox" name="remember" class="rounded">
                    <span>Ingat saya</span>
                </label>
                <button class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Masuk</button>
            </div>
        </form>
    </div>
</div>
@endsection
