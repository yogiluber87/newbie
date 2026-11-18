<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

/**
 * Preview cetak PDF: halaman preview menampilkan PDF di dalam aplikasi,
 * berkas disajikan inline, dan tombol unduh tetap tersedia.
 */
class PreviewCetakTest extends TestCase
{
    private function superadmin(): User
    {
        return User::whereIn('status', ['superadmin', 'admin'])->firstOrFail();
    }

    private function siswa(): User
    {
        return User::where('status', 'siswa')->firstOrFail();
    }

    public function test_halaman_preview_daftar_siswa_menampilkan_iframe_pdf()
    {
        $html = $this->actingAs($this->superadmin())
            ->get('/admin/print/daftar/preview')
            ->assertOk()
            ->assertSee('Preview Cetak', false)
            ->getContent();

        $this->assertStringContainsString('id="pdf-frame"', $html);
        $this->assertStringContainsString('/admin/print?inline=1', $html);
    }

    public function test_berkas_daftar_siswa_inline_untuk_preview_dan_unduh_untuk_berkas()
    {
        $this->actingAs($this->superadmin());

        $inline = $this->get('/admin/print?inline=1');
        $inline->assertOk();
        $this->assertSame('application/pdf', $inline->headers->get('content-type'));
        $this->assertStringStartsWith('inline', $inline->headers->get('content-disposition'));

        $unduh = $this->get('/admin/print');
        $unduh->assertOk();
        $this->assertStringStartsWith('attachment', $unduh->headers->get('content-disposition'));
    }

    public function test_halaman_preview_buku_induk_satu_siswa()
    {
        $siswa = $this->siswa();

        $html = $this->actingAs($this->superadmin())
            ->get('/admin/print/' . $siswa->nisn . '/preview')
            ->assertOk()
            ->assertSee($siswa->nama_lengkap, false)
            ->getContent();

        $this->assertStringContainsString('/admin/print/' . $siswa->nisn . '?inline=1', $html);
    }

    public function test_berkas_buku_induk_satu_siswa_inline()
    {
        $siswa = $this->siswa();

        $res = $this->actingAs($this->superadmin())
            ->get('/admin/print/' . $siswa->nisn . '?inline=1');

        $res->assertOk();
        $this->assertSame('application/pdf', $res->headers->get('content-type'));
        $this->assertStringStartsWith('inline', $res->headers->get('content-disposition'));
    }

    public function test_preview_cetak_terpilih_menyimpan_pilihan_dan_menyajikan_pdf()
    {
        $admin = $this->superadmin();
        $siswa = User::where('status', 'siswa')->take(2)->get();

        $this->actingAs($admin)
            ->post('/admin/print/terpilih/preview', ['nisn' => $siswa->pluck('nisn')->all()])
            ->assertRedirect('/admin/print/terpilih/preview');

        $this->get('/admin/print/terpilih/preview')
            ->assertOk()
            ->assertSee('2 siswa dipilih', false)
            ->assertSee('/admin/print/terpilih/berkas?inline=1', false);

        $berkas = $this->get('/admin/print/terpilih/berkas?inline=1');
        $berkas->assertOk();
        $this->assertSame('application/pdf', $berkas->headers->get('content-type'));
        $this->assertStringStartsWith('inline', $berkas->headers->get('content-disposition'));

        $unduh = $this->get('/admin/print/terpilih/berkas');
        $this->assertStringStartsWith('attachment', $unduh->headers->get('content-disposition'));
    }

    public function test_preview_terpilih_tanpa_pilihan_kembali_ke_daftar_siswa()
    {
        $this->actingAs($this->superadmin())
            ->get('/admin/print/terpilih/preview')
            ->assertRedirect('/admin/siswa');
    }

    public function test_preview_terpilih_dengan_pilihan_kosong_hasil_ditolak()
    {
        $this->actingAs($this->superadmin())
            ->post('/admin/print/terpilih/preview', ['nisn' => ['0000000000']])
            ->assertSessionHas('error');
    }

    public function test_tata_usaha_boleh_membuka_preview()
    {
        $tu = User::create([
            'nama_lengkap'  => 'Uji TU Preview',
            'email'         => 'tu-preview-' . uniqid() . '@contoh.test',
            'password'      => bcrypt('rahasia123'),
            'status'        => 'tata_usaha',
            'jenis_kelamin' => 'L',
            'nisn'          => (string) random_int(7000000000, 7099999999),
            'nis'           => (string) random_int(7000000000, 7099999999),
            'year_id'       => 0,
            'group_id'      => 0,
        ]);

        try {
            $this->actingAs($tu)->get('/admin/print/daftar/preview')->assertOk();
            $this->actingAs($tu)->get('/admin/print/' . $this->siswa()->nisn . '/preview')->assertOk();
        } finally {
            $tu->delete();
        }
    }

    public function test_siswa_hanya_boleh_preview_datanya_sendiri()
    {
        $siswa = $this->siswa();
        $lain = User::where('status', 'siswa')->where('id', '!=', $siswa->id)->firstOrFail();

        $this->actingAs($siswa)->get('/admin/print/' . $siswa->nisn . '/preview')->assertOk();

        $this->actingAs($siswa)->get('/admin/print/' . $lain->nisn . '/preview')->assertStatus(403);
        $this->actingAs($siswa)->get('/admin/print/' . $lain->nisn . '?inline=1')->assertStatus(403);
    }

    public function test_siswa_tidak_boleh_membuka_preview_daftar_siswa()
    {
        $this->actingAs($this->siswa())->get('/admin/print/daftar/preview')->assertStatus(403);
    }
}
