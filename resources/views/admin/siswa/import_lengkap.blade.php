@extends('layouts.base')

@section('content')
    <div class="container card shadow mt-3 mb-3 p-3">
      <div class="row justify-content-center mb-3">
        <h4 class="text-uppercase text-center">Impor CSV Lengkap</h4>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-10">

          @if (session()->has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
              <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if (session()->has('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
              <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          @endif

          @if (session()->has('import_detail'))
            @php
              $detail = collect(session('import_detail'));
              $bermasalah = $detail->where('status', 'gagal');
            @endphp
            <div class="alert alert-{{ $bermasalah->count() ? 'warning' : 'success' }} alert-dismissible fade show" role="alert">
              <strong><i class="bi bi-list-check me-2"></i>Rincian impor ({{ $detail->count() }} baris)</strong>
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
              <div class="table-responsive mt-2" style="max-height: 300px; overflow-y: auto;">
                <table class="table table-sm table-bordered mb-0 bg-white">
                  <thead>
                    <tr><th>Baris</th><th>Nama</th><th>Status</th><th>Keterangan</th></tr>
                  </thead>
                  <tbody>
                    @foreach ($detail as $d)
                      <tr>
                        <td>{{ $d['baris'] }}</td>
                        <td>{{ $d['nama'] }}</td>
                        <td>
                          @if ($d['status'] === 'baru')
                            <span class="badge bg-primary">baru</span>
                          @elseif ($d['status'] === 'diperbarui')
                            <span class="badge bg-success">diperbarui</span>
                          @else
                            <span class="badge bg-danger">gagal</span>
                          @endif
                        </td>
                        <td>{{ $d['pesan'] }}</td>
                      </tr>
                    @endforeach
                  </tbody>
                </table>
              </div>
            </div>
          @endif

          <div class="alert alert-info">
            <strong><i class="bi bi-info-circle me-2"></i>Petunjuk</strong>
            <ul class="mb-0 mt-2">
              <li>Berkas ini adalah kebalikan dari <strong>Daftar Siswa &rarr; Ekspor Semua (CSV)</strong>, jadi sebaiknya pakai berkas hasil ekspor tersebut.</li>
              <li>Baris pertama harus berisi nama kolom, minimal: <code>nama_lengkap;nisn</code> &plusmn; kolom <code>biodata_*</code>, <code>ayah_*</code>, <code>ibu_*</code>, <code>wali_*</code>, <code>mutasi_*</code>.</li>
              <li>Siswa dicocokkan berdasarkan <strong>NISN</strong>: kalau sudah ada &rarr; datanya dilengkapi/ditimpa; kalau belum ada &rarr; dibuatkan siswa baru (kata sandi awal <code>siswa123</code>).</li>
              <li><strong>Angka nol di depan aman</strong>: NISN/NIS/nomor HP yang ditulis <code>="0120568897"</code> atau <code>'013</code> dibaca sebagai teks, dan NISN/NIS yang nol depannya hilang akan dilengkapi otomatis mengikuti data siswa yang sudah ada.</li>
              <li>Kelas dan tahun ajaran yang belum ada akan dibuat otomatis.</li>
              <li>Pemisah <strong>titik koma (;)</strong> seperti hasil ekspor; berkas berpemisah koma juga tetap didukung. Maksimal 10 MB.</li>
            </ul>
          </div>

          <div class="mb-3 d-flex gap-2 flex-wrap">
            <a href="/admin/siswa/import-lengkap/template" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-download me-2"></i>Unduh Contoh Format CSV
            </a>
            <a href="/admin/siswa/import" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-upload me-2"></i>Impor CSV Biasa (siswa baru saja)
            </a>
            <a href="/admin/siswa" class="btn btn-outline-secondary btn-sm">
              <i class="bi bi-arrow-left me-2"></i>Daftar Siswa
            </a>
          </div>

          <form action="/admin/siswa/import-lengkap" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="file" class="form-label">Berkas CSV</label>
              <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" accept=".csv,text/csv">
              @error('file')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
              @enderror
            </div>

            <div class="form-check mb-3">
              <input class="form-check-input" type="checkbox" name="identitas" value="1" id="identitas" checked>
              <label class="form-check-label" for="identitas">
                Perbarui identitas siswa yang sudah ada (nama, jenis kelamin, email, kelas, tahun ajaran)
              </label>
            </div>

            <div class="row mb-3">
              <p class="fw-light"><span class="text-danger fw-bold">*</span> pastikan berkas berasal dari aplikasi ini dan sudah benar</p>
              <button type="submit" class="btn btn-primary w-100"><i class="bi bi-upload me-2"></i> Mulai Impor</button>
            </div>
            <a href="/admin/siswa" class="btn btn-light w-100">Batal</a>
          </form>
        </div>
      </div>
    </div>
@endsection
