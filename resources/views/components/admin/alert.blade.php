{{-- =======================================================
📦 Komponen Notifikasi Global <x-admin.alert />
Fungsi:
- Menampilkan pesan sukses, error, warning dari session
- Menampilkan error validasi Laravel ($errors)
- Warna dan ikon otomatis
======================================================= --}}
@props(['dismissible' => true])

@php
    $alerts = [];

    // Ambil pesan dari session (jika ada)
    if (session('success')) $alerts[] = ['type' => 'success', 'message' => session('success')];
    if (session('error'))   $alerts[] = ['type' => 'error',   'message' => session('error')];
    if (session('warning')) $alerts[] = ['type' => 'warning', 'message' => session('warning')];

    // Jika ada error validasi
    if ($errors->any()) {
        $msg = '<ul class="list-disc list-inside space-y-1 mt-1">';
        foreach ($errors->all() as $err) {
            $msg .= "<li>{$err}</li>";
        }
        $msg .= '</ul>';
        $alerts[] = ['type' => 'error', 'message' => $msg];
    }

    // Mapping warna & ikon
    $styles = [
        'success' => ['bg' => 'bg-green-100 dark:bg-green-900/30', 'text' => 'text-green-800 dark:text-green-300', 'icon' => '✅'],
        'error'   => ['bg' => 'bg-red-100 dark:bg-red-900/30',     'text' => 'text-red-800 dark:text-red-300',     'icon' => '❌'],
        'warning' => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/30','text' => 'text-yellow-800 dark:text-yellow-300','icon' => '⚠️'],
    ];
@endphp

@if(!empty($alerts))
    <div class="space-y-3 mb-6">
        @foreach($alerts as $alert)
            @php
                $s = $styles[$alert['type']] ?? $styles['success'];
            @endphp
            <div class="p-4 rounded-xl shadow-sm border {{ $s['bg'] }} {{ $s['text'] }} relative">
                <div class="flex items-start gap-3">
                    <span class="text-xl">{{ $s['icon'] }}</span>
                    <div class="flex-1 prose-sm max-w-none">
                        {!! $alert['message'] !!}
                    </div>
                    @if($dismissible)
                        <button type="button"
                            onclick="this.closest('div[role=alert]').remove()"
                            class="ml-3 text-sm font-bold opacity-60 hover:opacity-100 transition">
                            ✖
                        </button>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
@endif
