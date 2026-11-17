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
    ];

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
}
