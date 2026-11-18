<?php

namespace App\Http\Controllers;

use App\Models\User;
use PDF;
use Illuminate\Http\Request;

/**
 * Cetak PDF: daftar siswa, buku induk satu siswa, dan buku induk banyak siswa.
 *
 * Setiap cetakan punya dua jalur:
 * - halaman preview (/admin/print/.../preview) → menampilkan PDF di dalam aplikasi
 * - berkas (?inline=1 untuk tampil di browser, tanpa inline untuk unduh)
 */
class Printer extends Controller
{
    /**
     * Kunci session untuk menyimpan pilihan siswa yang akan dicetak saat preview.
     */
    private const SESSION_PILIHAN = 'print_pilihan';

    /* ------------------------------------------------------------------
     | Pembuat PDF
     | ------------------------------------------------------------------ */

    /**
     * PDF daftar seluruh siswa aktif.
     */
    private function pdfDaftarSiswa()
    {
        $res = User::where('status', 'siswa')->get();

        return PDF::loadView('printout/daftar_siswa', ['res' => $res]);
    }

    /**
     * PDF buku induk satu siswa, lengkap dengan kaki halaman dan nomor halaman.
     */
    private function pdfBukuIndukSiswa(User $siswa)
    {
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

        return $pdf;
    }

    /**
     * PDF buku induk untuk banyak siswa (satu siswa per halaman).
     *
     * @param  \Illuminate\Support\Collection  $res
     */
    private function pdfBukuIndukBanyak($res)
    {
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

        return $pdf;
    }

    /* ------------------------------------------------------------------
     | Bantuan
     | ------------------------------------------------------------------ */

    /**
     * Kirim PDF: tampil di browser (preview/cetak) atau unduh sebagai berkas.
     */
    private function jawab($pdf, string $nama, bool $inline)
    {
        return $inline
            ? $pdf->stream($nama, ['Attachment' => 0])
            : $pdf->download($nama);
    }

    /**
     * Hanya petugas (superadmin / tata usaha) atau siswa pemilik data yang
     * boleh mencetak. Rute siswa memakai middleware auth saja.
     */
    private function pastikanBolehCetak(User $siswa): void
    {
        $pengguna = auth()->user();
        $petugas  = $pengguna && $pengguna->isStaff();

        abort_unless($petugas || (int) $pengguna?->id === (int) $siswa->id, 403, 'Anda tidak berhak mencetak data siswa ini.');
    }

    /**
     * Validasi isian cetak pilihan (NISN terpilih atau filter pencarian).
     */
    private function validasiPilihan(Request $request): void
    {
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
    }

    /**
     * Ambil siswa yang akan dicetak dari daftar NISN dan/atau filter.
     */
    private function siswaPilihan(array $nisn, array $filters)
    {
        $query = User::query()
            ->daftarSiswa($filters)
            ->with(['biodata', 'ayah', 'ibu', 'wali', 'kelas', 'tahun_ajar']);

        if (!empty($nisn)) {
            $query->whereIn('nisn', $nisn);
        }

        return $query->orderBy('nama_lengkap')->get();
    }

    /**
     * Ambil pilihan yang tersimpan di session (dipakai halaman preview).
     */
    private function pilihanDariSession(): ?array
    {
        $pilihan = session(self::SESSION_PILIHAN);

        return is_array($pilihan) && isset($pilihan['nisn'], $pilihan['filters']) ? $pilihan : null;
    }

    /* ------------------------------------------------------------------
     | Daftar siswa
     | ------------------------------------------------------------------ */

    /**
     * PDF daftar siswa. Tambahkan ?inline=1 supaya tampil di browser
     * (dipakai oleh iframe halaman preview).
     */
    public function daftar_siswa(Request $request)
    {
        return $this->jawab($this->pdfDaftarSiswa(), 'Daftar Siswa.pdf', $request->boolean('inline'));
    }

    /**
     * Halaman preview daftar siswa.
     */
    public function preview_daftar()
    {
        return view('admin/print/preview', [
            'title'      => 'Preview Daftar Siswa',
            'judul'      => 'Preview Cetak — Daftar Siswa',
            'keterangan' => 'Semua siswa aktif. Periksa dulu sebelum mengunduh atau mencetak.',
            'previewUrl' => '/admin/print?inline=1',
            'unduhUrl'   => '/admin/print',
            'kembali'    => '/admin/siswa',
        ]);
    }

    /* ------------------------------------------------------------------
     | Buku induk satu siswa
     | ------------------------------------------------------------------ */

    /**
     * PDF buku induk satu siswa. Tambahkan ?inline=1 untuk tampil di browser.
     */
    public function data_siswa(Request $request, User $siswa)
    {
        $this->pastikanBolehCetak($siswa);

        $nama = trim(preg_replace('/[\\\\\/:*?"<>|]+/', ' ', (string) $siswa->nama_lengkap));

        return $this->jawab(
            $this->pdfBukuIndukSiswa($siswa),
            'Buku Induk - ' . $nama . ' - ' . $siswa->nisn . '.pdf',
            $request->boolean('inline')
        );
    }

    /**
     * Halaman preview buku induk satu siswa (petugas atau siswa pemilik data).
     */
    public function preview_siswa(User $siswa)
    {
        $this->pastikanBolehCetak($siswa);

        return view('admin/print/preview', [
            'title'      => 'Preview Buku Induk',
            'judul'      => 'Preview Cetak — Buku Induk Siswa',
            'keterangan' => $siswa->nama_lengkap . ' • NISN ' . $siswa->nisn,
            'previewUrl' => '/admin/print/' . $siswa->nisn . '?inline=1',
            'unduhUrl'   => '/admin/print/' . $siswa->nisn,
            'kembali'    => auth()->user()->isStaff() ? '/admin/siswa/' . $siswa->nisn : '/home',
        ]);
    }

    /* ------------------------------------------------------------------
     | Buku induk banyak siswa (dipilih / hasil filter)
     | ------------------------------------------------------------------ */

    /**
     * Cetak langsung (unduh) buku induk siswa terpilih atau hasil pencarian.
     */
    public function buku_induk_pilihan(Request $request)
    {
        $this->validasiPilihan($request);

        $filters = User::filtersDari($request->all());
        $res = $this->siswaPilihan((array) $request->input('nisn', []), $filters);

        if ($res->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang bisa dicetak.');
        }

        return $this->jawab(
            $this->pdfBukuIndukBanyak($res),
            'Buku Induk ' . $res->count() . ' Siswa.pdf',
            $request->boolean('inline')
        );
    }

    /**
     * Simpan pilihan lalu buka halaman preview.
     */
    public function siapkan_preview_pilihan(Request $request)
    {
        $this->validasiPilihan($request);

        $nisn    = array_values(array_filter((array) $request->input('nisn', [])));
        $filters = User::filtersDari($request->all());

        $res = $this->siswaPilihan($nisn, $filters);

        if ($res->isEmpty()) {
            return back()->with('error', 'Tidak ada siswa yang bisa dicetak.');
        }

        $request->session()->put(self::SESSION_PILIHAN, [
            'nisn'    => $nisn,
            'filters' => $filters,
        ]);

        return redirect('/admin/print/terpilih/preview');
    }

    /**
     * Halaman preview buku induk untuk siswa yang sudah dipilih.
     */
    public function preview_pilihan()
    {
        $pilihan = $this->pilihanDariSession();

        if (!$pilihan) {
            return redirect('/admin/siswa')->with('error', 'Pilih dulu siswa yang akan dicetak.');
        }

        $res = $this->siswaPilihan($pilihan['nisn'], $pilihan['filters']);

        if ($res->isEmpty()) {
            return redirect('/admin/siswa')->with('error', 'Tidak ada siswa yang bisa dicetak.');
        }

        return view('admin/print/preview', [
            'title'      => 'Preview Buku Induk',
            'judul'      => 'Preview Cetak — Buku Induk Siswa',
            'keterangan' => $res->count() . ' siswa dipilih • satu siswa satu halaman',
            'previewUrl' => '/admin/print/terpilih/berkas?inline=1',
            'unduhUrl'   => '/admin/print/terpilih/berkas',
            'kembali'    => '/admin/siswa',
        ]);
    }

    /**
     * Berkas PDF untuk pilihan yang tersimpan di session.
     */
    public function berkas_pilihan(Request $request)
    {
        $pilihan = $this->pilihanDariSession();

        if (!$pilihan) {
            return redirect('/admin/siswa')->with('error', 'Pilih dulu siswa yang akan dicetak.');
        }

        $res = $this->siswaPilihan($pilihan['nisn'], $pilihan['filters']);

        if ($res->isEmpty()) {
            return redirect('/admin/siswa')->with('error', 'Tidak ada siswa yang bisa dicetak.');
        }

        return $this->jawab(
            $this->pdfBukuIndukBanyak($res),
            'Buku Induk ' . $res->count() . ' Siswa.pdf',
            $request->boolean('inline')
        );
    }
}