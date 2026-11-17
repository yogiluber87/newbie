<?php

namespace App\Http\Controllers;

use App\Models\Mutation;
use App\Models\User;
use App\Models\Group;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class SiswaImportController extends Controller
{
    /**
     * Tampilkan form impor CSV.
     */
    public function form()
    {
        return view('admin/siswa/import', [
            'title' => 'Impor Data Siswa',
        ]);
    }

    /**
     * Unduh template CSV.
     */
    public function template()
    {
        $rows = [
            ['nama_lengkap', 'nisn', 'nis', 'jenis_kelamin', 'email', 'nama_kelas', 'tahun_ajaran'],
            ['Budi Santoso', '1234567890', '1234', 'L', 'budi@email.com', '12 F3', '2025/2026'],
            ['Siti Aminah', '1234567891', '1235', 'P', '', '12 F3', '2025/2026'],
        ];

        // Excel dengan locale Indonesia memakai titik koma sebagai pemisah kolom.
        // BOM UTF-8 dipasang agar Excel membaca karakter Indonesia dengan benar.
        $out = "\xEF\xBB\xBF";
        foreach ($rows as $r) {
            $out .= implode(';', $r) . "\r\n";
        }

        return response($out, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="template-impor-siswa.csv"',
        ]);
    }

    /**
     * Proses berkas CSV yang diunggah.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('file');
        $src  = $file->getRealPath() ?: $file->getPathname();

        if (!$src || !file_exists($src) || !is_readable($src)) {
            return back()->with('error', 'Berkas tidak dapat dibaca. Silakan unggah ulang.');
        }

        $handle = fopen($src, 'r');
        if (!$handle) {
            return back()->with('error', 'Gagal membuka berkas CSV.');
        }

        // Deteksi BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        // Baca semua baris lalu deteksi pemisah (koma atau titik koma).
        // Excel berbahasa Indonesia menyimpan CSV memakai titik koma.
        $semuaBaris = [];
        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if (trim($line) === '') {
                continue;
            }
            $semuaBaris[] = $line;
        }

        if (!$semuaBaris) {
            fclose($handle);
            return back()->with('error', 'CSV kosong atau format tidak dikenali.');
        }

        $headerLine = str_replace("\xEF\xBB\xBF", '', $semuaBaris[0]);
        $pemisah    = (substr_count($headerLine, ';') > substr_count($headerLine, ',')) ? ';' : ',';

        $header = array_map(function ($h) {
            return strtolower(trim($h, " \t\""));
        }, str_getcsv($headerLine, $pemisah));

        $wajib  = ['nama_lengkap', 'nisn', 'nis', 'jenis_kelamin', 'nama_kelas', 'tahun_ajaran'];
        $hilang = array_diff($wajib, $header);
        if ($hilang) {
            fclose($handle);
            return back()->with('error', 'Kolom wajib tidak ditemukan: ' . implode(', ', $hilang) . '. Kolom terbaca: ' . implode(', ', $header));
        }

        $idx = array_flip($header);

        $berhasil = 0;
        $dilewati = 0;
        $gagal    = 0;
        $detail   = [];
        $baris    = 1;

        DB::beginTransaction();
        try {
            foreach (array_slice($semuaBaris, 1) as $line) {
                $data = str_getcsv($line, $pemisah);
                $baris++;

                // Lewati baris kosong
                if (count($data) === 1 && trim((string) $data[0]) === '') {
                    continue;
                }

                $nilai = function ($kolom) use ($data, $idx) {
                    $i = $idx[$kolom] ?? null;
                    return $i === null ? null : $this->bersihkanSel((string) ($data[$i] ?? ''));
                };

                $nama     = $nilai('nama_lengkap');
                $nisn     = $this->lengkapiNol((string) $nilai('nisn'), 'nisn');
                $nisAwal  = (string) $nilai('nis');
                $nis      = $this->lengkapiNol($nisAwal, 'nis');
                $jk       = strtoupper($nilai('jenis_kelamin'));
                $email    = $nilai('email');
                $kelas    = $nilai('nama_kelas');
                $tahun    = $nilai('tahun_ajaran');

                // Validasi per baris
                if ($nama === '' || $nisn === '' || $nis === '' || !in_array($jk, ['L', 'P']) || $kelas === '' || $tahun === '') {
                    $gagal++;
                    $detail[] = ['baris' => $baris, 'nama' => $nama ?: '-', 'status' => 'gagal', 'pesan' => 'Data wajib tidak lengkap atau jenis_kelamin bukan L/P'];
                    continue;
                }

                // Duplikat NISN atau NIS
                if (User::where('nisn', $nisn)->orWhere('nis', $nis)->exists()) {
                    $dilewati++;
                    $detail[] = ['baris' => $baris, 'nama' => $nama, 'status' => 'dilewati', 'pesan' => 'NISN/NIS sudah terdaftar'];
                    continue;
                }

                // Auto-buat kelas bila belum ada
                $group = Group::firstOrCreate(
                    ['nama' => $kelas],
                    ['uri' => Str::random(40)]
                );

                // Auto-buat tahun ajaran bila belum ada
                $year = Year::firstOrCreate(
                    ['tahun_ajaran' => $tahun],
                    ['status' => 'aktif']
                );

                $user = User::create([
                    'nama_lengkap'  => $nama,
                    'nisn'          => $nisn,
                    'nis'           => $nis,
                    'jenis_kelamin' => $jk,
                    'email'         => $email !== '' ? $email : null,
                    'group_id'      => $group->id,
                    'year_id'       => $year->id,
                    'status'        => 'siswa',
                    'password'      => Hash::make('siswa123'),
                ]);

                $user->ayah()->create(['uri' => Str::random(40)]);
                $user->ibu()->create(['uri' => Str::random(40)]);
                $user->biodata()->create(['uri' => Str::random(40)]);
                $user->wali()->create(['uri' => Str::random(40)]);

                $berhasil++;
                $detail[] = ['baris' => $baris, 'nama' => $nama, 'status' => 'berhasil', 'pesan' => 'Tersimpan'];
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            fclose($handle);
            return back()->with('error', 'Impor dibatalkan karena kesalahan: ' . $e->getMessage());
        }

        fclose($handle);

        return redirect('/admin/siswa')
            ->with('success', "Impor selesai: {$berhasil} berhasil, {$dilewati} dilewati (duplikat), {$gagal} gagal.")
            ->with('import_detail', $detail);
    }

    /* ============================================================
     |  IMPOR CSV LENGKAP (kebalikan dari Ekspor Semua -> CSV)
     |  Membuat siswa baru atau melengkapi siswa yang sudah ada
     |  berdasarkan NISN: biodata, ayah, ibu, wali, dan mutasi.
     * ============================================================ */

    /**
     * Form impor CSV lengkap.
     */
    public function formLengkap()
    {
        return view('admin/siswa/import_lengkap', [
            'title' => 'Impor CSV Lengkap',
        ]);
    }

    /**
     * Unduh contoh format CSV lengkap (sama dengan hasil "Ekspor Semua -> CSV").
     */
    public function templateLengkap()
    {
        $judul = $this->judulKolomLengkap();

        $contoh = [
            'nama_lengkap'  => 'Budi Santoso',
            'nisn'          => '1234567890',
            'nis'           => '1234',
            'jenis_kelamin' => 'L',
            'email'         => 'budi@email.com',
            'kelas'         => '12 F3',
            'tahun_ajaran'  => '2025/2026',
            'biodata_alamat'              => 'Jl. Melati No. 1',
            'biodata_kota'                => 'Padang',
            'biodata_anak_ke'             => '1',
            'biodata_nomor_hp'            => '081234567890',
            'ayah_nama'                   => 'Santoso',
            'ayah_pekerjaan'              => 'Petani',
            'ibu_nama'                    => 'Siti Aminah',
            'ibu_pekerjaan'               => 'Guru',
            'wali_nama'                   => '',
            'mutasi_tujuan'               => '',
            'mutasi_alasan'               => '',
            'mutasi_tanggal_pindah'       => '',
        ];

        $baris = [];
        foreach ($judul as $k) {
            $baris[] = (string) ($contoh[$k] ?? '');
        }

        $handle = fopen('php://temp', 'r+');
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $judul, ';', '"', '\\');
        fputcsv($handle, $baris, ';', '"', '\\');
        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="contoh-impor-csv-lengkap.csv"',
        ]);
    }

    /**
     * Proses impor CSV lengkap.
     */
    public function storeLengkap(Request $request)
    {
        $request->validate([
            'file'      => 'required|file|mimes:csv,txt|max:10240',
            'identitas' => 'nullable|boolean',
        ], [], [
            'file' => 'berkas CSV',
        ]);

        $perbaruiIdentitas = $request->boolean('identitas');

        $file = $request->file('file');
        $src  = $file->getRealPath() ?: $file->getPathname();

        if (!$src || !file_exists($src) || !is_readable($src)) {
            return back()->with('error', 'Berkas tidak dapat dibaca. Silakan unggah ulang.');
        }

        $handle = fopen($src, 'r');
        if (!$handle) {
            return back()->with('error', 'Gagal membuka berkas CSV.');
        }

        // Deteksi BOM UTF-8
        $bom = fread($handle, 3);
        if ($bom !== "\xEF\xBB\xBF") {
            rewind($handle);
        }

        $semuaBaris = [];
        while (($line = fgets($handle)) !== false) {
            $line = rtrim($line, "\r\n");
            if (trim($line) === '') {
                continue;
            }
            $semuaBaris[] = $line;
        }
        fclose($handle);

        if (!$semuaBaris) {
            return back()->with('error', 'CSV kosong atau format tidak dikenali.');
        }

        $headerLine = str_replace("\xEF\xBB\xBF", '', $semuaBaris[0]);
        $pemisah    = (substr_count($headerLine, ';') > substr_count($headerLine, ',')) ? ';' : ',';

        $header = array_map(function ($h) {
            return strtolower(trim($h, " \t\""));
        }, str_getcsv($headerLine, $pemisah));

        $wajib  = ['nama_lengkap', 'nisn'];
        $hilang = array_diff($wajib, $header);
        if ($hilang) {
            return back()->with('error', 'Kolom wajib tidak ditemukan: ' . implode(', ', $hilang) . '. Gunakan berkas hasil "Ekspor Semua -> Format CSV".');
        }

        // Peta indeks kolom: identitas + bagian ber-prefix
        $idx = [];
        foreach (['nama_lengkap', 'nisn', 'nis', 'jenis_kelamin', 'email', 'kelas', 'tahun_ajaran'] as $k) {
            $idx[$k] = array_search($k, $header, true);
            if ($idx[$k] === false) {
                $idx[$k] = null;
            }
        }

        // Alias kolom kelas dari impor CSV biasa
        if ($idx['kelas'] === null) {
            $alias = array_search('nama_kelas', $header, true);
            $idx['kelas'] = $alias === false ? null : $alias;
        }

        $daftarKolom = [
            'biodata' => SiswaDataController::KOLOM_BIODATA,
            'ayah'    => SiswaDataController::KOLOM_ORANGTUA,
            'ibu'     => SiswaDataController::KOLOM_ORANGTUA,
            'wali'    => SiswaDataController::KOLOM_WALI,
            'mutasi'  => SiswaDataController::KOLOM_MUTASI,
        ];

        $peta = [];
        foreach ($daftarKolom as $bagian => $kolom) {
            $peta[$bagian] = [];
        }

        foreach ($header as $i => $h) {
            foreach ($daftarKolom as $bagian => $kolom) {
                $awalan = $bagian . '_';

                if (strpos($h, $awalan) !== 0) {
                    continue;
                }

                $namaKolom = substr($h, strlen($awalan));

                if (in_array($namaKolom, $kolom, true)) {
                    $peta[$bagian][$namaKolom] = $i;
                }
            }
        }

        $baru      = 0;
        $diperbarui = 0;
        $gagal     = 0;
        $detail    = [];
        $baris     = 1;

        foreach (array_slice($semuaBaris, 1) as $line) {
            $data  = str_getcsv($line, $pemisah);
            $baris++;

            $ambil = function ($kunci) use ($data, $idx) {
                $i = $idx[$kunci] ?? null;

                return $i === null ? '' : $this->bersihkanSel((string) ($data[$i] ?? ''));
            };

            $nama = $ambil('nama_lengkap');
            $nisn = $this->lengkapiNol($ambil('nisn'), 'nisn');

            if ($nisn === '') {
                $gagal++;
                $detail[] = ['baris' => $baris, 'nama' => $nama ?: '-', 'status' => 'gagal', 'pesan' => 'NISN kosong'];
                continue;
            }

            DB::beginTransaction();

            try {
                $siswa = User::where('nisn', $nisn)->first();
                $isBaru = false;

                if (!$siswa) {
                    if ($nama === '') {
                        throw new \RuntimeException('Nama siswa wajib diisi untuk siswa baru');
                    }

                    $nis = $this->lengkapiNol($ambil('nis'), 'nis') ?: $nisn;

                    if (User::where('nis', $nis)->exists()) {
                        throw new \RuntimeException('NIS ' . $nis . ' sudah dipakai siswa lain');
                    }

                    $kelasNama = $ambil('kelas') ?: 'Tanpa Kelas';
                    $kelas = Group::firstOrCreate(['nama' => $kelasNama], ['uri' => Str::random(40)]);

                    $tahunNama = $ambil('tahun_ajaran');
                    $tahun = $tahunNama !== ''
                        ? Year::firstOrCreate(['tahun_ajaran' => $tahunNama], ['status' => 'tidak aktif'])
                        : (Year::where('status', 'aktif')->first() ?: Year::firstOrCreate(['tahun_ajaran' => '-'], ['status' => 'tidak aktif']));

                    $jk = strtoupper($ambil('jenis_kelamin'));
                    $email = $ambil('email');

                    if ($email !== '' && User::where('email', $email)->exists()) {
                        $email = '';
                    }

                    $siswa = User::create([
                        'nama_lengkap'  => $nama,
                        'nisn'          => $nisn,
                        'nis'           => $nis,
                        'jenis_kelamin' => in_array($jk, ['L', 'P'], true) ? $jk : 'L',
                        'email'         => $email !== '' ? $email : null,
                        'group_id'      => $kelas->id,
                        'year_id'       => $tahun->id,
                        'status'        => 'siswa',
                        'password'      => Hash::make('siswa123'),
                    ]);

                    $isBaru = true;
                }

                // Identitas siswa lama (opsional)
                if (!$isBaru && $perbaruiIdentitas) {
                    if ($nama !== '') {
                        $siswa->nama_lengkap = $nama;
                    }

                    $jk = strtoupper($ambil('jenis_kelamin'));
                    if (in_array($jk, ['L', 'P'], true)) {
                        $siswa->jenis_kelamin = $jk;
                    }

                    $email = $ambil('email');
                    if ($email !== '' && !User::where('email', $email)->where('id', '!=', $siswa->id)->exists()) {
                        $siswa->email = $email;
                    }

                    $kelasNama = $ambil('kelas');
                    if ($kelasNama !== '') {
                        $kelas = Group::firstOrCreate(['nama' => $kelasNama], ['uri' => Str::random(40)]);
                        $siswa->group_id = $kelas->id;
                    }

                    $tahunNama = $ambil('tahun_ajaran');
                    if ($tahunNama !== '') {
                        $tahun = Year::firstOrCreate(['tahun_ajaran' => $tahunNama], ['status' => 'tidak aktif']);
                        $siswa->year_id = $tahun->id;
                    }

                    $siswa->save();
                }

                // Biodata, ayah, ibu, wali
                foreach (['biodata', 'ayah', 'ibu', 'wali'] as $bagian) {
                    if (!$peta[$bagian]) {
                        continue;
                    }

                    $nilai = [];

                    foreach ($peta[$bagian] as $kolom => $i) {
                        $isi = $this->bersihkanSel((string) ($data[$i] ?? ''));

                        if (in_array($kolom, SiswaDataController::KOLOM_ANGKA_BIODATA, true)) {
                            $nilai[$kolom] = $isi === '' ? null : (int) $isi;
                            continue;
                        }

                        if ($bagian !== 'biodata' && $kolom === 'penghasilan') {
                            $nilai[$kolom] = $isi === '' ? null : (int) $isi;
                            continue;
                        }

                        $nilai[$kolom] = $isi === '' ? null : $isi;
                    }

                    $this->simpanRelasi($siswa, $bagian, $nilai);
                }

                // Mutasi (menggantikan mutasi lama bila kolomnya ada di berkas)
                if ($peta['mutasi']) {
                    Mutation::where('user_id', $siswa->id)->delete();

                    // Ekspor masal menggabungkan beberapa mutasi dengan " | ", jadi dipecah lagi di sini
                    $pecah = [];
                    foreach ($peta['mutasi'] as $kolom => $i) {
                        $isi = $this->bersihkanSel((string) ($data[$i] ?? ''));
                        $pecah[$kolom] = $isi === '' ? [] : array_map('trim', preg_split('/\s*\|\s*/', $isi));
                    }

                    $jumlahMutasi = 0;
                    foreach ($pecah as $daftarNilai) {
                        $jumlahMutasi = max($jumlahMutasi, count($daftarNilai));
                    }

                    for ($n = 0; $n < $jumlahMutasi; $n++) {
                        $nilai = [];

                        foreach (SiswaDataController::KOLOM_MUTASI as $kolom) {
                            $isi = $pecah[$kolom][$n] ?? null;
                            $nilai[$kolom] = ($isi === null || $isi === '') ? null : $isi;
                        }

                        if (array_filter($nilai, function ($v) { return $v !== null; }) === []) {
                            continue;
                        }

                        if (!empty($nilai['tanggal_pindah'])) {
                            try {
                                $nilai['tanggal_pindah'] = \Illuminate\Support\Carbon::parse($nilai['tanggal_pindah'])->format('Y-m-d');
                            } catch (\Throwable $e) {
                                $nilai['tanggal_pindah'] = null;
                            }
                        }

                        $siswa->mutasi()->create($nilai);
                    }
                }

                DB::commit();

                if ($isBaru) {
                    $baru++;
                    $detail[] = ['baris' => $baris, 'nama' => $nama, 'status' => 'baru', 'pesan' => 'Siswa baru dibuat'];
                } else {
                    $diperbarui++;
                    $detail[] = ['baris' => $baris, 'nama' => $nama, 'status' => 'diperbarui', 'pesan' => 'Data dilengkapi'];
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $gagal++;
                $detail[] = ['baris' => $baris, 'nama' => $nama ?: '-', 'status' => 'gagal', 'pesan' => $e->getMessage()];
            }
        }

        return redirect('/admin/siswa/import-lengkap')
            ->with('success', "Impor CSV lengkap selesai: {$baru} siswa baru, {$diperbarui} diperbarui, {$gagal} gagal.")
            ->with('import_detail', $detail);
    }

    /**
     * Simpan biodata/ayah/ibu/wali tanpa mengubah "uri" yang sudah ada.
     */
    private function simpanRelasi(User $siswa, string $relasi, array $nilai): void
    {
        if ($nilai === []) {
            return;
        }

        $model = $siswa->{$relasi}()->first();

        if ($model) {
            $model->fill($nilai)->save();

            return;
        }

        $siswa->{$relasi}()->create(array_merge($nilai, ['uri' => Str::random(40)]));
    }

    /**
     * Susun daftar judul kolom untuk contoh format CSV lengkap.
     */
    private function judulKolomLengkap(): array
    {
        $judul = SiswaDataController::KOLOM_IDENTITAS;

        $bagian = [
            'biodata' => SiswaDataController::KOLOM_BIODATA,
            'ayah'    => SiswaDataController::KOLOM_ORANGTUA,
            'ibu'     => SiswaDataController::KOLOM_ORANGTUA,
            'wali'    => SiswaDataController::KOLOM_WALI,
            'mutasi'  => SiswaDataController::KOLOM_MUTASI,
        ];

        foreach ($bagian as $nama => $kolom) {
            foreach ($kolom as $k) {
                $judul[] = $nama . '_' . $k;
            }
        }

        return $judul;
    }

    /**
     * Bersihkan isi sel CSV dari penanda teks Excel.
     * Contoh: ="0120568897" menjadi 0120568897, '0130 menjadi 0130.
     */
    private function bersihkanSel(string $isi): string
    {
        $isi = trim($isi);

        // Ekspor aplikasi menulis NISN/NIS/nomor HP sebagai ="..." agar nol di depan aman
        if (preg_match('/^="(.*)"$/s', $isi, $cocok)) {
            $isi = trim($cocok[1]);
        }

        // Cara lain Excel menandai teks: apostrof di depan angka
        if ($isi !== '' && $isi[0] === "'") {
            $isi = ltrim($isi, "'");
        }

        return trim($isi);
    }

    /**
     * Kembalikan angka nol di depan NISN/NIS yang hilang (mis. karena Excel).
     * Caranya: cocokkan dulu dengan data yang sudah ada ("12" -> "012"),
     * kalau belum ada samakan panjangnya dengan data terbanyak.
     */
    private function lengkapiNol(string $nilai, string $kolom): string
    {
        $nilai = trim($nilai);

        if ($nilai === '' || !preg_match('/^[0-9]+$/', $nilai)) {
            return $nilai;
        }

        // 1. Sudah sama persis dengan data yang ada
        if (User::where($kolom, $nilai)->exists()) {
            return $nilai;
        }

        // 2. Cari data yang sudah ada dengan angka sama setelah ditambah nol di depan
        for ($panjang = strlen($nilai) + 1; $panjang <= 12; $panjang++) {
            $calon = str_pad($nilai, $panjang, '0', STR_PAD_LEFT);

            if (User::where($kolom, $calon)->exists()) {
                return $calon;
            }
        }

        // 3. Belum ada padanannya: ikuti panjang yang paling banyak dipakai
        $umum = $this->panjangUmum($kolom);

        if ($umum > strlen($nilai)) {
            return str_pad($nilai, $umum, '0', STR_PAD_LEFT);
        }

        return $nilai;
    }

    /**
     * Panjang NISN/NIS yang paling banyak dipakai pada data siswa saat ini.
     */
    private function panjangUmum(string $kolom): int
    {
        static $cache = [];

        if (isset($cache[$kolom])) {
            return $cache[$kolom];
        }

        $jumlah = [];

        foreach (User::where('status', 'siswa')->whereNotNull($kolom)->pluck($kolom) as $nilai) {
            $nilai = (string) $nilai;

            if ($nilai !== '' && preg_match('/^[0-9]+$/', $nilai)) {
                $jumlah[strlen($nilai)] = ($jumlah[strlen($nilai)] ?? 0) + 1;
            }
        }

        $cache[$kolom] = $jumlah ? (int) array_search(max($jumlah), $jumlah, true) : 0;

        return $cache[$kolom];
    }
}
