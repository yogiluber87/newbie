<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Group;
use App\Models\Year;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $siswa = User::where('status', 'siswa');
        return view('admin/siswa/list', [
            'title' => 'Daftar Siswa',
            'res' => $siswa->filter(request(['cari']))->paginate(30)->withQueryString(),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('admin/siswa/tambah',[
            'title' => 'Tambah Siswa',
            'kelas' => Group::latest()->get(),
            'tahun' => Year::latest()->get(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|max:50',
            'nisn' => 'required|max:16|regex:/[0-9]/|unique:users,nisn',
            'nis' => 'required|max:16|regex:/[0-9]/|unique:users,nis',
            'group_id' => 'required|integer|exists:groups,id',
            'year_id' => 'required|integer|exists:years,id',
            'jenis_kelamin' => 'required',
            'email' => 'nullable|email|unique:users,email',
            'foto' => 'nullable|image|file|max:1024',
        ], [
            'nisn.unique' => 'NISN ini sudah terdaftar. Gunakan NISN lain.',
            'nis.unique'  => 'NIS ini sudah terdaftar. Gunakan NIS lain.',
            'nisn.regex'  => 'NISN hanya boleh berisi angka.',
            'nis.regex'   => 'NIS hanya boleh berisi angka.',
        ]);
        unset($validated['foto']);
        if($request->hasFile('foto') && $request->file('foto')->isValid()){
            $file = $request->file('foto');
            $src = $file->getRealPath() ?: $file->getPathname();
            if($src && file_exists($src) && is_readable($src)){
                try {
                    $name = 'foto-siswa/'.\Illuminate\Support\Str::random(40).'.'.$file->getClientOriginalExtension();
                    if(\Illuminate\Support\Facades\Storage::disk("public")->put($name, file_get_contents($src))){
                        $validated['foto'] = $name;
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Upload foto gagal: '.$e->getMessage());
                }
            }
        }
        $validated['status'] = 'siswa';
        $validated['password'] = Hash::make('siswa123');

        $user = User::create($validated);
        $ayah = $user->ayah()->create(['uri' => Str::random(40)]);
        $ibu = $user->ibu()->create(['uri' => Str::random(40)]);
        $biodata = $user->biodata()->create(['uri' => Str::random(40)]);
        $wali = $user->wali()->create(['uri' => Str::random(40)]);
        return redirect('/dashboard')->with('success', 'Siswa Berhasil Ditambahkan');
    }
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function show(User $siswa)
    {
        return view('admin/siswa/detail', [
            'title' => $siswa->nama_lengkap,
            'res' => $siswa
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function edit(User $siswa)
    {
        return view('admin/siswa/ubah', [
            'title' => 'Ubah Data Siswa',
            'res' => $siswa,
            'kelas' => Group::latest()->get(),
            'tahun' => Year::latest()->get(),
        ]);
    }

    /**
     * Cek ketersediaan NISN dan NIS secara langsung (AJAX).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function cekDuplikat(Request $request)
    {
        $nisn  = trim((string) $request->input('nisn'));
        $nis   = trim((string) $request->input('nis'));
        $abaikan = $request->input('abaikan');

        $cek = function ($kolom, $nilai) use ($abaikan) {
            if ($nilai === '') {
                return false;
            }
            $q = User::where($kolom, $nilai);
            if ($abaikan) {
                $q->where('id', '!=', $abaikan);
            }
            return $q->exists();
        };

        return response()->json([
            'nisn_dipakai' => $cek('nisn', $nisn),
            'nis_dipakai'  => $cek('nis', $nis),
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, User $siswa)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|max:50',
            'nisn' => 'required|max:16|regex:/[0-9]/|unique:users,nisn,'.$siswa->id,
            'nis' => 'required|max:16|regex:/[0-9]/|unique:users,nis,'.$siswa->id,
            'jenis_kelamin' => 'required',
            'group_id' => 'required|integer|exists:groups,id',
            'year_id' => 'required|integer|exists:years,id',
            'email' => 'nullable|email|unique:users,email,'.$siswa->id,
            'foto' => 'nullable|image|file|max:1024',
        ], [
            'nisn.unique' => 'NISN ini sudah terdaftar. Gunakan NISN lain.',
            'nis.unique'  => 'NIS ini sudah terdaftar. Gunakan NIS lain.',
            'email.unique' => 'Email ini sudah terdaftar.',
            'nisn.regex'  => 'NISN hanya boleh berisi angka.',
            'nis.regex'   => 'NIS hanya boleh berisi angka.',
        ]);
        unset($validated['foto']);
        if ($request->hasFile('foto') && $request->file('foto')->isValid()) {
            $file = $request->file('foto');
            $src = $file->getRealPath() ?: $file->getPathname();
            if ($src && file_exists($src) && is_readable($src)) {
                if($siswa->foto != null){
                    Storage::disk("public")->delete($siswa->foto);
                }
                try {
                    $name = 'foto-siswa/'.\Illuminate\Support\Str::random(40).'.'.$file->getClientOriginalExtension();
                    if(\Illuminate\Support\Facades\Storage::disk("public")->put($name, file_get_contents($src))){
                        $validated['foto'] = $name;
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Upload foto gagal: '.$e->getMessage());
                }
            }
        }
        
        $siswa->update($validated);
        return redirect('/admin/siswa')->with('success', 'Data siswa berhasil diubah');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\User  $user
     * @return \Illuminate\Http\Response
     */
    public function destroy(User $siswa)
    {
        DB::transaction(function () use ($siswa) {
            if ($siswa->foto) {
                Storage::disk("public")->delete($siswa->foto);
            }
            $siswa->biodata()->delete();
            $siswa->ayah()->delete();
            $siswa->ibu()->delete();
            $siswa->wali()->delete();
            $siswa->mutasi()->delete();
            $siswa->delete();
        });

        return redirect('/admin/siswa')->with('success', 'Data siswa berhasil dihapus');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

}
