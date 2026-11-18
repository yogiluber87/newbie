<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Aturan akses dua peran petugas:
 * - superadmin : semua menu + Pengaturan + pengelolaan akun
 * - tata_usaha : semua menu petugas kecuali Pengaturan dan pengelolaan akun
 * - siswa      : tidak boleh masuk area admin sama sekali
 */
class PeranAksesTest extends TestCase
{
    /** @var User[] */
    private array $dibuat = [];

    protected function tearDown(): void
    {
        foreach ($this->dibuat as $user) {
            $user->biodata()->delete();
            $user->ayah()->delete();
            $user->ibu()->delete();
            $user->wali()->delete();
            $user->mutasi()->delete();
            $user->delete();
        }

        $this->dibuat = [];
        parent::tearDown();
    }

    private function akun(string $status): User
    {
        $user = User::create([
            'nama_lengkap'  => 'Uji Peran ' . Str::random(4),
            'email'         => 'uji-' . Str::random(10) . '@contoh.test',
            'password'      => bcrypt('rahasia123'),
            'status'        => $status,
            'jenis_kelamin' => 'L',
            'nisn'          => (string) random_int(8000000000, 8099999999),
            'nis'           => (string) random_int(8000000000, 8099999999),
            'year_id'       => 0,
            'group_id'      => 0,
        ]);

        $this->dibuat[] = $user;

        return $user;
    }

    public function test_superadmin_bisa_membuka_semua_menu_petugas()
    {
        $superadmin = $this->akun('superadmin');

        foreach (['/dashboard', '/admin/siswa', '/admin/grup', '/admin/tahun', '/admin/mutasi', '/admin/akun', '/admin/akun/create', '/admin/pengaturan'] as $url) {
            $this->actingAs($superadmin)->get($url)->assertStatus(200);
        }

        // Form ubah akun (lewat NISN milik akun petugas)
        $tu = $this->akun('tata_usaha');
        $this->actingAs($superadmin)->get('/admin/akun/' . $tu->nisn . '/edit')->assertStatus(200);
    }

    public function test_tata_usaha_bisa_semua_kecuali_pengaturan_dan_akun()
    {
        $tu = $this->akun('tata_usaha');

        foreach (['/dashboard', '/admin/siswa', '/admin/grup', '/admin/tahun', '/admin/mutasi'] as $url) {
            $this->actingAs($tu)->get($url)->assertStatus(200);
        }

        // Pengaturan ditolak
        $this->actingAs($tu)->get('/admin/pengaturan')->assertStatus(403);
        $this->actingAs($tu)->post('/admin/pengaturan', [])->assertStatus(403);

        // Pengelolaan akun ditolak
        $this->actingAs($tu)->get('/admin/akun')->assertStatus(403);
        $this->actingAs($tu)->get('/admin/akun/create')->assertStatus(403);
        $this->actingAs($tu)->post('/admin/akun', [])->assertStatus(403);
    }

    public function test_siswa_tidak_bisa_masuk_area_admin()
    {
        $siswa = $this->akun('siswa');

        $this->actingAs($siswa)->get('/dashboard')->assertStatus(403);
        $this->actingAs($siswa)->get('/admin/siswa')->assertStatus(403);
        $this->actingAs($siswa)->get('/admin/akun')->assertStatus(403);
        $this->actingAs($siswa)->get('/admin/pengaturan')->assertStatus(403);
    }

    public function test_superadmin_bisa_membuat_dan_mengubah_akun_tata_usaha()
    {
        $superadmin = $this->akun('superadmin');
        $email = 'tu-baru-' . Str::random(8) . '@contoh.test';

        $this->actingAs($superadmin)->post('/admin/akun', [
            'nama_lengkap'  => 'Tata Usaha Baru',
            'email'         => $email,
            'password'      => 'rahasia123',
            'role'          => 'tata_usaha',
            'jenis_kelamin' => 'P',
        ])->assertRedirect('/admin/akun');

        $tu = User::where('email', $email)->firstOrFail();
        $this->dibuat[] = $tu;

        $this->assertSame('tata_usaha', $tu->status);
        $this->assertTrue($tu->isTataUsaha());
        $this->assertFalse($tu->isSuperadmin());
        $this->assertNotEmpty($tu->nisn);
        $this->assertNotEmpty($tu->nis);

        // Akun baru bisa login dan membuka dashboard
        $this->actingAs($tu)->get('/dashboard')->assertStatus(200);

        // Superadmin bisa mengubah peran akun tersebut
        $this->actingAs($superadmin)->put('/admin/akun/' . $tu->nisn, [
            'nama_lengkap'  => 'Tata Usaha Diubah',
            'email'         => $email,
            'role'          => 'superadmin',
            'jenis_kelamin' => 'P',
        ])->assertRedirect('/admin/akun');

        $this->assertSame('superadmin', $tu->fresh()->status);
    }

    public function test_superadmin_tidak_bisa_menghapus_dirinya_sendiri()
    {
        $superadmin = $this->akun('superadmin');

        $this->actingAs($superadmin)
            ->delete('/admin/akun/' . $superadmin->nisn)
            ->assertRedirect('/admin/akun');

        $this->assertNotNull($superadmin->fresh());
    }

    public function test_sidebar_menyembunyikan_pengaturan_untuk_tata_usaha()
    {
        $tu = $this->akun('tata_usaha');
        $superadmin = $this->akun('superadmin');

        $this->actingAs($tu)->get('/dashboard')
            ->assertStatus(200)
            ->assertDontSee('/admin/pengaturan')
            ->assertDontSee('/admin/akun');

        $this->actingAs($superadmin)->get('/dashboard')
            ->assertStatus(200)
            ->assertSee('/admin/pengaturan')
            ->assertSee('/admin/akun');
    }

    public function test_akun_lama_berstatus_admin_diperlakukan_sebagai_superadmin()
    {
        $lama = $this->akun('admin');

        $this->assertTrue($lama->isSuperadmin());
        $this->assertTrue($lama->isStaff());

        $this->actingAs($lama)->get('/admin/pengaturan')->assertStatus(200);
        $this->actingAs($lama)->get('/admin/akun')->assertStatus(200);
    }
}
