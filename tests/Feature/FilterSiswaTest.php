<?php

namespace Tests\Feature;

use App\Models\Biodata;
use App\Models\Dad;
use App\Models\Group;
use App\Models\Guardian;
use App\Models\Mom;
use App\Models\Mutation;
use App\Models\User;
use App\Models\Year;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Test permanen untuk filter Daftar Siswa (User::FILTER_KEYS).
 *
 * Catatan: test ini memakai database aplikasi (bukan database test terpisah),
 * jadi setiap data uji dibuat sendiri dan SELALU dihapus lagi di finally.
 * Jangan memakai RefreshDatabase di sini supaya data asli tidak terhapus.
 */
class FilterSiswaTest extends TestCase
{
    private array $dibuat = [];

    protected function tearDown(): void
    {
        foreach ($this->dibuat as $siswa) {
            $siswa->ayah()->delete();
            $siswa->ibu()->delete();
            $siswa->biodata()->delete();
            $siswa->wali()->delete();
            Mutation::where('user_id', $siswa->id)->delete();
            $siswa->delete();
        }

        $this->dibuat = [];

        parent::tearDown();
    }

    /**
     * Buat satu siswa uji lengkap dengan data ayah, ibu, biodata, dan wali.
     */
    private function siswaUji(array $atribut = [], array $data = []): User
    {
        $grup = Group::firstOrFail();
        $tahun = Year::firstOrFail();

        $siswa = User::create(array_merge([
            'nama_lengkap' => 'Uji Filter ' . Str::random(5),
            'nisn' => (string) random_int(7000000000, 7999999999),
            'nis' => (string) random_int(7000000000, 7999999999),
            'group_id' => $grup->id,
            'year_id' => $tahun->id,
            'jenis_kelamin' => 'L',
            'status' => 'siswa',
            'password' => bcrypt('rahasia'),
        ], $atribut));

        $siswa->ayah()->create(array_merge(['uri' => Str::random(40)], $data['ayah'] ?? []));
        $siswa->ibu()->create(array_merge(['uri' => Str::random(40)], $data['ibu'] ?? []));
        $siswa->biodata()->create(array_merge(['uri' => Str::random(40)], $data['biodata'] ?? []));
        $siswa->wali()->create(['uri' => Str::random(40)]);

        $this->dibuat[] = $siswa;

        return $siswa->fresh();
    }

    private function admin(): User
    {
        return User::where('status', '!=', 'siswa')->firstOrFail();
    }

    /** Cari NISN siswa uji di antara hasil query. */
    private function ada(User $siswa, array $filter): bool
    {
        return User::where('status', 'siswa')->filter($filter)->pluck('nisn')->contains($siswa->nisn);
    }

    public function test_filter_nama_nisn_dan_nis()
    {
        $siswa = $this->siswaUji(['nama_lengkap' => 'Zulaeha Filter']);

        $this->assertTrue($this->ada($siswa, ['cari' => 'Zulaeha']));
        $this->assertTrue($this->ada($siswa, ['cari' => $siswa->nisn]));
        $this->assertTrue($this->ada($siswa, ['cari' => $siswa->nis]));
        $this->assertTrue($this->ada($siswa, ['nisn' => substr($siswa->nisn, 0, 5)]));
        $this->assertTrue($this->ada($siswa, ['nis' => substr($siswa->nis, 0, 5)]));
        $this->assertFalse($this->ada($siswa, ['cari' => 'zzz-tidak-ada']));
    }

    public function test_filter_tempat_tinggal()
    {
        $siswa = $this->siswaUji([], ['biodata' => [
            'kota' => 'Kota Padang',
            'kecamatan' => 'Lintau Buo',
            'alamat' => 'Jl. Melati No. 5',
        ]]);

        $this->assertTrue($this->ada($siswa, ['tempat_tinggal' => 'padang']));
        $this->assertTrue($this->ada($siswa, ['tempat_tinggal' => 'Lintau']));
        $this->assertTrue($this->ada($siswa, ['tempat_tinggal' => 'melati']));
        $this->assertTrue($this->ada($siswa, ['tempat_tinggal' => 'KOTA PADANG']));
        $this->assertFalse($this->ada($siswa, ['tempat_tinggal' => 'tidak-ada']));
    }

    public function test_filter_rentang_penghasilan_ayah_atau_ibu()
    {
        $siswa = $this->siswaUji([], [
            'ayah' => ['penghasilan' => 4000000],
            'ibu' => ['penghasilan' => 1000000],
        ]);

        // Ayah masuk rentang
        $this->assertTrue($this->ada($siswa, ['penghasilan_min' => 3500000, 'penghasilan_max' => 4500000]));
        // Ibu masuk rentang
        $this->assertTrue($this->ada($siswa, ['penghasilan_min' => 900000, 'penghasilan_max' => 1500000]));
        // Batas inklusif
        $this->assertTrue($this->ada($siswa, ['penghasilan_min' => 4000000, 'penghasilan_max' => 4000000]));
        // Di luar rentang
        $this->assertFalse($this->ada($siswa, ['penghasilan_min' => 5000000, 'penghasilan_max' => 9000000]));
        // Format ribuan & "Rp" tetap dikenali
        $this->assertTrue($this->ada($siswa, User::filtersDari([
            'penghasilan_min' => 'Rp 1.000.000',
            'penghasilan_max' => '5.000.000',
        ])));
        // Bukan angka: filter diabaikan (tidak melebar jadi 0)
        $this->assertTrue($this->ada($siswa, ['penghasilan_min' => 'abc']));
    }

    public function test_filter_kelas_tahun_dan_jenis_kelamin()
    {
        $grupLain = Group::where('id', '!=', Group::firstOrFail()->id)->first();
        $siswa = $this->siswaUji(['jenis_kelamin' => 'P']);

        $this->assertTrue($this->ada($siswa, ['group_id' => $siswa->group_id]));
        $this->assertTrue($this->ada($siswa, ['year_id' => $siswa->year_id]));
        $this->assertTrue($this->ada($siswa, ['jenis_kelamin' => 'P']));
        $this->assertTrue($this->ada($siswa, ['jenis_kelamin' => 'p']));
        $this->assertFalse($this->ada($siswa, ['jenis_kelamin' => 'L']));

        if ($grupLain) {
            $this->assertFalse($this->ada($siswa, ['group_id' => $grupLain->id]));
        }
    }

    public function test_filter_status_mutasi_dan_tanggal_mutasi()
    {
        $siswa = $this->siswaUji(['nama_lengkap' => 'Pindah Filter']);

        // Belum mutasi
        $this->assertTrue($this->ada($siswa, ['status_mutasi' => 'belum']));
        $this->assertFalse($this->ada($siswa, ['status_mutasi' => 'sudah']));

        // Setelah dimutasi seperti alur Admin@edit_mutasi (status jadi "mutasi")
        $siswa->update(['status' => 'mutasi']);
        $mutasi = Mutation::create([
            'user_id' => $siswa->id,
            'tujuan' => 'Sekolah Tujuan Uji',
            'alasan' => 'uji filter',
            'tanggal_pindah' => '2025-06-15',
        ]);

        $jumlahSudah = User::query()->daftarSiswa(['status_mutasi' => 'sudah'])
            ->where('nisn', $siswa->nisn)->count();
        $this->assertSame(1, $jumlahSudah, 'siswa yang sudah pindah harus muncul di filter "sudah mutasi"');

        $this->assertSame(1, User::query()->daftarSiswa(['mutasi_dari' => '2025-01-01', 'mutasi_sampai' => '2025-12-31'])
            ->where('nisn', $siswa->nisn)->count());
        $this->assertSame(0, User::query()->daftarSiswa(['mutasi_dari' => '2025-06-16'])
            ->where('nisn', $siswa->nisn)->count());

        // Tanpa filter mutasi, siswa berstatus "mutasi" tidak ikut daftar biasa
        $this->assertSame(0, User::query()->daftarSiswa([])->where('nisn', $siswa->nisn)->count());

        // Kembalikan ke kondisi awal
        $mutasi->delete();
        $siswa->update(['status' => 'siswa']);
    }

    public function test_input_tidak_valid_diabaikan()
    {
        // Array (mis. nisn[] tombol Cetak Terpilih), array tidak boleh jadi filter
        $this->assertSame([], User::filtersDari(['nisn' => ['0120']]));
        // Tanggal salah
        $this->assertSame([], User::filtersDari(['mutasi_dari' => 'abc', 'mutasi_sampai' => '2025-02-30']));
        // Tanggal format Indonesia dinormalkan
        $this->assertSame(['mutasi_sampai' => '2025-12-31'], User::filtersDari(['mutasi_sampai' => '31/12/2025']));
        // Angka dinormalkan
        $this->assertSame(['penghasilan_min' => '1000000'], User::filtersDari(['penghasilan_min' => 'Rp 1.000.000']));

        $this->assertNull(User::formatTanggalFilter('abc'));
        $this->assertSame('15/06/2025', User::formatTanggalFilter('2025-06-15'));
    }

    public function test_halaman_daftar_dan_ekspor_menerima_filter()
    {
        $siswa = $this->siswaUji(['nama_lengkap' => 'Halaman Filter']);
        $admin = $this->admin();

        $res = $this->actingAs($admin)->get('/admin/siswa?cari=' . urlencode('Halaman Filter') . '&jenis_kelamin=L&status_mutasi=belum');
        $res->assertOk();
        $res->assertSee('Halaman Filter', false);
        $res->assertSee('Filter aktif', false);

        // Ekspor CSV ikut filter yang sama
        $csv = $this->actingAs($admin)->get('/admin/siswa/ekspor-semua-csv?cari=' . urlencode('Halaman Filter'));
        $csv->assertOk();
        $this->assertStringContainsString($siswa->nisn, $csv->getContent());

        // Kombinasi bertentangan tetap aman (200) dan memberi peringatan
        $aneh = $this->actingAs($admin)->get('/admin/siswa?status_mutasi=belum&mutasi_sampai=2025-12-31');
        $aneh->assertOk();
        $aneh->assertSee('tidak cocok dengan rentang tanggal mutasi', false);

        $this->actingAs($admin)->get('/admin/siswa?reset=1');
    }

    public function test_kata_kunci_nol_tidak_dianggap_kosong()
    {
        // NISN tanpa angka 0 dan NISN dengan angka 0 sebagai pembanding
        // (nama harus eksplisit: Str::random bisa saja menghasilkan angka 0)
        $tanpaNol = $this->siswaUji(['nama_lengkap' => 'Uji Tanpa Nol', 'nisn' => '2222222222', 'nis' => '2222222222']);
        $denganNol = $this->siswaUji(['nama_lengkap' => 'Uji Dengan Nol', 'nisn' => '9000011111', 'nis' => '9000011111']);

        $hasil = User::where('status', 'siswa')->filter(['nisn' => '0'])->pluck('nisn')->all();

        $this->assertContains($denganNol->nisn, $hasil, 'NISN yang mengandung 0 harus muncul');
        $this->assertNotContains($tanpaNol->nisn, $hasil, 'filter "0" tidak boleh diabaikan (dulu semua siswa ikut)');

        // Jalur kedua: kata kunci umum (cari) juga harus menghormati "0"
        $hasilCari = User::where('status', 'siswa')->filter(['cari' => '0'])->pluck('nisn')->all();
        $this->assertContains($denganNol->nisn, $hasilCari);
        $this->assertNotContains($tanpaNol->nisn, $hasilCari, 'cari "0" tidak boleh diabaikan');
    }

    public function test_karakter_khusus_like_dicari_apa_adanya()
    {
        $persen = $this->siswaUji(['nama_lengkap' => 'Uji Diskon 100% Spesial']);
        $garis = $this->siswaUji(['nama_lengkap' => 'Uji Garis Bawah']);

        // "%" harus literal: hanya siswa dengan tanda % yang muncul
        $hasil = User::where('status', 'siswa')->filter(['cari' => '100%'])->pluck('nisn')->all();
        $this->assertContains($persen->nisn, $hasil);
        $this->assertNotContains($garis->nisn, $hasil);

        // "%" saja tidak boleh berarti "semua data"
        $semua = User::where('status', 'siswa')->filter(['cari' => '%'])->pluck('nisn')->all();
        $this->assertNotContains($garis->nisn, $semua, '"%" tunggal tidak boleh mencocokkan semua siswa');

        // "_" tidak boleh mencocokkan sembarang satu huruf
        $underscore = User::where('status', 'siswa')->filter(['cari' => 'Uji_Garis'])->pluck('nisn')->all();
        $this->assertNotContains($garis->nisn, $underscore);
    }

    public function test_daftar_biasa_tidak_menampilkan_siswa_mutasi()
    {
        $siswa = $this->siswaUji(['nama_lengkap' => 'Sudah Pindah']);
        $siswa->update(['status' => 'mutasi']);

        $halaman = $this->actingAs($this->admin())->get('/admin/siswa?reset=1');
        $html = $halaman->getContent();

        $this->assertStringNotContainsString('Sudah Pindah', $html);
    }
}
