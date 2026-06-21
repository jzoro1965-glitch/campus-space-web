# Campus Space — Dokumentasi Presentasi

## Overview Sistem

**Campus Space** adalah sistem manajemen pemesanan meja belajar kampus dengan dua platform:
- **Web Admin** (Blade + TailwindCSS) — untuk admin monitor dan kelola booking
- **REST API** (Laravel Sanctum) — untuk integrasi mobile app mahasiswa

---

## Arsitektur Sistem

```
┌─────────────────────────────────────────────────┐
│                  CLIENT LAYER                    │
├──────────────────────┬──────────────────────────┤
│    WEB ADMIN         │   MOBILE APP             │
│    (Blade Views)     │   (React Native/Flutter) │
└──────────┬───────────┴──────────────┬───────────┘
           │                          │
           │  HTTP Session            │  HTTP Bearer Token
           │                          │  (Laravel Sanctum)
           └──────────────┬───────────┘
                          ▼
           ┌──────────────────────────┐
           │     LARAVEL 11 BACKEND   │
           ├──────────────────────────┤
           │  • Role-based Middleware │
           │  • Business Logic        │
           │  • Conflict Detection    │
           │  • REST API              │
           └──────────────┬───────────┘
                          ▼
           ┌──────────────────────────┐
           │       DATABASE           │
           │  users · desks · bookings│
           └──────────────────────────┘
```

---

## Database Schema

### Tabel `users`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `nim` | string unique | Nomor Induk Mahasiswa |
| `name` | string | Nama lengkap |
| `email` | string unique | Email login |
| `password` | hashed | Bcrypt |
| `role` | enum | `admin` / `mahasiswa` |
| `is_super_admin` | boolean | Super admin tidak bisa diubah rolenya |

### Tabel `desks`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `code` | string unique | Kode meja (A1, B2, dst) |
| `location` | string | Lantai 1, 2, dst |
| `is_active` | boolean | false = nonaktif, tidak bisa dibooking |

### Tabel `bookings`
| Kolom | Tipe | Keterangan |
|-------|------|-----------|
| `user_id` | FK → users | Pemilik booking |
| `desk_id` | FK → desks | Meja yang dibooking |
| `booking_date` | date | Tanggal |
| `start_time` | time | Jam mulai |
| `end_time` | time | Jam selesai |
| `status` | string | `approved` / `cancelled` |

**Relational Diagram:**
```
users ──< bookings >── desks
```

---

## Fitur yang Sudah Diimplementasi

### A. Keamanan & Middleware
- Role-based access control via `EnsureRole` middleware
- `/admin/*` — hanya bisa diakses role `admin`
- `/mahasiswa/*` — hanya bisa diakses role `mahasiswa`
- Redirect otomatis ke halaman sesuai role setelah login
- Super admin protection — tidak bisa dihapus/diubah role

### B. Aturan Bisnis Booking (Web + API)
- Jam operasional: **07:00 – 21:00 WIB**
- Maksimal durasi: **3 jam per sesi**
- Maksimal **1 booking aktif per hari** per mahasiswa
- **Tidak bisa booking tanggal masa lalu**
- **Conflict detection** — sistem tolak booking jika slot sudah terisi
- Validasi meja aktif sebelum booking

### C. Web Admin
- **Dashboard** — statistik real-time, grafik 7 hari, denah visual meja
- **Auto-refresh** setiap 30 detik (live monitoring)
- **Kelola Meja** — CRUD + toggle aktif/nonaktif
- **Kelola Booking** — filter, buat manual, batalkan, hapus
- **Kelola User** — CRUD + ubah role (admin ↔ mahasiswa)

### D. Halaman Mahasiswa
- Denah meja interaktif (klik untuk pilih)
- Panel aturan booking
- Form booking dengan validasi client-side
- "Aktivitas Hari Ini" — booking semua mahasiswa (transparansi)
- Riwayat booking pribadi

### E. REST API (12 Endpoints)
```
POST   /api/login              ← dapat Bearer token
POST   /api/register
POST   /api/logout
GET    /api/profile
PUT    /api/profile            ← update profil dari mobile

GET    /api/desks              ← list + status ready/booked
GET    /api/desks/available    ← filter per slot waktu
GET    /api/desks/{id}         ← detail + booked_slots

GET    /api/bookings           ← riwayat saya
POST   /api/bookings           ← buat booking (validasi bisnis sama)
GET    /api/bookings/{id}      ← detail booking
DELETE /api/bookings/{id}      ← batalkan
```

---

## Poin Jual ke Dosen

### 1. Arsitektur Dual-Platform
> "Satu backend melayani dua platform — web admin (Blade) dan mobile app (REST API). Tidak perlu buat backend terpisah."

### 2. Dua Mekanisme Auth
> "Autentikasi web pakai session Laravel, mobile pakai Sanctum Bearer Token. Keduanya mekanisme berbeda tapi satu database, data selalu sinkron."

### 3. Business Logic Konsisten
> "Aturan bisnis (jam operasional, batas durasi, batas per hari, conflict detection) diterapkan di dua tempat — controller web dan API controller — dengan aturan yang identik."

### 4. Keamanan Role-Based
> "Middleware `EnsureRole` memastikan admin tidak bisa masuk ke halaman mahasiswa dan sebaliknya. Super admin punya hak akses khusus untuk demote admin."

### 5. Database Normalized
> "Relasi antar tabel sudah benar (hasMany, belongsTo), eager loading dipakai untuk cegah N+1 query problem, dan conflict detection menggunakan query overlap yang efisien."

---

## Jawaban Antisipasi Pertanyaan Dosen

**Q: "Bagaimana mencegah dua orang booking meja yang sama di waktu bersamaan?"**

A: Conflict detection menggunakan query overlap interval:
```php
Booking::where('desk_id', $deskId)
    ->where('status', 'approved')
    ->where('start_time', '<', $endTime)   // booking lain belum selesai
    ->where('end_time',   '>', $startTime) // saat kita mau mulai
    ->exists();
```
Jika ada hasil, sistem langsung tolak dengan pesan error.

---

**Q: "Kalau mobile developer mau pakai API ini, bagaimana mereka tahu formatnya?"**

A: Ada file `API_DOCUMENTATION.md` yang berisi semua 12 endpoint, lengkap dengan contoh request body, response sukses, response error, tabel parameter, dan contoh alur lengkap dari login sampai logout.

---

**Q: "Kenapa status booking langsung `approved`, tidak ada approval dulu?"**

A: Ini keputusan desain untuk UX — mahasiswa tidak perlu menunggu. Admin tetap bisa cancel booking yang sudah ada. Jika perlu workflow approval, kolom `status` sudah support nilai `pending` dan tinggal tambah logika di controller.

---

**Q: "Bagaimana handle kalau dua user submit booking bersamaan (race condition)?"**

A: Untuk production, bisa tambahkan database transaction + lock:
```php
DB::transaction(function () use ($validated) {
    // conflict check + insert dalam satu transaction
});
```
Untuk MVP/tugas akhir, race condition sangat jarang terjadi karena booking per user dibatasi 1 per hari.

---

**Q: "Apakah API ini sudah bisa langsung dipakai di Flutter/React Native?"**

A: Ya. Tinggal kirim HTTP request ke endpoint, dengan header `Authorization: Bearer {token}` untuk protected routes. Semua response sudah JSON dengan format konsisten `{ success, message, data }`.

---

## Skenario Demo

### Demo 1 — Login Sebagai Admin
1. Buka `http://localhost:8000`
2. Login: `admin@kampus.com` / `password`
3. Otomatis redirect ke dashboard admin
4. Tunjukkan: statistik cards, grafik 7 hari, denah meja real-time

### Demo 2 — Fitur Toggle Meja Nonaktif
1. Masuk ke Kelola Meja
2. Klik "Nonaktifkan" pada salah satu meja
3. Balik ke dashboard — meja berubah jadi abu-abu
4. Login mahasiswa — meja nonaktif tidak bisa dipilih

### Demo 3 — Booking dari Sisi Mahasiswa
1. Login: `leo@student.com` / `password`
2. Klik meja yang tersedia (hijau)
3. Isi tanggal dan jam
4. Submit — lihat notifikasi sukses
5. Meja langsung muncul di "Aktivitas Hari Ini"

### Demo 4 — REST API (simulasi mobile)
Pakai Postman atau curl:
```bash
# Login
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email":"leo@student.com","password":"password"}'

# Cek meja tersedia jam 09:00-11:00
curl http://localhost:8000/api/desks/available?date=2026-06-17&start=09:00&end=11:00 \
  -H "Authorization: Bearer {token}"

# Buat booking
curl -X POST http://localhost:8000/api/bookings \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{"desk_id":2,"booking_date":"2026-06-17","start_time":"09:00","end_time":"11:00"}'
```

---

## Status Implementasi

| Fitur | Status |
|-------|--------|
| Role-based middleware | ✅ Selesai |
| Validasi bisnis booking | ✅ Selesai |
| REST API 12 endpoints | ✅ Selesai |
| Dokumentasi API | ✅ Selesai |
| Dashboard admin (grafik, auto-refresh) | ✅ Selesai |
| Toggle meja aktif/nonaktif | ✅ Selesai |
| Halaman mahasiswa (denah, aktivitas hari ini) | ✅ Selesai |
| Flash notifications (auto-dismiss) | ✅ Selesai |
| Seeder data realistis untuk demo | ✅ Selesai |
| Email konfirmasi booking | ⏳ Opsional |
| Export PDF/Excel | ⏳ Opsional |
| QR Code check-in | ⏳ Opsional |
| Mobile app | ⏳ Next phase |
