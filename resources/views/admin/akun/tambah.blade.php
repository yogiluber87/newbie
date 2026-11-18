@extends('layouts.base')

@section('content')
    @if (session()->has('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert" style="z-index: 1">
          <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    <div class="panel">
      <div class="panel-heading">
        <span><i class="fas fa-user-plus me-2"></i>Tambah Akun Petugas</span>
      </div>
      <div class="panel-body">
      <div class="row justify-content-center mb-3">
        <div class="col-lg-9">
          <form action="/admin/akun" method="post">
            @csrf
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Nama Lengkap</span>
              <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" placeholder="Contoh : Siti Aminah" name="nama_lengkap" value="{{ old('nama_lengkap') }}">
              @error('nama_lengkap')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Email (untuk login)</span>
              <input type="email" class="form-control @error('email') is-invalid @enderror" placeholder="Contoh : tu@sekolah.sch.id" name="email" value="{{ old('email') }}">
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Password</span>
              <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Minimal 6 karakter" name="password">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Peran</span>
              <select name="role" class="form-select @error('role') is-invalid @enderror">
                @foreach ($roles as $nilai => $label)
                  <option value="{{ $nilai }}" @if (old('role') === $nilai) selected @endif>{{ $label }}</option>
                @endforeach
              </select>
              @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Jenis Kelamin</span>
              <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                <option value="L" @if (old('jenis_kelamin') === 'L') selected @endif>Laki-laki</option>
                <option value="P" @if (old('jenis_kelamin') === 'P') selected @endif>Perempuan</option>
              </select>
              @error('jenis_kelamin')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3">
              <p class="fw-light mb-2"><span class="text-danger fw-bold">*</span> Akun Tata Usaha dapat memakai semua menu kecuali Pengaturan.</p>
              <div class="d-flex justify-content-end">
                <a href="/admin/akun" class="btn btn-light btn-sm px-3 me-2">Batal</a>
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-cloud-check me-1"></i> Simpan</button>
              </div>
            </div>
          </form>
        </div>
      </div>
      </div>
    </div>
@endsection
