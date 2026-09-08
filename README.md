<p align="center">
  <img src="https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white" alt="Laravel 12">
  <img src="https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 8.2+">
  <img src="https://img.shields.io/badge/Firebase-RTDB-FFCA28?style=for-the-badge&logo=firebase&logoColor=black" alt="Firebase">
  <img src="https://img.shields.io/badge/Tailwind_CSS-4.0-06B6D4?style=for-the-badge&logo=tailwindcss&logoColor=white" alt="Tailwind CSS 4">
  <img src="https://img.shields.io/badge/Vite-7.0-646CFF?style=for-the-badge&logo=vite&logoColor=white" alt="Vite 7">
</p>

<h1 align="center">🛰️ SEDETIK</h1>
<h3 align="center">Sistem Elektronik Data Titik Koordinat</h3>
<p align="center"><em>Dinas Perhubungan — Transportation Survey & Coordinate Monitoring System</em></p>

<p align="center">
  Aplikasi web untuk pengelolaan survei lalu lintas dan pemantauan titik koordinat secara real-time.<br/>
  <em>A web application for managing traffic surveys and real-time coordinate point monitoring.</em>
</p>

---

## 📖 Deskripsi / Description

**SEDETIK** adalah sistem berbasis web yang dibangun untuk **Dinas Perhubungan (Dishub)** guna mengelola dan memantau kegiatan survei lalu lintas di berbagai titik koordinat. Sistem ini memungkinkan:

- Penetapan lokasi survei berbasis koordinat GPS dengan verifikasi radius
- Penugasan petugas lapangan (operator) ke titik survei tertentu
- Pencatatan jumlah kendaraan secara real-time oleh operator di lapangan
- Pemantauan langsung (live monitoring) oleh administrator melalui dashboard interaktif
- Pembuatan laporan dalam format PDF

**SEDETIK** is a web-based system built for the **Department of Transportation (Dishub)** to manage and monitor traffic survey activities across various coordinate points. The system enables GPS-based survey location management, field operator assignments, real-time vehicle counting, live administrative monitoring, and PDF report generation.

---

## ✨ Fitur Utama / Key Features

### 👨‍💼 Modul Admin
| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard Live** | Pemantauan real-time aktivitas survei, jumlah kendaraan, status operator, dan peta interaktif |
| **Manajemen Titik Koordinat (Tikor)** | CRUD lokasi survei dengan koordinat GPS, radius, kuota target, dan detail zona |
| **Objek & Tarif** | Pengelolaan klasifikasi kendaraan dan struktur tarif (motor, mobil, truk, bus, dll.) |
| **Penugasan** | Penugasan operator ke lokasi pada tanggal dan shift tertentu |
| **Manajemen User** | Registrasi, edit, dan hapus akun admin & operator |
| **Laporan Lokasi** | Filter hasil survei per lokasi/tanggal dengan ekspor PDF |
| **Laporan Operator** | Laporan kinerja operator dengan detail hitungan kendaraan (PDF) |
| **Log Aktivitas** | Audit log seluruh aktivitas sistem dengan ekspor PDF |

### 👷 Modul Operator (Petugas Lapangan)
| Fitur | Deskripsi |
|-------|-----------|
| **Dashboard Operator** | Ringkasan tugas aktif yang ditugaskan |
| **Verifikasi Geolokasi** | Verifikasi kehadiran fisik operator dalam radius lokasi survei |
| **Penghitung Kendaraan Live** | Interface interaktif untuk mencatat kendaraan berdasarkan klasifikasi |
| **Status Istirahat** | Toggle status istirahat vs. aktif menghitung |
| **Finalisasi Survei** | Submit laporan survei dan unduh ringkasan PDF |

### 🔐 Keamanan & Autentikasi
- Middleware berbasis role (`admin` dan `operator`)
- Alur lupa password dengan verifikasi **OTP via email**

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| **Backend** | [Laravel 12](https://laravel.com/) (PHP 8.2+) |
| **Database** | [Firebase Realtime Database](https://firebase.google.com/docs/database) via `kreait/laravel-firebase` |
| **Frontend** | [Blade Templates](https://laravel.com/docs/blade) + [Tailwind CSS 4](https://tailwindcss.com/) |
| **Build Tool** | [Vite 7](https://vitejs.dev/) + `laravel-vite-plugin` |
| **HTTP Client** | [Axios](https://axios-http.com/) |
| **PDF Export** | [Laravel DomPDF](https://github.com/barryvdh/laravel-dompdf) (`barryvdh/laravel-dompdf`) |

---

## 📁 Struktur Folder / Folder Structure

```
project-tikor/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── ActivityLogController.php    # Audit log & PDF export
│   │   │   ├── AdminController.php          # Admin dashboard & metrics
│   │   │   ├── DaftarUserController.php     # User management (CRUD)
│   │   │   ├── ForgotPasswordController.php # OTP password reset
│   │   │   ├── LaporanLokasiController.php  # Location reports & PDF
│   │   │   ├── LaporanOperatorController.php# Operator reports & PDF
│   │   │   ├── LiveDashboardController.php  # Real-time monitoring
│   │   │   ├── LoginController.php          # Auth & geolocation check
│   │   │   ├── ObjekTarifController.php     # Vehicle tariff CRUD
│   │   │   ├── OperatorController.php       # Field operator operations
│   │   │   ├── PenugasanController.php      # Task assignment CRUD
│   │   │   └── TikorController.php          # Coordinate point CRUD
│   │   └── Middleware/                      # Role-based middleware
│   └── Models/
│       ├── FirebaseUser.php                 # Firebase user wrapper
│       └── ObjekTarif.php                   # Firebase tariff wrapper
├── resources/
│   └── views/
│       ├── admin/                           # Admin panel views
│       ├── login/                           # Auth views (login, OTP, reset)
│       ├── operator/                        # Operator views
│       ├── template/                        # Shared layouts & scripts
│       └── landing.blade.php                # Landing page
├── routes/
│   └── web.php                              # All application routes
├── public/                                  # Public assets (JS, CSS, uploads)
├── config/                                  # Laravel & Firebase config
├── tests/                                   # Feature & unit tests
├── composer.json                            # PHP dependencies
├── package.json                             # Node.js dependencies
└── vite.config.js                           # Vite build configuration
```

---

## ⚙️ Instalasi / Installation

### Prasyarat / Prerequisites

- **PHP** >= 8.2
- **Composer** >= 2.x
- **Node.js** >= 18.x & **npm**
- **Firebase Project** dengan Realtime Database aktif
- **Firebase Service Account** credentials (JSON file)

### Langkah Instalasi / Steps

1. **Clone repository**
   ```bash
   git clone https://github.com/satriaarimbawa/project-tikor.git
   cd project-tikor
   ```

2. **Install dependensi PHP**
   ```bash
   composer install
   ```

3. **Install dependensi Node.js**
   ```bash
   npm install
   ```

4. **Salin file environment / Copy environment file**
   ```bash
   cp .env.example .env
   ```

5. **Generate application key**
   ```bash
   php artisan key:generate
   ```

6. **Konfigurasi Firebase** (lihat bagian [Konfigurasi](#-konfigurasi--configuration))

7. **Jalankan development server / Run development server**
   ```bash
   # Terminal 1 — Laravel server
   php artisan serve

   # Terminal 2 — Vite dev server (frontend assets)
   npm run dev
   ```

8. **Akses aplikasi / Access the application**
   ```
   http://localhost:8000
   ```

---

## 🔧 Konfigurasi / Configuration

### Firebase Setup

Tambahkan konfigurasi berikut di file `.env`:

```env
FIREBASE_CREDENTIALS=/path/to/firebase-service-account.json
FIREBASE_DATABASE_URL=https://your-project-id.firebaseio.com
```

> **📌 Catatan / Note:**
> - Dapatkan file `firebase-service-account.json` dari [Firebase Console](https://console.firebase.google.com/) → Project Settings → Service Accounts → Generate new private key.
> - `FIREBASE_DATABASE_URL` dapat ditemukan di Firebase Console → Realtime Database.

### Konfigurasi Email (untuk OTP Password Reset)

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=your-email@gmail.com
MAIL_FROM_NAME="${APP_NAME}"
```

### Environment Variables Penting

| Variable | Deskripsi |
|----------|-----------|
| `APP_NAME` | Nama aplikasi (default: `SEDETIK`) |
| `APP_ENV` | Environment (`local`, `production`) |
| `APP_DEBUG` | Mode debug (`true` / `false`) |
| `FIREBASE_CREDENTIALS` | Path ke file service account Firebase |
| `FIREBASE_DATABASE_URL` | URL Firebase Realtime Database |

---

## 🖼️ Screenshot

> 📸 _Segera ditambahkan / Coming soon_

<!-- 
Tambahkan screenshot di sini:
![Dashboard Admin](docs/screenshots/dashboard-admin.png)
![Live Monitoring](docs/screenshots/live-monitoring.png)
![Operator Survey](docs/screenshots/operator-survey.png)
-->

---

## 🤝 Kontribusi / Contributing

Kontribusi sangat diterima! Berikut panduan untuk berkontribusi:

1. **Fork** repository ini
2. **Buat branch** fitur baru
   ```bash
   git checkout -b feature/nama-fitur
   ```
3. **Commit** perubahan Anda
   ```bash
   git commit -m "feat: deskripsi singkat perubahan"
   ```
4. **Push** ke branch Anda
   ```bash
   git push origin feature/nama-fitur
   ```
5. Buat **Pull Request** ke branch `main`

### Konvensi Commit / Commit Convention

| Prefix | Penggunaan |
|--------|------------|
| `feat:` | Fitur baru |
| `fix:` | Perbaikan bug |
| `docs:` | Perubahan dokumentasi |
| `style:` | Formatting (tanpa perubahan logic) |
| `refactor:` | Refactoring kode |
| `test:` | Penambahan atau perbaikan test |
| `chore:` | Maintenance / konfigurasi |

---

## 🧪 Testing

Jalankan test suite dengan perintah berikut:

```bash
php artisan test
```

Atau untuk menjalankan test spesifik:

```bash
php artisan test --filter=LaporanFeatureTest
```

---

## 📝 Lisensi / License

Project ini bersifat **privat** dan dikembangkan untuk keperluan internal **Dinas Perhubungan**.

_This project is **private** and developed for internal use by the **Department of Transportation**._

---

<p align="center">
  Dibuat dengan ❤️ oleh Tim Pengembang SEDETIK<br/>
  <em>Built with ❤️ by the SEDETIK Development Team</em>
</p>
