# Changelog

Semua perubahan penting pada aplikasi **Buku Induk Siswa** (SMA Negeri 2 Lintau Buo) dicatat di berkas ini.

Format mengikuti [Keep a Changelog](https://keepachangelog.com/id/1.1.0/) dan penomoran versi mengikuti [Semantic Versioning](https://semver.org/lang/id/).
Urutan bagian: **Ditambahkan**, **Diubah**, **Diperbaiki**, **Keamanan**, **Catatan teknis**.

## [Belum dirilis]

Belum ada perubahan.

## [1.3.0] - 2026-11-18

### Ditambahkan

- **Peran pengguna**: `superadmin` dan `tata_usaha`. Tata Usaha dapat memakai semua menu kecuali Pengaturan dan pengelolaan akun; Superadmin mendapat akses penuh.
- **Pengelolaan akun petugas** di `/admin/akun` (daftar, tambah, ubah) khusus superadmin, dengan pengaman: tidak bisa menghapus akun sendiri maupun superadmin terakhir.
- **Kop cetak PDF** mengikuti kop surat resmi: gambar kiri, tiga baris instansi/dinas/sekolah, baris kontak miring, gambar kanan, dan garis ganda.
- **Unggah gambar kop** (kiri & kanan) beserta pratinjau langsung di halaman Pengaturan, hanya untuk superadmin.
- **Preview cetak PDF** di dalam aplikasi untuk daftar siswa, buku induk satu siswa, serta buku induk siswa terpilih/hasil pencarian. Tombol **Cetak**, **Unduh PDF**, dan **Tab Baru** tersedia di halaman preview.
- **Tema tampilan baru** mengikuti portal `tka.kemendikdasmen.go.id`: sidebar putih, header atas, page-band, panel, statistik kartu, tabel, dan sidebar off-canvas untuk layar kecil.

### Diubah

- Status pengguna lama `admin` otomatis dianggap **superadmin**; migrasi `upgrade_roles_to_superadmin` mengubah nilai yang tersimpan.
- Halaman Pengaturan dan Akun Pengguna dibatasi middleware `superadmin`.
- Semua tombol cetak (sidebar, dashboard, daftar siswa, detail siswa, beranda siswa) diarahkan ke halaman preview.
- Judul halaman ganda pada seluruh tampilan admin dan siswa dibersihkan.

### Diperbaiki

- Penamaan tombol cetak yang sebelumnya langsung mengunduh tanpa pratinjau.
- Beberapa tes yang bergantung pada urutan/isi data dibuat deterministik.

### Catatan teknis

- Database uji terpisah `db_data_induk_test` (`phpunit.xml` + `.env.testing`); seluruh tes tidak lagi menyentuh database asli.
- `RefreshDatabase` dihapus dari `ExampleTest` karena trait tersebut menjalankan `migrate:fresh`.
- 54 tes / 272 assertion lulus.

## [1.2.0] - 2026-11-17

### Ditambahkan

- **Pengaturan sekolah**: nama sekolah, alamat, dan logo yang dipakai di sidebar, favicon, dan kop cetak.
- **Ekspor CSV** untuk siswa terpilih maupun seluruh hasil pencarian.
- **Impor CSV lengkap**: biodata, data ayah, ibu, wali, dan mutasi dalam satu berkas.
- **Ekspor PDF buku induk**: satu siswa per berkas maupun banyak siswa dalam satu berkas, lengkap dengan tanggal cetak dan nomor halaman.
- Halaman detail siswa yang diperluas: ringkasan biodata, orang tua/wali, mutasi, serta tombol aksi cepat.

### Diperbaiki

- Stabilitas data: validasi data wali dan orang tua, perlindungan terhadap penghapusan data yang masih terpakai, dan penanganan mutasi.
- Penanganan tanggal pada impor CSV.

### Keamanan

- Pengecekan hak akses pada aksi cetak dan ekspor data siswa.

## [1.1.0] - 2026-11-15

### Ditambahkan

- **Impor data siswa via CSV** beserta tautan unduh template.
- **Pengecekan NISN/NIS duplikat** saat kolom diisi dan saat data disimpan (tambah maupun edit siswa).

### Diperbaiki

- Proses login dan pengalihan setelah masuk sesuai peran.
- Validasi pindah kelas serta validasi kelas dan tahun ajaran.
- Perlindungan penghapusan kelas dan tahun ajaran yang masih dipakai.
- Upload foto siswa.

### Keamanan

- Berkas `.env` berhenti dilacak repository agar kredensial tidak ikut tersimpan.

### Catatan teknis

- Template CSV memakai pemisah titik koma agar terbaca langsung di Excel berlokal Indonesia.

## [1.0.0] - 2025-09-07

### Ditambahkan

- Rilis awal aplikasi Buku Induk Siswa.
- Autentikasi dan pembatasan akses halaman admin serta siswa.
- Data siswa: biodata, data ayah, ibu, dan wali.
- Data mutasi siswa, daftar kelas, dan tahun ajaran.
- Halaman siswa untuk melihat dan melengkapi data induknya.
- Cetak buku induk siswa.

---

## Pemetaan versi

| Versi | Tanggal | Commit terakhir |
| ----- | ------- | --------------- |
| 1.3.0 | 2026-11-18 | `1c29f92` |
| 1.2.0 | 2026-11-17 | `de85803` |
| 1.1.0 | 2026-11-15 | `0a68d51` |
| 1.0.0 | 2025-09-07 | `dd0a011` |
