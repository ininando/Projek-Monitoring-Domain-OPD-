# Design System — Monitoring Domain OPD

Sumber kebenaran tampilan untuk seluruh halaman. Token diimplementasikan di
`public/css/app.css` (variabel `--ds-*`) di atas Bootstrap 5.3.

## Arah visual
- **Gaya:** Minimalism & Swiss Style — bersih, berbasis grid, kontras tinggi, tanpa efek dekoratif.
- **Kesan:** resmi, tenang, mudah dibaca. Warna dipakai untuk makna (status), bukan hiasan.
- **Mode:** terang saja (aplikasi kantor internal).

## Warna
| Token | Nilai | Pemakaian |
|---|---|---|
| `--ds-navy` | `#0F172A` | Sidebar, teks judul |
| `--ds-primary` | `#0369A1` | Tombol utama, tautan, menu aktif |
| `--ds-primary-hover` | `#075985` | Hover tombol utama |
| `--ds-primary-soft` | `#E0F2FE` | Latar sorotan ringan |
| `--ds-bg` | `#F8FAFC` | Latar halaman |
| `--ds-surface` | `#FFFFFF` | Kartu, tabel, form |
| `--ds-text` | `#020617` | Teks isi |
| `--ds-text-muted` | `#475569` | Teks sekunder (kontras 7.5:1 di putih) |
| `--ds-border` | `#E2E8F0` | Garis pemisah |
| `--ds-danger` | `#B91C1C` | Hapus, Critical |
| `--ds-warning` | `#92400E` | Warning (teks), latar `#FEF3C7` |
| `--ds-success` | `#166534` | Normal/aktif (teks), latar `#DCFCE7` |

Badge status memakai pasangan **teks gelap di latar lembut** (mis. `#B91C1C` di `#FEE2E2`)
agar kontras ≥ 4.5:1, dan selalu disertai teks label — warna tidak berdiri sendiri.

## Tipografi
- **Judul:** Lexend 500/600 · **Isi:** Source Sans 3 400/600 (Google Fonts).
- Skala: judul halaman 1.5rem, judul kartu 1.125rem, isi 1rem (16px), kecil 0.875rem. Tidak ada teks < 12px.
- Line-height isi 1.5. Angka di tabel memakai `font-variant-numeric: tabular-nums`.

## Spasi & bentuk
- Skala 4px: 4, 8, 12, 16, 24, 32. Padding kartu 24px (16px di layar kecil).
- Radius: 8px (kartu, input, tombol), 999px (badge).
- Bayangan hanya satu tingkat: `0 1px 2px rgba(15,23,42,.06)` + border 1px.

## Komponen
- **Header halaman:** breadcrumb → judul + deskripsi singkat → tombol aksi utama di kanan.
- **Tombol:** tinggi minimal 40px (44px untuk target sentuh di tablet). Aksi baris tabel memakai
  tombol outline berikon + teks (teks disembunyikan < 576px, tetap ada `aria-label`).
- **Form:** label selalu terlihat (bukan placeholder), tanda wajib `*` dijelaskan, teks bantuan di
  bawah field, error tepat di bawah field.
- **Tabel:** header abu lembut huruf kecil-kapital, baris hover, kolom angka rata tengah.
- **Empty state:** ikon + kalimat penjelasan + tombol aksi.
- **Fokus:** cincin 3px `rgba(3,105,161,.35)` di semua elemen interaktif; jangan dihapus.

## Grafik (Chart.js, helper `public/js/charts.js`)
- Satu seri = satu warna `--ds-primary`; tanpa kotak legenda (judul kartu sudah menamai seri).
- Bar ≤ 24px, ujung membulat 4px, label nilai di ujung bar; garis 2px dengan titik r4 + cincin putih 2px.
- Warna status (validasi `validate_palette.js`): Critical `#DC2626`, Warning `#F59E0B`, Normal `#16A34A`,
  Tidak aktif `#64748B` — selalu bersama label kategori/legenda berangka, tidak pernah warna saja.
- Satu sumbu Y saja; nama OPD panjang → bar horizontal dengan label dibungkus, tinggi baris menyesuaikan.
- Setiap grafik punya tooltip, `aria-label` ringkas, dan alternatif "Tampilkan sebagai tabel" atau tabel di halaman yang sama.
- Ekspor PNG memakai latar putih (plugin `latarPutih`).

## Gerak
- Transisi 150–200ms hanya untuk warna/latar; dimatikan bila `prefers-reduced-motion`.

## Breakpoint
- ≥ 992px: sidebar tetap 260px. < 992px: sidebar jadi offcanvas, tombol menu di topbar.
- < 576px (HP potret): tabel list memakai `.ds-table-stack` sehingga tiap baris menjadi kartu
  (`data-label` pada `<td>`, `.ds-stack-title`, `.ds-stack-actions`, `.ds-stack-hide`); panel filter
  terlipat di balik tombol "Filter & Pencarian" dan terbuka otomatis bila ada filter aktif/error.
- Tidak boleh ada scroll horizontal di level halaman pada lebar 375px.
- Diuji di 375px (HP), 768px (tablet), dan 1024px+ (desktop).
