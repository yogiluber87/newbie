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
    <div class="container card shadow w-100 mt-3 mb-3 p-3">
      <div class="row justify-content-center">
        @if (count($res) === 0)
            <div class="col-lg-9 text-center">
              <img src="/images/empty.jpg" alt="" class="img-fluid" width="200px">
              <h2 class="text-capitalize text-warning fw-bold">belum ada data kelas</h2>
              <a href="/admin/grup/create" class="btn btn-primary btn-sm px-3"><i class="bi bi-plus me-1"></i> Tambah Kelas</a>
            </div>
        @else
            <div class="col-lg-9">
              <table class="table text-center">
                <thead>
                  <th>Nama</th>
                  <th>Jumlah Siswa</th>
                  <th></th>
                </thead>
                @foreach ($res as $item)
                    <tr>
                      <td>{{$item->nama}}</td>
                      <td>{{count($item->siswa)}}</td>
                      <td style="width: 260px">
                        <a href="/admin/grup/{{$item->uri}}" class="btn btn-sm btn-info me-1" title="Lihat detail kelas"><i class="bi bi-eye"></i></a>
                        <a href="/admin/grup/{{$item->uri}}/edit" class="btn btn-sm btn-warning me-1" title="Ubah kelas"><i class="bi bi-pencil"></i></a>
                          <form action="/admin/grup/{{$item->uri}}" method="post" class="d-inline">
                            @method('delete')
                            @csrf
                            <button type="submit" class="btn btn-sm btn-danger me-1" title="Hapus kelas" onclick="return confirm('Anda yakin akan menghapus kelas ini?')"><i class="bi bi-trash"></i></button>
                          </form>
                      </td>
                    </tr>
                @endforeach
              </table>
            </div>
        @endif
      </div>
    </div>
@endsection