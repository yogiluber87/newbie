<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Setting extends Model
{
    protected $fillable = ['key', 'value'];

    /**
     * Nilai bawaan (dipakai jika belum pernah disimpan ke database).
     */
    public const DEFAULTS = [
        'nama_sekolah'   => 'Buku Induk Siswa',
        'alamat_sekolah' => '',
        'logo'           => null,
        // Kop cetak PDF (kertas surat)
        'kop_baris1'     => '',   // contoh: PEMERINTAH PROVINSI SUMATERA BARAT
        'kop_baris2'     => '',   // contoh: DINAS PENDIDIKAN
        'kop_kontak'     => '',   // contoh: Jalan ... • e-mail : ... • Telp. ...
        'kop_kiri'       => null, // gambar kop sisi kiri (path storage)
        'kop_kanan'      => null, // gambar kop sisi kanan (path storage)
    ];

    /**
     * Kunci gambar yang bisa diunggah dari halaman Pengaturan.
     */
    public const GAMBAR = ['logo', 'kop_kiri', 'kop_kanan'];

    /**
     * Cache per-request agar tidak query berulang.
     *
     * @var array|null
     */
    protected static $cached = null;

    /**
     * Ambil semua pengaturan dalam bentuk array siap pakai di view.
     */
    public static function data(): array
    {
        if (static::$cached !== null) {
            return static::$cached;
        }

        $values = [];

        try {
            $values = static::query()->pluck('value', 'key')->all();
        } catch (\Throwable $e) {
            // Tabel belum ada (misal saat migrasi / fresh clone)
            $values = [];
        }

        $data = static::DEFAULTS;

        foreach ($values as $key => $value) {
            if ($value !== null) {
                $data[$key] = $value;
            }
        }

        $logo = $data['logo'];
        $logoExists = $logo && Storage::disk('public')->exists($logo);

        $data['has_logo'] = (bool) $logoExists;
        $data['logo_url'] = $logoExists
            ? asset('storage/' . $logo)
            : asset('images/tut wuri handayani.png');

        // Gambar kop cetak PDF: kiri (fallback ke logo aplikasi) dan kanan.
        foreach (['kop_kiri', 'kop_kanan'] as $kunci) {
            $path = $data[$kunci] ?? null;
            $ada = $path && Storage::disk('public')->exists($path);

            $data['has_' . $kunci] = (bool) $ada;
            $data[$kunci . '_url'] = $ada ? asset('storage/' . $path) : null;
        }

        return static::$cached = $data;
    }

    /**
     * Ambil satu pengaturan.
     */
    public static function get(string $key, $default = null)
    {
        $data = static::data();

        if (array_key_exists($key, $data)) {
            return $data[$key];
        }

        return $default;
    }

    /**
     * Simpan / ubah satu pengaturan.
     */
    public static function put(string $key, $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);

        static::$cached = null;
    }

    /**
     * Buang cache agar data diambil ulang dari database.
     */
    public static function flushCache(): void
    {
        static::$cached = null;
    }
}
