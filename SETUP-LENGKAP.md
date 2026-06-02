# 🎯 Campus Space - Setup Lengkap

## ✅ Yang Telah Diperbaiki & Dilengkapi

### 1. **DATABASE & MODELS**
- ✅ Model `User` memiliki kolom: `nim` (string, unique), `name`, `email`, `password`, dan `role` (enum: admin, mahasiswa)
- ✅ Model `Desk` memiliki kolom: `code` (string, unique) dan `location` (string)
- ✅ Model `Booking` memiliki kolom: `user_id`, `desk_id`, `booking_date`, `start_time`, `end_time`, `status` (default: approved)
- ✅ Relasi `belongsTo` dan `hasMany` telah diatur dengan benar:
  - `User->bookings()` (hasMany)
  - `Desk->bookings()` (hasMany)
  - `Booking->user()` (belongsTo)
  - `Booking->desk()` (belongsTo)

### 2. **CONTROLLER LOGIC**
- ✅ `Admin\DashboardController` dengan method `index()`:
  - Mengambil seluruh data Desk
  - Mengambil data Booking hari ini dengan relasi `user` dan `desk` menggunakan eager loading
  - Mengirim data ke view `admin.dashboard`

### 3. **ROUTING**
- ✅ Rute `GET /admin/dashboard` terdaftar dan mengarah ke `DashboardController`
- ✅ Rute bypass tanpa middleware auth untuk testing developer
- ✅ File `auth.php` dimuat agar layout tidak crash
- ✅ Auth Controllers dibuat:
  - `AuthenticatedSessionController` (login & logout)
  - `RegisteredUserController` (register)

### 4. **BLADE VIEW & LAYOUT**
- ✅ View `admin/dashboard.blade.php` dibungkus oleh `<x-app-layout>`
- ✅ Tampilan 3 card statistik menggunakan Tailwind:
  - **Total Meja** (biru)
  - **Booking Aktif Hari Ini** (merah)
  - **Meja Tersedia** (hijau)
- ✅ **Denah Visual Interaktif** berbentuk grid:
  - Kotak **MERAH** = Status TER-BOOKING (jika desk_id ada di bookings hari ini)
  - Kotak **HIJAU** = Status KOSONG (jika desk_id tidak ada di bookings)
- ✅ **Tabel Log** menampilkan:
  - Nama mahasiswa
  - NIM
  - Kode meja
  - Range `start_time` – `end_time` (realtime dari database)

### 5. **AUTH VIEWS**
- ✅ `auth/login.blade.php` (halaman login)
- ✅ `auth/register.blade.php` (halaman registrasi)

---

## 🚀 Cara Menjalankan Aplikasi

### **1. Install Dependencies**
```bash
composer install
npm install
```

### **2. Setup Environment**
```bash
copy .env.example .env
php artisan key:generate
```

### **3. Jalankan Migrasi & Seeder**
```bash
php artisan migrate:fresh --seed
```

Ini akan membuat:
- 1 akun Admin (email: `admin@kampus.com`, password: `password`)
- 3 akun Mahasiswa
- 12 Meja (A1-A3, B1-B3, C1-C3, D1-D3)
- 3 Booking aktif hari ini

### **4. Compile Assets (Opsional)**
```bash
npm run dev
```

### **5. Jalankan Server**
```bash
php artisan serve
```

### **6. Akses Dashboard**
Buka browser dan kunjungi:
```
http://127.0.0.1:8000/admin/dashboard
```

---

## 📋 Data Seeder

### Admin
- **NIM**: 11111111
- **Email**: admin@kampus.com
- **Password**: password
- **Role**: admin

### Mahasiswa
1. **Leovan Gamalia**
   - NIM: 22010001
   - Email: leo@student.com
   - Password: password

2. **Siti Nurhaliza**
   - NIM: 22010002
   - Email: siti@student.com
   - Password: password

3. **Budi Santoso**
   - NIM: 22010003
   - Email: budi@student.com
   - Password: password

---

## 🎨 Fitur Dashboard

### **Card Statistik**
1. **Total Meja** — menampilkan jumlah total meja yang tersedia
2. **Booking Aktif Hari Ini** — jumlah booking dengan status approved hari ini
3. **Meja Tersedia** — selisih antara total meja dengan booking aktif

### **Denah Visual Grid**
- Grid interaktif yang menampilkan status setiap meja
- **Hijau** = Meja kosong dan bisa di-booking
- **Merah** = Meja sudah ter-booking hari ini
- Hover effect untuk pengalaman UI yang lebih baik
- Badge animasi untuk indikator status real-time

### **Tabel Log Booking**
- Menampilkan semua booking aktif hari ini
- Informasi mahasiswa lengkap (nama, email, NIM)
- Kode meja yang dibooking
- Range waktu booking (start_time – end_time)
- Empty state ketika belum ada booking

---

## 🔧 Teknologi yang Digunakan

- **Backend**: Laravel 11
- **Frontend**: Blade Templates + TailwindCSS (CDN)
- **Database**: SQLite (bisa diganti ke MySQL/PostgreSQL di `.env`)
- **Authentication**: Laravel Sanctum + Session

---

## 📁 Struktur File Penting

```
app/
├── Http/Controllers/
│   ├── Admin/
│   │   └── DashboardController.php
│   └── Auth/
│       ├── AuthenticatedSessionController.php
│       └── RegisteredUserController.php
├── Models/
│   ├── User.php (+ relasi bookings)
│   ├── Desk.php (+ relasi bookings)
│   └── Booking.php (+ relasi user & desk)

database/
├── migrations/
│   ├── 0001_01_01_000000_create_users_table.php
│   └── 2026_06_02_055809_create_desks_and_bookings_tables.php
└── seeders/
    └── DatabaseSeeder.php

resources/views/
├── admin/
│   └── dashboard.blade.php
├── auth/
│   ├── login.blade.php
│   └── register.blade.php
├── components/
│   └── app-layout.blade.php
└── welcome.blade.php

routes/
├── web.php
└── auth.php
```

---

## 🐛 Troubleshooting

### Error: "Class not found"
```bash
composer dump-autoload
```

### Error: "No application encryption key"
```bash
php artisan key:generate
```

### Error: Database locked (SQLite)
```bash
php artisan cache:clear
php artisan config:clear
```

### Ingin reset database
```bash
php artisan migrate:fresh --seed
```

---

## 📝 Catatan Developer

- Dashboard saat ini **tanpa middleware auth** untuk kemudahan testing
- Untuk production, tambahkan middleware `auth` dan `role:admin` pada route
- Warna merah/hijau pada grid meja menggunakan kondisi `$activeBookings->contains('desk_id', $desk->id)`
- Eager loading digunakan untuk optimasi query (menghindari N+1 problem)

---

## 🎯 Next Steps (Opsional)

1. ✅ Tambahkan middleware auth untuk proteksi route admin
2. ✅ Buat API endpoint untuk mobile app (sudah ada `DeskApiController`)
3. ✅ Tambahkan fitur filter berdasarkan tanggal
4. ✅ Tambahkan fitur export laporan (PDF/Excel)
5. ✅ Tambahkan notifikasi realtime dengan Laravel Echo + Pusher

---

**🚀 Aplikasi siap digunakan! Selamat coding!**
