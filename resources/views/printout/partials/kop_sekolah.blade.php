@php
  /*
   * Kop (kepala surat) untuk semua cetak PDF: buku induk siswa, semua siswa,
   * dan daftar siswa. Susunannya meniru kop sekolah resmi:
   * gambar kiri, tiga baris instansi + baris kontak di tengah, gambar kanan,
   * lalu garis ganda. Judul dokumen (opsional) dicetak di bawah garis ganda.
   *
   * Variabel opsional: $judul (judul dokumen, mis. "Buku Induk Siswa").
   * Semua isi diambil dari halaman Pengaturan (khusus superadmin).
   */
  $gambar = function (string $kunci) use ($setelan) {
      $path = $setelan[$kunci] ?? null;

      if ($path && \Illuminate\Support\Facades\Storage::disk('public')->exists($path)) {
          return storage_path('app/public/' . $path);
      }

      return null;
  };

  // Kiri: gambar kop, kalau belum diunggah pakai logo aplikasi.
  $gambarKiri = $gambar('kop_kiri');
  if (!$gambarKiri) {
      $gambarKiri = $setelan['has_logo']
          ? storage_path('app/public/' . $setelan['logo'])
          : public_path('images/tut wuri handayani.png');
  }

  $gambarKanan = $gambar('kop_kanan');

  $baris1 = trim((string) ($setelan['kop_baris1'] ?? ''));
  $baris2 = trim((string) ($setelan['kop_baris2'] ?? ''));

  // Baris kontak: kalau kolom khusus kosong, pakai alamat sekolah lama.
  $kontak = trim((string) ($setelan['kop_kontak'] ?? ''));
  if ($kontak === '') {
      $kontak = str_replace(["\r\n", "\r", "\n"], ' • ', trim((string) ($setelan['alamat_sekolah'] ?? '')));
  }

  $judulDokumen = trim((string) ($judul ?? ''));
@endphp

<table style="width: 100%; border-collapse: collapse; margin: 0;">
  <tr>
    <td style="width: 85px; vertical-align: middle; text-align: center; padding: 0;">
      <img src="{{ $gambarKiri }}" alt="Logo" style="width: 72px;">
    </td>
    <td style="text-align: center; vertical-align: middle; padding: 0 6px;">
      @if ($baris1 !== '')
        <div style="font-size: 12.5px; font-weight: bold; text-transform: uppercase; line-height: 1.3;">{{ $baris1 }}</div>
      @endif
      @if ($baris2 !== '')
        <div style="font-size: 14px; font-weight: bold; text-transform: uppercase; line-height: 1.3;">{{ $baris2 }}</div>
      @endif
      <div style="font-size: 17px; font-weight: bold; text-transform: uppercase; line-height: 1.3; letter-spacing: 0.4px;">
        {{ $setelan['nama_sekolah'] }}
      </div>
      @if ($kontak !== '')
        <div style="font-size: 9px; font-style: italic; line-height: 1.35; margin-top: 2px;">{{ $kontak }}</div>
      @endif
    </td>
    <td style="width: 85px; vertical-align: middle; text-align: center; padding: 0;">
      @if ($gambarKanan)
        <img src="{{ $gambarKanan }}" alt="Logo kanan" style="width: 72px;">
      @endif
    </td>
  </tr>
</table>

{{-- Garis ganda khas kop surat: garis tebal lalu garis tipis --}}
<div style="border-top: 2px solid #000; margin-top: 6px;"></div>
<div style="border-top: 1px solid #000; margin-top: 2px;"></div>

@if ($judulDokumen !== '')
  <div style="text-align: center; font-weight: bold; font-size: 13px; text-transform: uppercase; margin-top: 10px; color: #1f3b57; letter-spacing: 0.3px;">
    {{ $judulDokumen }}
  </div>
@endif