<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * Peran (role) akun pengguna. Disimpan di kolom status.
     *
     * - superadmin : akses penuh, termasuk Pengaturan dan pembuatan akun Tata Usaha
     * - tata_usaha : akses penuh ke data siswa/kelas/tahun/mutasi/cetak,
     *                kecuali Pengaturan dan pengelolaan akun
     * - siswa      : hanya melihat dan melengkapi datanya sendiri
     *
     * Nilai 'admin' lama tetap diterima dan diperlakukan sebagai superadmin.
     */
    public const ROLE_SUPERADMIN = 'superadmin';
    public const ROLE_TATA_USAHA = 'tata_usaha';
    public const ROLE_SISWA = 'siswa';

    /**
     * Peran yang boleh masuk ke area admin (dashboard & data siswa).
     */
    public const STAFF_ROLES = ['superadmin', 'tata_usaha', 'admin'];

    /**
     * Pilihan peran untuk form pengelolaan akun.
     */
    public const FORM_ROLES = [
        self::ROLE_SUPERADMIN => 'Superadmin',
        self::ROLE_TATA_USAHA => 'Tata Usaha',
    ];

    protected $guarded = ['id'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama_lengkap',
        'nisn',
        'nis',
        'foto',
        'email',
        'jenis_kelamin',
        'password',
        'status',
        'year_id',
        'group_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
    ];
    protected $with = [
        'ayah',
        'ibu',
        'biodata',
        'wali',
        'kelas',
        'mutasi',
        'tahun_ajar',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    public function ayah(){
        return $this->hasOne(Dad::class);
    }

    public function ibu(){
        return $this->hasOne(Mom::class);
    }

    public function biodata(){
        return $this->hasOne(Biodata::class);
    }

    public function wali(){
        return $this->hasOne(Guardian::class);
    }

    public function kelas(){
        return $this->belongsTo(Group::class, 'group_id');
    }

    public function tahun_ajar(){
        return $this->belongsTo(Year::class, 'year_id');
    }

    public function mutasi(){
        return $this->hasOne(Mutation::class);
    }

    public function getRouteKeyName(){
        return 'nisn';
    }

    /**
     * Apakah akun ini superadmin (termasuk akun 'admin' versi lama)?
     */
    public function isSuperadmin(): bool
    {
        return in_array($this->status, [self::ROLE_SUPERADMIN, 'admin'], true);
    }

    /**
     * Apakah akun ini tata usaha?
     */
    public function isTataUsaha(): bool
    {
        return $this->status === self::ROLE_TATA_USAHA;
    }

    /**
     * Apakah akun ini termasuk petugas (superadmin atau tata usaha)?
     * Dipakai untuk membedakan petugas dari siswa.
     */
    public function isStaff(): bool
    {
        return in_array($this->status, self::STAFF_ROLES, true);
    }

    /**
     * Nama peran untuk ditampilkan di halaman.
     */
    public function roleLabel(): string
    {
        if ($this->isSuperadmin()) {
            return 'Superadmin';
        }

        if ($this->isTataUsaha()) {
            return 'Tata Usaha';
        }

        return 'Siswa';
    }

    /**
     * Daftar kunci filter yang dikenali scopeFilter().
     * Dipakai juga oleh halaman Daftar Siswa, ekspor CSV, dan cetak PDF
     * supaya hasil filter selalu sama dengan yang tampil di layar.
     */
    public const FILTER_KEYS = [
        'cari',             // kata kunci umum: nama, NISN, NIS
        'nisn',             // NISN
        'nis',              // NIS
        'tempat_tinggal',   // kota / kecamatan / alamat
        'penghasilan_min',  // rentang penghasilan orang tua
        'penghasilan_max',
        'group_id',         // kelas
        'year_id',          // tahun masuk / tahun ajaran saat diterima
        'jenis_kelamin',    // L / P (hanya dua nilai ini; impor CSV juga dinormalkan ke L/P)
        'status_mutasi',    // sudah / belum mutasi
        'mutasi_dari',      // rentang tanggal pindah (mutasi)
        'mutasi_sampai',
    ];

    /**
     * Filter yang isinya tanggal (format Y-m-d).
     */
    public const FILTER_TANGGAL = ['mutasi_dari', 'mutasi_sampai'];

    /**
     * Filter yang isinya angka (uang / penghasilan).
     */
    public const FILTER_ANGKA = ['penghasilan_min', 'penghasilan_max'];

    /**
     * Ubah nilai tanggal filter (Y-m-d) menjadi format baca 31/12/2025.
     * Mengembalikan null bila kosong atau bukan tanggal yang valid.
     */
    public static function formatTanggalFilter($nilai): ?string
    {
        $tanggal = self::normalisasiTanggalFilter($nilai);

        return $tanggal ? \DateTime::createFromFormat('!Y-m-d', $tanggal)->format('d/m/Y') : null;
    }

    /**
     * Normalkan tanggal filter ke Y-m-d. Menerima 2025-06-15, 15/06/2025,
     * atau 15-06-2025. Mengembalikan null bila tanggalnya tidak valid.
     */
    public static function normalisasiTanggalFilter($nilai): ?string
    {
        $nilai = is_string($nilai) ? trim($nilai) : '';

        if ($nilai === '') {
            return null;
        }

        foreach (['Y-m-d', 'd/m/Y', 'd-m-Y'] as $format) {
            $tanggal = \DateTime::createFromFormat('!' . $format, $nilai);

            if ($tanggal && $tanggal->format($format) === $nilai) {
                return $tanggal->format('Y-m-d');
            }
        }

        return null;
    }

    /**
     * Ubah teks menjadi pola LIKE yang aman.
     *
     * Karakter khusus LIKE (%, _, dan \\) di-escape supaya dicari apa adanya:
     * mencari "100%" tidak berubah menjadi "semua data", dan "_" tidak
     * mencocokkan sembarang satu huruf.
     */
    private static function polaLike(string $nilai): string
    {
        $aman = str_replace(['\\', '%', '_'], ['\\\\', '\%', '\_'], $nilai);

        return '%' . $aman . '%';
    }

    /**
     * Bersihkan satu nilai filter mentah menjadi nilai yang siap dipakai query.
     *
     * Mengembalikan null bila nilai harus diabaikan: array/objek (mis. nisn[]
     * dari tombol "Cetak Terpilih"), teks kosong, tanggal tidak valid, atau
     * angka yang tidak valid. Dipakai bersama oleh filtersDari() dan
     * scopeFilter() supaya perilakunya selalu sama.
     *
     * @param  string  $kunci   salah satu FILTER_KEYS
     * @param  mixed  $nilai   nilai mentah dari request
     */
    private static function nilaiFilter(string $kunci, $nilai): ?string
    {
        if (is_array($nilai) || is_object($nilai)) {
            return null;
        }

        $nilai = trim((string) $nilai);

        if ($nilai === '') {
            return null;
        }

        // Tanggal: pastikan formatnya benar ("31/12/2025" => "2025-12-31")
        if (in_array($kunci, self::FILTER_TANGGAL, true)) {
            return self::normalisasiTanggalFilter($nilai);
        }

        // Angka: buang "Rp", titik, koma, spasi ("Rp 1.000.000" => "1000000")
        if (in_array($kunci, self::FILTER_ANGKA, true)) {
            $angka = preg_replace('/[^0-9]/', '', $nilai);

            return $angka === '' ? null : (string) (int) $angka;
        }

        return $nilai;
    }

    /**
     * Ambil filter yang valid dari sebuah input request/array.
     * Nilai yang bukan teks/angka (mis. input nisn[] dari tombol "Cetak Terpilih")
     * dan nilai kosong akan dilewati, sehingga tidak memicu error
     * "Array to string conversion" saat filter dipakai.
     */
    public static function filtersDari(array $input): array
    {
        $hasil = [];

        foreach (self::FILTER_KEYS as $kunci) {
            if (!array_key_exists($kunci, $input)) {
                continue;
            }

            $nilai = self::nilaiFilter($kunci, $input[$kunci]);

            if ($nilai !== null) {
                $hasil[$kunci] = $nilai;
            }
        }

        return $hasil;
    }

    /**
     * Query dasar Daftar Siswa: hanya siswa berstatus "siswa", kecuali bila
     * filter status_mutasi=sudah dipakai — saat itu siswa yang sudah pindah
     * (status "mutasi") ikut ditampilkan supaya filter tersebut berguna.
     */
    public function scopeDaftarSiswa($query, array $filters = [])
    {
        $status = isset($filters['status_mutasi']) ? trim((string) $filters['status_mutasi']) : '';
        $adaFilterMutasi = $status === 'sudah'
            || !empty($filters['mutasi_dari'])
            || !empty($filters['mutasi_sampai']);

        if ($adaFilterMutasi) {
            // Filter mutasi: siswa yang sudah pindah (status "mutasi") ikut ditampilkan
            $query->whereIn('status', ['siswa', 'mutasi']);
        } else {
            $query->where('status', 'siswa');
        }

        return $query->filter($filters);
    }

    /**
     * Terapkan filter pencarian siswa.
     *
     * Setiap filter opsional dan bisa dikombinasikan, dengan dua catatan:
     * - status_mutasi=sudah atau filter tanggal mutasi akan ikut menampilkan
     *   siswa berstatus "mutasi" (yang sudah pindah).
     * - status_mutasi=belum tidak masuk akal digabung dengan filter tanggal
     *   mutasi (hasilnya selalu kosong); halaman menampilkan peringatan.
     *
     * Daftar kunci yang dikenali ada di FILTER_KEYS:
     * - cari             : kata kunci nama siswa / NISN / NIS
     * - nisn, nis        : pencarian sebagian nomor induk
     * - tempat_tinggal   : kota / kecamatan / alamat pada biodata
     * - penghasilan_min / penghasilan_max : rentang penghasilan orang tua
     *                      (dicek ke data ayah ATAU ibu, batas ikut dihitung)
     * - group_id         : kelas
     * - year_id          : tahun masuk / tahun ajaran saat diterima
     * - jenis_kelamin    : L atau P
     * - status_mutasi    : sudah (punya data mutasi) atau belum
     * - mutasi_dari / mutasi_sampai : rentang tanggal pindah
     *
     * Nilai kosong, array, tanggal tidak valid, dan angka tidak valid
     * diabaikan supaya filter tidak pernah membuat halaman error.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  array<string, mixed>  $filters
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeFilter($query, array $filters)
    {
        $isi = function ($kunci) use ($filters) {
            // Satu tempat normalisasi untuk filtersDari() dan scopeFilter()
            return self::nilaiFilter($kunci, $filters[$kunci] ?? null);
        };

        // Kata kunci umum: nama siswa, NISN, atau NIS
        // Catatan: pakai "!== null", bukan pemeriksaan truthy, supaya kata kunci
        // "0" tidak dianggap kosong dan diam-diam filter diabaikan.
        if (($kata = $isi('cari')) !== null) {
            $like = self::polaLike($kata);
            $query->where(function ($q) use ($like) {
                $q->where('nama_lengkap', 'like', $like)
                  ->orWhere('nisn', 'like', $like)
                  ->orWhere('nis', 'like', $like);
            });
        }

        if (($nisn = $isi('nisn')) !== null) {
            $query->where('nisn', 'like', self::polaLike($nisn));
        }

        if (($nis = $isi('nis')) !== null) {
            $query->where('nis', 'like', self::polaLike($nis));
        }

        // Tempat tinggal: cocokkan ke kota, kecamatan, atau alamat di biodata
        if (($tempat = $isi('tempat_tinggal')) !== null) {
            $like = self::polaLike($tempat);
            $query->whereHas('biodata', function ($q) use ($like) {
                $q->where('kota', 'like', $like)
                  ->orWhere('kecamatan', 'like', $like)
                  ->orWhere('alamat', 'like', $like);
            });
        }

        // Rentang penghasilan orang tua (ayah atau ibu) per bulan
        $min = $isi('penghasilan_min');
        $max = $isi('penghasilan_max');

        if ($min !== null || $max !== null) {
            $query->where(function ($q) use ($min, $max) {
                foreach (['ayah', 'ibu'] as $relasi) {
                    $q->orWhereHas($relasi, function ($ortu) use ($min, $max) {
                        if ($min !== null) {
                            $ortu->where('penghasilan', '>=', (int) $min);
                        }
                        if ($max !== null) {
                            $ortu->where('penghasilan', '<=', (int) $max);
                        }
                    });
                }
            });
        }

        if (($kelas = $isi('group_id')) !== null) {
            $query->where('group_id', $kelas);
        }

        if (($tahun = $isi('year_id')) !== null) {
            $query->where('year_id', $tahun);
        }

        // Jenis kelamin: L (laki-laki) atau P (perempuan)
        if (($jk = $isi('jenis_kelamin')) !== null) {
            $query->whereIn('jenis_kelamin', [$jk, strtoupper($jk)]);
        }

        // Status mutasi: siswa yang sudah punya data mutasi / yang belum
        if (($mutasi = $isi('status_mutasi')) !== null) {
            if ($mutasi === 'sudah') {
                $query->whereHas('mutasi');
            } elseif ($mutasi === 'belum') {
                $query->whereDoesntHave('mutasi');
            }
        }

        // Rentang tanggal mutasi (tanggal pindah)
        $mutasiDari = $isi('mutasi_dari');
        $mutasiSampai = $isi('mutasi_sampai');

        if ($mutasiDari !== null || $mutasiSampai !== null) {
            $query->whereHas('mutasi', function ($q) use ($mutasiDari, $mutasiSampai) {
                if ($mutasiDari !== null) {
                    $q->whereDate('tanggal_pindah', '>=', $mutasiDari);
                }
                if ($mutasiSampai !== null) {
                    $q->whereDate('tanggal_pindah', '<=', $mutasiSampai);
                }
            });
        }

        return $query;
    }
}
