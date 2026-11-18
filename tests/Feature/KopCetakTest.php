<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Kop cetak PDF: gambar kiri/kanan, baris instansi, dan baris kontak
 * diatur dari halaman Pengaturan (khusus superadmin).
 */
class KopCetakTest extends TestCase
{
    /** @var array<string, mixed> */
    private array $asli = [];

    /** @var string[] berkas yang dibuat selama test */
    private array $berkas = [];

    protected function setUp(): void
    {
        parent::setUp();

        // Simpan pengaturan asli supaya bisa dikembalikan setelah test
        $this->asli = Setting::query()->pluck('value', 'key')->all();
    }

    protected function tearDown(): void
    {
        // Kembalikan pengaturan seperti semula
        Setting::query()->delete();

        foreach ($this->asli as $key => $value) {
            Setting::query()->insert(['key' => $key, 'value' => $value]);
        }

        // Hapus berkas gambar yang dibuat selama test
        foreach ($this->berkas as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        Setting::flushCache();
        parent::tearDown();
    }

    private function renderKop(string $judul = ''): string
    {
        return view('printout.partials.kop_sekolah', [
            'setelan' => Setting::data(),
            'judul' => $judul,
        ])->render();
    }

    public function test_kop_menampilkan_baris_instansi_kontak_dan_judul_dokumen()
    {
        Setting::put('nama_sekolah', 'SMA NEGERI 2 LINTAU BUO');
        Setting::put('kop_baris1', 'PEMERINTAH PROVINSI SUMATERA BARAT');
        Setting::put('kop_baris2', 'DINAS PENDIDIKAN');
        Setting::put('kop_kontak', 'Jalan Raya Sitangkai - Balai Tangah Km 2 • e-mail : sma2lintaubuo@gmail.com');

        $html = $this->renderKop('Buku Induk Siswa');

        $this->assertStringContainsString('PEMERINTAH PROVINSI SUMATERA BARAT', $html);
        $this->assertStringContainsString('DINAS PENDIDIKAN', $html);
        $this->assertStringContainsString('SMA NEGERI 2 LINTAU BUO', $html);
        $this->assertStringContainsString('sma2lintaubuo@gmail.com', $html);
        $this->assertStringContainsString('Buku Induk Siswa', $html);
        // Garis ganda kop
        $this->assertStringContainsString('border-top: 2px solid #000', $html);
        $this->assertStringContainsString('border-top: 1px solid #000', $html);
    }

    public function test_kop_memakai_gambar_kiri_dan_kanan_yang_diunggah()
    {
        Storage::disk('public')->put('kop/uji-kiri.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
        ));
        Storage::disk('public')->put('kop/uji-kanan.png', base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII='
        ));
        $this->berkas = ['kop/uji-kiri.png', 'kop/uji-kanan.png'];

        Setting::put('kop_kiri', 'kop/uji-kiri.png');
        Setting::put('kop_kanan', 'kop/uji-kanan.png');

        $html = $this->renderKop();

        $this->assertStringContainsString('uji-kiri.png', $html);
        $this->assertStringContainsString('uji-kanan.png', $html);
    }

    public function test_kop_tanpa_gambar_kanan_tidak_menampilkan_kolom_kanan()
    {
        Setting::put('kop_kiri', null);
        Setting::put('kop_kanan', null);
        Setting::put('kop_kontak', '');

        $html = $this->renderKop();

        $this->assertStringNotContainsString('Logo kanan', $html);
    }

    public function test_kontak_memakai_alamat_sekolah_bila_kolom_kontak_kosong()
    {
        Setting::put('kop_kontak', '');
        Setting::put('alamat_sekolah', "Jl. Pendidikan No. 1\nKel. Sukamaju");

        $html = $this->renderKop();

        $this->assertStringContainsString('Jl. Pendidikan No. 1 • Kel. Sukamaju', $html);
    }

    public function test_superadmin_dapat_mengunggah_gambar_kop()
    {
        $superadmin = \App\Models\User::whereIn('status', ['superadmin', 'admin'])->firstOrFail();

        Setting::put('kop_kiri', null);
        Setting::put('kop_kanan', null);

        $this->actingAs($superadmin)->post('/admin/pengaturan', [
            'nama_sekolah' => 'SMA UJI KOP',
            'kop_baris1'   => 'PEMERINTAH PROVINSI UJI',
            'kop_baris2'   => 'DINAS PENDIDIKAN',
            'kop_kontak'   => 'Telp. 12345',
            'kop_kiri'     => UploadedFile::fake()->image('kiri.png', 60, 60),
            'kop_kanan'    => UploadedFile::fake()->image('kanan.png', 60, 60),
        ])->assertRedirect('/admin/pengaturan');

        Setting::flushCache();
        $data = Setting::data();

        $this->assertSame('SMA UJI KOP', $data['nama_sekolah']);
        $this->assertTrue($data['has_kop_kiri']);
        $this->assertTrue($data['has_kop_kanan']);
        $this->assertNotEmpty($data['kop_kiri_url']);
        $this->assertTrue(Storage::disk('public')->exists($data['kop_kiri']));

        $this->berkas[] = $data['kop_kiri'];
        $this->berkas[] = $data['kop_kanan'];
    }

    public function test_tata_usaha_tidak_boleh_mengubah_kop()
    {
        $tu = \App\Models\User::create([
            'nama_lengkap'  => 'Uji TU Kop',
            'email'         => 'tu-kop-' . uniqid() . '@contoh.test',
            'password'      => bcrypt('rahasia123'),
            'status'        => 'tata_usaha',
            'jenis_kelamin' => 'L',
            'nisn'          => (string) random_int(8000000000, 8099999999),
            'nis'           => (string) random_int(8000000000, 8099999999),
            'year_id'       => 0,
            'group_id'      => 0,
        ]);

        try {
            $this->actingAs($tu)->post('/admin/pengaturan', ['nama_sekolah' => 'DICURI'])
                ->assertStatus(403);
        } finally {
            $tu->biodata()->delete();
            $tu->delete();
        }
    }
}
