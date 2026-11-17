@php
    /*
     * Tombol simpan untuk form data siswa (ayah, ibu, biodata, wali).
     * Saat diisi lewat alur pengisian (?lanjut=1) tombol berubah menjadi
     * "Simpan & Lanjut" + "Lewati", sehingga urutan pengisian berjalan terus
     * dan data yang sudah diisi tetap tersimpan walau ada langkah yang dilewati.
     */
    $langkahSekarang = $langkah ?? 'ayah';
    $lanjutAktif = $lanjut ?? false;

    $berikutnya = [
        'ayah' => [
            'label' => 'Data Ibu',
            'url'   => optional($siswa->ibu)->uri ? '/admin/ibu/' . $siswa->ibu->uri . '?lanjut=1' : '/admin/siswa/' . $siswa->nisn,
        ],
        'ibu' => [
            'label' => 'Biodata Siswa',
            'url'   => optional($siswa->biodata)->uri ? '/admin/biodata/' . $siswa->biodata->uri . '?lanjut=1' : '/admin/siswa/' . $siswa->nisn,
        ],
        'biodata' => [
            'label' => 'Data Wali',
            'url'   => optional($siswa->wali)->uri ? '/admin/wali/' . $siswa->wali->uri . '?lanjut=1' : '/admin/siswa/' . $siswa->nisn,
        ],
        'wali' => [
            'label' => 'Selesai',
            'url'   => '/admin/siswa/' . $siswa->nisn,
        ],
    ][$langkahSekarang];
@endphp

<div class="position-sticky bottom-0 bg-white border-top pt-2 pb-2 mt-3" style="z-index: 3;">
  <p class="fw-light mb-2 small">
    <span class="text-danger fw-bold">*</span> tidak semua kolom wajib diisi. Data yang sudah diisi langsung tersimpan
    dan bisa dilengkapi kapan saja dari halaman detail siswa.
  </p>

  <div class="d-grid gap-2 w-100">
    @if ($lanjutAktif)
      <input type="hidden" name="lanjut" value="1">
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-cloud-check me-2"></i> Simpan & Lanjut ke {{ $berikutnya['label'] }}
      </button>
      <a href="{{ $berikutnya['url'] }}" class="btn btn-outline-secondary w-100">
        <i class="bi bi-skip-forward me-2"></i> Lewati langkah ini
      </a>
      <a href="/admin/siswa/{{ $siswa->nisn }}" class="btn btn-link btn-sm">Berhenti dulu, lihat data siswa</a>
    @else
      <button type="submit" class="btn btn-primary w-100">
        <i class="bi bi-cloud-check me-2"></i> Simpan
      </button>
      <a href="/admin/siswa/{{ $siswa->nisn }}" class="btn btn-secondary w-100">
        <i class="bi bi-arrow-left-circle me-2"></i> Kembali ke Data Siswa
      </a>
    @endif
  </div>
</div>
