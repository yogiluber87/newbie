<?php

namespace App\Http\Controllers;

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
                    return $i === null ? null : trim((string) ($data[$i] ?? ''));
                };

                $nama     = $nilai('nama_lengkap');
                $nisn     = $nilai('nisn');
                $nis      = $nilai('nis');
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
}
