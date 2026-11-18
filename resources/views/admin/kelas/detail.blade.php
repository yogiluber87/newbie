@extends('layouts.base')

@section('content')
    <div class="container card shadow mt-3 mb-3 p-3">
      <div class="row justify-content-center mb-3">
        @if(count($res->siswa) > 0)
        <div class="col-lg-11">
          <form action="/pindah" method="post">
            @csrf
            <table class="table mb-3">
              <thead>
                <th></th>
                <th>Nama</th>
                <th>Alamat</th>
                <th>Nomor Telefon</th>
              </thead>
              @foreach ($res->siswa as $item)
              <tr>
                <td><input type="checkbox" name="daftar[]" value="{{ $item->id }}" class="form-check-input"></td>
                <td>{{ $item->nama_lengkap }}</td>
                <td>{{ optional($item->biodata)->alamat ?? '-' }}</td>
                <td>{{ optional($item->biodata)->nomor_hp ?? '-' }}</td>
              </tr>
            @endforeach

            </table>
            <div class="mb-3">
              <label for="group_id" class="form-label">Pindah menuju kelas</label>
              <input class="form-control @error('group_id') is-invalid @enderror" list="listKelas" name="group_id" placeholder="Ketik nama kelas...">
              <datalist id="listKelas">
                @foreach ($kelas as $item)
                    <option value="{{$item->id}}">{{$item->nama}}</option>
                @endforeach
              </datalist>
              @error('group_id')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
            </div>
            <div class="mb-3">
              <p class="fw-light mb-2"><span class="text-danger fw-bold">*</span> pastikan data yang kamu masukkan benar dan sesuai ketentuan</p>
              <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary btn-sm px-3"><i class="bi bi-cloud-check me-1"></i> Simpan</button>
              </div>
            </div>
          </form>
        </div>
        @else
        <div class="col-lg-9 text-center">
          <img src="{{ asset('images/empty.jpg') }}" alt="" class="img-fluid" width="200px">
          <h2 class="text-capitalize text-warning fw-bold">data siswa kosong</h2>
          <a href="/admin/siswa/create" class="btn btn-primary btn-sm px-3"><i class="bi bi-plus me-1"></i> Tambah Siswa</a>
        </div>
        @endif
      </div>
    </div>
@endsection