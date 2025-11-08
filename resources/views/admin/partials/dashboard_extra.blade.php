{{-- resources/views/admin/partials/dashboard_extra.blade.php --}}
{{-- Partial tambahan untuk Dashboard Admin.
   Catatan:
   - Jangan gunakan @extends / @section di file partial
   - Jangan meng-include file ini dari dalam file ini lagi (no self-include)
--}}

<div class="mt-8 space-y-8">
    {{-- Tempatkan konten tambahan dashboard di sini
       (mis. quick links, tabel user terbaru, ringkasan unit, dll.)
       Gunakan pengecekan defensif agar tidak error saat variabel belum ada.
    --}}

    @if(isset($users) && $users instanceof \Illuminate\Contracts\Pagination\Paginator)
        <x-admin.card title="👤 Pengguna Terbaru">
            <x-admin.table :headers="['Nama', 'Email', 'Role', 'Dibuat']">
                @foreach($users as $u)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-6 py-3 font-medium">{{ $u->name }}</td>
                        <td class="px-6 py-3">{{ $u->email }}</td>
                        <td class="px-6 py-3 capitalize">{{ implode(', ', $u->getRoleNames()->toArray() ?? []) }}</td>
                        <td class="px-6 py-3">{{ optional($u->created_at)->format('d M Y') }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
            {{ $users->links() }}
        </x-admin.card>
    @endif

    @if(isset($units) && count($units))
        <x-admin.card title="🏫 Daftar Unit">
            <x-admin.table :headers="['Unit', 'Kode', 'Keterangan']">
                @foreach($units as $unit)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        <td class="px-6 py-3">{{ $unit->nama_unit }}</td>
                        <td class="px-6 py-3">{{ $unit->kode_unit }}</td>
                        <td class="px-6 py-3">{{ $unit->keterangan }}</td>
                    </tr>
                @endforeach
            </x-admin.table>
        </x-admin.card>
    @endif

    {{-- Tambahkan section lain sesuai kebutuhan Anda --}}
</div>
