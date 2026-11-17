<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Dad;
use App\Models\Mutation;
use App\Models\Mom;
use App\Models\Group;
use App\Models\Guardian;
use App\Models\Year;
use App\Models\Biodata;

class Admin extends Controller
{
    public function index(){
        $mutasi = Mutation::latest()->get();
        $murid = User::where('status', 'siswa')->get();
        $tahun = Year::all();

        // Siswa bisa saja belum punya data ayah/ibu (mis. hasil impor CSV biasa),
        // jadi semua akses relasi harus aman terhadap null.
        $siswa_yatim_piatu = $murid->filter(function($val){
            return optional($val->ayah)->status === 'Telah Meninggal'
                || optional($val->ibu)->status === 'Telah Meninggal';
        });

        // Hanya dihitung bila penghasilan ayah/ibu memang terisi; siswa tanpa data
        // orang tua jangan otomatis dianggap kurang mampu.
        $siswa_kurang_mampu = $murid->filter(function($val){
            $penghasilan = [optional($val->ayah)->penghasilan, optional($val->ibu)->penghasilan];

            if ($penghasilan[0] === null && $penghasilan[1] === null) {
                return false;
            }

            return (int) $penghasilan[0] + (int) $penghasilan[1] < 3000000;
        });
        $siswa = Biodata::latest()->get();
        $kelas = Group::latest()->get();
        return view('admin/dashboard', [
            'title' => 'Dashboard',
            // Jumlah siswa = jumlah murid aktif, bukan jumlah baris biodata
            // (siswa hasil impor bisa belum punya biodata).
            'jumlah_siswa' => $murid->count(),
            'siswa' => $siswa,
            'jumlah_kelas' => $kelas->count(),
            'kelas' => $kelas,
            'jumlah_mutasi' => $mutasi->count(),
            'mutasi' => $mutasi,
            'murid_perempuan' => $murid->where('jenis_kelamin', 'P')->count(),
            'murid_laki' => $murid->where('jenis_kelamin', 'L')->count(),
            'siswa_kurang_mampu' => $siswa_kurang_mampu->count(),
            'siswa_yatim_piatu' => $siswa_yatim_piatu->count(),
            'tahun' => $tahun->filter(function($value){ return $value->status === 'aktif'; }),
        ]);
    }

    public function move_students(Request $request){
        $validated = $request->validate([
            'daftar'   => 'required|array|min:1',
            'daftar.*' => 'exists:users,id',
            'group_id' => 'required|exists:groups,id',
        ], [
            'daftar.required'   => 'Pilih minimal satu siswa yang akan dipindahkan.',
            'group_id.required' => 'Pilih kelas tujuan terlebih dahulu.',
            'group_id.exists'   => 'Kelas tujuan tidak ditemukan.',
        ]);

        foreach ($validated['daftar'] as $item) {
            User::where('id', $item)->update(['group_id' => $validated['group_id']]);
        }
        return redirect('/admin/grup')->with('success', 'Kelas siswa berhasil diperbarui');
    }

    /**
     * Ubah nilai kosong menjadi null supaya kolom angka/ tanggal tidak error
     * saat sebagian data siswa belum diisi.
     */
    /**
     * Buang kolom yang dikirim kosong oleh form.
     *
     * Formulir boleh dikirim sebagian (mis. hanya satu kolom diisi), jadi kolom
     * kosong TIDAK boleh menimpa data yang sudah tersimpan. Kolom kosong
     * dilewati, bukan diubah menjadi null.
     *
     * Catatan: middleware ConvertEmptyStringsToNull membuat isian kosong tiba
     * sebagai null, jadi null juga harus dibuang.
     */
    private function tanpaKosong(array $validated): array
    {
        return array_filter($validated, function ($nilai) {
            if ($nilai === null) {
                return false;
            }

            return !(is_string($nilai) && trim($nilai) === '');
        });
    }

    public function dad_form(Dad $dad){
        return view('admin/info_siswa/ayah', [
            'title' => 'Data Ayah Siswa',
            'res' => $dad,
            'siswa' => $dad->user,
            'langkah' => 'ayah',
            'lanjut' => request()->boolean('lanjut'),
        ]);
    }

    public function edit_dad(Request $request, Dad $dad){
        // Semua kolom boleh kosong: data yang sudah diisi tetap tersimpan
        $validated = $request->validate([
            'nama' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:50',
            'kewarganegaraan' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:100',
            'pendidikan' => 'nullable|in:SD/Sederajat,SMP/Sederajat,SMA/Sederajat,Diploma,Strata-1,Strata-2,Strata-3',
            'penghasilan' => 'nullable|numeric|min:0',
            'alamat' => 'nullable|string|max:200',
            'nomor_hp' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:50',
        ]);
        Dad::where('uri', $dad->uri)->update($this->tanpaKosong($validated));

        $siswa = $dad->user;

        // Alur pengisian berlanjut: Ayah -> Ibu -> Biodata -> Wali -> Selesai
        if ($request->boolean('lanjut') && $siswa) {
            $ibu = $siswa->ibu;

            // Bila data ibu belum ada, jangan arahkan ke alamat kosong (404)
            if ($ibu) {
                return redirect('/admin/ibu/' . $ibu->uri . '?lanjut=1')
                    ->with('success', 'Data ayah tersimpan. Lanjut ke data ibu.');
            }

            return redirect('/admin/siswa/' . $siswa->nisn)
                ->with('success', 'Data ayah tersimpan. Data ibu belum ada, silakan lengkapi dari halaman siswa.');
        }

        return redirect('/admin/siswa/' . optional($siswa)->nisn)
            ->with('success', 'Data ayah siswa berhasil diperbarui');
    }

    public function mom_form(Mom $mom){
        return view('admin/info_siswa/ibu', [
            'title' => 'Data Ibu Siswa',
            'res' => $mom,
            'siswa' => $mom->user,
            'langkah' => 'ibu',
            'lanjut' => request()->boolean('lanjut'),
        ]);
    }

    public function edit_mom(Request $request, Mom $mom){
        $validated = $request->validate([
            'nama' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:50',
            'kewarganegaraan' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:100',
            'pendidikan' => 'nullable|in:SD/Sederajat,SMP/Sederajat,SMA/Sederajat,Diploma,Strata-1,Strata-2,Strata-3',
            'penghasilan' => 'nullable|numeric|min:0',
            'alamat' => 'nullable|string|max:200',
            'nomor_hp' => 'nullable|string|max:20',
            'status' => 'nullable|string|max:50',
        ]);
        Mom::where('uri', $mom->uri)->update($this->tanpaKosong($validated));

        $siswa = $mom->user;

        if ($request->boolean('lanjut') && $siswa) {
            $biodata = $siswa->biodata;

            if ($biodata) {
                return redirect('/admin/biodata/' . $biodata->uri . '?lanjut=1')
                    ->with('success', 'Data ibu tersimpan. Lanjut ke biodata siswa.');
            }

            return redirect('/admin/siswa/' . $siswa->nisn)
                ->with('success', 'Data ibu tersimpan. Biodata belum ada, silakan lengkapi dari halaman siswa.');
        }

        return redirect('/admin/siswa/' . optional($siswa)->nisn)
            ->with('success', 'Data ibu siswa berhasil diperbarui');
    }

    public function guardian_form(Guardian $guardian){
        return view('admin/info_siswa/wali', [
            'title' => 'Data Wali Siswa',
            'res' => $guardian,
            'siswa' => $guardian->user,
            'langkah' => 'wali',
            'lanjut' => request()->boolean('lanjut'),
        ]);
    }

    public function edit_guardian(Request $request, Guardian $guardian){
        $validated = $request->validate([
            'nama' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:50',
            'kewarganegaraan' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:100',
            'pendidikan' => 'nullable|in:SD/Sederajat,SMP/Sederajat,SMA/Sederajat,Diploma,Strata-1,Strata-2,Strata-3',
            'penghasilan' => 'nullable|numeric|min:0',
            'alamat' => 'nullable|string|max:200',
            'nomor_hp' => 'nullable|string|max:20',
        ]);
        Guardian::where('uri', $guardian->uri)->update($this->tanpaKosong($validated));

        $siswa = $guardian->user;

        if ($request->boolean('lanjut') && $siswa) {
            return redirect('/admin/siswa/' . $siswa->nisn)
                ->with('success', 'Seluruh langkah pengisian data sudah dilewati. Data yang terisi sudah tersimpan.');
        }

        return redirect('/admin/siswa/' . optional($siswa)->nisn)
            ->with('success', 'Data wali siswa berhasil diperbarui');
    }

    public function bio_form(Biodata $biodata){
        return view('admin/info_siswa/biodata', [
            'title' => 'Biodata Siswa',
            'res' => $biodata,
            'siswa' => $biodata->user,
            'langkah' => 'biodata',
            'lanjut' => request()->boolean('lanjut'),
            'kelengkapan' => $biodata->kelengkapan(),
        ]);
    }

    public function edit_bio(Request $request, Biodata $biodata){
        // Semua kolom boleh kosong; yang sudah diisi tetap tersimpan
        $validated = $request->validate([
            'alamat' => 'nullable|string|max:200',
            'kota' => 'nullable|string|max:100',
            'kecamatan' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'anak_ke' => 'nullable|integer|gt:0',
            'jlh_saudara' => 'nullable|integer|gte:0',
            'saudara_tiri' => 'nullable|integer|gte:0',
            'saudara_angkat' => 'nullable|integer|gte:0',
            'bahasa' => 'nullable|string|max:50',
            'agama' => 'nullable|string|max:50',
            'jarak' => 'nullable|numeric|gt:0',
            'nomor_hp' => 'nullable|string|max:20',
            'goldar' => 'nullable|string|max:5',
            'tinggi' => 'nullable|integer|gt:50|lt:300',
            'berat' => 'nullable|integer|gt:20|lt:200',
            'penyakit' => 'nullable|string|max:100',
            'hobi' => 'nullable|string|max:100',
            'kewarganegaraan' => 'nullable|string|max:50',
            'sekolah_asal' => 'nullable|string|max:100',
        ]);

        Biodata::where('uri', $biodata->uri)->update($this->tanpaKosong($validated));

        $siswa = $biodata->user;

        if ($request->boolean('lanjut') && $siswa) {
            $wali = $siswa->wali;

            if ($wali) {
                return redirect('/admin/wali/' . $wali->uri . '?lanjut=1')
                    ->with('success', 'Biodata tersimpan. Lanjut ke data wali.');
            }

            return redirect('/admin/siswa/' . $siswa->nisn)
                ->with('success', 'Biodata tersimpan. Data wali belum ada, silakan lengkapi dari halaman siswa.');
        }

        return redirect('/admin/siswa/' . optional($siswa)->nisn)
            ->with('success', 'Biodata berhasil diperbarui!');
    }

    public function mutasi_form(User $siswa){
        return view('admin/info_siswa/mutasi', [
            'title' => 'Mutasi Siswa',
            'res'=> $siswa,
        ]);
    }

    public function delete_mutasi(Mutation $mutation)
    {
    // opsional: ubah status siswa jadi aktif lagi kalau mutasi dihapus
    $mutation->user->update(['status' => 'siswa']); 

    // hapus mutasi dari database
    $mutation->delete();

    return redirect('/admin/siswa')->with('success', 'Data mutasi berhasil dihapus');
    }


    public function edit_mutasi(Request $request, User $siswa){
    $validated = $request->validate([
        'tujuan' => 'required|string|min:8',
        'alasan' => 'required',
        'tanggal_pindah' => 'required'
    ]);

    // Simpan atau update mutasi siswa
    Mutation::updateOrCreate(
        ['user_id' => $siswa->id],
        $validated
    );

    // Biodata TIDAK dihapus: data siswa tetap utuh untuk arsip/cetak,
    // siswa hanya dikeluarkan dari daftar aktif lewat perubahan status.

    // Ubah status siswa agar tidak masuk list aktif lagi
    $siswa->update([
        'status' => 'mutasi'
    ]);

    return redirect('/dashboard')->with('success', 'Siswa berhasil dimutasi');
}


    public function list_mutasi(){
        return view('admin/info_siswa/list_mutasi', [
            'title' => 'Daftar Mutasi Siswa',
            'res' => Mutation::all(),
            'siswa' => User::all()
        ]);
    }
}
