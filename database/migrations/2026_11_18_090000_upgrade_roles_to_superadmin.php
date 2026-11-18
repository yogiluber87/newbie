<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Memperkenalkan dua peran petugas:
 * - superadmin : akses penuh termasuk Pengaturan dan pembuatan akun Tata Usaha
 * - tata_usaha : akses penuh kecuali Pengaturan
 *
 * Akun lama berstatus "admin" diubah menjadi "superadmin" supaya tidak
 * kehilangan akses. Kolom status tetap string, jadi tidak ada perubahan struktur.
 */
class UpgradeRolesToSuperadmin extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('users')
            ->where('status', 'admin')
            ->update(['status' => 'superadmin']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('users')
            ->where('status', 'superadmin')
            ->update(['status' => 'admin']);
    }
}
