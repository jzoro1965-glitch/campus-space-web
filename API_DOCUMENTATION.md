# Campus Space — REST API Documentation

**Base URL:** `http://localhost:8000/api`  
**Format:** JSON (`Content-Type: application/json`)  
**Auth:** Bearer Token (Laravel Sanctum)

---

## Daftar Isi

1. [Authentication](#1-authentication)
2. [Profile](#2-profile)
3. [Desks (Meja)](#3-desks-meja)
4. [Bookings](#4-bookings)
5. [Aturan Bisnis](#5-aturan-bisnis)
6. [Kode Error](#6-kode-error)

---

## 1. Authentication

### POST `/api/login`

Login dan dapatkan token. Tidak perlu header Authorization.

**Request Body:**
```json
{
  "email": "leo@student.com",
  "password": "password"
}
```

**Response 200 — Sukses:**
```json
{
  "success": true,
  "message": "Login berhasil.",
  "token": "1|abcdefghijklmnopqrstuvwxyz123456",
  "user": {
    "id": 2,
    "nim": "22010001",
    "name": "Leovan Gamalia",
    "email": "leo@student.com",
    "role": "mahasiswa",
    "is_super_admin": false
  }
}
```

**Response 401 — Gagal:**
```json
{
  "success": false,
  "message": "Email atau password salah."
}
```

---

### POST `/api/register`

Daftar akun baru (otomatis role `mahasiswa`). Tidak perlu header Authorization.

**Request Body:**
```json
{
  "nim": "22010099",
  "name": "Nama Mahasiswa",
  "email": "nama@student.com",
  "password": "password123",
  "password_confirmation": "password123"
}
```

**Response 201 — Sukses:**
```json
{
  "success": true,
  "message": "Registrasi berhasil.",
  "token": "2|abcdefghijklmnopqrstuvwxyz123456",
  "user": {
    "id": 6,
    "nim": "22010099",
    "name": "Nama Mahasiswa",
    "email": "nama@student.com",
    "role": "mahasiswa",
    "is_super_admin": false
  }
}
```

**Response 422 — Validasi Gagal:**
```json
{
  "message": "The email has already been taken.",
  "errors": {
    "email": ["The email has already been taken."]
  }
}
```

---

### POST `/api/logout`

Hapus token yang sedang aktif. **Butuh Authorization header.**

**Headers:**
```
Authorization: Bearer {token}
```

**Response 200:**
```json
{
  "success": true,
  "message": "Logout berhasil."
}
```

---

## 2. Profile

> Semua endpoint di bagian ini butuh header:
> ```
> Authorization: Bearer {token}
> ```

### GET `/api/profile`

Ambil data profil user yang sedang login.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 2,
    "nim": "22010001",
    "name": "Leovan Gamalia",
    "email": "leo@student.com",
    "role": "mahasiswa",
    "is_super_admin": false
  }
}
```

---

### PUT `/api/profile`

Update data profil. Semua field bersifat opsional (kirim yang ingin diubah saja).

**Request Body (semua opsional):**
```json
{
  "name": "Leovan Baru",
  "email": "leobaru@student.com",
  "nim": "22010001",
  "password": "newpassword123",
  "password_confirmation": "newpassword123"
}
```

**Response 200 — Sukses:**
```json
{
  "success": true,
  "message": "Profil berhasil diperbarui.",
  "data": {
    "id": 2,
    "nim": "22010001",
    "name": "Leovan Baru",
    "email": "leobaru@student.com",
    "role": "mahasiswa",
    "is_super_admin": false
  }
}
```

---

## 3. Desks (Meja)

> Semua endpoint di bagian ini butuh header:
> ```
> Authorization: Bearer {token}
> ```

### GET `/api/desks`

Daftar semua meja aktif beserta status tersedia/ter-booking untuk tanggal tertentu.

**Query Parameters (opsional):**

| Parameter | Tipe   | Default     | Keterangan         |
|-----------|--------|-------------|--------------------|
| `date`    | string | hari ini    | Format: `YYYY-MM-DD` |

**Contoh Request:**
```
GET /api/desks?date=2026-06-17
```

**Response 200:**
```json
{
  "success": true,
  "date": "2026-06-17",
  "data": [
    {
      "id": 1,
      "code": "A1",
      "location": "Lantai 1",
      "status": "booked"
    },
    {
      "id": 2,
      "code": "A2",
      "location": "Lantai 1",
      "status": "ready"
    }
  ]
}
```

---

### GET `/api/desks/{id}`

Detail satu meja beserta semua slot waktu yang sudah ter-booking pada tanggal tertentu.

**Path Parameter:** `id` — ID meja

**Query Parameters (opsional):**

| Parameter | Tipe   | Default  | Keterangan         |
|-----------|--------|----------|--------------------|
| `date`    | string | hari ini | Format: `YYYY-MM-DD` |

**Contoh Request:**
```
GET /api/desks/1?date=2026-06-17
```

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "code": "A1",
    "location": "Lantai 1",
    "date": "2026-06-17",
    "booked_slots": [
      { "start": "08:00", "end": "10:00" },
      { "start": "13:00", "end": "15:00" }
    ],
    "available": false
  }
}
```

**Response 404:**
```json
{
  "success": false,
  "message": "Meja tidak ditemukan atau tidak aktif."
}
```

---

### GET `/api/desks/available`

Cek semua meja yang **masih tersedia** untuk rentang waktu spesifik.

**Query Parameters (wajib):**

| Parameter | Tipe   | Keterangan              |
|-----------|--------|-------------------------|
| `date`    | string | Format: `YYYY-MM-DD`    |
| `start`   | string | Jam mulai, format `HH:MM` |
| `end`     | string | Jam selesai, format `HH:MM` |

**Contoh Request:**
```
GET /api/desks/available?date=2026-06-17&start=09:00&end=11:00
```

**Response 200:**
```json
{
  "success": true,
  "date": "2026-06-17",
  "start": "09:00",
  "end": "11:00",
  "count": 10,
  "available": [
    { "id": 2, "code": "A2", "location": "Lantai 1" },
    { "id": 3, "code": "A3", "location": "Lantai 1" }
  ]
}
```

**Response 422 — Validasi gagal:**
```json
{
  "message": "The end field must be a date after start.",
  "errors": {
    "end": ["The end field must be a date after start."]
  }
}
```

---

## 4. Bookings

> Semua endpoint di bagian ini butuh header:
> ```
> Authorization: Bearer {token}
> ```

### GET `/api/bookings`

Riwayat semua booking milik user yang sedang login, diurutkan dari terbaru.

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "desk_id": 1,
      "desk_code": "A1",
      "desk_location": "Lantai 1",
      "booking_date": "2026-06-17",
      "start_time": "08:00",
      "end_time": "10:00",
      "status": "approved",
      "created_at": "2026-06-17T01:00:00.000000Z"
    }
  ]
}
```

---

### GET `/api/bookings/{id}`

Detail satu booking. Hanya bisa akses booking milik sendiri.

**Path Parameter:** `id` — ID booking

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "desk_id": 1,
    "desk_code": "A1",
    "desk_location": "Lantai 1",
    "booking_date": "2026-06-17",
    "start_time": "08:00",
    "end_time": "10:00",
    "status": "approved",
    "created_at": "2026-06-17T01:00:00.000000Z"
  }
}
```

**Response 404:**
```json
{
  "success": false,
  "message": "Booking tidak ditemukan."
}
```

---

### POST `/api/bookings`

Buat booking baru. Sistem akan melakukan semua validasi bisnis secara otomatis.

**Request Body:**
```json
{
  "desk_id": 2,
  "booking_date": "2026-06-18",
  "start_time": "09:00",
  "end_time": "11:00"
}
```

| Field          | Tipe   | Keterangan                              |
|----------------|--------|-----------------------------------------|
| `desk_id`      | int    | ID meja (harus ada di database)         |
| `booking_date` | string | Format `YYYY-MM-DD`, tidak boleh masa lalu |
| `start_time`   | string | Format `HH:MM`, min `07:00`             |
| `end_time`     | string | Format `HH:MM`, max `21:00`, harus > `start_time` |

**Response 201 — Sukses:**
```json
{
  "success": true,
  "message": "Booking berhasil dibuat.",
  "data": {
    "id": 5,
    "desk_id": 2,
    "desk_code": "A2",
    "desk_location": "Lantai 1",
    "booking_date": "2026-06-18",
    "start_time": "09:00",
    "end_time": "11:00",
    "status": "approved",
    "created_at": "2026-06-17T08:30:00.000000Z"
  }
}
```

**Response 422 — Konflik jadwal:**
```json
{
  "success": false,
  "message": "Meja sudah dibooking pada rentang waktu tersebut."
}
```

**Response 422 — Durasi terlalu panjang:**
```json
{
  "success": false,
  "message": "Maksimal durasi booking adalah 3 jam."
}
```

**Response 422 — Sudah booking hari itu:**
```json
{
  "success": false,
  "message": "Anda hanya bisa membuat 1 booking aktif per hari."
}
```

**Response 422 — Di luar jam operasional:**
```json
{
  "success": false,
  "message": "Booking hanya tersedia antara jam 07:00 – 21:00 WIB."
}
```

---

### DELETE `/api/bookings/{id}`

Batalkan booking. Hanya bisa membatalkan booking milik sendiri.

**Path Parameter:** `id` — ID booking

**Response 200:**
```json
{
  "success": true,
  "message": "Booking berhasil dibatalkan."
}
```

**Response 404:**
```json
{
  "success": false,
  "message": "Booking tidak ditemukan."
}
```

**Response 422 — Sudah dibatalkan sebelumnya:**
```json
{
  "success": false,
  "message": "Booking sudah dibatalkan."
}
```

**Response 422 — Booking sudah expired:**
```json
{
  "success": false,
  "message": "Booking sudah expired (sesi berakhir)."
}
```

---

## 5. Aturan Bisnis

| Aturan                    | Nilai              |
|---------------------------|--------------------|
| Jam operasional           | 07:00 – 21:00 WIB  |
| Maksimal durasi per sesi  | 3 jam              |
| Maksimal booking per hari | 1 booking aktif    |
| Booking masa lalu         | Tidak diizinkan    |
| Status awal booking       | `approved`         |
| Auto-expire               | 30 menit setelah `end_time` berlalu, status berubah jadi `expired` otomatis via scheduler |
| Syarat booking            | Mahasiswa harus punya paket aktif dengan kuota tersisa |

---

## 5b. Plans & Payments

### GET `/api/plans`

Daftar semua paket aktif yang tersedia untuk dibeli.

**Response 200:**
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Paket Harian",
      "description": "Cocok untuk mahasiswa yang sesekali butuh meja.",
      "price": 3000,
      "price_label": "Rp 3.000",
      "booking_quota": 2,
      "duration_days": 1
    }
  ]
}
```

---

### POST `/api/payments`

Buat order baru untuk membeli paket. Respon berisi `snap_token` untuk membuka halaman pembayaran Midtrans.

**Request Body:**
```json
{
  "plan_id": 2
}
```

**Response 201 — Sukses:**
```json
{
  "success": true,
  "message": "Order berhasil dibuat.",
  "data": {
    "payment_id": 1,
    "order_id": "CS-2-1718600000",
    "amount": 10000,
    "snap_token": "xxxx-xxxx-xxxx",
    "snap_url": "https://app.sandbox.midtrans.com/snap/v2/vtweb/xxxx"
  }
}
```

> Buka `snap_url` di WebView/browser untuk menyelesaikan pembayaran. Setelah bayar, Midtrans akan callback ke `/payment/notification` dan status otomatis diupdate.

---

### GET `/api/payments`

Riwayat semua pembayaran milik user yang login, plus info paket yang sedang aktif.

**Response 200:**
```json
{
  "success": true,
  "active_package": {
    "id": 1,
    "plan_name": "Paket Mingguan",
    "status": "paid",
    "quota_remaining": 8,
    "active_until": "2026-06-27"
  },
  "data": [...]
}
```

---

### GET `/api/payments/{id}`

Detail satu pembayaran milik user.

**Response 200:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "order_id": "CS-2-1718600000",
    "plan_name": "Paket Mingguan",
    "amount": 10000,
    "amount_label": "Rp 10.000",
    "status": "paid",
    "payment_type": "bank_transfer",
    "quota_remaining": 8,
    "active_from": "2026-06-20",
    "active_until": "2026-06-27",
    "paid_at": "2026-06-20T10:00:00.000000Z"
  }
}
```

**Response 402 — Tidak punya paket aktif (saat coba booking):**
```json
{
  "success": false,
  "message": "Anda belum memiliki paket aktif. Beli paket terlebih dahulu.",
  "action": "buy_plan"
}
```

### Status Booking

| Status      | Keterangan                                                    |
|-------------|---------------------------------------------------------------|
| `approved`  | Booking aktif, meja dianggap terpakai                        |
| `cancelled` | Dibatalkan oleh mahasiswa atau admin                         |
| `expired`   | Sesi berakhir, sistem mengubah otomatis setelah grace period |

> **Catatan:** Booking `expired` tidak dihitung sebagai konflik. Mahasiswa yang booking-nya expired hari ini **boleh membuat booking baru** di hari yang sama.

---

## 6. Kode Error

| HTTP Status | Keterangan                                              |
|-------------|---------------------------------------------------------|
| `200`       | Request berhasil                                        |
| `201`       | Resource berhasil dibuat                                |
| `401`       | Tidak terautentikasi (token salah/tidak ada)           |
| `404`       | Resource tidak ditemukan                                |
| `422`       | Validasi gagal atau pelanggaran aturan bisnis           |
| `500`       | Internal server error                                   |

---

## Contoh Alur Mobile App

### Login → Beli Paket → Booking

```
1. POST /api/login              → dapat token
2. GET  /api/plans              → lihat daftar paket
3. POST /api/payments           → buat order, dapat snap_token
   (buka snap_url di WebView/browser untuk bayar)
4. GET  /api/payments/{id}      → cek status pembayaran
5. GET  /api/desks/available    → meja apa saja yang kosong di jam 09:00-11:00
6. GET  /api/desks/{id}         → detail meja + slot terisi
7. POST /api/bookings           → buat booking (butuh paket aktif)
8. GET  /api/bookings           → cek riwayat
9. DELETE /api/bookings/{id}    → batalkan jika perlu (kuota dikembalikan)
10. POST /api/logout            → logout
```

---

*Dokumentasi ini dibuat untuk Campus Space v1.0 — Sistem Booking Meja Belajar.*
