<?php

namespace Tests\Feature;

use App\Models\Biodata;
use App\Models\Dad;
use App\Models\Group;
use App\Models\Guardian;
use App\Models\Mom;
use App\Models\User;
use App\Models\Year;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Simpan bertahap pada formulir biodata/orang tua/wali.
 *
 * Aturan yang dijaga: mengirim formulir dengan sebagian kolom kosong TIDAK
 * boleh menghapus data yang sudah tersimpan (dulu semua kolom kosong ditulis
 * sebagai null sehingga data hilang).
 *
 * Data uji selalu dibuat dan dihapus di file ini agar data asli aman.
 */
class SimpanBertahapTest extends TestCase
{
    private ?User $siswa = null;

    protected function tearDown(): void
    {
        if ($this->siswa) {
            $this->siswa->ayah()->delete();
            $this->siswa->ibu()->delete();
            $this->siswa->biodata()->delete();
            $this->siswa->wali()->delete();
            $this->siswa->delete();
            $this->siswa = null;
        }

        parent::tearDown();
    }

    private function siswaUji(): User
    {
        $siswa = User::create([
            'nama_lengkap' => 'Uji Simpan ' . Str::random(4),
            'nisn' => (string) random_int(7100000000, 7199999999),
            'nis' => (string) random_int(7100000000, 7199999999),
            'group_id' => Group::firstOrFail()->id,
            'year_id' => Year::firstOrFail()->id,
            'jenis_kelamin' => 'L',
            'status' => 'siswa',
            'password' => bcrypt('rahasia'),
        ]);

        $siswa->ayah()->create(['uri' => Str::random(40), 'nama' => 'Ayah Uji', 'pekerjaan' => 'Petani']);
        $siswa->ibu()->create(['uri' => Str::random(40), 'nama' => 'Ibu Uji', 'pekerjaan' => 'Guru']);
        $siswa->biodata()->create(['uri' => Str::random(40), 'kota' => 'Kab. Tanah Datar', 'hobi' => 'Membaca']);
        $siswa->wali()->create(['uri' => Str::random(40), 'nama' => 'Wali Uji']);

        $this->siswa = $siswa->fresh();

        return $this->siswa;
    }

    private function admin(): User
    {
        return User::where('status', '!=', 'siswa')->firstOrFail();
    }

    public function test_biodata_tidak_terhapus_saat_form_dikirim_sebagian()
    {
        $siswa = $this->siswaUji();

        $res = $this->actingAs($this->admin())->put('/admin/biodata/' . $siswa->biodata->uri, [
            'alamat' => 'Jl. Baru No. 10',
            'kota' => '',
            'hobi' => '',
        ]);

        $res->assertRedirect();
        $bio = $siswa->biodata->fresh();

        $this->assertSame('Jl. Baru No. 10', $bio->alamat, 'kolom yang diisi harus tersimpan');
        $this->assertSame('Kab. Tanah Datar', $bio->kota, 'kolom kosong tidak boleh menghapus data lama');
        $this->assertSame('Membaca', $bio->hobi, 'kolom kosong tidak boleh menghapus data lama');
    }

    public function test_data_ayah_ibu_wali_tidak_terhapus_saat_form_dikirim_sebagian()
    {
        $siswa = $this->siswaUji();
        $admin = $this->admin();

        // Ayah: kirim hanya nomor_hp
        $this->actingAs($admin)->put('/admin/ayah/' . $siswa->ayah->uri, [
            'nomor_hp' => '081200000000',
            'nama' => '',
            'pekerjaan' => '',
        ])->assertRedirect();

        $ayah = $siswa->ayah->fresh();
        $this->assertSame('081200000000', $ayah->nomor_hp);
        $this->assertSame('Ayah Uji', $ayah->nama);
        $this->assertSame('Petani', $ayah->pekerjaan);

        // Ibu: kirim hanya pendidikan
        $this->actingAs($admin)->put('/admin/ibu/' . $siswa->ibu->uri, [
            'pendidikan' => 'Strata-1',
            'nama' => '',
        ])->assertRedirect();

        $ibu = $siswa->ibu->fresh();
        $this->assertSame('Strata-1', $ibu->pendidikan);
        $this->assertSame('Ibu Uji', $ibu->nama);
        $this->assertSame('Guru', $ibu->pekerjaan);

        // Wali: kirim hanya nomor_hp
        $this->actingAs($admin)->put('/admin/wali/' . $siswa->wali->uri, [
            'nomor_hp' => '081277777777',
            'nama' => '',
        ])->assertRedirect();

        $wali = $siswa->wali->fresh();
        $this->assertSame('081277777777', $wali->nomor_hp);
        $this->assertSame('Wali Uji', $wali->nama);
    }

    public function test_kelengkapan_tab_biodata_dihitung_benar()
    {
        $bio = new Biodata([
            'alamat' => 'Jl. A', 'kota' => 'Kab. B', 'kecamatan' => 'C',
            'tempat_lahir' => 'D', 'tanggal_lahir' => '2008-01-01', 'anak_ke' => 1,
            'jlh_saudara' => 2, 'saudara_tiri' => 0, 'saudara_angkat' => 0,
            'bahasa' => 'Indonesia', 'agama' => 'Islam',
        ]);

        $k = $bio->kelengkapan();

        $this->assertSame(9, $k[1]['terisi'], 'angka 0 tetap dihitung terisi');
        $this->assertSame(9, $k[1]['total']);
        $this->assertSame('lengkap', $k[1]['status']);

        $this->assertSame(2, $k[2]['terisi']);
        $this->assertSame(7, $k[2]['total']);
        $this->assertSame('sebagian', $k[2]['status']);

        $this->assertSame(0, $k[3]['terisi']);
        $this->assertSame('kosong', $k[3]['status']);
    }

    public function test_halaman_biodata_menampilkan_penanda_kelengkapan_tab()
    {
        $siswa = $this->siswaUji();

        $html = $this->actingAs($this->admin())
            ->get('/admin/biodata/' . $siswa->biodata->uri)
            ->assertOk()
            ->getContent();

        // Data uji: kota (tab 1) dan hobi (tab 3) terisi, tab 2 kosong.
        // Angka terisi dan total dirender terpisah (span + teks), jadi periksa keduanya.
        $this->assertSame(2, preg_match_all('/class="jumlah-terisi">1</', $html), 'tab 1 dan tab 3 masing-masing terisi 1');
        $this->assertSame(1, preg_match_all('/class="jumlah-terisi">0</', $html), 'tab 2 belum diisi');
        $this->assertStringContainsString('/9 terisi', $html);
        $this->assertStringContainsString('/7 terisi', $html);
        $this->assertStringContainsString('/4 terisi', $html);
        $this->assertStringContainsString('status-tahap', $html);
        $this->assertStringContainsString('belum diisi', $html);
        $this->assertStringContainsString('sebagian', $html);
    }
}
