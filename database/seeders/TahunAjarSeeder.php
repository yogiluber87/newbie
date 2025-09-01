<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TahunAjar;

class TahunAjarSeeder extends Seeder
{
    public function run(): void
    {
        TahunAjar::create([
            'tahun_ajaran' => '2024/2025',
        ]);

        TahunAjar::create([
            'tahun_ajaran' => '2025/2026',
        ]);
    }
}
