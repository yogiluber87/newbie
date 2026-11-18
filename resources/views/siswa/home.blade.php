@extends('layouts.base')

@section('content')
    
    <div class="container card shadow mt-3 mb-3">
      @if (session()->has('Updated'))
          <div class="alert alert-success alert-dismissible fade show position-absolute w-100" role="alert">
            <i class="bi bi-check-circle me-2"></i>{{ session('Updated') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
        @endif
      <div class="row justify-content-center p-3">
        <div class="col-lg-3">
          <img src="@if (auth()->user()->foto === NULL) @if(auth()->user()->jenis_kelamin === 'L') /images/male.png @else /images/female.png @endif @else{{asset('storage/'.auth()->user()->foto)}}@endif" class="img-fluid rounded-start" alt="..." style="max-width: 220px">
        </div>
        <div class="col-lg-6 mt-3">
          <h1 class="text-uppercase">{{auth()->user()->nama_lengkap}}</h1>
          <h4 class="fw-normal">NISN : <span class="text-primary">{{auth()->user()->nisn}}</span></h4>
          <h4 class="fw-normal">NIS : <span class="text-primary">{{auth()->user()->nis}}</span></h4>
          <p class="sub-title">Diterima pada tahun ajaran {{ optional(auth()->user()->tahun_ajar)->tahun_ajaran ?? '-' }}</p>
          @if (auth()->user()->kelas)
            <p class="sub-title">Kelas : {{auth()->user()->kelas->nama}}</p>
          @endif
        </div>
        <div class="col-lg-2">
          <a href="/admin/print/{{auth()->user()->nisn}}/preview" class="btn btn-warning btn-sm px-3"><i class="bi bi-printer me-1"></i> Cetak</a>
        </div>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-4">
          <table class="table">
            <tr>
              <th>Tempat/Tanggal Lahir</th>
              <td>{{optional(auth()->user()->biodata)->tempat_lahir}}/{{optional(auth()->user()->biodata)->tanggal_lahir}}</td>
            </tr>
            <tr>
              <th>Jenis Kelamin</th>
              <td>{{auth()->user()->jenis_kelamin}}</td>
            </tr>
            <tr>
              <th>Alamat</th>
              <td>{{optional(auth()->user()->biodata)->alamat}}</td>
            </tr>
            <tr>
              <th>Kecamatan</th>
              <td>{{optional(auth()->user()->biodata)->kecamatan}}</td>
            </tr>
            <tr>
              <th>Jumlah Saudara Kandung</th>
              <td>{{optional(auth()->user()->biodata)->jlh_saudara}}</td>
            </tr>
            <tr>
              <th>Jumlah Saudara Tiri</th>
              <td>{{optional(auth()->user()->biodata)->saudara_tiri}}</td>
            </tr>
            <tr>
              <th>Jumlah Saudara Angkat</th>
              <td>{{optional(auth()->user()->biodata)->saudara_angkat}}</td>
            </tr>
          </table>
        </div>
        <div class="col-lg-4">
          <table class="table">
            <tr>
              <th>Bahasa sehari-hari</th>
              <td>{{optional(auth()->user()->biodata)->bahasa}}</td>
            </tr>
            <tr>
              <th>Agama</th>
              <td>{{optional(auth()->user()->biodata)->agama}}</td>
            </tr>
            <tr>
              <th>Jarak ke sekolah</th>
              <td>{{ optional(auth()->user()->biodata)->jarak ? optional(auth()->user()->biodata)->jarak . ' Km' : '-' }}</td>
            </tr>
            <tr>
              <th>Nomor Telefon</th>
              <td>{{optional(auth()->user()->biodata)->nomor_hp}}</td>
            </tr>
            <tr>
              <th>Golongan Darah</th>
              <td>{{optional(auth()->user()->biodata)->goldar}}</td>
            </tr>
            <tr>
              <th>Tinggi badan</th>
              <td>{{optional(auth()->user()->biodata)->tinggi.' Cm'}}</td>
            </tr>
            <tr>
              <th>Berat badan</th>
              <td>{{optional(auth()->user()->biodata)->berat.' Kg'}}</td>
            </tr>
          </table>
        </div>
        <div class="col-lg-4">
          <table class="table">
            <tr>
              <th>Riwayat Penyakit</th>
              <td>{{optional(auth()->user()->biodata)->penyakit}}</td>
            </tr>
            <tr>
              <th>Kegemaran</th>
              <td>{{optional(auth()->user()->biodata)->hobi}}</td>
            </tr>
            <tr>
              <th>Kewarganegaraan</th>
              <td>{{optional(auth()->user()->biodata)->kewarganegaraan}}</td>
            </tr>
            <tr>
              <th>Nama Ayah Siswa</th>
              <td>{{optional(auth()->user()->ayah)->nama}}</td>
            </tr>
            <tr>
              <th>Pekerjaan Ayah</th>
              <td>{{optional(auth()->user()->ayah)->pekerjaan}}</td>
            </tr>
            <tr>
              <th>Nama Ibu Siswa</th>
              <td>{{optional(auth()->user()->ibu)->nama}}</td>
            </tr>
            <tr>
              <th>Pekerjaan Ibu</th>
              <td>{{optional(auth()->user()->ibu)->pekerjaan}}</td>
            </tr>
          </table>
        </div>
      </div>
    </div>
@endsection