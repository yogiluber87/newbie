@extends('layouts.base')

@section('content')
    <div class="container card shadow mt-3 mb-3 p-3">
      <div class="row justify-content-center mb-3">
        <h4 class="text-uppercase text-center">Impor Data Siswa</h4>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-9">

          @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          <div class="alert alert-info">
            <strong><i class="bi bi-info-circle me-2"></i>Petunjuk</strong>
            <ul class="mb-0 mt-2">
              <li>Unggah berkas <strong>CSV</strong> (dari Excel: <em>File &rarr; Save As &rarr; CSV</em>).</li>
              <li>Baris pertama harus berisi nama kolom:
                <code>nama_lengkap;nisn;nis;jenis_kelamin;email;nama_kelas;tahun_ajaran</code>
              </li>
              <li>Template memakai pemisah <strong>titik koma (;)</strong> agar terbaca rapi di Excel. Berkas dengan pemisah koma juga tetap didukung.</li>
              <li>Kolom <code>jenis_kelamin</code> diisi <strong>L</strong> atau <strong>P</strong>.</li>
              <li>Kelas dan tahun ajaran yang belum ada akan dibuat otomatis.</li>
              <li>Siswa dengan NISN/NIS yang sudah terdaftar akan <strong>dilewati</strong>.</li>
              <li>Angka nol di depan aman: NISN/NIS yang ditulis <code>="0123456789"</code> atau <code>'013</code> tetap dibaca utuh, dan kalau nol depannya hilang akan dilengkapi otomatis mengikuti data siswa yang sudah ada.</li>
            </ul>
          </div>

          <div class="mb-3">
            <a href="/admin/siswa/import/template" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-download me-2"></i>Unduh Template CSV
            </a>
          </div>

          <form action="/admin/siswa/import" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="file" class="form-label">Berkas CSV</label>
              <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" accept=".csv,text/csv">
              @error('file')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
            </div>

            <div class="row mb-3">
              <p class="fw-light"><span class="text-danger fw-bold">*</span> maksimal 5 MB per unggahan</p>
              <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload me-2"></i> Mulai Impor</button>
            </div>
            <a href="/admin/siswa" class="btn btn-light w-100">Batal</a>
          </form>
        </div>
      </div>
    </div>
@endsection
