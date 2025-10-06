# TODO: Perbaikan Tampilan Data Tambahan Mahasiswa KP

## Tugas Utama
- Perbaiki tampilan data tambahan di halaman admin/pengajuan/show.blade.php agar tidak menampilkan JSON mentah untuk mahasiswa KP.

## Langkah-langkah
1. [ ] Edit bagian tampilan data tambahan di resources/views/admin/pengajuan/show.blade.php
   - Ubah logika untuk menampilkan array of objects sebagai blok readable, bukan JSON.
   - Untuk setiap mahasiswa, tampilkan nama, nim, dll dalam format list.

2. [ ] Test tampilan di browser untuk memastikan tidak ada error dan data tampil benar.

## File yang Terlibat
- resources/views/admin/pengajuan/show.blade.php

## Status
- [x] Analisis selesai
- [ ] Edit file
- [ ] Test
