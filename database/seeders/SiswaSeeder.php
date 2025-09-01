<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Siswa;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        Siswa::create([
            'nisn' => '20280',
            'nis' => '001',
            'nama_lengkap' => 'Budi Santoso',
            'jenis_kelamin' => 'L',
            'tahun_id' => 1, // relasi ke TahunAjar ID=1
        ]);
    }
}
