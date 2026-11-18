<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Form pengaturan aplikasi (identitas sekolah, logo, dan kop cetak PDF).
     * Hanya superadmin yang bisa membuka halaman ini (middleware superadmin).
     */
    public function edit()
    {
        return view('admin/pengaturan/index', [
            'title' => 'Pengaturan Aplikasi',
            'setelan' => Setting::data(),
        ]);
    }

    /**
     * Simpan pengaturan aplikasi.
     */
    public function update(Request $request)
    {
        $request->validate([
            'nama_sekolah'   => 'required|string|max:100',
            'alamat_sekolah' => 'nullable|string|max:255',
            'kop_baris1'     => 'nullable|string|max:120',
            'kop_baris2'     => 'nullable|string|max:120',
            'kop_kontak'     => 'nullable|string|max:255',
            'logo'           => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'kop_kiri'       => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'kop_kanan'      => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'hapus_logo'     => 'nullable|boolean',
            'hapus_kop_kiri' => 'nullable|boolean',
            'hapus_kop_kanan'=> 'nullable|boolean',
        ], [], [
            'nama_sekolah'   => 'nama sekolah',
            'alamat_sekolah' => 'alamat sekolah',
            'kop_baris1'     => 'baris pertama kop',
            'kop_baris2'     => 'baris kedua kop',
            'kop_kontak'     => 'baris kontak kop',
            'logo'           => 'logo sekolah',
            'kop_kiri'       => 'gambar kop kiri',
            'kop_kanan'      => 'gambar kop kanan',
        ]);

        Setting::put('nama_sekolah', trim($request->nama_sekolah));
        Setting::put('alamat_sekolah', trim((string) $request->alamat_sekolah));
        Setting::put('kop_baris1', trim((string) $request->kop_baris1));
        Setting::put('kop_baris2', trim((string) $request->kop_baris2));
        Setting::put('kop_kontak', trim((string) $request->kop_kontak));

        // Semua gambar (logo aplikasi, kop kiri, kop kanan) disimpan dengan cara
        // yang sama: unggah baru menggantikan yang lama, centang hapus mengosongkan.
        foreach (Setting::GAMBAR as $field) {
            $lama = Setting::get($field);

            if ($request->hasFile($field)) {
                if ($lama) {
                    Storage::disk('public')->delete($lama);
                }

                $file = $request->file($field);
                $nama = $field . '-' . now()->format('YmdHis') . '-' . Str::random(6)
                    . '.' . strtolower($file->getClientOriginalExtension());
                $folder = $field === 'logo' ? 'logo' : 'kop';

                Setting::put($field, $file->storeAs($folder, $nama, 'public'));
            } elseif ($request->boolean('hapus_' . $field)) {
                if ($lama) {
                    Storage::disk('public')->delete($lama);
                }

                Setting::put($field, null);
            }
        }

        return redirect('/admin/pengaturan')->with('success', 'Pengaturan aplikasi berhasil disimpan!');
    }
}
