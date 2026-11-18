<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * Pengelolaan akun petugas (superadmin & tata usaha).
 *
 * Hanya superadmin yang boleh mengakses controller ini (lihat routes/web.php).
 * Superadmin bisa menambahkan akun Tata Usaha, mengubahnya, dan menghapusnya.
 */
class StaffController extends Controller
{
    /**
     * Daftar akun petugas (superadmin + tata usaha).
     */
    public function index()
    {
        $res = User::whereIn('status', User::STAFF_ROLES)
            ->orderBy('status')
            ->orderBy('nama_lengkap')
            ->get();

        return view('admin/akun/list', [
            'title' => 'Akun Pengguna',
            'res' => $res,
        ]);
    }

    /**
     * Form tambah akun petugas.
     */
    public function create()
    {
        return view('admin/akun/tambah', [
            'title' => 'Tambah Akun',
            'roles' => User::FORM_ROLES,
        ]);
    }

    /**
     * Simpan akun petugas baru.
     *
     * Akun petugas tidak memakai kelas/tahun ajaran, tetapi kolom year_id dan
     * group_id wajib terisi, jadi diisi 0. NISN/NIS juga wajib unik dan
     * diisi nomor acak karena petugas login memakai email.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap'  => 'required|string|max:50',
            'email'         => 'required|email|unique:users,email',
            'password'      => 'required|string|min:6',
            'role'          => 'required|in:' . implode(',', array_keys(User::FORM_ROLES)),
            'jenis_kelamin' => 'required|in:L,P',
        ], [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'role.in'      => 'Peran yang dipilih tidak dikenal.',
        ], [
            'nama_lengkap'  => 'nama lengkap',
            'jenis_kelamin' => 'jenis kelamin',
            'password'      => 'password',
        ]);

        User::create([
            'nama_lengkap'  => $validated['nama_lengkap'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'status'        => $validated['role'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
            'nisn'          => $this->nomorUnik(),
            'nis'           => $this->nomorUnik(),
            'year_id'       => 0,
            'group_id'      => 0,
        ]);

        return redirect('/admin/akun')->with('success', 'Akun ' . User::FORM_ROLES[$validated['role']] . ' berhasil dibuat.');
    }

    /**
     * Form ubah akun petugas.
     */
    public function edit(User $akun)
    {
        abort_unless($akun->isStaff(), 404);

        return view('admin/akun/ubah', [
            'title' => 'Ubah Akun',
            'res' => $akun,
            'roles' => User::FORM_ROLES,
        ]);
    }

    /**
     * Simpan perubahan akun petugas.
     */
    public function update(Request $request, User $akun)
    {
        abort_unless($akun->isStaff(), 404);

        $validated = $request->validate([
            'nama_lengkap'  => 'required|string|max:50',
            'email'         => 'required|email|unique:users,email,' . $akun->id,
            'password'      => 'nullable|string|min:6',
            'role'          => 'required|in:' . implode(',', array_keys(User::FORM_ROLES)),
            'jenis_kelamin' => 'required|in:L,P',
        ], [
            'email.unique' => 'Email ini sudah dipakai akun lain.',
            'role.in'      => 'Peran yang dipilih tidak dikenal.',
        ], [
            'nama_lengkap'  => 'nama lengkap',
            'jenis_kelamin' => 'jenis kelamin',
            'password'      => 'password',
        ]);

        // Superadmin terakhir tidak boleh diturunkan menjadi tata usaha,
        // supaya selalu ada akun yang bisa membuka Pengaturan.
        if ($akun->isSuperadmin() && $validated['role'] !== User::ROLE_SUPERADMIN
            && User::whereIn('status', [User::ROLE_SUPERADMIN, 'admin'])->count() <= 1) {
            return back()->withInput()->with('error', 'Ini satu-satunya akun superadmin. Buat superadmin lain dulu sebelum mengubah perannya.');
        }

        $data = [
            'nama_lengkap'  => $validated['nama_lengkap'],
            'email'         => $validated['email'],
            'status'        => $validated['role'],
            'jenis_kelamin' => $validated['jenis_kelamin'],
        ];

        if (!empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $akun->update($data);

        return redirect('/admin/akun')->with('success', 'Akun berhasil diperbarui.');
    }

    /**
     * Hapus akun petugas.
     */
    public function destroy(User $akun)
    {
        abort_unless($akun->isStaff(), 404);

        if ((int) $akun->id === (int) auth()->id()) {
            return redirect('/admin/akun')->with('error', 'Anda tidak bisa menghapus akun yang sedang dipakai.');
        }

        if ($akun->isSuperadmin() && User::whereIn('status', [User::ROLE_SUPERADMIN, 'admin'])->count() <= 1) {
            return redirect('/admin/akun')->with('error', 'Ini satu-satunya akun superadmin, jadi tidak bisa dihapus.');
        }

        DB::transaction(function () use ($akun) {
            $akun->biodata()->delete();
            $akun->ayah()->delete();
            $akun->ibu()->delete();
            $akun->wali()->delete();
            $akun->mutasi()->delete();
            $akun->delete();
        });

        return redirect('/admin/akun')->with('success', 'Akun berhasil dihapus.');
    }

    /**
     * Nomor acak 9 digit yang belum dipakai sebagai NISN/NIS.
     */
    private function nomorUnik(): string
    {
        do {
            $nomor = (string) random_int(100000000, 999999999);
        } while (User::where('nisn', $nomor)->orWhere('nis', $nomor)->exists());

        return $nomor;
    }
}
