<?php

namespace App\Http\Controllers;
use App\Models\User;
use PDF;
use Illuminate\Http\Request;

class Printer extends Controller
{
    public function daftar_siswa(){
        $res = User::where('status', 'siswa')->get();
        $pdf = PDF::loadView('printout/daftar_siswa', ['res' => $res]);
        return $pdf->download('Daftar Siswa.pdf');
    }

    public function data_siswa(User $siswa){
        // Rute ini hanya memakai middleware auth: siswa boleh mencetak datanya
        // sendiri, admin boleh mencetak semua. Selain itu ditolak.
        $pengguna = auth()->user();
        $admin = $pengguna && $pengguna->status !== 'siswa';

        abort_unless($admin || (int) $pengguna?->id === (int) $siswa->id, 403, 'Anda tidak berhak mencetak data siswa ini.');

        $siswa->load(['biodata', 'ayah', 'ibu', 'wali', 'kelas', 'tahun_ajar']);

        $pdf = PDF::loadView('printout/buku_induk_siswa', ['res' => $siswa]);

        // Nama berkas: buang karakter yang tidak boleh ada di nama file
        $nama = trim(preg_replace('/[\\\\\/:*?"<>|]+/', ' ', (string) $siswa->nama_lengkap));

        // Keterangan kecil di setiap halaman lanjutan + nomor halaman + tanggal cetak
        $nisn    = (string) $siswa->nisn;
        $tanggal = 'Dicetak ' . now()->locale('id')->translatedFormat('d F Y');

        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_document',
            'f' => function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($nama, $nisn, $tanggal) {
                $font  = $fontMetrics->getFont('helvetica', 'normal');
                $kanan = $canvas->get_width() - 15;

                // Halaman 2 dan seterusnya: pengingat siswa siapa yang tercetak
                if ($pageNumber > 1) {
                    $teks = 'Buku Induk Siswa - ' . $nama . ' | NISN ' . $nisn;
                    $canvas->text(15, 20, $teks, $font, 9, [0.3, 0.3, 0.3]);
                    $canvas->line(15, 24, $kanan, 24, [0.8, 0.8, 0.8], 0.6);
                }

                // Kaki halaman: tanggal cetak (kiri) dan nomor halaman (kanan)
                $bawah = $canvas->get_height() - 20;

                $canvas->text(15, $bawah, $tanggal, $font, 8, [0.45, 0.45, 0.45]);

                $nomor = 'Halaman ' . $pageNumber . ' dari ' . $pageCount;
                $lebar = $fontMetrics->getTextWidth($nomor, $font, 8);
                $canvas->text($kanan - $lebar, $bawah, $nomor, $font, 8, [0.45, 0.45, 0.45]);
            },
        ]]);

        return $pdf->download('Buku Induk - ' . $nama . ' - ' . $siswa->nisn . '.pdf');
    }

    /**
     * Cetak buku induk untuk siswa yang dipilih (satu PDF, satu siswa per halaman).
     * Bisa dari daftar NISN terpilih, atau dari kata kunci pencarian.
     */
    public function buku_induk_pilihan(Request $request){
        $request->validate([
            'nisn'   => 'required_without_all:cari,tempat_tinggal,nis,group_id,year_id,jenis_kelamin,status_mutasi,mutasi_dari,mutasi_sampai,penghasilan_min,penghasilan_max|array',
            'nisn.*' => 'string',
            'cari'   => 'nullable|string|max:100',
            'nis'    => 'nullable|string|max:100',
            'tempat_tinggal' => 'nullable|string|max:100',
            'group_id' => 'nullable|integer',
            'year_id'  => 'nullable|integer',
            'penghasilan_min' => 'nullable|numeric',
            'penghasilan_max' => 'nullable|numeric',
            'jenis_kelamin' => 'nullable|in:L,P',
            'status_mutasi' => 'nullable|in:sudah,belum',
            'mutasi_dari' => 'nullable|date',
            'mutasi_sampai' => 'nullable|date',
        ], [], [
            'nisn' => 'pilihan siswa',
            'cari' => 'kata kunci',
        ]);

        $filters = User::filtersDari($request->all());

        $query = User::query()
            ->daftarSiswa($filters)
            ->with(['biodata', 'ayah', 'ibu', 'wali', 'kelas', 'tahun_ajar']);

        if ($request->filled('nisn')) {
            $query->whereIn('nisn', $request->nisn);
        }

        $res = $query->orderBy('nama_lengkap')->get();

        if ($res->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang bisa dicetak.');
        }

        $pdf = PDF::loadView('printout/buku_induk_semua', ['res' => $res]);

        // Nomor halaman + tanggal cetak di setiap halaman
        $tanggal = 'Dicetak ' . now()->locale('id')->translatedFormat('d F Y');

        $pdf->getDomPDF()->setCallbacks([[
            'event' => 'end_document',
            'f' => function ($pageNumber, $pageCount, $canvas, $fontMetrics) use ($tanggal) {
                $font  = $fontMetrics->getFont('helvetica', 'normal');
                $kanan = $canvas->get_width() - 15;
                $bawah = $canvas->get_height() - 20;
                $nomor = 'Halaman ' . $pageNumber . ' dari ' . $pageCount;

                $canvas->text(15, $bawah, $tanggal, $font, 8, [0.45, 0.45, 0.45]);

                $lebar = $fontMetrics->getTextWidth($nomor, $font, 8);
                $canvas->text($kanan - $lebar, $bawah, $nomor, $font, 8, [0.45, 0.45, 0.45]);
            },
        ]]);

        return $pdf->download('Buku Induk '.$res->count().' Siswa.pdf');
    }
}
