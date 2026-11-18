@extends('layouts.base')

@section('subtitle', 'Identitas sekolah dan kop cetak PDF — hanya superadmin')

@section('content')
  @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  @if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
      <i class="bi bi-exclamation-triangle me-2"></i>Ada isian yang belum benar: {{ $errors->first() }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  <form action="/admin/pengaturan" method="post" enctype="multipart/form-data">
    @csrf

    <div class="row g-3">
      {{-- Identitas sekolah --}}
      <div class="col-lg-6">
        <div class="panel h-100">
          <div class="panel-heading">
            <span><i class="fas fa-school me-2"></i>Identitas Sekolah</span>
          </div>
          <div class="panel-body">
            <div class="text-center mb-3">
              <img src="{{ $setelan['logo_url'] }}" alt="Logo sekolah" class="img-thumbnail" style="max-height: 120px;">
              <div class="text-muted small mt-2">
                @if ($setelan['has_logo'])
                  Logo aplikasi saat ini (sidebar, halaman masuk, dan kop bila gambar kiri belum diunggah).
                @else
                  Masih memakai logo bawaan aplikasi.
                @endif
              </div>
            </div>

            <label class="form-label" for="logo">Logo Aplikasi</label>
            <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control mb-2 @error('logo') is-invalid @enderror" id="logo" name="logo">
            @error('logo') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

            @if ($setelan['has_logo'])
              <div class="form-check mb-3">
                <input class="form-check-input" type="checkbox" name="hapus_logo" value="1" id="hapus_logo">
                <label class="form-check-label" for="hapus_logo">Hapus logo dan kembalikan ke logo bawaan</label>
              </div>
            @endif

            <label class="form-label" for="nama_sekolah">Nama Sekolah</label>
            <input type="text" class="form-control mb-3 @error('nama_sekolah') is-invalid @enderror" id="nama_sekolah" placeholder="Contoh : SMA NEGERI 2 LINTAU BUO" name="nama_sekolah" value="{{ old('nama_sekolah', $setelan['nama_sekolah']) }}">
            @error('nama_sekolah') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

            <label class="form-label" for="alamat_sekolah">Alamat Sekolah</label>
            <textarea class="form-control @error('alamat_sekolah') is-invalid @enderror" id="alamat_sekolah" rows="2" placeholder="Contoh : Jl. Pendidikan No. 1, Kel. Sukamaju, Kec. Setiabudi" name="alamat_sekolah">{{ old('alamat_sekolah', $setelan['alamat_sekolah']) }}</textarea>
            @error('alamat_sekolah') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            <p class="text-muted small mt-2 mb-0">Nama sekolah tampil di sidebar dan halaman masuk. Alamat dipakai sebagai baris kontak kop bila kolom kontak dikosongkan.</p>
          </div>
        </div>
      </div>

      {{-- Kop cetak PDF --}}
      <div class="col-lg-6">
        <div class="panel h-100">
          <div class="panel-heading">
            <span><i class="fas fa-file-invoice me-2"></i>Kop Cetak PDF</span>
          </div>
          <div class="panel-body">
            <p class="text-muted small mb-3">Tampilan kop pada cetak buku induk dan daftar siswa:</p>

            {{-- Pratinjau kop --}}
            <div class="border rounded p-3 mb-4" style="background:#fff;">
              <div class="d-flex align-items-center gap-3">
                <img src="{{ $setelan['kop_kiri_url'] ?? $setelan['logo_url'] }}" alt="Kop kiri" style="width:56px;height:56px;object-fit:contain;">
                <div class="flex-fill text-center lh-sm">
                  @if (trim((string) $setelan['kop_baris1']) !== '')
                    <div class="text-uppercase fw-bold" style="font-size:11px">{{ $setelan['kop_baris1'] }}</div>
                  @endif
                  @if (trim((string) $setelan['kop_baris2']) !== '')
                    <div class="text-uppercase fw-bold" style="font-size:12px">{{ $setelan['kop_baris2'] }}</div>
                  @endif
                  <div class="text-uppercase fw-bold" style="font-size:14px">{{ $setelan['nama_sekolah'] }}</div>
                  @if (trim((string) $setelan['kop_kontak']) !== '')
                    <div class="fst-italic text-muted" style="font-size:9.5px">{{ $setelan['kop_kontak'] }}</div>
                  @endif
                </div>
                <div style="width:56px">
                  @if ($setelan['kop_kanan_url'])
                    <img src="{{ $setelan['kop_kanan_url'] }}" alt="Kop kanan" style="width:56px;height:56px;object-fit:contain;">
                  @endif
                </div>
              </div>
              <div style="border-top:2px solid #000;margin-top:8px"></div>
              <div style="border-top:1px solid #000;margin-top:2px"></div>
            </div>

            <div class="row g-3 mb-3">
              <div class="col-sm-6">
                <label class="form-label" for="kop_kiri">Foto / Logo Kop Kiri</label>
                <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control @error('kop_kiri') is-invalid @enderror" id="kop_kiri" name="kop_kiri">
                @error('kop_kiri') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <div class="form-text">Bila kosong, memakai logo aplikasi.</div>
                @if ($setelan['has_kop_kiri'])
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="hapus_kop_kiri" value="1" id="hapus_kop_kiri">
                    <label class="form-check-label" for="hapus_kop_kiri">Hapus gambar kop kiri</label>
                  </div>
                @endif
              </div>
              <div class="col-sm-6">
                <label class="form-label" for="kop_kanan">Foto / Logo Kop Kanan</label>
                <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control @error('kop_kanan') is-invalid @enderror" id="kop_kanan" name="kop_kanan">
                @error('kop_kanan') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
                <div class="form-text">Opsional, misalnya logo dinas atau tut wuri handayani.</div>
                @if ($setelan['has_kop_kanan'])
                  <div class="form-check mt-2">
                    <input class="form-check-input" type="checkbox" name="hapus_kop_kanan" value="1" id="hapus_kop_kanan">
                    <label class="form-check-label" for="hapus_kop_kanan">Hapus gambar kop kanan</label>
                  </div>
                @endif
              </div>
            </div>

            <label class="form-label" for="kop_baris1">Baris 1 (instansi)</label>
            <input type="text" class="form-control mb-3 @error('kop_baris1') is-invalid @enderror" id="kop_baris1" placeholder="PEMERINTAH PROVINSI SUMATERA BARAT" name="kop_baris1" value="{{ old('kop_baris1', $setelan['kop_baris1']) }}">
            @error('kop_baris1') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

            <label class="form-label" for="kop_baris2">Baris 2 (dinas)</label>
            <input type="text" class="form-control mb-3 @error('kop_baris2') is-invalid @enderror" id="kop_baris2" placeholder="DINAS PENDIDIKAN" name="kop_baris2" value="{{ old('kop_baris2', $setelan['kop_baris2']) }}">
            @error('kop_baris2') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror

            <label class="form-label" for="kop_kontak">Baris kontak (satu baris)</label>
            <input type="text" class="form-control @error('kop_kontak') is-invalid @enderror" id="kop_kontak" placeholder="Jalan Raya ... • e-mail : ... • Telp. ..." name="kop_kontak" value="{{ old('kop_kontak', $setelan['kop_kontak']) }}">
            @error('kop_kontak') <div class="invalid-feedback d-block">{{ $message }}</div> @enderror
            <p class="text-muted small mt-2 mb-0">Gambar PNG / JPG / WEBP maksimal 2 MB, sebaiknya latar transparan atau putih.</p>
          </div>
        </div>
      </div>
    </div>

    <div class="d-flex justify-content-end align-items-center gap-2 mb-4">
      <span class="text-muted small me-auto"><span class="text-danger fw-bold">*</span> pastikan data yang dimasukkan benar dan sesuai ketentuan</span>
      <button type="submit" class="btn btn-primary btn-sm px-3"><i class="fas fa-save me-1"></i> Simpan Pengaturan</button>
    </div>
  </form>
@endsection