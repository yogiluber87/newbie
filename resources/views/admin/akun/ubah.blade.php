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
        <span><i class="fas fa-user-edit me-2"></i>Ubah Akun</span>
        <span class="tools text-muted" style="text-transform:none;letter-spacing:0;font-weight:400">{{ $res->nama_lengkap }} &middot; {{ $res->roleLabel() }}</span>
      </div>
      <div class="panel-body">
        <div class="row justify-content-center mb-3">
          <div class="col-lg-9">
          <form action="/admin/akun/{{ $res->nisn }}" method="post">
            @method('put')
            @csrf
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Nama Lengkap</span>
              <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" name="nama_lengkap" value="{{ old('nama_lengkap', $res->nama_lengkap) }}">
              @error('nama_lengkap')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Email (untuk login)</span>
              <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $res->email) }}">
              @error('email')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Password Baru</span>
              <input type="password" class="form-control @error('password') is-invalid @enderror" placeholder="Kosongkan bila tidak ingin mengubah" name="password">
              @error('password')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Peran</span>
              <select name="role" class="form-select @error('role') is-invalid @enderror">
                @foreach ($roles as $nilai => $label)
                  <option value="{{ $nilai }}" @if (old('role', $res->isSuperadmin() ? 'superadmin' : 'tata_usaha') === $nilai) selected @endif>{{ $label }}</option>
                @endforeach
              </select>
              @error('role')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="input-group mb-3">
              <span class="input-group-text bg-light">Jenis Kelamin</span>
              <select name="jenis_kelamin" class="form-select @error('jenis_kelamin') is-invalid @enderror">
                <option value="L" @if (old('jenis_kelamin', $res->jenis_kelamin) === 'L') selected @endif>Laki-laki</option>
                <option value="P" @if (old('jenis_kelamin', $res->jenis_kelamin) === 'P') selected @endif>Perempuan</option>
              </select>
              @error('jenis_kelamin')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
            <div class="mb-3">
              <p class="fw-light mb-2"><span class="text-danger fw-bold">*</span> pastikan data yang kamu masukkan benar dan sesuai ketentuan</p>
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
