<?php

namespace Tests\Feature;

use App\Models\Group;
use App\Models\User;
use App\Models\Year;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * Ekspor & impor CSV: tanggal harus terbaca sebagai tanggal.
 *
 * Dulu CSV menulis tanggal apa adanya (2008-05-30) sehingga Excel
 * menampilkannya sebagai angka serial (39598). Sekarang tanggal ditulis
 * ="dd/mm/yyyy", dan impor menerima dd/mm/yyyy, yyyy-mm-dd, maupun angka
 * serial Excel.
 */
class CsvTanggalTest extends TestCase
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

    private function admin(): User
    {
        return User::where('status', '!=', 'siswa')->firstOrFail();
    }

    private function siswaUji(): User
    {
        $siswa = User::create([
            'nama_lengkap' => 'Uji Tanggal ' . Str::random(4),
            'nisn' => (string) random_int(7400000000, 7499999999),
            'nis' => (string) random_int(7400000000, 7499999999),
            'group_id' => Group::firstOrFail()->id,
            'year_id' => Year::firstOrFail()->id,
            'jenis_kelamin' => 'L',
            'status' => 'siswa',
            'password' => bcrypt('rahasia'),
        ]);

        $siswa->biodata()->create(['uri' => Str::random(40), 'tempat_lahir' => 'Batusangkar', 'tanggal_lahir' => '2008-05-30']);
        $siswa->ayah()->create(['uri' => Str::random(40), 'nama' => 'Ayah Uji', 'tanggal_lahir' => '1965-04-25']);
        $siswa->ibu()->create(['uri' => Str::random(40), 'nama' => 'Ibu Uji', 'tanggal_lahir' => '1987-05-27']);
        $siswa->wali()->create(['uri' => Str::random(40), 'nama' => 'Wali Uji']);

        $this->siswa = $siswa->fresh();

        return $this->siswa;
    }

    public function test_ekspor_menulis_tanggal_dalam_format_terbaca()
    {
        $siswa = $this->siswaUji();

        $csv = $this->actingAs($this->admin())
            ->get('/admin/siswa/ekspor-semua-csv?cari=' . urlencode($siswa->nama_lengkap))
            ->assertOk()
            ->getContent();

        // fputcsv meng-escape kutip ganda, jadi bentuk mentahnya: =""30/05/2008""
        $this->assertStringContainsString('=""30/05/2008""', $csv, 'tanggal biodata harus dd/mm/yyyy');
        $this->assertStringContainsString('=""25/04/1965""', $csv, 'tanggal ayah harus dd/mm/yyyy');
        $this->assertStringContainsString('=""27/05/1987""', $csv, 'tanggal ibu harus dd/mm/yyyy');

        // Format lama yang bikin Excel menampilkan angka serial tidak boleh muncul lagi
        $this->assertStringNotContainsString('"2008-05-30"', $csv);
    }

    public function test_ekspor_lalu_impor_kembali_tanggal_tetap_benar()
    {
        $siswa = $this->siswaUji();
        $admin = $this->admin();

        $csv = $this->actingAs($admin)
            ->get('/admin/siswa/ekspor-semua-csv?cari=' . urlencode($siswa->nama_lengkap))
            ->assertOk()
            ->getContent();

        // Kosongkan dulu supaya ketahuan apakah impor benar-benar memulihkan
        $siswa->biodata()->update(['tanggal_lahir' => null]);

        $this->actingAs($admin)->post('/admin/siswa/import-lengkap', [
            'file' => UploadedFile::fake()->createWithContent('data.csv', $csv),
        ])->assertRedirect();

        $this->assertSame('2008-05-30', substr((string) $siswa->biodata->fresh()->tanggal_lahir, 0, 10));
        $this->assertSame('1965-04-25', substr((string) $siswa->ayah->fresh()->tanggal_lahir, 0, 10));
        $this->assertSame('1987-05-27', substr((string) $siswa->ibu->fresh()->tanggal_lahir, 0, 10));
        $this->assertSame(1, User::where('nisn', $siswa->nisn)->count(), 'impor tidak boleh membuat duplikat');
    }

    public function test_impor_menerima_berbagai_format_tanggal()
    {
        $csv = implode("\n", [
            'nama_lengkap;nisn;nis;jenis_kelamin;biodata_tanggal_lahir;ayah_tanggal_lahir;ibu_tanggal_lahir',
            'Uji Format Tanggal;7400000001;7400000001;L;30/05/2008;39598;2008-05-30',
        ]);

        $siswa = null;

        try {
            $this->actingAs($this->admin())->post('/admin/siswa/import-lengkap', [
                'file' => UploadedFile::fake()->createWithContent('format.csv', $csv),
            ])->assertRedirect();

            $siswa = User::where('nisn', '7400000001')->firstOrFail();

            $this->assertSame('2008-05-30', substr((string) $siswa->biodata->tanggal_lahir, 0, 10), 'dd/mm/yyyy harus dikenali');
            $this->assertSame('2008-05-30', substr((string) $siswa->ayah->tanggal_lahir, 0, 10), 'angka serial Excel harus dikenali');
            $this->assertSame('2008-05-30', substr((string) $siswa->ibu->tanggal_lahir, 0, 10), 'yyyy-mm-dd harus dikenali');
        } finally {
            if ($siswa) {
                $siswa->ayah()->delete();
                $siswa->ibu()->delete();
                $siswa->biodata()->delete();
                $siswa->wali()->delete();
                $siswa->delete();
            }
        }
    }
}
