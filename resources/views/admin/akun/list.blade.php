@extends('layouts.base')

@section('content')
      @if (session()->has('success'))
          <div class="alert alert-success alert-dismissible fade show" role="alert" style="z-index: 1">
            <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      @endif
      @if (session()->has('error'))
          <div class="alert alert-danger alert-dismissible fade show" role="alert" style="z-index: 1">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      @endif
    <div class="panel">
      <div class="panel-heading">
        <span><i class="fas fa-user-cog me-2"></i>Daftar Akun Petugas</span>
        <span class="tools">
          <a href="/admin/akun/create" class="btn btn-xs bg-aqua text-white"><i class="fas fa-plus me-1"></i>Tambah Akun</a>
        </span>
      </div>
      <div class="panel-body">
        <p class="text-muted small mb-3">Superadmin bisa membuat akun Tata Usaha. Tata Usaha bisa memakai semua menu kecuali Pengaturan.</p>
        <div class="row justify-content-center">
          <div class="col-12">
            <table class="table align-middle">
            <thead class="text-center">
              <th>Nama</th>
              <th>Email</th>
              <th>Peran</th>
              <th style="width: 160px"></th>
            </thead>
            <tbody>
              @forelse ($res as $item)
                <tr>
                  <td>
                    {{ $item->nama_lengkap }}
                    @if ((int) $item->id === (int) auth()->id())
                      <span class="badge bg-info ms-1">Anda</span>
                    @endif
                  </td>
                  <td>{{ $item->email ?? '-' }}</td>
                  <td class="text-center">
                    @if ($item->isSuperadmin())
                      <span class="badge bg-primary">Superadmin</span>
                    @else
                      <span class="badge bg-success">Tata Usaha</span>
                    @endif
                  </td>
                  <td class="text-center">
                    <a href="/admin/akun/{{ $item->nisn }}/edit" class="btn btn-sm btn-warning me-1" title="Ubah akun"><i class="bi bi-pencil"></i></a>
                    <form action="/admin/akun/{{ $item->nisn }}" method="post" class="d-inline">
                      @method('delete')
                      @csrf
                      <button type="submit" class="btn btn-sm btn-danger" title="Hapus akun" onclick="return confirm('Anda yakin akan menghapus akun ini?')"><i class="bi bi-trash"></i></button>
                    </form>
                  </td>
                </tr>
              @empty
                <tr>
                  <td colspan="4" class="text-center text-muted py-4">Belum ada akun petugas.</td>
                </tr>
              @endforelse
            </tbody>
          </table>
          </div>
        </div>
      </div>
    </div>
@endsection
