<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\Year;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Regresi untuk data yang belum lengkap.
 *
 * Siswa hasil impor CSV biasa bisa ada tanpa biodata/ayah/ibu/wali, dan
 * siswa bisa mengisi form secara bertahap. Semua halaman harus tetap bisa
 * dibuka (bukan error 500) dan data yang sudah ada tidak boleh hilang.
 */
class StabilitasDataTest extends TestCase
{
    private array $dibuat = [];

    protected function tearDown(): void
    {
        foreach ($this->dibuat as $siswa) {
            $siswa->ayah()->delete();
            $siswa->ibu()->delete();
            $siswa->biodata()->delete();
            $siswa->wali()->delete();
            $siswa->mutasi()->delete();
            $siswa->delete();
        }

        $this->dibuat = [];
        parent::tearDown();
    }

    private function admin(): User
    {
        return User::where('status', '!=', 'siswa')->firstOrFail();
    }

    /** Siswa tanpa biodata/ayah/ibu/wali sama sekali. */
    private function siswaTanpaRelasi(): User
    {
        $siswa = User::create([
            'nama_lengkap' => 'Uji Stabilitas ' . Str::random(4),
            'nisn' => (string) random_int(7100000000, 7199999999),
            'nis' => (string) random_int(7100000000, 7199999999),
            'group_id' => Group::firstOrFail()->id,
            'year_id' => Year::firstOrFail()->id,
            'jenis_kelamin' => 'L',
            'status' => 'siswa',
            'password' => bcrypt('rahasia'),
        ]);

        $this->dibuat[] = $siswa;

        return $siswa;
    }

    /** Siswa dengan seluruh relasi terisi. */
    private function siswaLengkap(): User
    {
        $siswa = $this->siswaTanpaRelasi();
        $siswa->biodata()->create(['uri' => Str::random(40)]);
        $siswa->ayah()->create(['uri' => Str::random(40), 'nama' => 'Ayah Uji']);
        $siswa->ibu()->create(['uri' => Str::random(40), 'nama' => 'Ibu Uji']);
        $siswa->wali()->create(['uri' => Str::random(40), 'nama' => 'Wali Uji']);

        return $siswa->fresh();
    }

    public function test_semua_halaman_admin_aman_untuk_siswa_tanpa_relasi()
    {
        $siswa = $this->siswaTanpaRelasi();
        $admin = $this->admin();

        $urls = [
            '/dashboard',
            '/admin/siswa',
            '/admin/siswa/' . $siswa->nisn,
            '/admin/siswa/' . $siswa->nisn . '/ekspor',
            '/admin/mutasi',
            '/admin/print',
            '/admin/grup',
            '/admin/tahun',
            '/admin/pengaturan',
            '/admin/siswa/import',
            '/admin/siswa/import-lengkap',
            '/admin/siswa/create',
            '/admin/print/' . $siswa->nisn,
        ];

        foreach ($urls as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_halaman_siswa_aman_tanpa_relasi()
    {
        $siswa = $this->siswaTanpaRelasi();

        $this->actingAs($siswa)->get('/home')->assertOk()
            // Menu yang datanya belum ada tampil nonaktif, bukan tautan rusak
            ->assertSee('Data belum dibuat oleh petugas');
        $this->actingAs($siswa)->get('/password/' . $siswa->nisn)->assertOk();
    }

    public function test_siswa_dapat_menyimpan_keempat_form_sendiri()
    {
        $siswa = $this->siswaLengkap();

        foreach ([
            ['/biodata/' . $siswa->biodata->uri, ['alamat' => 'Jl. Siswa']],
            ['/data-ayah/' . $siswa->ayah->uri, ['nama' => 'Ayah Baru']],
            ['/data-ibu/' . $siswa->ibu->uri, ['nama' => 'Ibu Baru']],
            ['/data-wali/' . $siswa->wali->uri, ['nama' => 'Wali Baru']],
        ] as [$url, $data]) {
            $this->actingAs($siswa)->get($url)->assertOk();
            $this->actingAs($siswa)->put($url, $data)->assertRedirect();
        }

        $siswa = $siswa->fresh();
        $this->assertSame('Jl. Siswa', $siswa->biodata->alamat);
        $this->assertSame('Ayah Baru', $siswa->ayah->nama);
        $this->assertSame('Ibu Baru', $siswa->ibu->nama);
        $this->assertSame('Wali Baru', $siswa->wali->nama);
    }

    public function test_isi_sebagian_wali_tetap_tersimpan()
    {
        $siswa = $this->siswaLengkap();

        // Hanya nama yang diisi: kolom lain tidak boleh memblokir penyimpanan
        $this->actingAs($siswa)
            ->put('/data-wali/' . $siswa->wali->uri, ['nama' => 'Wali Separo'])
            ->assertSessionHasNoErrors();

        $this->assertSame('Wali Separo', $siswa->fresh()->wali->nama);
    }

    public function test_biodata_menerima_tinggi_berat_dan_anak_tunggal()
    {
        $siswa = $this->siswaLengkap();

        $this->actingAs($siswa)->put('/biodata/' . $siswa->biodata->uri, [
            'tinggi' => 170,
            'berat' => 60,
            'jarak' => 3,
            'anak_ke' => 2,
            'jlh_saudara' => 0,
            'saudara_tiri' => 0,
            'saudara_angkat' => 0,
        ])->assertSessionHasNoErrors();

        $bio = $siswa->fresh()->biodata;
        $this->assertSame(170, (int) $bio->tinggi);
        $this->assertSame(60, (int) $bio->berat);
        $this->assertSame(0, (int) $bio->jlh_saudara);
    }

    public function test_mutasi_tidak_menghapus_biodata_dan_bisa_dibatalkan()
    {
        $siswa = $this->siswaLengkap();
        $admin = $this->admin();

        $this->actingAs($admin)->post('/admin/mutasi/' . $siswa->nisn, [
            'tujuan' => 'Sekolah Tujuan Uji',
            'alasan' => 'Pindah domisili',
            'tanggal_pindah' => '2025-06-15',
        ])->assertRedirect();

        $siswa = $siswa->fresh();
        $this->assertSame('mutasi', $siswa->status);
        $this->assertNotNull($siswa->biodata, 'biodata tidak boleh terhapus saat mutasi');

        $this->actingAs($admin)->get('/admin/siswa/' . $siswa->nisn)->assertOk();
        $this->actingAs($admin)->get('/admin/mutasi/' . $siswa->nisn)->assertOk();

        // Hapus mutasi memakai id mutasi (bukan id user)
        $idMutasi = $siswa->mutasi()->first()->id;
        $this->actingAs($admin)->delete('/admin/mutasi/' . $idMutasi)->assertRedirect();

        $siswa = $siswa->fresh();
        $this->assertSame('siswa', $siswa->status);
        $this->assertSame(0, $siswa->mutasi()->count());
    }

    public function test_lanjut_wizard_tidak_menuju_alamat_kosong()
    {
        $admin = $this->admin();

        // Hanya ayah yang ada: setelah simpan, alur lanjut tidak boleh 404
        $siswa = $this->siswaTanpaRelasi();
        $siswa->ayah()->create(['uri' => Str::random(40)]);

        $res = $this->actingAs($admin)->put('/admin/ayah/' . $siswa->ayah->uri . '?lanjut=1', [
            'nama' => 'Ayah Wizard',
        ]);

        $tujuan = $res->headers->get('Location');
        $this->assertNotNull($tujuan);
        $this->actingAs($admin)->get($tujuan)->assertOk();
    }

    public function test_jumlah_siswa_dashboard_menghitung_siswa_aktif()
    {
        $this->siswaTanpaRelasi();
        $admin = $this->admin();

        $jumlahAktif = User::where('status', 'siswa')->count();

        $this->actingAs($admin)->get('/dashboard')
            ->assertOk()
            ->assertSee('Siswa aktif')
            ->assertSee((string) $jumlahAktif);
    }

    public function test_tambah_siswa_admin_langsung_membuat_relasi_lengkap()
    {
        $admin = $this->admin();
        $nisn = (string) random_int(7200000000, 7299999999);

        $res = $this->actingAs($admin)->post('/admin/siswa', [
            'nama_lengkap' => 'Uji Tambah ' . Str::random(4),
            'nisn' => $nisn,
            'nis' => $nisn,
            'group_id' => Group::firstOrFail()->id,
            'year_id' => Year::firstOrFail()->id,
            'jenis_kelamin' => 'P',
        ]);

        $siswa = User::where('nisn', $nisn)->first();
        $this->assertNotNull($siswa);
        $this->dibuat[] = $siswa;

        $siswa = $siswa->fresh();
        $this->assertNotNull($siswa->ayah);
        $this->assertNotNull($siswa->ibu);
        $this->assertNotNull($siswa->biodata);
        $this->assertNotNull($siswa->wali);

        // Langkah pertama wizard bisa langsung dibuka
        $this->actingAs($admin)->get($res->headers->get('Location'))->assertOk();
    }
}
