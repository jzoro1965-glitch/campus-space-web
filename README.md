# Campus Space — Sistem Manajemen Pemesanan Meja Belajar

Sistem manajemen pemesanan meja belajar kampus berbasis web dengan fitur booking mentor berbayar via Midtrans. Dibangun menggunakan Laravel 11, MySQL, TailwindCSS, dan Alpine.js.

---

## Fitur Utama

- **Booking Meja Belajar** — Mahasiswa bisa pesan meja belajar secara gratis dengan konfirmasi instan
- **Booking Sesi Mentor** — Mahasiswa bisa booking sesi mentoring berbayar via Midtrans Snap (transfer bank, GoPay, QRIS, kartu kredit)
- **Auto-Expire Booking** — Booking yang lewat waktu otomatis di-expire via Laravel Scheduler
- **Dashboard Admin** — Monitoring real-time dengan grafik, denah meja, dan statistik
- **REST API** — 16 endpoints untuk integrasi mobile app (Laravel Sanctum)
- **Role-Based Access** — Admin dan Mahasiswa punya akses berbeda

---

## Teknologi

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 11, PHP 8.5 |
| Database | MySQL |
| Auth Web | Laravel Session |
| Auth API | Laravel Sanctum |
| Payment | Midtrans Snap |
| Frontend | Blade, TailwindCSS, Alpine.js, Chart.js |

---

## Cara Instalasi

### 1. Clone dan install dependencies

```bash
git clone <repo-url>
cd campus-space
composer install
```

### 2. Setup environment

```bash
cp .env.example .env
php artisan key:generate
```

### 3. Konfigurasi `.env`

Isi konfigurasi berikut di file `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_campus_space
DB_USERNAME=root
DB_PASSWORD=

# Midtrans Sandbox (ambil dari dashboard.sandbox.midtrans.com)
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_SNAP_URL=https://app.sandbox.midtrans.com/snap/snap.js

# Untuk webhook lokal, gunakan ngrok:
# MIDTRANS_NOTIFICATION_URL=https://xxxx.ngrok-free.app/payment/notification
```

### 4. Migrasi database dan seed data

```bash
php artisan migrate:fresh --seed
```

### 5. Jalankan server

```bash
php artisan serve
```

Buka di browser: `http://127.0.0.1:8000`

---

## Akun Demo (Setelah Seed)

| Role | Email | Password |
|------|-------|----------|
| Super Admin | admin@kampus.com | password |
| Mahasiswa | leo@student.com | password |
| Mahasiswa | siti@student.com | password |

---

## URL Penting

| URL | Keterangan |
|-----|-----------|
| `/` | Redirect ke halaman login |
| `/admin/dashboard` | Dashboard admin |
| `/mahasiswa` | Halaman booking meja mahasiswa |
| `/mahasiswa/mentors` | Halaman booking sesi mentor |
| `/api/...` | REST API endpoints |

---

## Menjalankan Scheduler (Auto-Expire Booking)

Untuk development lokal:
```bash
php artisan schedule:work
```

Untuk production, tambahkan cron job:
```
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

---

## REST API

Dokumentasi lengkap API tersedia di file `API_DOCUMENTATION.md`.

Base URL: `http://localhost:8000/api`

Auth: `Authorization: Bearer {token}`

---

## Struktur Database

- `users` — Admin dan mahasiswa
- `desks` — Data meja belajar
- `bookings` — Booking meja (gratis)
- `mentors` — Data mentor
- `mentor_schedules` — Jadwal tersedia per mentor
- `mentor_bookings` — Booking sesi mentor (berbayar via Midtrans)
