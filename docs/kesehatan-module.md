# Modul Kesehatan Kesantrian

Dokumen ringkas berikut menjelaskan alur kerja modul kesehatan yang melibatkan koordinator kesehatan, kabag kesantrian, serta musyrif/musyrifah.

## Peran & Akses

- **Superadmin / Kabag Kesantrian**: memiliki akses penuh tanpa batasan gender.
- **Koordinator Kesehatan Putra/Putri**: akses penuh tetapi dibatasi santri sesuai gendernya.
- **Musyrif/Musyrifah**: hanya bisa membuat dan melihat log kesehatan yang mereka laporkan sendiri, serta terbatas pada santri di asrama tanggung jawabnya.

Implementasi pengecekan akses dilakukan melalui `SantriHealthLogPolicy` dan query `SantriHealthLogResource`.

## Alur Operasional

1. **Penunjukan Musyrif/Musyrifah**
   - Input melalui resource `MusyrifAssignment`.
   - Setiap assignment menghubungkan guru dengan asrama berikut rentang waktu tugas.
2. **Pencatatan Santri Sakit (Musyrif/Musyrifah)**
   - Menu `Log Kesehatan Santri` pada panel Kesantrian.
   - Form otomatis memfilter santri berdasarkan asrama & gender serta mencatat keluhan, penanganan sementara, dan kebutuhan rujukan.
3. **Intervensi Koordinator**
   - Koordinator membuka log, meninjau detail, lalu menambahkan tindakan lanjutan via relation manager `actions`.
   - Status log dapat diperbarui menjadi ditangani, dirujuk, atau selesai.
4. **Dashboard & Laporan**
   - Widget `KesehatanSummaryWidget` dan `KesehatanTrendChart` menampilkan statistik kasus aktif, status tindak lanjut, serta tren mingguan.
   - Modul web kesantrian putra/putri menarik data real-time yang sama untuk ringkasan cepat.

## Validasi Penting

- Filter gender diterapkan di policy dan resource, memastikan koordinator putra/putri tidak bisa melihat santri lawan gender.
- Akses musyrif diverifikasi melalui assignment aktif dan kecocokan `reporter_id`.
- Penetapan default `reporter_id`/`musyrif_assignment_id` terjadi otomatis saat form dibuat.

## Seeder

`AsramaSeeder` dan `MusyrifAssignmentSeeder` menyiapkan data awal asrama serta penugasan musyrif standar. Seeder assignment akan membuat guru/asrama placeholder bila belum tersedia sehingga aman dijalankan berulang.

## Rekomendasi Pengujian

1. **Policy Test**
   - Pastikan koordinator putra hanya bisa `view` log santri laki-laki.
   - Verifikasi musyrif hanya melihat log dengan `reporter_id` miliknya.
2. **Resource Test**
   - Simulasikan pengguna dengan peran berbeda dan cek hasil query `SantriHealthLogResource::getEloquentQuery`.
3. **Seeder Test (opsional)**
   - Jalankan seeder pada database kosong dan pastikan data guru/asrama terbentuk tanpa error.

Dokumen ini bisa diperluas saat fitur lanjutan (notifikasi real-time, lampiran medis) mulai dikerjakan.
