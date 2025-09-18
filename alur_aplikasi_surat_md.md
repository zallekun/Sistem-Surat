# ALUR APLIKASI SISTEM PERSURATAN FAKULTAS

## DAFTAR ISI
1. [Struktur Organisasi](#struktur-organisasi)
2. [Daftar Role dan Jabatan](#daftar-role-dan-jabatan)
3. [Alur Surat](#alur-surat)
4. [Status Surat](#status-surat)
5. [Hak Akses](#hak-akses)
6. [Notifikasi](#notifikasi)
7. [Implementasi Teknis](#implementasi-teknis)

---

## STRUKTUR ORGANISASI

```
ADMIN SISTEM
└── Super Admin (Kelola user, master data, monitoring)

LEVEL PRODI
├── Staff Prodi (Pembuat surat atas nama Kaprodi)
└── Kaprodi (Monitoring surat prodi)

LEVEL FAKULTAS
├── Staff Fakultas/Bagian Umum (Filter & verifikasi + buat surat)
├── Dekan (Disposisi utama)
├── Wakil Dekan 1 (Bidang Akademik)
├── Wakil Dekan 2 (Bidang Keuangan & SDM)
├── Wakil Dekan 3 (Bidang Kemahasiswaan)
└── Kabag TU (Koordinator ke divisi)

LEVEL DIVISI (EKSEKUTOR)
├── Divisi Akademik
├── Divisi Keuangan
├── Divisi Kerjasama
├── Divisi Kemahasiswaan
└── Divisi Umum & Perlengkapan
```

---

## DAFTAR ROLE DAN JABATAN

### Tabel Role (2 role utama)
| ID | Nama Role | Deskripsi |
|----|-----------|-----------|
| 1 | admin | Administrator sistem |
| 2 | user | Pengguna dengan jabatan tertentu |

### Tabel Jabatan
| ID | Nama Jabatan | Deskripsi | Level |
|----|--------------|-----------|-------|
| 1 | staff_prodi | Staff Program Studi - Pembuat surat | 1 |
| 2 | kaprodi | Kepala Program Studi - Monitoring | 2 |
| 3 | staff_fakultas | Staff Fakultas/Bagian Umum - Filter & Verifikasi | 3 |
| 4 | dekan | Dekan Fakultas - Disposisi utama | 4 |
| 5 | wd1 | Wakil Dekan Bidang Akademik | 4 |
| 6 | wd2 | Wakil Dekan Bidang Keuangan & SDM | 4 |
| 7 | wd3 | Wakil Dekan Bidang Kemahasiswaan | 4 |
| 8 | kabag_tu | Kepala Bagian Tata Usaha - Koordinator | 5 |
| 9 | divisi_akademik | Staff Divisi Akademik | 6 |
| 10 | divisi_keuangan | Staff Divisi Keuangan | 6 |
| 11 | divisi_kerjasama | Staff Divisi Kerjasama | 6 |
| 12 | divisi_kemahasiswaan | Staff Divisi Kemahasiswaan | 6 |
| 13 | divisi_umum_perlengkapan | Staff Divisi Umum & Perlengkapan | 6 |

---

## ALUR SURAT

### ALUR 1: SURAT STANDAR (KE WD/KABAG)

#### Contoh Kasus: Surat Kerja Praktek

1. **PEMBUATAN SURAT**
   - Staff Prodi membuat surat
   - Mengisi: nomor surat, tanggal, perihal, tujuan
   - Upload file PDF
   - Status: `draft`

2. **SUBMIT KE BAGIAN UMUM**
   - Staff submit surat
   - Status: `verifikasi_umum`
   - Notifikasi ke Staff Fakultas

3. **VERIFIKASI BAGIAN UMUM**
   - Staff Fakultas memeriksa:
     * Format surat
     * Nomor surat
     * Kelengkapan lampiran
   - **Jika APPROVE:**
     * Status: `disposisi_pimpinan`
     * Lanjut ke tujuan (misal: WD1)
   - **Jika REJECT:**
     * Status: `ditolak_umum`
     * Kembali ke Staff Prodi
     * Harus revisi dengan nomor baru (10 → 10a)

4. **DISPOSISI WD1**
   - WD1 menerima notifikasi
   - Mengisi disposisi: "ACC, buatkan surat pengantar"
   - Memilih tujuan: Kabag TU
   - Status: `disposisi_kabag`

5. **DISPOSISI KABAG TU**
   - Kabag TU melihat disposisi WD1
   - Mengisi disposisi baru
   - Distribusi ke: Divisi Akademik
   - Status: `proses_divisi`

6. **PROSES DIVISI AKADEMIK**
   - **Jika APPROVE:**
     * Membuat surat pengantar KP
     * Upload surat final
     * Status: `selesai`
   - **Jika REJECT (salah alamat):**
     * Status: `ditolak_divisi`
     * Kembali ke Kabag TU
     * Kabag redirect ke divisi yang tepat

7. **ARSIP**
   - Surat final tersimpan
   - Status: `arsip`

### ALUR 2: SURAT KE DEKAN (DISPOSISI PARALEL)

#### Contoh Kasus: Surat Kerjasama Laboratorium

1. **PEMBUATAN & VERIFIKASI**
   - Staff Prodi buat surat → Bagian Umum verifikasi

2. **DISPOSISI DEKAN**
   - Dekan menerima surat
   - Disposisi paralel ke: WD1, WD2, WD3
   - Status: `disposisi_paralel`

3. **DISPOSISI PARALEL WAKIL DEKAN**
   - WD1: "Siapkan daftar alat lab"
   - WD2: "Siapkan anggaran"
   - WD3: "Koordinasi dengan mahasiswa"
   - **SEMUA WD HARUS SELESAI DISPOSISI**
   - Sistem menunggu semua complete

4. **KABAG TU**
   - Setelah semua WD selesai
   - Kabag menerima notifikasi
   - Distribusi ke multiple divisi:
     * Divisi Akademik (daftar alat)
     * Divisi Keuangan (anggaran)
     * Divisi Kerjasama (PKS)

5. **PROSES DIVISI**
   - Semua divisi proses paralel
   - Upload dokumen masing-masing
   - Status: `selesai`

---

## STATUS SURAT

| ID | Status | Deskripsi | Aksi Selanjutnya |
|----|--------|-----------|------------------|
| 1 | draft | Surat dalam penyusunan | Submit |
| 2 | verifikasi_umum | Menunggu verifikasi Bagian Umum | Approve/Reject |
| 3 | ditolak_umum | Ditolak Bagian Umum | Revisi dengan nomor baru |
| 4 | disposisi_pimpinan | Di pimpinan (Dekan/WD) | Isi disposisi |
| 5 | disposisi_paralel | Multiple WD (tunggu semua) | Monitoring |
| 6 | disposisi_kabag | Di Kabag TU | Distribusi ke divisi |
| 7 | proses_divisi | Sedang diproses divisi | Tindak lanjut |
| 8 | ditolak_divisi | Salah alamat | Kembali ke Kabag |
| 9 | selesai | Surat final tersedia | Download/Arsip |
| 10 | arsip | Diarsipkan | View only |

---

## HAK AKSES

### Staff Prodi
- CREATE: Buat surat baru
- READ: Lihat surat sendiri
- UPDATE: Edit surat (draft/ditolak)
- DELETE: Hapus draft
- TRACKING: Surat sendiri

### Kaprodi
- READ: Semua surat prodi
- MONITORING: Dashboard & report
- NO CREATE/UPDATE/DELETE

### Staff Fakultas (Bagian Umum)
- CREATE: Buat surat baru
- READ: Semua surat fakultas
- VERIFIKASI: Approve/Reject dari prodi
- MONITORING: Ekspedisi surat

### Dekan
- DISPOSISI: Single atau paralel
- READ: Semua surat
- MONITORING: Dashboard eksekutif

### Wakil Dekan (WD1/WD2/WD3)
- DISPOSISI: Sesuai bidang
- READ: Surat yang didisposisi
- VIEW: Disposisi WD lain (paralel)

### Kabag TU
- DISPOSISI: Ke divisi
- DISTRIBUSI: Single atau multiple
- RE-ROUTE: Jika divisi reject

### Staff Divisi
- PROSES: Tindak lanjut surat
- UPLOAD: Surat final
- APPROVE/REJECT: Validasi alamat
- ARSIP: Dokumen final

---

## NOTIFIKASI

### Trigger Notifikasi
1. **Surat Baru** → Notif ke Bagian Umum
2. **Verifikasi Selesai** → Notif ke pembuat & tujuan
3. **Surat Ditolak** → Notif ke pembuat dengan alasan
4. **Disposisi Baru** → Notif ke tujuan disposisi
5. **Disposisi Paralel Complete** → Notif ke Kabag
6. **Divisi Reject** → Notif ke Kabag
7. **Surat Selesai** → Notif ke pembuat

### Format Notifikasi
```
[TIMESTAMP] [JENIS] [DARI] [PESAN]
2025-01-15 10:30 | SURAT_BARU | Staff Prodi IF | Surat KP menunggu verifikasi
2025-01-15 11:00 | APPROVED | Bagian Umum | Surat diteruskan ke WD1
```

---

## IMPLEMENTASI TEKNIS

### Database Tables

#### 1. users
- id_user (PK)
- nama
- email
- password_hash
- role_id (FK)
- jabatan_id (FK)
- prodi_id (FK, nullable)

#### 2. surat
- id_surat (PK)
- nomor_surat
- perihal
- tujuan
- file_pdf
- status_id (FK)
- created_by (FK)
- tanggal_surat

#### 3. disposisi
- id_disposisi (PK)
- id_surat (FK)
- dari_user (FK)
- kepada_user (FK)
- instruksi
- created_at

#### 4. disposisi_paralel
- id (PK)
- id_surat (FK)
- dari_jabatan
- kepada_jabatan (JSON)
- status_per_jabatan (JSON)

#### 5. tracking
- id_tracking (PK)
- id_surat (FK)
- id_user (FK)
- id_status (FK)
- catatan
- timestamp

### Nomor Surat Format
```
[TAHUN]/[KODE_FAKULTAS]/[KODE_PRODI]/[NOMOR]
Contoh: 2025/FT/IF/001

Revisi: 2025/FT/IF/001a (revisi pertama)
        2025/FT/IF/001b (revisi kedua)
```

### Dual-Layer Tracking
```
Layer 1 (Formal/Jabatan):
Surat dari: Kaprodi IF
Disposisi: Dekan → WD1 → Kabag TU → Divisi

Layer 2 (Real/Individu):
Dibuat: Andi (staff_prodi)
Verifikasi: Siti (staff_fakultas)
Disposisi: Dr. Budi (dekan)
Proses: Tono (divisi_akademik)
```

### API Endpoints
```
POST   /api/surat                 - Buat surat
GET    /api/surat                 - List surat
PUT    /api/surat/{id}           - Update surat
POST   /api/surat/{id}/submit    - Submit ke verifikasi
POST   /api/surat/{id}/verify    - Approve/Reject verifikasi
POST   /api/surat/{id}/disposisi - Buat disposisi
GET    /api/surat/{id}/tracking  - Lihat tracking
POST   /api/surat/{id}/final     - Upload surat final
```

### Environment Variables
```
APP_NAME=Sistem_Persuratan_Fakultas
APP_ENV=production
APP_URL=https://surat.fakultas.ac.id

DB_DATABASE=surat_fakultas
DB_USERNAME=surat_user
DB_PASSWORD=secure_password

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=notif@fakultas.ac.id
MAIL_PASSWORD=app_password

FILESYSTEM_DISK=local
SURAT_UPLOAD_PATH=storage/app/public/surat
MAX_FILE_SIZE=10MB
ALLOWED_EXTENSIONS=pdf
```

---

## CATATAN PENTING

1. **Bagian Umum = Staff Fakultas** (dual function)
2. **Disposisi Paralel** harus tunggu semua selesai
3. **Revisi Surat** dengan nomor baru (10→10a)
4. **History** semua versi tersimpan
5. **Tracking** dual-layer (jabatan + individu)
6. **Notifikasi** real-time ke semua pihak terkait
7. **File PDF** wajib untuk setiap surat

---

*Dokumen ini adalah panduan implementasi Sistem Persuratan Fakultas Teknik*
*Versi: 1.0 | Update: 15 Januari 2025*