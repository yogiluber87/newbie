@php
    /*
     * Bilah langkah pengisian data siswa.
     * Dipakai oleh form Ayah, Ibu, Biodata, dan Wali saat diisi lewat alur
     * "isi data siswa" (parameter ?lanjut=1).
     *
     * Variabel: $siswa (User) dan $langkah (ayah|ibu|biodata|wali).
     */
    $langkahSekarang = $langkah ?? 'ayah';

    $tahap = [
        [
            'kunci' => 'siswa',
            'label' => 'Data Siswa',
            'url'   => '/admin/siswa/' . $siswa->nisn,
            'terisi' => true,
        ],
        [
            'kunci' => 'ayah',
            'label' => 'Data Ayah',
            'url'   => optional($siswa->ayah)->uri ? '/admin/ayah/' . $siswa->ayah->uri . '?lanjut=1' : null,
            'terisi' => optional($siswa->ayah)->nama ? true : false,
        ],
        [
            'kunci' => 'ibu',
            'label' => 'Data Ibu',
            'url'   => optional($siswa->ibu)->uri ? '/admin/ibu/' . $siswa->ibu->uri . '?lanjut=1' : null,
            'terisi' => optional($siswa->ibu)->nama ? true : false,
        ],
        [
            'kunci' => 'biodata',
            'label' => 'Biodata',
            'url'   => optional($siswa->biodata)->uri ? '/admin/biodata/' . $siswa->biodata->uri . '?lanjut=1' : null,
            'terisi' => optional($siswa->biodata)->alamat ? true : false,
        ],
        [
            'kunci' => 'wali',
            'label' => 'Data Wali',
            'url'   => optional($siswa->wali)->uri ? '/admin/wali/' . $siswa->wali->uri . '?lanjut=1' : null,
            'terisi' => optional($siswa->wali)->nama ? true : false,
        ],
    ];
@endphp

<div class="card bg-light border-secondary mb-3">
  <div class="card-body py-2">
    <div class="d-flex flex-wrap align-items-center gap-2">
      <span class="small text-muted me-1">Langkah pengisian:</span>
      @foreach ($tahap as $i => $t)
        @php
          $aktif = $t['kunci'] === $langkahSekarang;
          $kelas = $aktif ? 'bg-primary text-white' : ($t['terisi'] ? 'bg-success text-white' : 'bg-white border text-muted');
        @endphp
        <a href="{{ $t['url'] ?? '#' }}"
           class="badge rounded-pill text-decoration-none {{ $kelas }} {{ $t['url'] ? '' : 'disabled' }}"
           style="font-size: .78rem; padding: .5em .75em;">
          @if ($t['terisi'] && !$aktif)
            <i class="bi bi-check-circle-fill me-1"></i>
          @else
            {{ $i + 1 }}.
          @endif
          {{ $t['label'] }}
        </a>
        @if (!$loop->last)
          <i class="bi bi-chevron-right text-muted" style="font-size: .7rem;"></i>
        @endif
      @endforeach
      <a href="/admin/siswa/{{ $siswa->nisn }}" class="badge rounded-pill bg-dark text-decoration-none" style="font-size: .78rem; padding: .5em .75em;">
        <i class="bi bi-flag-fill me-1"></i>Selesai
      </a>
    </div>
    <div class="small text-muted mt-2">
      Isi data semampunya. Semua kolom boleh dikosongkan dan data yang sudah diisi akan tetap tersimpan,
      jadi sisanya bisa dilengkapi kapan saja dari halaman detail siswa.
    </div>
  </div>
</div>
