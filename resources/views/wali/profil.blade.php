@extends('layouts.wali')

@section('content')
    <x-breadcrumb />

    <div class="grid gap-6 md:grid-cols-2">
        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow">
            <h2 class="text-xl font-semibold mb-4">Data Akun Wali</h2>
            <dl class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                <div>
                    <dt class="font-semibold text-gray-800 dark:text-gray-100">Nama Lengkap</dt>
                    <dd>{{ $user->name }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-800 dark:text-gray-100">Email</dt>
                    <dd>{{ $user->email }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-800 dark:text-gray-100">Username</dt>
                    <dd>{{ $user->username }}</dd>
                </div>
                <div>
                    <dt class="font-semibold text-gray-800 dark:text-gray-100">Role</dt>
                    <dd class="capitalize">{{ str_replace('_', ' ', $user->role ?? '-') }}</dd>
                </div>
            </dl>
            <p class="mt-4 text-xs text-gray-500">
                Data akun mengikuti informasi yang tersimpan di panel admin Filament.
            </p>
        </div>

        <div class="p-6 bg-white dark:bg-gray-900 rounded-2xl shadow">
            <h2 class="text-xl font-semibold mb-4">Daftar Anak Terhubung</h2>
            @if ($anak->isEmpty())
                <p class="text-gray-600 dark:text-gray-300">Belum ada data santri.</p>
            @else
                <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-300">
                    @foreach ($anak as $santri)
                        <li class="border border-gray-100 dark:border-gray-800 rounded-xl p-3">
                            <p class="text-base font-semibold text-gray-900 dark:text-gray-50">{{ $santri->nama }}</p>
                            <p class="text-xs uppercase tracking-wide text-gray-500">
                                {{ $santri->unit->nama_unit ?? 'Unit belum diatur' }}
                            </p>
                            <p class="mt-2">
                                Total hafalan terdata:
                                <span class="font-semibold text-blue-600">{{ $santri->hafalan_count ?? 0 }}</span>
                            </p>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </div>
@endsection
