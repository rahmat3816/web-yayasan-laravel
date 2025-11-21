<?php

namespace Database\Seeders;

use App\Models\PelanggaranCategory;
use App\Models\PelanggaranType;
use Illuminate\Database\Seeder;

class KeamananViolationSeeder extends Seeder
{
    public function run(): void
    {
        $kategori = PelanggaranCategory::pluck('id', 'nama');

        $items = [
            // IBADAH
            ['kategori' => 'Ibadah', 'nama' => 'Tidak memakai alas kaki ke/dari masjid', 'poin' => 5],
            ['kategori' => 'Ibadah', 'nama' => 'Tidur di masjid setelah adzan', 'poin' => 5],
            ['kategori' => 'Ibadah', 'nama' => 'Terlambat ke masjid', 'poin' => 5],
            ['kategori' => 'Ibadah', 'nama' => 'Tidak berdzikir pagi/petang/ba’da shalat', 'poin' => 5],
            ['kategori' => 'Ibadah', 'nama' => 'Berbuat gaduh di masjid', 'poin' => 5],
            ['kategori' => 'Ibadah', 'nama' => 'Terlambat sholat berjamaah', 'poin' => 10],
            ['kategori' => 'Ibadah', 'nama' => 'Mengganggu orang shalat', 'poin' => 10],
            ['kategori' => 'Ibadah', 'nama' => 'Menunda-nunda mandi suci', 'poin' => 25],
            ['kategori' => 'Ibadah', 'nama' => 'Tidak shalat berjamaah di masjid', 'poin' => 30],
            ['kategori' => 'Ibadah', 'nama' => 'Menyebarkan paham menyimpang', 'poin' => 100],
            ['kategori' => 'Ibadah', 'nama' => 'Tidak puasa Ramadhan tanpa udzur', 'poin' => 50],
            ['kategori' => 'Ibadah', 'nama' => 'Tidak shalat fardhu', 'poin' => 75],

            // PENGAJARAN
            ['kategori' => 'Pengajaran', 'nama' => 'Tidur / tidak memperhatikan pelajaran', 'poin' => 5],
            ['kategori' => 'Pengajaran', 'nama' => 'Tidak mengerjakan piket', 'poin' => 10],
            ['kategori' => 'Pengajaran', 'nama' => 'Di asrama saat KBM tanpa udzur', 'poin' => 10],
            ['kategori' => 'Pengajaran', 'nama' => 'Tidak memakai sepatu ke kelas', 'poin' => 15],
            ['kategori' => 'Pengajaran', 'nama' => 'Masuk/keluar ruangan tidak lewat pintu', 'poin' => 15],
            ['kategori' => 'Pengajaran', 'nama' => 'Masuk ruangan pengajar tanpa izin', 'poin' => 15],
            ['kategori' => 'Pengajaran', 'nama' => 'Tidak mengikuti kegiatan pondok', 'poin' => 15],
            ['kategori' => 'Pengajaran', 'nama' => 'Tidak berpakaian seragam/baju syar’i', 'poin' => 15],
            ['kategori' => 'Pengajaran', 'nama' => 'Merubah absensi', 'poin' => 15],
            ['kategori' => 'Pengajaran', 'nama' => 'Merusak/mencoret inventaris pondok/madrasah', 'poin' => 20],

            // ASRAMA
            ['kategori' => 'Asrama', 'nama' => 'Terlambat bangun', 'poin' => 5],
            ['kategori' => 'Asrama', 'nama' => 'Terlambat kegiatan pondok', 'poin' => 5],
            ['kategori' => 'Asrama', 'nama' => 'Tidak tidur di ranjang masing-masing', 'poin' => 5],
            ['kategori' => 'Asrama', 'nama' => 'Makan di asrama tanpa udzur', 'poin' => 5],
            ['kategori' => 'Asrama', 'nama' => 'Tidak merapikan barang pribadi', 'poin' => 5],
            ['kategori' => 'Asrama', 'nama' => 'Tidur tanpa celana panjang', 'poin' => 5],
            ['kategori' => 'Asrama', 'nama' => 'Di luar asrama saat istirahat', 'poin' => 10],
            ['kategori' => 'Asrama', 'nama' => 'Masuk ruangan orang lain tanpa izin', 'poin' => 10],
            ['kategori' => 'Asrama', 'nama' => 'Membuka lemari orang lain tanpa izin', 'poin' => 15],
            ['kategori' => 'Asrama', 'nama' => 'Pindah asrama tanpa izin', 'poin' => 20],
            ['kategori' => 'Asrama', 'nama' => 'Tidur bersama satu ranjang', 'poin' => 20],
            ['kategori' => 'Asrama', 'nama' => 'Gunakan barang orang lain tanpa izin', 'poin' => 25],
            ['kategori' => 'Asrama', 'nama' => 'Membuat rangkaian listrik tanpa izin', 'poin' => 30],

            // BAHASA
            ['kategori' => 'Bahasa', 'nama' => 'Tidak berbahasa Arab', 'poin' => 5],
            ['kategori' => 'Bahasa', 'nama' => 'Membuat tren bahasa baru', 'poin' => 5],
            ['kategori' => 'Bahasa', 'nama' => 'Menggunakan bahasa daerah', 'poin' => 5],

            // KONSUMSI
            ['kategori' => 'Konsumsi', 'nama' => 'Menyisakan / membuang makanan', 'poin' => 5],
            ['kategori' => 'Konsumsi', 'nama' => 'Meminta-minta makanan', 'poin' => 5],
            ['kategori' => 'Konsumsi', 'nama' => 'Berbicara saat makan', 'poin' => 5],
            ['kategori' => 'Konsumsi', 'nama' => 'Makan bersandar', 'poin' => 5],
            ['kategori' => 'Konsumsi', 'nama' => 'Telat/tidak mencuci alat makan', 'poin' => 10],
            ['kategori' => 'Konsumsi', 'nama' => 'Telat/tidak antar tempat makan', 'poin' => 10],
            ['kategori' => 'Konsumsi', 'nama' => 'Tidak makan', 'poin' => 10],
            ['kategori' => 'Konsumsi', 'nama' => 'Makan/minum berdiri/tangan kiri/berjalan', 'poin' => 10],
            ['kategori' => 'Konsumsi', 'nama' => 'Mencela makanan', 'poin' => 10],
            ['kategori' => 'Konsumsi', 'nama' => 'Makanan/minuman terlarang', 'poin' => 10],
            ['kategori' => 'Konsumsi', 'nama' => 'Minum obat tanpa diketahui kesehatan', 'poin' => 20],

            // PERIZINAN
            ['kategori' => 'Perizinan', 'nama' => 'Tidak kumpulkan tugas liburan', 'poin' => 20],
            ['kategori' => 'Perizinan', 'nama' => 'Salahgunakan perizinan', 'poin' => 20],
            ['kategori' => 'Perizinan', 'nama' => 'Terlambat kembali liburan/perizinan', 'poin' => 25],
            ['kategori' => 'Perizinan', 'nama' => 'Memalsukan tanda tangan/stempel/tanggal', 'poin' => 50],
            ['kategori' => 'Perizinan', 'nama' => 'Salahgunakan uang SPP', 'poin' => 50],
            ['kategori' => 'Perizinan', 'nama' => 'Meninggalkan area pondok tanpa izin', 'poin' => 75],
            ['kategori' => 'Perizinan', 'nama' => 'Meninggalkan pondok tanpa mahram (putri)', 'poin' => 75],

            // PAKAIAN & PENAMPILAN
            ['kategori' => 'Pakaian & Penampilan', 'nama' => 'Tidak pakai peci/cadar/kaus kaki (putri) di luar ruangan', 'poin' => 10],
            ['kategori' => 'Pakaian & Penampilan', 'nama' => 'Memanjangkan/mewarnai kuku', 'poin' => 10],
            ['kategori' => 'Pakaian & Penampilan', 'nama' => 'Tidak mengganti baju', 'poin' => 10],
            ['kategori' => 'Pakaian & Penampilan', 'nama' => 'Potong/rapikan jenggot (putra)', 'poin' => 20],
            ['kategori' => 'Pakaian & Penampilan', 'nama' => 'Pakaian tidak syar’i/isbal/bergambar makhluk nyawa/ketat', 'poin' => 50],
            ['kategori' => 'Pakaian & Penampilan', 'nama' => 'Model rambut qozza’/tasyabbuh/tidak sesuai aturan', 'poin' => 50],
            ['kategori' => 'Pakaian & Penampilan', 'nama' => 'Tato/tindik telinga', 'poin' => 75],

            // KEAMANAN & KETERTIBAN
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Membuang sampah sembarangan', 'poin' => 5],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Bekerjasama dalam pelanggaran', 'poin' => 50],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Berhutang tanpa izin ortu/wali', 'poin' => 10],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Kegiatan/bisnis tanpa izin kesantrian', 'poin' => 10],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Ucapan keji/dusta/fitnah/dll', 'poin' => 75],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Membuat gaduh', 'poin' => 10],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Memberi julukan jelek', 'poin' => 15],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Merusak/menghilangkan barang santri/ustadz/karyawan', 'poin' => 20],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Tidak hormat yang lebih tua', 'poin' => 50],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Menggangu lewat lisan/tulisan', 'poin' => 20],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Persahabatan tidak wajar', 'poin' => 20],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Mengendarai sepeda/motor/mobil di pondok', 'poin' => 20],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Bermain berbahaya', 'poin' => 30],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Membuat geng sesama santri', 'poin' => 50],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Barang tajam berbahaya tanpa izin', 'poin' => 50],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Memiliki/ menggunakan media sosial', 'poin' => 50],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Mencuri di luar/dalam pondok', 'poin' => 75],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Menentang tata tertib secara terang-terangan', 'poin' => 75],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Komunikasi lawan jenis tanpa udzur syar’i', 'poin' => 75],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Cemarkan nama baik santri', 'poin' => 75],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Cemarkan nama baik ustadz/pegawai/pondok', 'poin' => 100],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Perbuatan asusila', 'poin' => 200, 'langsung_sp3' => true],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Pacaran', 'poin' => 100],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Rokok/miras/narkoba/dll', 'poin' => 200, 'langsung_sp3' => true],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Mengancam dengan barang tajam', 'poin' => 200, 'langsung_sp3' => true],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Berzina', 'poin' => 300, 'langsung_sp3' => true],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Homoseksual/lesbian', 'poin' => 300, 'langsung_sp3' => true],
            ['kategori' => 'Keamanan & Ketertiban', 'nama' => 'Demo/kriminal/pidana/terorisme', 'poin' => 300, 'langsung_sp3' => true],

            // HIBURAN
            ['kategori' => 'Hiburan', 'nama' => 'Gunakan HP ustadz/karyawan/tamu tanpa izin', 'poin' => 20],
            ['kategori' => 'Hiburan', 'nama' => 'Simpan gambar yang menyelisihi syariat', 'poin' => 20],
            ['kategori' => 'Hiburan', 'nama' => 'Komik/cerita fiktif/novel dll', 'poin' => 50],
            ['kategori' => 'Hiburan', 'nama' => 'Time zone/playstation/game online', 'poin' => 50],
            ['kategori' => 'Hiburan', 'nama' => 'Catur/kartu/monopoli/gramboll', 'poin' => 50],
            ['kategori' => 'Hiburan', 'nama' => 'Ulang tahun/valentine/natal/tahun baru', 'poin' => 50],
            ['kategori' => 'Hiburan', 'nama' => 'Game/TV/video/bioskop/musik (umum)', 'poin' => 100],
            ['kategori' => 'Hiburan', 'nama' => 'Internet/media sosial (berat/tanpa izin)', 'poin' => 75],
            ['kategori' => 'Hiburan', 'nama' => 'Simpan/miliki HP/alat elektronik tanpa izin', 'poin' => 75],
            ['kategori' => 'Hiburan', 'nama' => 'Dengarkan musik/menyanyi/menari', 'poin' => 75],
            ['kategori' => 'Hiburan', 'nama' => 'Berjudi', 'poin' => 200, 'langsung_sp3' => false],
            ['kategori' => 'Hiburan', 'nama' => 'Transaksi riba', 'poin' => 200, 'langsung_sp3' => false],
            ['kategori' => 'Hiburan', 'nama' => 'Konten/aksi pornografi', 'poin' => 300, 'langsung_sp3' => true],
        ];

        foreach ($items as $item) {
            $kategoriId = $kategori[$item['kategori']] ?? null;
            if (! $kategoriId) {
                continue;
            }

            PelanggaranType::updateOrCreate(
                ['nama' => $item['nama']],
                [
                    'kategori_id' => $kategoriId,
                    'deskripsi' => $item['deskripsi'] ?? null,
                    'poin_default' => $item['poin'],
                    'langsung_sp3' => $item['langsung_sp3'] ?? false,
                    'aktif' => true,
                ]
            );
        }
    }
}
