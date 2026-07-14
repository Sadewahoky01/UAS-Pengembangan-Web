# 🎓 Smart Event Campus
### Sistem Informasi Manajemen Event Kampus
**Tugas Akhir Semester (UAS) - Mata Kuliah Pengembangan Web**

---

## 📋 Deskripsi Proyek

**Smart Event Campus** adalah aplikasi web berbasis PHP yang dirancang untuk mengelola dan mempublikasikan berbagai kegiatan kampus di **Universitas Potensi Utama**. Aplikasi ini memungkinkan administrator untuk menambahkan, mengedit, dan menghapus event kampus, sementara mahasiswa/pengunjung dapat melihat seluruh event yang tersedia secara publik.

---

## 👨‍💻 Identitas Mahasiswa

| Keterangan | Detail |
|------------|--------|
| **Nama** | Ananda Pratama |
| **Program Studi** | Teknik Informatika |
| **Universitas** | Universitas Potensi Utama |
| **Mata Kuliah** | Pengembangan Web |

---

## ✨ Fitur Utama

### 🌐 Halaman Publik (Pengunjung)
- **Landing Page** — Menampilkan semua event kampus dalam tampilan kartu yang menarik
- **Live Search** — Pencarian event secara real-time tanpa refresh halaman
- **Filter Kategori** — Filter event berdasarkan: Seminar, Workshop, Lomba, Pelatihan
- **Modal Detail** — Popup detail event tanpa pindah halaman
- **Halaman Detail Event** — Halaman penuh untuk setiap event dengan breadcrumb & event serupa
- **Tombol Share** — Bagikan event ke WhatsApp atau salin tautan

### 🔐 Panel Administrator
- **Login Aman** — Autentikasi dengan bcrypt password hashing
- **Dashboard** — Statistik event (Total, Akan Datang, Sedang Berjalan, Selesai)
- **Tambah Event** — Form lengkap dengan upload gambar (JPG/PNG/WEBP, maks 2MB)
- **Edit Event** — Perbarui data event dengan preview gambar
- **Hapus Event** — Hapus event beserta file gambarnya
- **Profil Admin** — Ubah nama tampilan dan ganti password
- **Logout** — Keluar dengan penghapusan sesi yang aman

---

## 🛠️ Teknologi yang Digunakan

| Komponen | Teknologi |
|----------|-----------|
| **Backend** | PHP 8.x (Native, tanpa framework) |
| **Database** | MySQL 5.7+ / MariaDB |
| **Frontend** | HTML5, CSS3 Vanilla, JavaScript ES6 |
| **Web Server** | Apache (via XAMPP) |
| **Font** | Google Fonts - Outfit |
| **Database Driver** | PDO (PHP Data Objects) |

---

## 📁 Struktur File Proyek

```
UAS_Pengembangan Web/
│
├── 📄 index.php              # Landing page publik (daftar event)
├── 📄 event_detail.php       # Halaman detail event (publik)
├── 📄 login.php              # Halaman login administrator
├── 📄 logout.php             # Handler logout
│
├── 📄 admin_dashboard.php    # Dashboard pengelolaan event
├── 📄 admin_profile.php      # Halaman profil & ganti password admin
├── 📄 event_add.php          # Form tambah event baru
├── 📄 event_edit.php         # Form ubah data event
├── 📄 event_delete.php       # Handler hapus event
│
├── 📄 config.php             # Konfigurasi koneksi database & helper
├── 📄 setup.php              # Wizard inisialisasi database
├── 📄 404.php                # Halaman error 404 kustom
│
├── 🎨 style.css              # Stylesheet utama (Design System)
├── 🔒 .htaccess              # Konfigurasi keamanan Apache
├── 🗄️  database.sql          # Schema & data awal database
└── 📂 assets/images/         # Direktori upload gambar event
```

---

## 🗄️ Struktur Database

### Tabel `users` (Admin)
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT AUTO_INCREMENT | Primary Key |
| `username` | VARCHAR(50) UNIQUE | Username login |
| `password` | VARCHAR(255) | Hash bcrypt |
| `name` | VARCHAR(100) | Nama tampilan |
| `created_at` | TIMESTAMP | Waktu dibuat |

### Tabel `events`
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | INT AUTO_INCREMENT | Primary Key |
| `title` | VARCHAR(255) | Judul event |
| `category` | ENUM | seminar / workshop / lomba / pelatihan |
| `description` | TEXT | Deskripsi lengkap |
| `event_date` | DATE | Tanggal pelaksanaan |
| `event_time` | TIME | Waktu mulai |
| `location` | VARCHAR(255) | Tempat/lokasi |
| `speaker` | VARCHAR(100) | Pembicara/pemateri |
| `image_path` | VARCHAR(255) | Path file gambar |
| `status` | ENUM | upcoming / ongoing / completed |
| `created_at` | TIMESTAMP | Waktu dibuat |

---

## 🚀 Cara Menjalankan Proyek

### Prasyarat
- XAMPP (Apache + MySQL) sudah terinstall
- PHP versi 7.4 atau lebih tinggi

### Langkah Instalasi

1. **Clone / Copy** folder proyek ke:
   ```
   C:\xampp\htdocs\UAS_Pengembangan Web\
   ```

2. **Jalankan XAMPP Control Panel**, aktifkan:
   - ✅ Apache
   - ✅ MySQL

3. **Inisialisasi Database** — Buka browser dan akses:
   ```
   http://localhost/UAS_Pengembangan Web/setup.php
   ```
   Klik tombol **"Inisialisasi Database"**

4. **Akses Aplikasi:**
   ```
   http://localhost/UAS_Pengembangan Web/index.php
   ```

5. **Login Admin:**
   - URL: `http://localhost/UAS_Pengembangan Web/login.php`
   - Username: `admin`
   - Password: `admin123`

---

## 🎨 Design System

Aplikasi menggunakan tema **"Deep Space Modern"** dengan palet warna:

| Token | Nilai | Kegunaan |
|-------|-------|----------|
| `--primary` | `#6366f1` (Indigo) | Aksi utama, gradient |
| `--secondary` | `#a855f7` (Violet) | Aksen, gradient |
| `--accent` | `#06b6d4` (Cyan) | Info, tanggal |
| `--bg-main` | `#090d16` | Background utama |
| `--success` | `#10b981` | Status berhasil |
| `--error` | `#ef4444` | Status error |

---

## 🔒 Keamanan

- Password di-hash menggunakan **bcrypt** (`PASSWORD_DEFAULT`)
- Query database menggunakan **Prepared Statements** (mencegah SQL Injection)
- Output di-escape menggunakan `htmlspecialchars()` (mencegah XSS)
- Halaman admin dilindungi pengecekan sesi
- File `.htaccess` melarang akses langsung ke file `.sql`, `.log`, `.md`
- Header HTTP keamanan (X-Frame-Options, X-Content-Type-Options)

---

## 📸 Tampilan Aplikasi

| Halaman | Deskripsi |
|---------|-----------|
| Landing Page | Daftar event dengan search & filter kategori |
| Detail Event | Halaman penuh event dengan info lengkap & event serupa |
| Login Admin | Form autentikasi bergaya glassmorphism |
| Dashboard | Tabel manajemen + statistik event |
| Profil Admin | Ubah nama & ganti password dengan indikator kekuatan |

---

## 📝 Catatan Pengembangan

- Aplikasi ini dikembangkan menggunakan **PHP Native** tanpa framework untuk memenuhi kompetensi mata kuliah
- Design menggunakan teknik **Glassmorphism** dan **CSS Custom Properties**
- JavaScript digunakan untuk interaksi real-time (search, filter, modal, hamburger menu) tanpa library eksternal
- Responsive design menggunakan **hamburger menu** untuk tampilan mobile

---

*© 2026 Smart Event Campus — Universitas Potensi Utama*
