# Memasang Buku Induk Siswa di cPanel

Panduan singkat untuk meng-upload aplikasi ini ke hosting cPanel (LiteSpeed/Apache).
Semua berkas pendukung ada di folder `deploy/cpanel/`.

## Struktur yang benar

Laravel **tidak boleh** ditaruh langsung di `public_html`. Hanya isi folder `public/`
yang boleh diakses lewat web.

```
/home/USERNAME/induk/            <- seluruh proyek (app, vendor, .env, storage)
/home/USERNAME/induk/public/     <- satu-satunya folder yang dibuka ke internet
```

Kalau `public_html` Anda sudah dipakai WordPress atau situs lain, **jangan** ditumpuk.
Buat subdomain baru yang document root-nya menunjuk ke `.../induk/public`.

## Membuat paket upload

Jalankan di komputer pengembang:

```bash
bash deploy/build-paket.sh
```

Hasilnya di folder `dist/`:

| Berkas | Isi | Dipakai untuk |
| ------ | --- | ------------- |
| `induk.zip` (≈22 MB) | seluruh aplikasi: `app/`, `vendor/`, `public/`, `storage/`, `artisan`, `composer.json` | cara A dan cara B |
| `public_html.zip` (≈2 MB) | isi folder `public/` + `index.php` siap pakai + `.htaccess` minimal | hanya cara B |

Yang tidak ikut dibungkus: `.git`, `.env`, `.env.testing`, `tests/`, `deploy/`, `dist/`, log, dan cache.

## Langkah upload (cara A - document root diarahkan ke public)

1. Zip proyek tanpa `node_modules`, `.git`, dan `.env` lokal. Pastikan `vendor/` ikut
   (`composer install --no-dev --optimize-autoloader` sebelum di-zip).
2. Upload zip ke `/home/USERNAME/`, ekstrak menjadi `/home/USERNAME/induk`.
3. cPanel -> Domains -> buat subdomain, Document Root: `/home/USERNAME/induk/public`.
4. Upload `.env` server (lihat bagian Variabel .env di bawah).
5. Pasang `.htaccess` versi minimal di `/home/USERNAME/induk/public/.htaccess`
   (sudah termasuk di `induk.zip`).
6. Symlink storage (opsional):
   `ln -s /home/USERNAME/induk/storage/app/public /home/USERNAME/induk/public/storage`
   Aplikasi tetap menampilkan gambar walau symlink gagal, karena ada rute
   cadangan `/storage/{path}` di `routes/web.php`.
7. Izin folder: `storage` dan `bootstrap/cache` = 775, berkas = 644.
8. Impor database dari dump (`db_data_induk-*.sql`), lalu sesuaikan `DB_*` di `.env`.
9. Bersihkan cache: `php artisan config:clear route:clear view:clear` (kalau ada Terminal).

## Langkah upload (cara B - document root terkunci di public_html)

Dipakai kalau hosting tidak mengizinkan document root di luar `public_html`.

1. Proyek tetap di `/home/USERNAME/induk` (di luar `public_html`).
2. Ekstrak `public_html.zip` langsung di dalam `public_html/` — sudah berisi
   `index.php` (varian cPanel), `.htaccess` minimal, `css`, `js`, `images`.
3. Kalau nama folder proyek bukan `induk`, sesuaikan `$proyek = __DIR__ . '/../induk';`
   di `public_html/index.php`.
4. `.htaccess`: kalau aplikasi dibuka dari subfolder (misal `domain.com/induk`),
   tambahkan `RewriteBase /induk/`.
5. Symlink storage (opsional): `ln -s /home/USERNAME/induk/storage/app/public /home/USERNAME/public_html/storage`
   — tanpa symlink pun gambar tetap tampil lewat rute cadangan.

## Variabel .env di server

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=cpaneluser_db_data_induk
DB_USERNAME=cpaneluser_dbuser
DB_PASSWORD=...
```

`APP_KEY` harus sama dengan yang dipakai di lokal, atau jalankan `php artisan key:generate`.

## Mengatasi 403 Forbidden

| Gejala | Penyebab | Perbaikan |
| ------ | -------- | --------- |
| 403 di `/`, tapi `/index.php` tampil | rewrite tidak jalan; folder tanpa index ditolak `Options -Indexes` | pakai `htaccess-minimal.txt`, tambah `DirectoryIndex index.php` |
| 403 di `/` **dan** `/index.php` | izin/owner berkas salah, atau document root salah | folder 755, berkas 644, `chown -R USER:USER`; pastikan docroot = `.../induk/public` |
| 403 hanya di gambar/CSS | symlink `public/storage` belum ada | biasanya sudah tertangani rute cadangan `/storage/{path}`; pastikan folder `storage/app/public` berisi berkasnya |
| 500 dengan pesan `Options not allowed here` | hosting melarang direktif `Options` | hapus blok `<IfModule mod_negotiation>` di `.htaccess` |
| 403 pada folder tertentu | aturan `.htaccess` WordPress/LiteSpeed lama masih aktif | pisahkan docroot (subdomain), jangan menumpuk dengan WordPress |
| 403 setelah login sebagai Tata Usaha | bukan masalah server: memang tidak boleh membuka Pengaturan | login sebagai superadmin |

Cek cepat: upload `deploy/cpanel/cek-server.php` ke docroot, buka
`https://domain-anda.com/cek-server.php`, lalu **hapus kembali berkasnya** setelah selesai.

## Kalau halaman berjalan tapi tanpa tampilan

CSS/JS memakai path absolut (`/css/portal.css`), jadi aplikasi harus berada di akar
domain/subdomain. Kalau dipasang di subfolder, tampilan akan rusak — lebih aman
memakai subdomain.
