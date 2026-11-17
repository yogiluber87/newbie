<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Dad;
use App\Models\Mom;
use App\Models\Guardian;
use App\Models\Biodata;

class Siswa extends Controller
{

    /**
     * Pastikan siswa hanya bisa membuka/mengubah data miliknya sendiri.
     * Tanpa ini, siapa pun yang sudah login bisa mengubah biodata/orang tua
     * siswa lain hanya dengan menebak alamat URL-nya.
     */
    private function pastikanMilikSendiri($model): void
    {
        $pemilik = (int) ($model->user_id ?? $model->id ?? 0);

        abort_unless($pemilik === (int) auth()->id(), 403, 'Anda hanya dapat mengubah data milik sendiri.');
    }

    public function index(){
        return view('siswa/home', [
            'title' => 'Beranda',
        ]);
    }

    public function bio_form(Biodata $biodata){
        $this->pastikanMilikSendiri($biodata);
        return view('siswa.biodata', [
            'title' => 'Biodata Siswa',
            'res' => $biodata,
        ]);
    }

    public function edit_bio(Request $request, Biodata $biodata){
        $this->pastikanMilikSendiri($biodata);
        $validated = $request->validate([
            'alamat' => 'nullable|string',
            'kota' => 'nullable|string',
            'kecamatan' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'anak_ke' => 'nullable|integer|gt:0',
            'jlh_saudara' => 'nullable|integer|gte:0',
            'saudara_tiri' => 'nullable|integer|gte:0',
            'saudara_angkat' => 'nullable|integer|gte:0',
            'bahasa' => 'nullable',
            'agama' => 'nullable',
            'jarak' => 'nullable|numeric|gt:0',
            'nomor_hp' => 'nullable|string|max:13|regex:/[0-9]/',
            'goldar' => 'nullable',
            'tinggi' => 'nullable|integer|gt:50|lt:300',
            'berat' => 'nullable|integer|gt:20|lt:200',
            'penyakit' => 'nullable|string',
            'hobi' => 'nullable|string',
            'kewarganegaraan' => 'nullable|string',
            'sekolah_asal' => 'nullable',
        ]);
        
        Biodata::where('uri', $biodata->uri)->update($this->tanpaKosong($validated));
        return redirect('/home')->with('Updated', 'Biodata berhasil diperbarui!');
    }

    public function dad_form(Dad $dad){
        $this->pastikanMilikSendiri($dad);
        return view('siswa.ayah', [
            'title' => 'Data Ayah Siswa',
            'res' => $dad
        ]);
    }

    public function edit_dad(Request $request, Dad $dad){
        $this->pastikanMilikSendiri($dad);
        $validated = $request->validate([
            'nama' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable',
            'kewarganegaraan' => 'nullable|string',
            'pekerjaan' => 'nullable|string',
            'pendidikan' => 'nullable',
            'penghasilan' => 'nullable|numeric',
            'alamat' => 'nullable|string|max:200',
            'nomor_hp' => 'nullable|string|max:13|regex:/[0-9]/',
            'status' => 'nullable',
        ]);
        Dad::where('uri', $dad->uri)->update($this->tanpaKosong($validated));
        return redirect('/home')->with('Updated', 'Data ayah siswa berhasil diperbarui');
    }

    public function mom_form(Mom $mom){
        $this->pastikanMilikSendiri($mom);
        return view('siswa.ibu', [
            'title' => 'Data Ibu Siswa',
            'res' => $mom
        ]);
    }

    public function edit_mom(Request $request, Mom $mom){
        $this->pastikanMilikSendiri($mom);
        $validated = $request->validate([
            'nama' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable',
            'kewarganegaraan' => 'nullable|string',
            'pekerjaan' => 'nullable|string',
            'pendidikan' => 'nullable',
            'penghasilan' => 'nullable|numeric',
            'alamat' => 'nullable|string|max:200',
            'nomor_hp' => 'nullable|string|max:13|regex:/[0-9]/',
            'status' => 'nullable',
        ]);
        Mom::where('uri', $mom->uri)->update($this->tanpaKosong($validated));
        return redirect('/home')->with('Updated', 'Data ibu siswa berhasil diperbarui');
    }

    public function guardian_form(Guardian $guardian){
        $this->pastikanMilikSendiri($guardian);
        return view('siswa.wali', [
            'title' => 'Data Wali Siswa',
            'res' => $guardian,
        ]);
    }

    public function edit_guardian(Request $request, Guardian $guardian){
        $this->pastikanMilikSendiri($guardian);
        // Semua kolom boleh kosong supaya pengisian bertahap tidak terblokir.
        $validated = $request->validate([
            'nama' => 'nullable|string|max:100',
            'tempat_lahir' => 'nullable|string|max:100',
            'tanggal_lahir' => 'nullable|date',
            'agama' => 'nullable|string|max:50',
            'kewarganegaraan' => 'nullable|string|max:50',
            'pekerjaan' => 'nullable|string|max:100',
            'pendidikan' => 'nullable',
            'penghasilan' => 'nullable|numeric|min:0',
            'alamat' => 'nullable|string|max:200',
            'nomor_hp' => 'nullable|string|max:20',
        ]);
        Guardian::where('uri', $guardian->uri)->update($this->tanpaKosong($validated));
        return redirect('/home')->with('Updated', 'Data wali siswa berhasil diperbarui');
    }

    public function password_form(User $user){
        abort_unless((int) $user->id === (int) auth()->id(), 403, 'Anda hanya dapat mengubah password milik sendiri.');
        return view('siswa.password', [
            'title' => 'Ubah Password',
        ]);
    }

    public function change_password(Request $request, User $user){
        abort_unless((int) $user->id === (int) auth()->id(), 403, 'Anda hanya dapat mengubah password milik sendiri.');
        $validated = $request->validate([
            'old_password' => ['required', function ($attribute, $value, $fail) use ($user) {
                if (!\Hash::check($value, $user->password)) {
                    return $fail(__('Password saat ini tidak sesuai'));
                }
            }],
            'new_password' =>  [
                                    'required',
                                    'string',
                                    'min:8',             // must be at least 10 characters in length
                                    'regex:/[a-z]/',      // must contain at least one lowercase letter
                                    'regex:/[A-Z]/',      // must contain at least one uppercase letter
                                    'regex:/[0-9]/',      // must contain at least one digit
                                    'regex:/[@$!%*#?&]/', // must contain a special character
                                ],
        ]);
        $new_password = Hash::make($validated['new_password']);
        User::where('id', $user->id)->update(['password' => $new_password]);
        return redirect('/home')->with('Updated', 'Password berhasil diperbarui');
    }

    /**
     * Buang kolom yang dikirim kosong oleh form, supaya data yang sudah
     * tersimpan tidak terhapus saat siswa hanya mengisi sebagian kolom.
     * (Middleware ConvertEmptyStringsToNull membuat isian kosong menjadi null.)
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

}
