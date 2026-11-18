<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Cadangan penayangan berkas unggahan bila hosting tidak menyediakan
 * symlink public/storage (umum terjadi di cPanel).
 */
class StorageFallbackTest extends TestCase
{
    private array $berkas = [];

    protected function tearDown(): void
    {
        foreach ($this->berkas as $path) {
            if (Storage::disk('public')->exists($path)) {
                Storage::disk('public')->delete($path);
            }
        }

        parent::tearDown();
    }

    public function test_berkas_unggahan_dapat_disajikan_lewat_rute_cadangan()
    {
        Storage::disk('public')->put('kop/uji-tampil.txt', 'isi uji kop');
        $this->berkas[] = 'kop/uji-tampil.txt';

        $res = $this->get('/storage/kop/uji-tampil.txt');

        $res->assertOk();
        $this->assertSame('isi uji kop', $res->baseResponse->getFile()->getContent());
    }

    public function test_berkas_yang_tidak_ada_menghasilkan_404()
    {
        $this->get('/storage/kop/tidak-ada.txt')->assertNotFound();
    }

    public function test_tidak_bisa_keluar_dari_folder_storage()
    {
        $this->get('/storage/../../.env')->assertNotFound();
        $this->get('/storage/..%2F..%2F.env')->assertNotFound();
    }
}
