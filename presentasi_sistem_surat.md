---

### **Slide 1: Judul**

# **Sistem Manajemen Surat**
## Digitalisasi Administrasi Surat di Fakultas Sains dan Informatika

---

### **Slide 2: Latar Belakang dan Tujuan**

*   **Tujuan Proyek:**
    *   Mendigitalisasi proses pengajuan dan pembuatan surat mahasiswa untuk meningkatkan efisiensi.
*   **Ruang Lingkup:**
    *   Mencakup seluruh alur: dari pengajuan oleh mahasiswa, proses persetujuan berjenjang, hingga pembuatan dan distribusi surat.
*   **Manfaat Utama:**
    *   **Efisien:** Mempercepat waktu proses surat.
    *   **Transparan:** Mahasiswa dapat melacak status pengajuan secara *real-time*.
    *   **Paperless:** Mengurangi penggunaan kertas dan memudahkan pengarsipan.
    *   **Terdokumentasi:** Semua alur dan histori tercatat dengan baik (audit trail).

---

### **Slide 3: Pengguna Sistem dan Perannya**

Berikut adalah peran pengguna yang memiliki akses login ke dalam sistem:

*   **Staff Prodi:**
    *   Melakukan verifikasi awal, menyetujui/menolak, dan membuat surat pengantar.
*   **Staff Fakultas:**
    *   Memproses pengajuan dari prodi dan membuat surat final.
*   **Admin:**
    *   Mengelola keseluruhan sistem, pengguna, master data, dan melakukan intervensi jika ada kendala.

*\*Selain itu, **Mahasiswa** berinteraksi dengan sistem secara publik untuk mengajukan dan melacak status surat tanpa perlu login.*

---

### **Slide 4: Fitur-Fitur Utama Sistem**

*   **Pengajuan Publik & Pelacakan:**
    *   Mahasiswa dapat mengajukan surat tanpa perlu login.
    *   Pelacakan status menggunakan *tracking token* yang unik.
*   **Alur Persetujuan (Workflow) Berjenjang:**
    *   Proses persetujuan multi-level: **Prodi → Fakultas**.
    *   Setiap level dapat memberikan persetujuan atau penolakan (dengan alasan).
*   **Manajemen untuk Admin:**
    *   Dashboard dengan statistik dan grafik.
    *   Manajemen User, Prodi, Fakultas, dan Jenis Surat.
    *   Fitur Intervensi: *Force Complete* atau *Reopen* pengajuan yang bermasalah.
*   **Audit Trail & Laporan:**
    *   Semua aksi penting tercatat dalam *Audit Trail*.
    *   Ekspor data pengajuan dan laporan ke format Excel.

---

### **Slide 5: Alur Kerja (Use Case): Pengajuan Surat oleh Mahasiswa**

1.  **Akses Form:** Mahasiswa membuka halaman publik pengajuan surat.
2.  **Isi Data:** Memilih jenis surat dan mengisi form yang dinamis sesuai jenisnya (NIM, nama, keperluan, dll).
3.  **Submit:** Sistem melakukan validasi data.
4.  **Dapatkan Token:** Jika valid, pengajuan disimpan dan sistem memberikan **Tracking Token** kepada mahasiswa.
5.  **Notifikasi:** Sistem secara otomatis memberitahu Staff Prodi bahwa ada pengajuan baru.

---

### **Slide 6: Alur Kerja (Use Case): Proses Persetujuan & Pelacakan**

1.  **Pelacakan (Mahasiswa):**
    *   Mahasiswa memasukkan *Tracking Token* di halaman pelacakan.
    *   Sistem menampilkan detail dan linimasa status surat.
    *   Jika selesai, link unduh surat akan tersedia.
2.  **Persetujuan (Staff):**
    *   Staff (Prodi/Fakultas) login dan melihat daftar pengajuan.
    *   Melakukan review data, lalu klik **Approve** atau **Reject**.
    *   Sistem mencatat histori dan meneruskan pengajuan ke level selanjutnya atau mengembalikannya ke mahasiswa.

---

### **Slide 7: Arsitektur & Teknologi**

*   **Pola Arsitektur:** MVC (Model-View-Controller)
*   **Framework Utama:** Laravel 12
*   **Bahasa Pemrograman:** PHP 8.3+
*   **Frontend:**
    *   Blade Templates
    *   Tailwind CSS (Styling)
    *   Alpine.js & Livewire (Interaktivitas)
*   **Database:** MySQL / MariaDB
*   **Otentikasi & Otorisasi:** Laravel Breeze & Spatie Laravel Permission

---

### **Slide 8: Desain Database (Tabel Kunci)**

*   `pengajuan_surats`: Menyimpan semua data pengajuan yang diisi oleh mahasiswa.
*   `surat_generated`: Informasi mengenai surat (PDF) yang sudah final, termasuk link unduhnya.
*   `approval_histories`: Log untuk setiap tahapan persetujuan (siapa, kapan, status).
*   `audit_trails`: Log khusus untuk aksi-aksi penting yang dilakukan oleh Admin (misal: *force complete*).
*   `users`, `roles`, `prodi`, `fakultas`: Tabel master untuk data pendukung sistem.

---

### **Slide 9: Kebutuhan Non-Fungsional**

*   **Keamanan (Security):**
    *   Perlindungan terhadap CSRF, XSS, dan SQL Injection.
    *   Hak akses berbasis peran (Role-Based Access Control).
    *   Password disimpan menggunakan *hashing*.
*   **Kinerja (Performance):**
    *   Waktu muat halaman < 3 detik.
    *   Waktu respon untuk operasi umum < 2 detik.
*   **Keandalan (Reliability):**
    *   Strategi backup data dan logging error yang jelas.
    *   Fitur *soft delete* untuk pemulihan data.
*   **Kemudahan Penggunaan (Usability):**
    *   Desain antarmuka yang responsif (desktop & mobile).
    - Navigasi yang intuitif dan konsisten.

---

### **Slide 10: Terima Kasih**

## **Sesi Tanya Jawab**