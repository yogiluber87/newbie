<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\Year;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Siswa hanya boleh membuka dan mengubah datanya sendiri.
 *
 * Rute siswa (/biodata/{uri}, /data-ayah/{uri}, dst.) memakai middleware
 * "auth" saja, sehingga tanpa pemeriksaan kepemilikan siapa pun yang sudah
 * login bisa mengubah data siswa lain hanya dengan menebak URL-nya.
 */
class KeamananSiswaTest extends TestCase
{
    private array $dibuat = [];

    protected function tearDown(): void
    {
        foreach ($this->dibuat as $siswa) {
            $siswa->ayah()->delete();
            $siswa->ibu()->delete();
            $siswa->biodata()->delete();
            $siswa->wali()->delete();
            $siswa->delete();
        }

        $this->dibuat = [];
        parent::tearDown();
    }

    private function siswa(array $namaRelasi = ['biodata', 'ayah', 'ibu', 'wali']): User
    {
        $siswa = User::create([
            'nama_lengkap' => 'Uji Aman ' . Str::random(4),
            'nisn' => (string) random_int(7000000000, 7099999999),
            'nis' => (string) random_int(7000000000, 7099999999),
            'group_id' => Group::firstOrFail()->id,
            'year_id' => Year::firstOrFail()->id,
            'jenis_kelamin' => 'L',
            'status' => 'siswa',
            'password' => bcrypt('rahasia'),
        ]);

        if (in_array('biodata', $namaRelasi, true)) {
            $siswa->biodata()->create(['uri' => Str::random(40)]);
        }
        if (in_array('ayah', $namaRelasi, true)) {
            $siswa->ayah()->create(['uri' => Str::random(40), 'nama' => 'Ayah Asli']);
        }
        if (in_array('ibu', $namaRelasi, true)) {
            $siswa->ibu()->create(['uri' => Str::random(40), 'nama' => 'Ibu Asli']);
        }
        if (in_array('wali', $namaRelasi, true)) {
            $siswa->wali()->create(['uri' => Str::random(40), 'nama' => 'Wali Asli']);
        }

        $this->dibuat[] = $siswa = $siswa->fresh();

        return $siswa;
    }

    public function test_siswa_tidak_boleh_mengubah_data_siswa_lain()
    {
        $a = $this->siswa();
        $b = $this->siswa();

        $this->actingAs($a)->get('/biodata/' . $b->biodata->uri)->assertForbidden();
        $this->actingAs($a)->put('/biodata/' . $b->biodata->uri, ['kota' => 'Diambil Alih'])->assertForbidden();
        $this->actingAs($a)->get('/data-ayah/' . $b->ayah->uri)->assertForbidden();
        $this->actingAs($a)->put('/data-ayah/' . $b->ayah->uri, ['nama' => 'Diambil Alih'])->assertForbidden();
        $this->actingAs($a)->get('/data-ibu/' . $b->ibu->uri)->assertForbidden();
        $this->actingAs($a)->put('/data-ibu/' . $b->ibu->uri, ['nama' => 'Diambil Alih'])->assertForbidden();
        $this->actingAs($a)->get('/data-wali/' . $b->wali->uri)->assertForbidden();
        $this->actingAs($a)->put('/data-wali/' . $b->wali->uri, ['nama' => 'Diambil Alih'])->assertForbidden();
        $this->actingAs($a)->get('/password/' . $b->nisn)->assertForbidden();

        // Data milik B tidak berubah
        $this->assertSame('Ayah Asli', $b->ayah->fresh()->nama);
        $this->assertSame('Ibu Asli', $b->ibu->fresh()->nama);
        $this->assertSame('Wali Asli', $b->wali->fresh()->nama);
    }

    public function test_siswa_tetap_bisa_membuka_data_sendiri()
    {
        $siswa = $this->siswa();

        $this->actingAs($siswa)->get('/biodata/' . $siswa->biodata->uri)->assertOk();
        $this->actingAs($siswa)->get('/data-ayah/' . $siswa->ayah->uri)->assertOk();
        $this->actingAs($siswa)->get('/data-ibu/' . $siswa->ibu->uri)->assertOk();
        $this->actingAs($siswa)->get('/data-wali/' . $siswa->wali->uri)->assertOk();
        $this->actingAs($siswa)->get('/password/' . $siswa->nisn)->assertOk();
    }

    public function test_cetak_pdf_hanya_untuk_diri_sendiri_atau_admin()
    {
        $a = $this->siswa();
        $b = $this->siswa();
        $admin = User::where('status', '!=', 'siswa')->firstOrFail();

        $this->actingAs($a)->get('/admin/print/' . $b->nisn)->assertForbidden();
        $this->actingAs($a)->get('/admin/print/' . $a->nisn)->assertOk();
        $this->actingAs($admin)->get('/admin/print/' . $b->nisn)->assertOk();
    }
}
