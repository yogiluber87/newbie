@extends('layouts.base')

@section('subtitle', $keterangan ?? 'Periksa hasil cetak sebelum diunduh atau dicetak')

@section('content')
  <div class="panel mb-0">
    <div class="panel-heading">
      <span><i class="fas fa-file-pdf me-2"></i>{{ $judul }}</span>
      <span class="tools">
        <button type="button" class="btn btn-xs bg-aqua text-white" onclick="cetakPdf()" title="Buka dialog cetak browser">
          <i class="fas fa-print me-1"></i>Cetak
        </button>
        <a href="{{ $unduhUrl }}" class="btn btn-xs btn-success" title="Simpan berkas PDF">
          <i class="fas fa-download me-1"></i>Unduh PDF
        </a>
        <a href="{{ $previewUrl }}" target="_blank" rel="noopener" class="btn btn-xs btn-light border" title="Buka PDF di tab baru">
          <i class="fas fa-external-link-alt me-1"></i>Tab Baru
        </a>
        <a href="{{ $kembali }}" class="btn btn-xs btn-light border">
          <i class="fas fa-arrow-left me-1"></i>Kembali
        </a>
      </span>
    </div>

    <div class="panel-body py-2 d-flex flex-wrap align-items-center gap-2">
      <span class="text-muted small">
        <i class="fas fa-info-circle me-1"></i>{{ $keterangan }}
      </span>
      <span class="text-muted small ms-auto">
        Jika PDF tidak tampil, klik <strong>Tab Baru</strong>.
      </span>
    </div>

    <div class="position-relative border-top" id="kotak-pdf" style="background:#525659;">
      <div id="pemuat" class="position-absolute top-50 start-50 translate-middle text-white text-center" style="pointer-events:none;">
        <i class="fas fa-circle-notch fa-spin fa-2x mb-2"></i>
        <div class="small">Menyiapkan PDF…</div>
      </div>
      <iframe id="pdf-frame" src="{{ $previewUrl }}" title="{{ $judul }}"
              style="display:block;width:100%;height:calc(100vh - 250px);min-height:420px;border:0;position:relative;z-index:1;"
              onload="document.getElementById('pemuat').style.display='none';"></iframe>
    </div>
  </div>
@endsection

@push('scripts')
<script>
  var ALAMAT_PDF = @json($previewUrl);

  // Cetak: minta jendela PDF di dalam iframe membuka dialog cetak.
  // Kalau browser menolak (plugin PDF), buka PDF di tab baru.
  function cetakPdf() {
    var bingkai = document.getElementById('pdf-frame');

    try {
      bingkai.contentWindow.focus();
      bingkai.contentWindow.print();
    } catch (e) {
      window.open(ALAMAT_PDF, '_blank');
    }
  }
</script>
@endpush
