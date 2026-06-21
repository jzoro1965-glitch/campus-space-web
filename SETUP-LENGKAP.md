# Campus Space — Setup & Panduan Menjalankan

## Cara Menjalankan Aplikasi

### 1. Install Dependencies
```bash
composer install
npm install
```

### 2. Setup Environment
```bash
copy .env.example .env
php artisan key:generate
```

### 3. Migrasi & Seed Data
```bash
php artisan migrate:fresh --seed
```

### 4. Jalankan Server
```bash
php artisan serve
```

### 5. Akses Aplikasi
| URL | Keterangan |
|-----|-----------|
| `http://127.0.0.1:8000` | Redirect ke halaman login |
| `http://127.0.0.1:8000/admin/dashboard` | Dashboard admin |
| `http://127.0.0.1:8000/mahasiswa` | Halaman booking mahasiswa |

---

## Akun Demo (Setelah Seed)

| Role | Email | Password | Keterangan |
|------|-------|----------|-----------|
| Super Admin | admin@kampus.com | password | Akses penuh, tidak bisa dihapus |
| Mahasiswa | leo@student.com | password | Leovan Gamalia |
| Mahasiswa | siti@student.com | password | Siti Nurhaliza |
| Mahasiswa | budi@student.com | password | Budi Santoso |
| Mahasiswa | dewi@student.com | password | Dewi Rahayu |
| Mahasiswa | raka@student.com | password | Raka Pratama |

---

## Data yang Dibuat Seeder

- **12 meja** — A1–A3 (Lantai 1), B1–B3 (Lantai 2), C1–C3 (Lantai 3), D1–D3 (Lantai 4)
- **1 meja nonaktif** — D3 (demo fitur toggle nonaktif)
- **5 booking hari ini** — dashboard langsung terisi
- **~20 booking 7 hari terakhir** — grafik Chart.js terlihat bervariasi
- **2 booking cancelled** — untuk variasi data riwayat

---

## Struktur File Penting

```
app/Http/Controllers/
├── Admin/
│   ├── DashboardController.php     ← statistik, grafik, denah real-time
│   ├── BookingController.php       ← CRUD + cancel booking
│   ├── DeskController.php          ← CRUD + toggle aktif/nonaktif
│   └── UserController.php          ← CRUD + ubah role
├── Mahasiswa/
│   └── HomeController.php          ← index, store (booking), cancel
├── Auth/
│   ├── AuthenticatedSessionController.php
│   └── RegisteredUserController.php
└── Api/
    ├── AuthApiController.php        ← login, register, logout, profile
    ├── DeskApiController.php        ← index, show, available
    └── BookingApiController.php     ← index, show, store, cancel

app/Http/Middleware/
└── EnsureRole.php                  ← role:admin / role:mahasiswa

routes/
├── web.php                         ← admin, mahasiswa, auth routes
├── api.php                         ← 12 REST API endpoints
└── auth.php                        ← login, register, logout

database/
├── migrations/
│   ├── 000..._create_users_table.php        ← nim, role, is_super_admin
│   └── 2026..._create_desks_and_bookings.php ← is_active di desks
└── seeders/DatabaseSeeder.php

resources/views/
├── admin/
│   ├── dashboard.blade.php          ← auto-refresh 30s + Chart.js
│   ├── bookings/ (index, create)
│   ├── desks/ (index, create, edit) ← ada toggle is_active
│   └── users/ (index, create, edit)
├── mahasiswa/
│   └── home.blade.php               ← denah + form + aktivitas hari ini
├── auth/ (login, register)
└── components/app-layout.blade.php  ← sidebar + flash notifications
```

---

## API Endpoints (untuk Mobile App)

Lihat detail lengkap di `API_DOCUMENTATION.md`.

| Method | Endpoint | Keterangan |
|--------|----------|-----------|
| POST | `/api/login` | Login, dapat token |
| POST | `/api/register` | Daftar akun mahasiswa |
| POST | `/api/logout` | Hapus token |
| GET | `/api/profile` | Data profil user |
| PUT | `/api/profile` | Update profil |
| GET | `/api/desks` | Daftar meja + status |
| GET | `/api/desks/available` | Meja tersedia per slot waktu |
| GET | `/api/desks/{id}` | Detail meja + slot terisi |
| GET | `/api/bookings` | Riwayat booking saya |
| POST | `/api/bookings` | Buat booking baru |
| GET | `/api/bookings/{id}` | Detail satu booking |
| DELETE | `/api/bookings/{id}` | Batalkan booking |

Semua endpoint (kecuali login & register) butuh header:
```
Authorization: Bearer {token}
```

---

## Aturan Bisnis

| Aturan | Nilai |
|--------|-------|
| Jam operasional | 07:00 – 21:00 WIB |
| Maksimal durasi | 3 jam per sesi |
| Maksimal booking per hari | 1 booking aktif |
| Booking masa lalu | Tidak diizinkan |
| Conflict detection | Otomatis, real-time |

---

## Troubleshooting

```bash
# Error class not found
composer dump-autoload

# Error no encryption key
php artisan key:generate

# Reset database + data baru
php artisan migrate:fresh --seed

# Clear semua cache
php artisan config:clear && php artisan cache:clear && php artisan view:clear
```

---

## Teknologi

- **Backend** — Laravel 11, PHP 8.2
- **Auth Web** — Laravel Session
- **Auth Mobile** — Laravel Sanctum (Bearer Token)
- **Database** — MySQL (bisa SQLite untuk development)
- **Frontend** — Blade + TailwindCSS (CDN) + Alpine.js + Chart.js
