<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Biodata extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Pengelompokan kolom biodata per tab pada formulir biodata.
     * Dipakai untuk menandai tab mana yang belum diisi.
     */
    public const TAB_KOLOM = [
        1 => ['alamat', 'kota', 'kecamatan', 'tempat_lahir', 'tanggal_lahir', 'anak_ke', 'jlh_saudara', 'saudara_tiri', 'saudara_angkat'],
        2 => ['bahasa', 'agama', 'jarak', 'nomor_hp', 'goldar', 'tinggi', 'berat'],
        3 => ['penyakit', 'hobi', 'kewarganegaraan', 'sekolah_asal'],
    ];

    public function getRouteKeyName(){
        return 'uri';
    }

    /**
     * Hitung kelengkapan pengisian tiap tab.
     *
     * Hasil: [1 => ['terisi' => 3, 'total' => 9, 'status' => 'sebagian'], ...]
     * Status: "kosong" (belum ada isi), "sebagian", atau "lengkap".
     */
    public function kelengkapan(): array
    {
        $hasil = [];

        foreach (self::TAB_KOLOM as $tab => $kolom) {
            $terisi = 0;

            foreach ($kolom as $nama) {
                $nilai = $this->{$nama};

                // Angka 0 tetap dianggap terisi (mis. saudara_tiri = 0)
                if ($nilai !== null && trim((string) $nilai) !== '') {
                    $terisi++;
                }
            }

            $total = count($kolom);

            $hasil[$tab] = [
                'terisi' => $terisi,
                'total' => $total,
                'status' => $terisi === 0 ? 'kosong' : ($terisi === $total ? 'lengkap' : 'sebagian'),
            ];
        }

        return $hasil;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
