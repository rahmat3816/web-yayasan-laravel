@extends('layouts.app')
@section('label', 'Sedang Dikembangkan')

@section('content')
<div class="max-w-2xl mx-auto text-center bg-white dark:bg-gray-900 border border-dashed border-amber-400 dark:border-amber-300 rounded-3xl p-10 shadow">
    <div class="text-5xl mb-4">🚧</div>
    <p class="text-2xl font-semibold text-gray-800 dark:text-gray-100">Masih dalam Pengembangan</p>
    <p class="mt-3 text-gray-600 dark:text-gray-300">
        Tim sedang menyiapkan fitur ini. Silakan kembali lagi nanti atau hubungi superadmin jika butuh akses lebih cepat.
    </p>
    <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-2 mt-6 px-5 py-2.5 bg-amber-500 text-white rounded-full shadow hover:bg-amber-600 transition">
        ⬅️ Kembali ke Dashboard
    </a>
</div>
@endsection
