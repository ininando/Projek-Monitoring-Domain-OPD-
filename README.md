# Sistem Informasi Monitoring dan Pemetaan Domain OPD

Aplikasi web Dinas Komunikasi dan Informatika Kota Tomohon untuk memantau masa berlaku dan status domain milik
Organisasi Perangkat Daerah (OPD), memetakan kepemilikan domain per OPD, menampilkan dashboard, dan
menghasilkan laporan PDF/Excel.

## Fitur

| Modul | Isi | Super Admin | Admin |
|---|---|:---:|:---:|
| **Dashboard** | Kartu Total / Aktif / Tidak Aktif / Akan Expired (30 hari), tabel domain paling mendesak, grafik domain per OPD dan pertumbuhan domain per bulan (Chart.js, data via JSON), unduh grafik PNG | ✔ | ✔ |
| **Data OPD** | CRUD, pencarian, paginasi; OPD yang masih memiliki domain tidak dapat dihapus | ✔ | ✔ |
| **Data Domain** | CRUD dengan validasi server-side, filter (OPD, status, rentang tanggal expired), pencarian, badge countdown berwarna, halaman detail (info teknis: registrar, nameserver, IP, hosting, SSL) | ✔ | ✔ |
| **Pemetaan Domain** | Tabel jumlah domain per OPD, detail per OPD dengan bar chart (masa berlaku) & pie chart (status), export Excel, unduh grafik PNG | ✔ | ✔ |
| **Laporan** | Per OPD (+ periode tanggal expired), per status, domain expired (sudah / akan / keduanya); pratinjau di web, unduh **PDF** dan **Excel** | ✔ | ✔ |
| **Manajemen User** | CRUD user, role Super Admin/Admin; tidak bisa menghapus atau menurunkan role akun sendiri | ✔ | 403 |

Hak akses ditegakkan oleh middleware `role` pada route group — Admin yang membuka URL `/user` mendapat **403**,
bukan sekadar menu yang disembunyikan.

### Aturan status expired

Dihitung di model `Domain` (accessor), tidak diduplikasi di view:

| Sisa hari (`tanggal_expired − hari ini`) | Label | Warna |
|---|---|---|
| ≤ 7 hari atau sudah lewat | **Critical** | merah |
| 8 – 30 hari | **Warning** | kuning |
| > 30 hari | **Normal** | hijau |

Domain yang sudah lewat ditampilkan sebagai "Expired X hari lalu". Query scope: `Domain::akanExpired($days = 30)`,
`Domain::sudahExpired()`, `Domain::aktif()`, `Domain::filter([...])`. Zona waktu aplikasi: `Asia/Makassar` (WITA).

## Teknologi

- PHP 8.3, Laravel 12 (MVC, Blade — tanpa SPA/Livewire), MySQL 8
- Bootstrap 5.3, Bootstrap Icons, Chart.js 4 (via CDN jsDelivr), font Lexend + Source Sans 3 (Google Fonts)
- `barryvdh/laravel-dompdf` (PDF), `maatwebsite/excel` 4.x (Excel)
- PHPUnit 11 (feature test)

> Aset front-end dimuat dari CDN, sehingga browser pengguna membutuhkan akses internet. Tidak perlu `npm run build`.

## Kebutuhan

- PHP **8.3+** dengan ekstensi: `pdo_mysql`, `mbstring`, `xml`, `dom`, `zip`, `fileinfo`, **`gd`**
  (wajib untuk Excel; aktifkan `extension=gd` di `php.ini` bila belum)
- Composer 2
- MySQL 8 (misalnya dari Laragon)

## Instalasi

```bash
git clone <url-repo> monitoring-domain-opd
cd monitoring-domain-opd
composer install
cp .env.example .env
php artisan key:generate
```

Sesuaikan koneksi database di `.env`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=domain_opd
DB_USERNAME=root
DB_PASSWORD=
```

Buat database `domain_opd` (utf8mb4), lalu jalankan migrasi beserta data contoh:

```bash
php artisan migrate --seed
php artisan serve
```

Buka `http://localhost:8000` — Anda akan diarahkan ke halaman login.

### Data contoh (seeder)

`php artisan migrate:fresh --seed` membuat:

- 2 akun default (lihat di bawah)
- 10 OPD Kota Tomohon (alamat, kontak, dan nama kepala OPD bersifat **dummy**)
- 30 domain `*.tomohon.go.id` dengan variasi tanggal expired: sudah lewat, ≤ 7 hari, 8–30 hari, dan > 30 hari

## Akun default

| Role | Email | Password |
|---|---|---|
| Super Admin | `superadmin@tomohon.go.id` | `superadmin123` |
| Admin | `admin@tomohon.go.id` | `admin123` |

> **Segera ganti password** setelah instalasi — lewat menu **Manajemen User** (login sebagai Super Admin),
> atau ubah `database/seeders/UserSeeder.php` lalu jalankan `php artisan db:seed --class=UserSeeder`.
> Login dikunci sementara setelah 5 kali gagal berturut-turut.

## Logo

Logo pada kop laporan PDF diambil dari `public/images/logo-diskominfo.png`. File yang ada saat ini hanya
**placeholder** — ganti dengan logo resmi Diskominfo (PNG, disarankan persegi ± 240×240 px) dengan nama file yang sama.

## Menjalankan test

```bash
php artisan test
```

Secara default test memakai SQLite in-memory (lihat `phpunit.xml`). Untuk menjalankan di MySQL, buat database
`domain_opd_test` lalu:

```bash
DB_CONNECTION=mysql DB_DATABASE=domain_opd_test php vendor/bin/phpunit
```

Cakupan feature test (`tests/Feature`):

| File | Menguji |
|---|---|
| `AuthTest` | login, pesan "Email/Password salah", regenerasi session, rate limit, logout |
| `RoleMiddlewareTest`, `UserTest` | otorisasi role (Admin 403 di semua route user), CRUD user, hash password, larangan hapus/turunkan role diri sendiri |
| `DomainTest` | CRUD domain, validasi (wajib, format tanggal, unik, expired ≥ registrasi, IP), filter & urutan list |
| `DomainStatusExpiredTest` | perhitungan sisa hari dan label Critical/Warning/Normal di batas 0, 7, 8, 30, 31 hari |
| `OpdTest` | CRUD OPD, pencarian, paginasi, penolakan hapus OPD yang masih punya domain |
| `DashboardTest`, `PemetaanTest` | statistik, urutan tabel mendesak, data chart JSON, export Excel |
| `LaporanTest` | filter laporan per OPD/periode, status, expired; isi PDF; unduhan PDF & Excel |
| `KeamananTest` | token CSRF di semua form, request tanpa CSRF ditolak (419), escape output (XSS), input berbahaya |
| `TanpaNPlusSatuTest` | jumlah query halaman list/dashboard tidak bertambah seiring jumlah data |

## Struktur penting

```
app/
├── Exports/                 LaporanExport, PemetaanExport, PemetaanDetailExport (Excel)
├── Http/
│   ├── Controllers/         Auth, Dashboard, Opd, Domain, Pemetaan, Laporan, User
│   ├── Middleware/          RoleMiddleware (alias "role")
│   └── Requests/            OpdRequest, DomainRequest, LaporanRequest, UserRequest
├── Models/                  User, Opd (tabel "opd"), Domain (accessor & scope status expired)
└── Services/LaporanDomain   satu sumber data untuk pratinjau, PDF, dan Excel
resources/views/
├── layouts/app.blade.php    layout utama (sidebar + navbar)
├── layouts/auth.blade.php   layout login & halaman error
├── components/              page-header, badge-expired, badge-status, error-summary
└── dashboard, opd, domain, pemetaan, laporan, user, auth, errors
public/css/app.css           design system (token --ds-*) di atas Bootstrap
public/js/charts.js          helper Chart.js bersama
design-system/MASTER.md      dokumentasi design system (warna, tipografi, komponen, grafik, breakpoint)
```

## Keamanan

- Password di-hash (bcrypt, cast `hashed`); session diregenerasi saat login dan diinvalidasi saat logout
- CSRF token di semua form POST/PUT/DELETE; output Blade selalu di-escape (`{{ }}`)
- Validasi server-side dengan FormRequest; seluruh query memakai Eloquent/Query Builder dengan parameter terikat
- Hak akses lewat middleware `auth` + `role` pada route group

## Tampilan

Antarmuka berbahasa Indonesia dan responsif untuk desktop, tablet, dan ponsel (≥ 375 px): sidebar menjadi menu geser
di bawah 992 px, tabel menjadi kartu bertumpuk di bawah 576 px. Detail aturan desain ada di
[`design-system/MASTER.md`](design-system/MASTER.md).

## Di luar cakupan

Registrasi domain otomatis, pembayaran perpanjangan, monitoring uptime website, dan CMS website OPD **tidak** termasuk
dalam aplikasi ini.
