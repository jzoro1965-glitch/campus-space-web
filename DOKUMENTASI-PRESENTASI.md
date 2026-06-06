# 📊 DOKUMENTASI PRESENTASI: CAMPUS SPACE MANAGEMENT SYSTEM

## 🎯 OVERVIEW APLIKASI

**Campus Space** adalah sistem manajemen pemesanan ruang belajar kampus yang memungkinkan:
- **Admin**: Monitoring real-time ketersediaan meja melalui web dashboard
- **Mahasiswa**: Booking meja belajar melalui aplikasi mobile (planned)

---

## 📐 ARSITEKTUR SISTEM

### **1. Backend Architecture (Laravel 11)**
```
┌─────────────────────────────────────────────────┐
│              CLIENT LAYER                        │
├──────────────────┬──────────────────────────────┤
│   WEB ADMIN      │    MOBILE APP (Future)       │
│   (Blade Views)  │    (React Native/Flutter)    │
└────────┬─────────┴──────────────┬───────────────┘
         │                        │
         └────────────┬───────────┘
                      │
         ┌────────────▼─────────────┐
         │      LARAVEL 11 API      │
         │    (Backend Server)      │
         ├──────────────────────────┤
         │   • REST API (Sanctum)   │
         │   • Authentication       │
         │   • Business Logic       │
         └────────────┬─────────────┘
                      │
         ┌────────────▼─────────────┐
         │   DATABASE (SQLite)      │
         │   • users                │
         │   • desks                │
         │   • bookings             │
         └──────────────────────────┘
```

---

## ✅ YANG SUDAH DIKERJAKAN

### **A. DATABASE SCHEMA & MODELS**

#### **1. Tabel `users`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint | Primary key |
| `nim` | string (unique) | Nomor Induk Mahasiswa |
| `name` | string | Nama lengkap |
| `email` | string (unique) | Email untuk login |
| `password` | string (hashed) | Password terenkripsi |
| `role` | enum(admin, mahasiswa) | Role user |
| `timestamps` | datetime | created_at, updated_at |

**Relasi:**
- `User hasMany Bookings` → Satu mahasiswa bisa punya banyak riwayat booking

#### **2. Tabel `desks`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint | Primary key |
| `code` | string (unique) | Kode meja (A1, B2, dst) |
| `location` | string | Lokasi meja (Lantai 1, 2, dst) |
| `timestamps` | datetime | created_at, updated_at |

**Relasi:**
- `Desk hasMany Bookings` → Satu meja bisa punya banyak riwayat booking

#### **3. Tabel `bookings`**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | bigint | Primary key |
| `user_id` | foreignId | FK ke tabel users |
| `desk_id` | foreignId | FK ke tabel desks |
| `booking_date` | date | Tanggal booking |
| `start_time` | time | Jam mulai (08:00) |
| `end_time` | time | Jam selesai (10:00) |
| `status` | string | approved/pending/rejected |
| `timestamps` | datetime | created_at, updated_at |

**Relasi:**
- `Booking belongsTo User` → Booking milik satu mahasiswa
- `Booking belongsTo Desk` → Booking untuk satu meja

**Relational Diagram:**
```
┌─────────────┐          ┌──────────────┐          ┌─────────────┐
│    users    │          │   bookings   │          │    desks    │
├─────────────┤          ├──────────────┤          ├─────────────┤
│ id          │◄─────────┤ user_id (FK) │          │ id          │
│ nim ✓       │          │ desk_id (FK) │─────────►│ code ✓      │
│ name        │          │ booking_date │          │ location    │
│ email ✓     │          │ start_time   │          └─────────────┘
│ password    │          │ end_time     │
│ role        │          │ status       │
└─────────────┘          └──────────────┘

✓ = unique constraint
```

---

### **B. BACKEND LOGIC (Controllers)**

#### **1. DashboardController.php** (Web Admin)
**Path:** `app/Http/Controllers/Admin/DashboardController.php`

**Method `index()`:**
```php
public function index()
{
    // 1. Ambil SEMUA data meja
    $desks = Desk::all();
    
    // 2. Ambil booking HARI INI dengan eager loading relasi
    $today = now()->format('Y-m-d');
    $bookings = Booking::with(['user', 'desk'])
        ->where('booking_date', $today)
        ->where('status', 'approved')
        ->get();

    // 3. Kirim ke view
    return view('admin.dashboard', [
        'desks' => $desks,
        'activeBookings' => $bookings
    ]);
}
```

**Keunggulan:**
- ✅ **Eager Loading** (`with(['user', 'desk'])`) → Mencegah N+1 query problem
- ✅ **Filter by Date** → Hanya ambil booking hari ini
- ✅ **Separation of Concerns** → Business logic terpisah dari view

---

#### **2. Auth Controllers**

**A. AuthenticatedSessionController.php** (Login/Logout)
**Path:** `app/Http/Controllers/Auth/AuthenticatedSessionController.php`

**Fitur:**
- Login dengan email & password
- Redirect berdasarkan role (admin → dashboard, mahasiswa → home)
- Logout dan invalidasi session

**B. RegisteredUserController.php** (Registrasi)
**Path:** `app/Http/Controllers/Auth/RegisteredUserController.php`

**Fitur:**
- Registrasi mahasiswa dengan NIM
- Password validation dengan Laravel Rules
- Auto-login setelah registrasi

---

### **C. ROUTING STRUCTURE**

#### **1. web.php** (Web Routes)
```php
// Public routes
Route::get('/', fn() => view('welcome'));

// Admin Dashboard (bypass auth untuk testing)
Route::get('/admin/dashboard', [DashboardController::class, 'index'])
    ->name('admin.dashboard');

// Auth routes dari file eksternal
require __DIR__.'/auth.php';
```

#### **2. auth.php** (Authentication Routes)
```php
// Guest routes (belum login)
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create']);
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create']);
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

// Protected routes (sudah login)
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy']);
});
```

---

### **D. FRONTEND (Blade Views + TailwindCSS)**

#### **1. Admin Dashboard** (`resources/views/admin/dashboard.blade.php`)

**Komponen Utama:**

##### **A. Card Statistik (3 Cards)**
```blade
┌────────────────────┐  ┌────────────────────┐  ┌────────────────────┐
│  Total Meja        │  │ Booking Aktif      │  │ Meja Tersedia      │
│      12            │  │       3            │  │       9            │
└────────────────────┘  └────────────────────┘  └────────────────────┘
   (Biru)                  (Merah)                 (Hijau)
```

**Logika:**
```blade
{{-- Total Meja --}}
{{ $desks->count() }}

{{-- Booking Aktif --}}
{{ $activeBookings->count() }}

{{-- Meja Tersedia --}}
{{ $desks->count() - $activeBookings->count() }}
```

##### **B. Denah Visual Grid (Interactive Grid)**
```blade
@foreach($desks as $desk)
    @php
        // Cek apakah meja ini ada di booking hari ini
        $isBooked = $activeBookings->contains('desk_id', $desk->id);
    @endphp

    @if($isBooked)
        {{-- Kotak MERAH: TER-BOOKING --}}
        <div class="bg-red-50 border-red-200">
            <div>{{ $desk->code }}</div>
            <span>TER-BOOKING</span>
        </div>
    @else
        {{-- Kotak HIJAU: KOSONG --}}
        <div class="bg-emerald-50 border-emerald-200">
            <div>{{ $desk->code }}</div>
            <span>KOSONG</span>
        </div>
    @endif
@endforeach
```

**Visual Grid:**
```
┌─────┐ ┌─────┐ ┌─────┐ ┌─────┐
│  A1 │ │  A2 │ │  A3 │ │  B1 │
│ 🔴  │ │ 🟢  │ │ 🟢  │ │ 🔴  │
└─────┘ └─────┘ └─────┘ └─────┘
TER-    KOSONG  KOSONG  TER-
BOOKING                 BOOKING
```

##### **C. Tabel Log Booking**
```blade
<table>
    <thead>
        <tr>
            <th>Nama Mahasiswa</th>
            <th>NIM</th>
            <th>Kode Meja</th>
            <th>Sesi Jam Akses</th>
        </tr>
    </thead>
    <tbody>
        @forelse($activeBookings as $booking)
            <tr>
                <td>{{ $booking->user->name }}</td>
                <td>{{ $booking->user->nim }}</td>
                <td>{{ $booking->desk->code }}</td>
                <td>{{ $booking->start_time }} – {{ $booking->end_time }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="4">Belum ada booking hari ini</td>
            </tr>
        @endforelse
    </tbody>
</table>
```

---

### **E. DATA SEEDER (Sample Data)**

**File:** `database/seeders/DatabaseSeeder.php`

**Data yang Di-generate:**

#### **Users (4 akun)**
| Role | NIM | Email | Password |
|------|-----|-------|----------|
| admin | 11111111 | admin@kampus.com | password |
| mahasiswa | 22010001 | leo@student.com | password |
| mahasiswa | 22010002 | siti@student.com | password |
| mahasiswa | 22010003 | budi@student.com | password |

#### **Desks (12 meja)**
| Kode | Lokasi |
|------|--------|
| A1, A2, A3 | Lantai 1 |
| B1, B2, B3 | Lantai 2 |
| C1, C2, C3 | Lantai 3 |
| D1, D2, D3 | Lantai 4 |

#### **Bookings (3 booking aktif hari ini)**
| User | Desk | Waktu | Status |
|------|------|-------|--------|
| Leovan | A1 | 08:00-10:00 | approved |
| Siti | B1 | 09:00-11:30 | approved |
| Budi | C1 | 13:00-15:00 | approved |

**Command:**
```bash
php artisan migrate:fresh --seed
```

---

## 🔧 TEKNOLOGI YANG DIGUNAKAN

### **Backend:**
- **Laravel 11** (Framework PHP)
- **Laravel Sanctum** (API Authentication untuk Mobile)
- **Eloquent ORM** (Database Abstraction)
- **SQLite** (Database - bisa diganti MySQL/PostgreSQL)

### **Frontend Web:**
- **Blade Templates** (Server-side rendering)
- **TailwindCSS** (Utility-first CSS framework)
- **Alpine.js** (Minimal JavaScript framework)

### **Development Tools:**
- **Composer** (PHP Package Manager)
- **NPM** (Node Package Manager)
- **Artisan CLI** (Laravel Command Line)

---

## ❌ KEKURANGAN YANG BELUM DIKERJAKAN

### **1. KEAMANAN & AUTHORIZATION**

#### **Problem:**
```php
// Route tanpa proteksi middleware auth
Route::get('/admin/dashboard', [DashboardController::class, 'index']);
```

#### **Solusi yang Harus Ditambahkan:**
```php
// Proteksi route admin dengan middleware
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::get('/admin/dashboard', [DashboardController::class, 'index']);
});
```

**Yang perlu dibuat:**
- ✅ Middleware `role:admin` untuk cek role user
- ✅ Redirect ke login jika user belum auth
- ✅ Authorization policy untuk aksi tertentu

---

### **2. REST API UNTUK MOBILE APP**

#### **Yang Sudah Ada:**
```
app/Http/Controllers/Api/DeskApiController.php (belum lengkap)
```

#### **Yang Harus Ditambahkan:**

**A. API Authentication (Sanctum)**
```php
// Login API untuk mobile
POST /api/login
{
    "email": "leo@student.com",
    "password": "password"
}
Response: {
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": { ... }
}
```

**B. Booking API Endpoints**
```php
// List meja tersedia
GET /api/desks?date=2026-06-04&time=09:00
Response: [
    {"id": 1, "code": "A1", "location": "Lantai 1", "available": false},
    {"id": 2, "code": "A2", "location": "Lantai 1", "available": true}
]

// Buat booking baru
POST /api/bookings
{
    "desk_id": 2,
    "booking_date": "2026-06-04",
    "start_time": "09:00",
    "end_time": "11:00"
}

// Lihat riwayat booking saya
GET /api/my-bookings

// Cancel booking
DELETE /api/bookings/{id}
```

**C. Real-time Availability API**
```php
// Cek ketersediaan meja real-time
GET /api/desks/{id}/availability?date=2026-06-04
Response: {
    "desk": {...},
    "available_slots": [
        {"start": "08:00", "end": "09:00"},
        {"start": "11:00", "end": "12:00"}
    ],
    "booked_slots": [
        {"start": "09:00", "end": "11:00", "user": "Leovan"}
    ]
}
```

---

### **3. MOBILE APP INTEGRATION**

#### **Rencana Arsitektur Mobile:**

```
┌────────────────────────────────────────────┐
│         MOBILE APP LAYER                    │
│  (React Native / Flutter / Kotlin-Swift)   │
├────────────────────────────────────────────┤
│  • Login/Register Screen                   │
│  • Desk Availability Screen (Grid View)    │
│  • Booking Form                            │
│  • My Bookings History                     │
│  • Profile Management                      │
└─────────────────┬──────────────────────────┘
                  │
         HTTP/REST API (JSON)
                  │
┌─────────────────▼──────────────────────────┐
│        LARAVEL BACKEND API                 │
│         (Sanctum Token Auth)               │
└────────────────────────────────────────────┘
```

#### **API Routes yang Dibutuhkan Mobile:**
```php
// routes/api.php
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [UserController::class, 'profile']);
    Route::get('/desks', [DeskController::class, 'index']);
    Route::get('/desks/{id}', [DeskController::class, 'show']);
    Route::post('/bookings', [BookingController::class, 'store']);
    Route::get('/my-bookings', [BookingController::class, 'myBookings']);
    Route::delete('/bookings/{id}', [BookingController::class, 'cancel']);
    Route::post('/logout', [AuthController::class, 'logout']);
});
```

---

### **4. VALIDASI & ERROR HANDLING**

#### **Yang Harus Ditambahkan:**

**A. Form Validation**
```php
// BookingController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'desk_id' => 'required|exists:desks,id',
        'booking_date' => 'required|date|after_or_equal:today',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
    ]);
    
    // Cek konflik jadwal
    $conflict = Booking::where('desk_id', $validated['desk_id'])
        ->where('booking_date', $validated['booking_date'])
        ->where(function ($query) use ($validated) {
            $query->whereBetween('start_time', [$validated['start_time'], $validated['end_time']])
                  ->orWhereBetween('end_time', [$validated['start_time'], $validated['end_time']]);
        })
        ->exists();
    
    if ($conflict) {
        return response()->json([
            'message' => 'Meja sudah dibooking di waktu tersebut'
        ], 422);
    }
    
    // Buat booking
    $booking = Booking::create($validated + ['user_id' => auth()->id()]);
    
    return response()->json($booking, 201);
}
```

**B. Custom Error Responses untuk API**
```php
// app/Exceptions/Handler.php
public function render($request, Throwable $exception)
{
    if ($request->is('api/*')) {
        return response()->json([
            'message' => $exception->getMessage(),
            'code' => $exception->getCode()
        ], 500);
    }
    
    return parent::render($request, $exception);
}
```

---

### **5. FITUR TAMBAHAN UNTUK PRODUCTION**

#### **A. Notifikasi**
- Push notification ke mobile ketika booking approved/rejected
- Email reminder sebelum waktu booking

#### **B. QR Code Check-in**
- Generate QR code untuk setiap booking
- Scan QR code saat mahasiswa datang ke meja
- Auto-check-in system

#### **C. Admin Management**
- CRUD Desks (Tambah/Edit/Hapus meja)
- Approve/Reject booking manual
- Export laporan booking (PDF/Excel)
- Analytics dashboard (grafik usage meja per hari/minggu)

#### **D. Scheduling & Rules**
- Batas waktu booking per user (max 2 jam/hari)
- Booking advance (max 7 hari ke depan)
- Overtime penalty system
- Blacklist system untuk user yang sering no-show

#### **E. Real-time Updates**
- WebSocket/Pusher untuk live update status meja
- Auto-refresh grid dashboard tanpa reload page
- Instant notification di mobile app

---

## 🎯 ROADMAP PENGEMBANGAN

### **FASE 1: Backend API (Prioritas Tinggi)**
**Estimasi:** 2-3 minggu
- [ ] Buat REST API lengkap untuk mobile
- [ ] Implement Sanctum authentication
- [ ] Validasi booking (conflict detection)
- [ ] Unit testing untuk API endpoints

### **FASE 2: Mobile App Development**
**Estimasi:** 4-6 minggu
- [ ] Setup project React Native/Flutter
- [ ] Screen: Login/Register
- [ ] Screen: Desk Grid (seperti web dashboard)
- [ ] Screen: Booking Form
- [ ] Screen: My Bookings
- [ ] Integrasi API dengan backend

### **FASE 3: Security & Authorization**
**Estimasi:** 1-2 minggu
- [ ] Middleware role-based access control
- [ ] API rate limiting
- [ ] CORS configuration
- [ ] SSL/HTTPS setup

### **FASE 4: Advanced Features**
**Estimasi:** 3-4 minggu
- [ ] QR Code check-in system
- [ ] Push notifications (FCM/OneSignal)
- [ ] Email notifications
- [ ] Admin analytics dashboard
- [ ] Export laporan

### **FASE 5: Testing & Deployment**
**Estimasi:** 2-3 minggu
- [ ] Testing end-to-end
- [ ] Bug fixing
- [ ] Performance optimization
- [ ] Deploy backend ke server (AWS/DigitalOcean)
- [ ] Publish mobile app (Play Store/App Store)

---

## 📱 INTEGRASI WEB & MOBILE

### **Skenario User Flow:**

#### **Web Dashboard (Admin):**
```
1. Admin login di web browser
2. Lihat dashboard real-time
3. Monitor meja mana yang sedang dipakai
4. Lihat log booking hari ini
5. Export laporan (future)
```

#### **Mobile App (Mahasiswa):**
```
1. Download app dari Play Store/App Store
2. Register dengan NIM & email kampus
3. Login dengan kredensial
4. Lihat denah meja tersedia hari ini
5. Pilih meja & waktu booking
6. Submit booking
7. Dapat notifikasi approval
8. Scan QR code saat datang ke meja
9. Check-in otomatis
10. Lihat riwayat booking
```

### **Data Synchronization:**
```
┌──────────────┐
│  Mobile App  │ ─────┐
└──────────────┘      │
                      ├───► Laravel Backend (Single Source of Truth)
┌──────────────┐      │                 │
│ Web Dashboard│ ─────┘                 │
└──────────────┘                        │
                                        ▼
                                  SQLite/MySQL
                                   (Database)
```

**Keuntungan Arsitektur Ini:**
- ✅ Single database untuk semua platform
- ✅ Real-time sync antara web & mobile
- ✅ Konsistensi data terjamin
- ✅ Mudah maintenance dan debugging

---

## 🔒 KEAMANAN & BEST PRACTICES

### **Yang Sudah Diimplementasi:**
- ✅ Password hashing dengan bcrypt
- ✅ CSRF protection (Blade forms)
- ✅ SQL injection protection (Eloquent ORM)
- ✅ XSS protection (Blade auto-escaping)

### **Yang Harus Ditambahkan:**
- [ ] Rate limiting untuk API (prevent abuse)
- [ ] API token expiration (Sanctum)
- [ ] Input sanitization
- [ ] HTTPS enforcement
- [ ] Environment variable untuk credentials (.env)
- [ ] Database backup strategy
- [ ] Logging & monitoring system

---

## 📊 DEMO DATA UNTUK PRESENTASI

### **Skenario Demo:**

**1. Tampilkan Dashboard Web**
```
URL: http://127.0.0.1:8000/admin/dashboard

Tunjukkan:
- 3 card statistik (12 meja, 3 booking, 9 tersedia)
- Grid interaktif (A1, B1, C1 warna merah = ter-booking)
- Tabel log (3 mahasiswa sedang booking)
```

**2. Simulasi Booking Baru**
```bash
# Via tinker (simulasi API call dari mobile)
php artisan tinker

$user = User::find(4); // Mahasiswa Budi
$desk = Desk::find(4); // Meja B1

Booking::create([
    'user_id' => $user->id,
    'desk_id' => $desk->id,
    'booking_date' => now()->format('Y-m-d'),
    'start_time' => '14:00:00',
    'end_time' => '16:00:00',
    'status' => 'approved'
]);
```

**3. Refresh Dashboard**
```
- Card "Booking Aktif" naik jadi 4
- Meja B1 berubah jadi merah di grid
- Tabel log bertambah 1 baris
```

---

## 💡 POIN PENTING UNTUK PRESENTASI KE DOSEN

### **Highlight:**

1. **"Kami sudah membangun backend yang solid dengan Laravel 11"**
   - Database schema yang normalized (3NF)
   - Relasi antar tabel sudah tepat (hasMany, belongsTo)
   - Eager loading untuk optimize performance

2. **"Web dashboard sudah berfungsi penuh untuk admin"**
   - Real-time monitoring ketersediaan meja
   - Visual grid yang intuitif (merah/hijau)
   - Tabel log booking dengan detail lengkap

3. **"Sistem sudah siap untuk integrasi mobile"**
   - Laravel Sanctum sudah terinstall
   - Struktur database sudah support multi-platform
   - Tinggal buat REST API endpoints

4. **"Yang masih perlu dikerjakan adalah:"**
   - REST API lengkap untuk mobile app
   - Middleware authorization (role-based access)
   - Mobile app development (React Native/Flutter)
   - Advanced features (notifikasi, QR code, analytics)

### **Jawaban Antisipasi Pertanyaan Dosen:**

**Q: "Kenapa pakai Laravel?"**
**A:** Laravel adalah framework PHP yang mature, punya ekosistem lengkap (authentication, ORM, API), dan support native untuk RESTful API dengan Sanctum. Cocok untuk aplikasi yang butuh web dashboard + mobile app karena bisa share backend yang sama.

**Q: "Bagaimana mobile app akan komunikasi dengan backend?"**
**A:** Via REST API dengan JSON format. Mobile app akan kirim HTTP request ke endpoint Laravel (contoh: POST /api/bookings), backend proses logic, lalu return response JSON. Authentication pakai token-based (Sanctum) yang di-store di local storage mobile.

**Q: "Apakah bisa multiple user booking bersamaan?"**
**A:** Iya, dengan validasi conflict detection. Sebelum create booking, sistem cek dulu apakah ada booking lain di meja yang sama dengan range waktu yang overlap. Jika ada, akan return error "Meja sudah dibooking".

**Q: "Bagaimana handle real-time update?"**
**A:** Untuk web dashboard, bisa pakai polling (auto-refresh setiap 5 detik) atau WebSocket (Laravel Echo + Pusher) untuk instant update. Untuk mobile, pakai push notification ketika ada perubahan status booking.

**Q: "Berapa lama estimasi selesai full development?"**
**A:** Backend API: 2-3 minggu. Mobile app: 4-6 minggu. Testing & deployment: 2-3 minggu. **Total estimasi: 2-3 bulan** untuk MVP (Minimum Viable Product).

---

## 🚀 KESIMPULAN

### **Kelebihan Sistem Ini:**
- ✅ Arsitektur modern & scalable
- ✅ Separation of concerns (MVC pattern)
- ✅ Database normalized & efficient
- ✅ Siap integrasi mobile
- ✅ Code yang clean & maintainable

### **Next Action Items:**
1. Develop REST API untuk mobile
2. Implement authorization middleware
3. Build mobile app prototype
4. Testing & bug fixing
5. Deployment ke production server

---

**Prepared by: [Nama Anda]**  
**Date: June 4, 2026**  
**Project: Campus Space Management System**
