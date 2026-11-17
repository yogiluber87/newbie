@extends('layouts.base')

@section('content')
    <div class="container card shadow w-100 mb-3 mt-3 p-3">
      @if (session()->has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
          <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
      @endif

      <div class="row mb-3">
        <h3 class="text-center text-uppercase">{{$title}}</h3>
      </div>

      <div class="row justify-content-center">
        <div class="col-lg-8">
          <form action="/admin/pengaturan" method="post" enctype="multipart/form-data">
            @csrf

            <div class="mb-4 text-center">
              <p class="fw-bold mb-2">Logo Sekolah</p>
              <img src="{{ $setelan['logo_url'] }}" alt="Logo sekolah" class="img-thumbnail" style="max-height: 140px;">
              <p class="text-muted small mt-2 mb-0">
                @if ($setelan['has_logo'])
                  Logo saat ini. Unggah file baru untuk menggantinya.
                @else
                  Masih memakai logo bawaan aplikasi.
                @endif
              </p>
            </div>

            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Logo</span>
              <input type="file" accept="image/png,image/jpeg,image/webp" class="form-control @error('logo') is-invalid @enderror" name="logo">
              @error('logo')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
              @enderror
            </div>
            <p class="fw-light small mb-3">Format PNG / JPG / WEBP, maksimal 2 MB. Sebaiknya logo kotak atau bulat.</p>

            @if ($setelan['has_logo'])
              <div class="form-check mb-4">
                <input class="form-check-input" type="checkbox" name="hapus_logo" value="1" id="hapus_logo">
                <label class="form-check-label" for="hapus_logo">
                  Hapus logo dan kembalikan ke logo bawaan
                </label>
              </div>
            @endif

            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Nama Sekolah</span>
              <input type="text" class="form-control @error('nama_sekolah') is-invalid @enderror" placeholder="Contoh : SMKN 1 Jakarta" name="nama_sekolah" value="{{ old('nama_sekolah', $setelan['nama_sekolah']) }}">
              @error('nama_sekolah')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
              @enderror
            </div>

            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Alamat Sekolah</span>
              <input type="text" class="form-control @error('alamat_sekolah') is-invalid @enderror" placeholder="Contoh : Jl. Pendidikan No. 1, Jakarta" name="alamat_sekolah" value="{{ old('alamat_sekolah', $setelan['alamat_sekolah']) }}">
              @error('alamat_sekolah')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
              @enderror
            </div>
            <p class="fw-light small mb-3">Nama dan alamat sekolah tampil di sidebar, halaman masuk, dan cetak PDF.</p>

            <div class="d-grip gap-2 mb-3 w-100">
              <p class="fw-light"><span class="text-danger fw-bold">*</span> pastikan data yang kamu masukkan benar dan sesuai ketentuan</p>
              <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cloud-check me-2"></i> Simpan</button>
            </div>
          </form>
        </div>
      </div>
    </div>
@endsection
