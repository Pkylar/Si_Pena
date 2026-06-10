# SI-PENA (Sistem Informasi Pengajuan Dana)

## Deskripsi Singkat

SI-PENA adalah sistem informasi berbasis web untuk mengelola pengajuan dana kegiatan di lingkungan Fakultas Rekayasa Industri (FRI), Telkom University. Sistem ini memfasilitasi proses pengajuan, verifikasi, dan persetujuan dana kegiatan organisasi kemahasiswaan maupun lomba secara digital dan terstruktur.

---

## Tech Stack

| Komponen | Teknologi |
|----------|-----------|
| Backend | Laravel 13 (PHP 8.3) |
| Database | MySQL |
| Frontend | Blade Template + Vanilla CSS + JavaScript |
| Chart | Chart.js |
| Icon | Font Awesome 6 |

---

## Fitur Utama

1. **Multi-role Authentication** — Login berdasarkan role (Ormawa, Kemahasiswaan, Keuangan, WD2)
2. **Pengajuan Dana** — Ormawa dapat mengajukan dana kegiatan lengkap dengan proposal PDF
3. **Alur Persetujuan Bertingkat** — Pengajuan melewati beberapa tahap verifikasi
4. **Sistem Revisi** — Kemahasiswaan dan Keuangan dapat memberikan catatan revisi
5. **Persetujuan Dana oleh WD2** — WD2 menentukan nominal dana yang disetujui
6. **Grafik Realisasi Dana** — Visualisasi bar chart dana yang telah disetujui per unit per triwulan
7. **Dashboard Statistik** — Ringkasan jumlah pengajuan berdasarkan status
8. **Format Rupiah Otomatis** — Input nominal otomatis terformat dengan titik pemisah ribuan
9. **Responsive Design** — Tampilan menyesuaikan ukuran layar

---

## Role & Hak Akses

| Role | Hak Akses |
|------|-----------|
| **Ormawa** | Mengajukan dana, melihat status pengajuan sendiri |
| **Kemahasiswaan** | Melihat semua pengajuan, mengubah status, memberikan revisi |
| **Keuangan** | Melihat semua pengajuan, mengubah status, memberikan revisi |
| **WD2 (Wakil Dekan 2)** | Menentukan nominal dana disetujui, approve/tolak pengajuan |

---

## Alur Pengajuan Dana

```
┌─────────────────────────────────────────────────────────────────────────┐
│                         ALUR PENGAJUAN DANA                             │
└─────────────────────────────────────────────────────────────────────────┘

  [ORMAWA]                [KEMAHASISWAAN]           [KEUANGAN]              [WD2]
     │                          │                       │                     │
     │  Ajukan Dana             │                       │                     │
     │  (isi form +             │                       │                     │
     │   upload proposal)       │                       │                     │
     │                          │                       │                     │
     ├─────────────────────────►│                       │                     │
     │                          │                       │                     │
     │                    Review pengajuan              │                     │
     │                          │                       │                     │
     │                    ┌─────┼─────┐                 │                     │
     │                    │     │     │                 │                     │
     │                 Revisi  Tolak  Teruskan          │                     │
     │                    │     │     │                 │                     │
     │◄───────────────────┘     │     └────────────────►│                     │
     │  (perbaiki & ajukan      │                       │                     │
     │   ulang)                 │                 Review pengajuan            │
     │                          │                       │                     │
     │                          │                 ┌─────┼─────┐               │
     │                          │                 │     │     │               │
     │                          │              Revisi  Tolak  Teruskan ke WD2 │
     │                          │                 │     │     │               │
     │◄───────────────────────────────────────────┘     │     └──────────────►│
     │                          │                       │                     │
     │                          │                       │               Isi nominal
     │                          │                       │               dana disetujui
     │                          │                       │                     │
     │                          │                       │               ┌─────┼─────┐
     │                          │                       │               │           │
     │                          │                       │           Disetujui    Ditolak
     │                          │                       │               │           │
     │                          │                       │               ▼           │
     │                          │                       │         Masuk Grafik      │
     │                          │                       │                           │
     └──────────────────────────┴───────────────────────┴───────────────────────────┘
```

### Status Pengajuan

| Status | Keterangan |
|--------|-----------|
| Belum Diproses | Pengajuan baru masuk, belum ditinjau |
| Sedang Diproses Kemahasiswaan | Kemahasiswaan sedang meninjau |
| Revisi | Perlu perbaikan, dikembalikan ke ormawa |
| Diteruskan ke Keuangan | Lolos review kemahasiswaan |
| Sedang Diproses Keuangan | Keuangan sedang meninjau |
| Menunggu Persetujuan WD2 | Lolos review keuangan, menunggu WD2 |
| Disetujui | WD2 menyetujui pengajuan + nominal dana |
| Ditolak | Pengajuan ditolak di salah satu tahap |

---

## Struktur Database

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint | Primary key |
| name | string | Nama lengkap |
| username | string | Username login (unique) |
| password | string | Password (hashed) |
| role | enum | mahasiswa, ormawa, kemahasiswaan, keuangan, wd2 |
| organization_name | string (nullable) | Nama organisasi (untuk role ormawa) |

### Tabel `fund_requests`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint | Primary key |
| user_id | foreign key | Relasi ke users |
| tahun_ajaran | string | Contoh: "2025 Genap" |
| tanggal_mulai | date | Tanggal mulai kegiatan |
| tanggal_selesai | date | Tanggal selesai kegiatan |
| jenis_kegiatan | string | "Organisasi Kemahasiswaan" atau "Lomba" |
| tingkat_kegiatan | string | Fakultas/Universitas/Regional/Nasional/Internasional |
| nama_kegiatan | string | Nama kegiatan |
| deskripsi | text | Deskripsi kegiatan |
| proposal_file | string | Path file proposal (PDF) |
| dana_diajukan | decimal | Nominal dana yang diajukan |
| dana_disetujui | decimal (nullable) | Nominal dana yang disetujui oleh WD2 |
| status | string | Status pengajuan saat ini |

### Tabel `revisions`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint | Primary key |
| fund_request_id | foreign key | Relasi ke fund_requests |
| user_id | foreign key | User yang memberi revisi |
| catatan | text | Isi catatan revisi |

### Tabel `fund_budgets`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| id | bigint | Primary key |
| kategori | enum | "ormawa" atau "lomba" |
| nama_unit | string | Nama unit (HMTI, HMSI, TI, SI, dll) |
| triwulan | integer | 1-4 |
| total_dana | decimal | Total anggaran |
| sisa_dana | decimal | Sisa anggaran |

---

## Struktur Folder Penting

```
si-pena/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          → Login, Register, Logout
│   │   │   ├── DashboardController.php     → Halaman dashboard + statistik
│   │   │   ├── FundRequestController.php   → CRUD pengajuan dana
│   │   │   └── GrafikController.php        → Grafik realisasi dana
│   │   └── Middleware/
│   │       └── RoleMiddleware.php          → Pembatasan akses berdasarkan role
│   └── Models/
│       ├── User.php
│       ├── FundRequest.php
│       ├── Revision.php
│       ├── Approval.php
│       └── FundBudget.php
├── database/
│   ├── migrations/                         → Struktur tabel
│   └── seeders/
│       └── DatabaseSeeder.php              → Data awal (user, pengajuan, budget)
├── resources/views/
│   ├── auth/
│   │   ├── login.blade.php
│   │   └── register.blade.php
│   ├── layouts/
│   │   └── app.blade.php                  → Layout utama (navbar, modal logout)
│   ├── dashboard.blade.php
│   ├── pengajuan/
│   │   ├── index.blade.php                → Daftar pengajuan
│   │   ├── create.blade.php               → Form ajukan dana
│   │   └── show.blade.php                 → Detail + aksi (revisi, status, dana)
│   └── grafik/
│       ├── ormawa.blade.php               → Grafik dana organisasi mahasiswa
│       └── lomba.blade.php                → Grafik dana lomba
├── public/
│   ├── css/app.css                        → Seluruh styling
│   └── images/
│       ├── fri.png                        → Logo Fakultas Rekayasa Industri
│       ├── telyu.png                      → Logo Telkom University
│       └── icon.png                       → Ilustrasi halaman login
└── routes/
    └── web.php                            → Definisi semua route
```

---

## Cara Menjalankan Aplikasi

### Prasyarat
- PHP 8.3+
- Composer
- MySQL
- Node.js (opsional)

### Langkah-langkah

```bash
# 1. Clone atau masuk ke folder project
cd si-pena

# 2. Install dependency PHP
composer install

# 3. Copy file environment
cp .env.example .env

# 4. Generate application key
php artisan key:generate

# 5. Konfigurasi database di file .env
# Buka .env dan sesuaikan:
#   DB_DATABASE=si_pena
#   DB_USERNAME=root
#   DB_PASSWORD=

# 6. Buat database di MySQL
# mysql -u root -e "CREATE DATABASE si_pena;"

# 7. Jalankan migrasi dan seeder
php artisan migrate --seed

# 8. Buat symbolic link untuk storage (upload file proposal)
php artisan storage:link

# 9. Jalankan server
php artisan serve
```

Aplikasi berjalan di: **http://localhost:8000**

---

## Akun Login (dari Seeder)

| Username | Password | Role | Keterangan |
|----------|----------|------|-----------|
| keuanganFRI | keuangan123 | Keuangan | Staff keuangan FRI |
| kemahasiswaanFRI | kemahasiswaan123 | Kemahasiswaan | Staff kemahasiswaan FRI |
| wd2FRI | wd2123 | WD2 | Wakil Dekan 2 |
| HMTI | hmti123 | Ormawa | Himpunan Mahasiswa Teknik Informatika |
| HMSI | hmsi123 | Ormawa | Himpunan Mahasiswa Sistem Informasi |

---

## Grafik Realisasi Dana

Grafik menampilkan total dana yang telah disetujui per unit per triwulan:

- **Grafik Organisasi Mahasiswa** — Menampilkan dana disetujui untuk unit HMTI, HMSI, HMTL, SIECA, MTO
- **Grafik Lomba** — Menampilkan dana disetujui untuk prodi TI, SI, TL, MR

Triwulan ditentukan otomatis berdasarkan bulan tanggal mulai kegiatan:
- Triwulan 1: Januari - Maret
- Triwulan 2: April - Juni
- Triwulan 3: Juli - September
- Triwulan 4: Oktober - Desember

Data grafik diambil secara otomatis dari pengajuan yang statusnya "Disetujui" atau "Selesai" dan memiliki nominal `dana_disetujui`.

---

## Fitur Keamanan

- Password di-hash menggunakan bcrypt (bawaan Laravel)
- Role-based middleware untuk membatasi akses endpoint
- Validasi server-side pada setiap form submission
- CSRF protection pada semua form
- WD2 hanya bisa approve jika status pengajuan "Menunggu Persetujuan WD2"
- Ormawa hanya bisa melihat pengajuan milik sendiri

---

## Skenario Demo untuk Presentasi

### Skenario 1: Pengajuan Dana Berhasil
1. Login sebagai **HMTI** → Ajukan dana "Seminar AI" Rp 5.000.000
2. Login sebagai **Kemahasiswaan** → Status "Diteruskan ke Keuangan"
3. Login sebagai **Keuangan** → Status "Menunggu Persetujuan WD2"
4. Login sebagai **WD2** → Isi dana disetujui Rp 4.000.000, status "Disetujui"
5. Buka halaman Grafik → Muncul bar Rp 4.000.000 di HMTI

### Skenario 2: Pengajuan Direvisi
1. Login sebagai **HMSI** → Ajukan dana
2. Login sebagai **Kemahasiswaan** → Tambah catatan revisi, status "Revisi"
3. Login sebagai **HMSI** → Lihat catatan revisi di detail pengajuan

### Skenario 3: Pengajuan Ditolak
1. Login sebagai **HMTI** → Ajukan dana
2. Login sebagai **Kemahasiswaan** → Status "Ditolak"
3. Dashboard menampilkan jumlah pengajuan ditolak bertambah
