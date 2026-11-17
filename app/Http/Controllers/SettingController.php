<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    /**
     * Form pengaturan aplikasi (nama sekolah + logo).
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
            'logo'           => 'nullable|image|mimes:png,jpg,jpeg,webp|max:2048',
            'hapus_logo'     => 'nullable|boolean',
        ], [], [
            'nama_sekolah'   => 'nama sekolah',
            'alamat_sekolah' => 'alamat sekolah',
            'logo'           => 'logo sekolah',
        ]);

        Setting::put('nama_sekolah', trim($request->nama_sekolah));
        Setting::put('alamat_sekolah', trim((string) $request->alamat_sekolah));

        $logoLama = Setting::get('logo');

        if ($request->hasFile('logo')) {
            // Hapus logo lama kalau ada
            if ($logoLama) {
                Storage::disk('public')->delete($logoLama);
            }

            $file = $request->file('logo');
            $nama = 'logo-' . now()->format('YmdHis') . '-' . Str::random(6)
                . '.' . strtolower($file->getClientOriginalExtension());

            $path = $file->storeAs('logo', $nama, 'public');

            Setting::put('logo', $path);
        } elseif ($request->boolean('hapus_logo')) {
            if ($logoLama) {
                Storage::disk('public')->delete($logoLama);
            }

            Setting::put('logo', null);
        }

        return redirect('/admin/pengaturan')->with('success', 'Pengaturan aplikasi berhasil disimpan!');
    }
}
