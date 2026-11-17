<?php

namespace App\Http\Controllers;

use App\Models\Mutation;
use App\Models\User;
use Illuminate\Http\Request;

class SiswaDataController extends Controller
{
    /**
     * Daftar kolom yang dipakai untuk ekspor & impor (kolom lain tidak disentuh).
     */
    public const KOLOM_BIODATA = [
        'alamat', 'sekolah_asal', 'kota', 'kecamatan', 'tempat_lahir', 'tanggal_lahir',
        'anak_ke', 'jlh_saudara', 'saudara_tiri', 'saudara_angkat', 'bahasa', 'agama',
        'jarak', 'nomor_hp', 'goldar', 'tinggi', 'berat', 'penyakit', 'hobi', 'kewarganegaraan',
    ];

    public const KOLOM_ORANGTUA = [
        'nama', 'tempat_lahir', 'tanggal_lahir', 'agama', 'kewarganegaraan',
        'pekerjaan', 'pendidikan', 'penghasilan', 'alamat', 'nomor_hp', 'status',
    ];

    public const KOLOM_WALI = [
        'nama', 'tempat_lahir', 'tanggal_lahir', 'agama', 'kewarganegaraan',
        'pekerjaan', 'pendidikan', 'penghasilan', 'alamat', 'nomor_hp',
    ];

    public const KOLOM_MUTASI = ['tujuan', 'alasan', 'tanggal_pindah'];

    /**
     * Kolom biodata yang disimpan sebagai angka (bukan teks).
     */
    public const KOLOM_ANGKA_BIODATA = [
        'anak_ke', 'jlh_saudara', 'saudara_tiri', 'saudara_angkat', 'jarak', 'tinggi', 'berat',
    ];

    /**
     * Kolom berisi angka yang nol di depannya harus tetap utuh (NISN, NIS, nomor HP).
     * Ditulis sebagai teks Excel (="...") supaya Excel tidak memangkas nol di depan.
     */
    private const KOLOM_JAGA_NOL = ['nisn', 'nis', 'nomor_hp'];

    /**
     * Kolom berisi tanggal. Ditulis sebagai teks (="dd/mm/yyyy") supaya Excel
     * menampilkannya sebagai tanggal yang terbaca, bukan angka serial (39598).
     */
    private const KOLOM_TANGGAL = ['tanggal_lahir'];

    /**
     * Kolom identitas siswa untuk berkas CSV.
     */
    public const KOLOM_IDENTITAS = [
        'nama_lengkap', 'nisn', 'nis', 'jenis_kelamin', 'email', 'kelas', 'tahun_ajaran',
    ];

    /**
     * Unduh data satu siswa dalam bentuk CSV (satu baris) dari halaman detail siswa.
     */
    public function exportSatuCsv(User $siswa)
    {
        $siswa->load(['biodata', 'ayah', 'ibu', 'wali', 'kelas', 'tahun_ajar']);

        $mutasi = Mutation::where('user_id', $siswa->id)->orderBy('tanggal_pindah')->get()->groupBy('user_id');

        return $this->unduhCsv(
            $this->buatCsv(collect([$siswa]), $mutasi),
            'data-siswa-' . $siswa->nisn . '.csv'
        );
    }

    /**
     * Unduh seluruh data siswa dalam satu berkas CSV (satu baris = satu siswa).
     */
    public function exportSemuaCsv(Request $request)
    {
        $semua = $this->querySiswa($request)->get();

        if ($semua->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang bisa diekspor.');
        }

        $mutasi = Mutation::whereIn('user_id', $semua->pluck('id'))->orderBy('tanggal_pindah')->get()->groupBy('user_id');

        return $this->unduhCsv($this->buatCsv($semua, $mutasi), 'data-siswa-semua-' . now()->format('Ymd-His') . '.csv');
    }

    /**
     * Ekspor CSV siswa yang dipilih (dicentang di halaman Daftar Siswa) atau hasil pencarian.
     */
    public function exportPilihan(Request $request)
    {
        $request->validate([
            'nisn'   => 'required_without_all:cari,tempat_tinggal,nis,group_id,year_id,jenis_kelamin,status_mutasi,mutasi_dari,mutasi_sampai,penghasilan_min,penghasilan_max|array',
            'nisn.*' => 'string',
            'cari'   => 'nullable|string|max:100',
            'nis'    => 'nullable|string|max:100',
            'tempat_tinggal' => 'nullable|string|max:100',
            'group_id' => 'nullable|integer',
            'year_id'  => 'nullable|integer',
            'penghasilan_min' => 'nullable|numeric',
            'penghasilan_max' => 'nullable|numeric',
            'jenis_kelamin' => 'nullable|in:L,P',
            'status_mutasi' => 'nullable|in:sudah,belum',
            'mutasi_dari' => 'nullable|date',
            'mutasi_sampai' => 'nullable|date',
        ], [], [
            'nisn' => 'pilihan siswa',
            'cari' => 'kata kunci',
        ]);

        $query = $this->querySiswa($request);

        if ($request->filled('nisn')) {
            $query->whereIn('nisn', $request->nisn);
        }

        $siswa = $query->get();

        if ($siswa->isEmpty()) {
            return back()->with('error', 'Siswa yang dipilih tidak ditemukan.');
        }

        $mutasi = Mutation::whereIn('user_id', $siswa->pluck('id'))
            ->orderBy('tanggal_pindah')
            ->get()
            ->groupBy('user_id');

        return $this->unduhCsv($this->buatCsv($siswa, $mutasi), 'data-siswa-terpilih-' . now()->format('Ymd-His') . '.csv');
    }

    /**
     * Query daftar siswa; ikut semua filter halaman Daftar Siswa bila ada.
     */
    private function querySiswa(Request $request)
    {
        $filters = User::filtersDari($request->all());

        $query = User::query()
            ->daftarSiswa($filters)
            ->with(['biodata', 'ayah', 'ibu', 'wali', 'kelas', 'tahun_ajar']);

        return $query->orderBy('nama_lengkap');
    }

    /**
     * Susun isi berkas CSV dari sekumpulan siswa.
     */
    private function buatCsv($semua, $mutasi): string
    {
        // Susun baris judul kolom
        $judul = self::KOLOM_IDENTITAS;

        foreach (['biodata' => self::KOLOM_BIODATA, 'ayah' => self::KOLOM_ORANGTUA, 'ibu' => self::KOLOM_ORANGTUA, 'wali' => self::KOLOM_WALI] as $bagian => $kolom) {
            foreach ($kolom as $k) {
                $judul[] = $bagian . '_' . $k;
            }
        }

        $judul[] = 'mutasi_tujuan';
        $judul[] = 'mutasi_alasan';
        $judul[] = 'mutasi_tanggal_pindah';

        // Tulis CSV ke buffer sementara
        $handle = fopen('php://temp', 'r+');

        // BOM UTF-8 + titik koma agar terbaca benar di Excel berbahasa Indonesia
        fwrite($handle, "\xEF\xBB\xBF");
        fputcsv($handle, $judul, ';', '"', '\\');

        foreach ($semua as $siswa) {
            $arsip = $this->susunArsip($siswa, $mutasi->get($siswa->id, collect()));

            $baris = [];

            foreach (self::KOLOM_IDENTITAS as $k) {
                $baris[] = $this->nilaiCsv($arsip['siswa'][$k] ?? null, $k);
            }

            foreach (['biodata' => self::KOLOM_BIODATA, 'ayah' => self::KOLOM_ORANGTUA, 'ibu' => self::KOLOM_ORANGTUA, 'wali' => self::KOLOM_WALI] as $bagian => $kolom) {
                foreach ($kolom as $k) {
                    $baris[] = $this->nilaiCsv($arsip[$bagian][$k] ?? null, $k);
                }
            }

            // Mutasi bisa lebih dari satu, digabung dengan pemisah " | "
            $baris[] = $this->nilaiCsv(collect($arsip['mutasi'])->pluck('tujuan')->filter()->implode(' | '));
            $baris[] = $this->nilaiCsv(collect($arsip['mutasi'])->pluck('alasan')->filter()->implode(' | '));
            $baris[] = $this->nilaiCsv(collect($arsip['mutasi'])->pluck('tanggal_pindah')->filter()
                ->map(function ($tanggal) {
                    return $this->tanggalTampil((string) $tanggal);
                })
                ->implode(' | '));

            fputcsv($handle, $baris, ';', '"', '\\');
        }

        rewind($handle);
        $csv = stream_get_contents($handle);
        fclose($handle);

        return $csv;
    }

    /**
     * Ubah isi CSV menjadi berkas unduhan.
     */
    private function unduhCsv(string $csv, string $namaBerkas)
    {
        return response($csv, 200, [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $namaBerkas . '"',
        ]);
    }

    /**
     * Ubah nilai menjadi teks yang aman untuk satu sel CSV.
     */
    private function nilaiCsv($nilai, string $kolom = ''): string
    {
        if ($nilai === null) {
            return '';
        }

        if (is_bool($nilai)) {
            return $nilai ? '1' : '0';
        }

        $teks = (string) $nilai;

        // Tanggal: tampilkan dd/mm/yyyy dan kunci sebagai teks agar Excel tidak
        // mengubahnya menjadi angka serial (mis. 2008-05-30 -> 39598).
        if ($teks !== '' && in_array($kolom, self::KOLOM_TANGGAL, true)) {
            return '="' . $this->tanggalTampil($teks) . '"';
        }

        // NISN, NIS, dan nomor HP: tulis sebagai teks Excel agar nol di depan tidak hilang
        // saat berkas dibuka/diubah di Excel (="0120568897").
        if ($teks !== '' && in_array($kolom, self::KOLOM_JAGA_NOL, true) && preg_match('/^[0-9+]+$/', $teks)) {
            return '="' . $teks . '"';
        }

        return $teks;
    }

    /**
     * Ubah tanggal dari database (Y-m-d) menjadi dd/mm/yyyy untuk dibaca manusia.
     * Nilai yang bukan tanggal dikembalikan apa adanya.
     */
    private function tanggalTampil(string $nilai): string
    {
        $nilai = trim($nilai);

        try {
            return \Illuminate\Support\Carbon::parse($nilai)->format('d/m/Y');
        } catch (\Throwable $e) {
            return $nilai;
        }
    }

    /**
     * Susun satu arsip siswa (identitas + biodata + ayah + ibu + wali + mutasi).
     */
    private function susunArsip(User $siswa, $mutasi): array
    {
        return [
            'siswa'   => [
                'nama_lengkap'  => $siswa->nama_lengkap,
                'nisn'          => $siswa->nisn,
                'nis'           => $siswa->nis,
                'jenis_kelamin' => $siswa->jenis_kelamin,
                'email'         => $siswa->email,
                'kelas'         => optional($siswa->kelas)->nama,
                'tahun_ajaran'  => optional($siswa->tahun_ajar)->tahun_ajaran,
            ],
            'biodata' => $siswa->biodata ? $this->ambilKolom($siswa->biodata, self::KOLOM_BIODATA) : null,
            'ayah'    => $siswa->ayah ? $this->ambilKolom($siswa->ayah, self::KOLOM_ORANGTUA) : null,
            'ibu'     => $siswa->ibu ? $this->ambilKolom($siswa->ibu, self::KOLOM_ORANGTUA) : null,
            'wali'    => $siswa->wali ? $this->ambilKolom($siswa->wali, self::KOLOM_WALI) : null,
            'mutasi'  => collect($mutasi)->map(function ($m) {
                return $this->ambilKolom($m, self::KOLOM_MUTASI);
            })->values()->all(),
        ];
    }

    /**
     * Ambil nilai kolom tertentu dari sebuah model.
     */
    private function ambilKolom($model, array $kolom): array
    {
        $hasil = [];

        foreach ($kolom as $k) {
            $hasil[$k] = $model->{$k};
        }

        return $hasil;
    }
}
