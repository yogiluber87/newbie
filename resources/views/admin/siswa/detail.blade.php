@extends('layouts.base')

@section('content')
    @php
      // Relasi bisa kosong (mis. siswa hasil impor CSV), jadi akses dibuat aman.
      $bio  = $res->biodata;
      $ayah = $res->ayah;
      $ibu  = $res->ibu;
      $wali = $res->wali;

      // Tampilkan tanggal sebagai dd/mm/yyyy; nilai kosong/rusak -> '-'
      $tgl = function ($nilai) {
          if (empty($nilai)) {
              return '-';
          }

          try {
              return \Illuminate\Support\Carbon::parse($nilai)->format('d/m/Y');
          } catch (\Throwable $e) {
              return $nilai;
          }
      };

      // Rupiah; kosong -> '-'
      $rupiah = function ($nilai) {
          return ($nilai === null || $nilai === '') ? '-' : 'Rp. ' . number_format((float) $nilai, 2, ',', '.');
      };
    @endphp
    <div class="container card shadow w-100 mt-3 mb-3 p-3">
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
      <div class="row justify-content-center">
        <h3 class="text-center"><i class="bi bi-person me-2"></i> Data Induk siswa</h3>
        <div class="col-lg-10 pt-2 pb-4">
          <div class="d-flex flex-wrap justify-content-center gap-2">
            <a href="/admin/print/{{$res->nisn}}" class="btn btn-danger btn-sm"><i class="bi bi-filetype-pdf me-2"></i>Ekspor PDF</a>
            @if ($ayah)
              <a href="/admin/ayah/{{ $ayah->uri }}" class="btn btn-success btn-sm"><i class="bi bi-pencil me-2"></i>Ayah</a>
            @else
              <span class="btn btn-outline-success btn-sm disabled" title="Data ayah belum ada"><i class="bi bi-pencil me-2"></i>Ayah</span>
            @endif
            @if ($ibu)
              <a href="/admin/ibu/{{ $ibu->uri }}" class="btn btn-sm" style="background-color: pink;"><i class="bi bi-pencil me-2"></i>Ibu</a>
            @else
              <span class="btn btn-outline-secondary btn-sm disabled" title="Data ibu belum ada"><i class="bi bi-pencil me-2"></i>Ibu</span>
            @endif
            @if ($bio)
              <a href="/admin/biodata/{{ $bio->uri }}" class="btn btn-secondary btn-sm"><i class="bi bi-pencil me-2"></i>Biodata</a>
            @else
              <span class="btn btn-outline-secondary btn-sm disabled" title="Biodata belum ada"><i class="bi bi-pencil me-2"></i>Biodata</span>
            @endif
            @if ($res->wali)
              <a href="/admin/wali/{{optional($wali)->uri}}" class="btn btn-sm" style="background-color: greenyellow"><i class="bi bi-pencil me-2"></i>Wali</a>
            @endif
            <a href="/admin/mutasi/{{$res->nisn}}" class="btn btn-sm" style="background-color: skyblue;"><i class="bi bi-arrow-left-right me-2"></i>Mutasi</a>
            <a href="/admin/siswa/{{$res->nisn}}/ekspor" class="btn btn-dark btn-sm"><i class="bi bi-filetype-csv me-2"></i>Ekspor Data (CSV)</a>
          </div>
        </div>
      </div>
      <div class="row mb-3 align-items-start">
        <div class="col-lg-3">
          <img src="@if ($res->foto === NULL) @if($res->jenis_kelamin === 'L') /images/male.png @else /images/female.png @endif @else{{asset('storage/'.$res->foto)}}@endif" class="img-fluid rounded-start" alt="..." style="max-width: 220px">
        </div>
        <div class="col-lg-5" style="padding-top: 30px;">
          <h3 class="mb-3 text-uppercase">{{$res->nama_lengkap}}</h3>
          <h5 class="fw-normal">Nomor Induk Siswa Nasional : {{$res->nisn}}</h5>
          <h5 class="fw-normal">Nomor Induk Siswa : {{$res->nis}}</h5>
          <p>diterima pada tahun ajaran {{ optional($res->tahun_ajar)->tahun_ajaran ?? '-' }}</p>
        </div>
      </div>
      <div class="row mb-3 justify-content-center">
        <div class="col-lg-11">
          <div class="card border-secondary bg-light">
            <div class="card-body py-2">
              @php
                $bagian = [
                  ['label' => 'Ayah', 'terisi' => !empty(optional($res->ayah)->nama), 'url' => optional($res->ayah)->uri ? '/admin/ayah/' . optional($ayah)->uri . '?lanjut=1' : null],
                  ['label' => 'Ibu', 'terisi' => !empty(optional($res->ibu)->nama), 'url' => optional($res->ibu)->uri ? '/admin/ibu/' . optional($ibu)->uri . '?lanjut=1' : null],
                  ['label' => 'Biodata', 'terisi' => !empty(optional($res->biodata)->alamat), 'url' => optional($res->biodata)->uri ? '/admin/biodata/' . optional($bio)->uri . '?lanjut=1' : null],
                  ['label' => 'Wali', 'terisi' => !empty(optional($res->wali)->nama), 'url' => optional($res->wali)->uri ? '/admin/wali/' . optional($wali)->uri . '?lanjut=1' : null],
                ];
              @endphp
              <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="small fw-bold me-1">Kelengkapan data:</span>
                @foreach ($bagian as $b)
                  <a href="{{ $b['url'] ?? '#' }}"
                     class="badge text-decoration-none {{ $b['terisi'] ? 'bg-success' : 'bg-secondary' }}"
                     title="{{ $b['terisi'] ? 'Sudah diisi, klik untuk mengubah' : 'Belum diisi, klik untuk melengkapi' }}">
                    <i class="bi {{ $b['terisi'] ? 'bi-check-circle-fill' : 'bi-circle' }} me-1"></i>{{ $b['label'] }}
                  </a>
                @endforeach
                <a href="{{ optional($res->ayah)->uri ? '/admin/ayah/' . optional($ayah)->uri . '?lanjut=1' : '#' }}" class="btn btn-success btn-sm ms-auto">
                  <i class="bi bi-play-fill me-1"></i>Lanjutkan pengisian data
                </a>
              </div>
              <div class="small text-muted mt-2">
                Data yang sudah diisi tetap tersimpan walau sebagian kolom masih kosong. Buka lagi kapan saja untuk melengkapi.
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="row mb-3 justify-content-center">
        <h3 class="mb-3">A. Keterangan Siswa</h3>
        <div class="col-lg-5">
          <table class="table">
            <tr>
              <th>Alamat</th>
              <td>:</td>
              <td>{{optional($bio)->alamat}}</td>
            </tr>
            <tr>
              <th>Kabupaten/Kota</th>
              <td>:</td>
              <td>{{optional($bio)->kota}}</td>
            </tr>
            <tr>
              <th>Sekolah Asal</th>
              <td>:</td>
              <td>{{optional($bio)->sekolah_asal}}</td>
            </tr>
            <tr>
              <th>Kecamatan</th>
              <td>:</td>
              <td>{{optional($bio)->kecamatan}}</td>
            </tr>
            <tr>
              <th>Kelahiran</th>
              <td>:</td>
              <td>{{optional($bio)->tempat_lahir}}</td>
            </tr>
            <tr>
              <th>Tanggal Lahir</th>
              <td>:</td>
              <td>{{ $tgl(optional($bio)->tanggal_lahir) }}</td>
            </tr>
            <tr>
              <th>Anak ke</th>
              <td>:</td>
              <td>{{optional($bio)->anak_ke}}</td>
            </tr>
            <tr>
              <th>Saudara Kandung</th>
              <td>:</td>
              <td>{{optional($bio)->jlh_saudara}}</td>
            </tr>
            <tr>
              <th>Saudara Angkat</th>
              <td>:</td>
              <td>{{optional($bio)->saudara_angkat}}</td>
            </tr>
            <tr>
              <th>Saudara Tiri</th>
              <td>:</td>
              <td>{{optional($bio)->saudara_tiri}}</td>
            </tr>
          </table>
        </div>
        <div class="col-lg-5">
          <table class="table">
            <tr>
              <th>Bahasa</th>
              <td>:</td>
              <td>{{optional($bio)->bahasa}}</td>
            </tr>
            <tr>
              <th>Agama</th>
              <td>:</td>
              <td>{{optional($bio)->agama}}</td>
            </tr>
            <tr>
              <th>Kewarganegaraan</th>
              <td>:</td>
              <td>{{optional($bio)->kewarganegaraan}}</td>
            </tr>
            <tr>
              <th>Kegemaran</th>
              <td>:</td>
              <td>{{optional($bio)->hobi}}</td>
            </tr>
            <tr>
              <th>Jarak ke sekolah</th>
              <td>:</td>
              <td>{{ optional($bio)->jarak ? optional($bio)->jarak . ' Km' : '-' }}</td>
            </tr>
            <tr>
              <th>Nomor HP</th>
              <td>:</td>
              <td>{{optional($bio)->nomor_hp}}</td>
            </tr>
            <tr>
              <th>Alamat Email</th>
              <td>:</td>
              <td>{{$res->email}}</td>
            </tr>
            <tr>
              <th>Golongan Darah</th>
              <td>:</td>
              <td>{{optional($bio)->goldar}}</td>
            </tr>
            <tr>
              <th>Tinggi Badan</th>
              <td>:</td>
              <td>{{optional($bio)->tinggi.' Cm'}}</td>
            </tr>
            <tr>
              <th>Berat Badan</th>
              <td>:</td>
              <td>{{optional($bio)->berat.' Kg'}}</td>
            </tr>
            <tr>
              <th>Riwayat Penyakit</th>
              <td>:</td>
              <td>{{optional($bio)->penyakit}}</td>
            </tr>
          </table>
        </div>
      </div>
      <div class="row mb-3 justify-content-center">
        <h3 class="mb-3">B. Keterangan Orang Tua Siswa</h3>
        <div class="col-lg-5">
          <table class="table">
            <tr>
              <th>Nama Ayah</th>
              <td>:</td>
              <td>{{optional($ayah)->nama}}</td>
            </tr>
            <tr>
              <th>Alamat Ayah</th>
              <td>:</td>
              <td>{{optional($ayah)->alamat}}</td>
            </tr>
            <tr>
              <th>Kabupaten/Kota Kelahiran Ayah</th>
              <td>:</td>
              <td>{{optional($ayah)->tempat_lahir}}</td>
            </tr>
            <tr>
              <th>Tanggal Lahir Ayah</th>
              <td>:</td>
              <td>{{ $tgl(optional($ayah)->tanggal_lahir) }}</td>
            </tr>
            <tr>
              <th>Agama Ayah</th>
              <td>:</td>
              <td>{{optional($ayah)->agama}}</td>
            </tr>
            <tr>
              <th>Kewarganegaraan Ayah</th>
              <td>:</td>
              <td>{{optional($ayah)->kewarganegaraan}}</td>
            </tr>
            <tr>
              <th>Pekerjaan Ayah</th>
              <td>:</td>
              <td>{{optional($ayah)->pekerjaan}}</td>
            </tr>
            <tr>
              <th>Pendidikan Terakhir Ayah</th>
              <td>:</td>
              <td>{{optional($ayah)->pendidikan}}</td>
            </tr>
            <tr>
              <th>Penghasilan perbulan Ayah</th>
              <td>:</td>
              <td>{{ $rupiah(optional($ayah)->penghasilan) }}</td>
            </tr>
            <tr>
              <th>Nomor Telepon</th>
              <td>:</td>
              <td>{{optional($ayah)->nomor_hp}}</td>
            </tr>
          </table>
        </div>
        <div class="col-lg-5">
          <table class="table">
            <tr>
              <th>Nama ibu</th>
              <td>:</td>
              <td>{{optional($ibu)->nama}}</td>
            </tr>
            <tr>
              <th>Alamat ibu</th>
              <td>:</td>
              <td>{{optional($ibu)->alamat}}</td>
            </tr>
            <tr>
              <th>Kabupaten/Kota Kelahiran Ibu</th>
              <td>:</td>
              <td>{{optional($ibu)->tempat_lahir}}</td>
            </tr>
            <tr>
              <th>Tanggal Lahir ibu</th>
              <td>:</td>
              <td>{{ $tgl(optional($ibu)->tanggal_lahir) }}</td>
            </tr>
            <tr>
              <th>Agama ibu</th>
              <td>:</td>
              <td>{{optional($ibu)->agama}}</td>
            </tr>
            <tr>
              <th>Kewarganegaraan ibu</th>
              <td>:</td>
              <td>{{optional($ibu)->kewarganegaraan}}</td>
            </tr>
            <tr>
              <th>Pekerjaan ibu</th>
              <td>:</td>
              <td>{{optional($ibu)->pekerjaan}}</td>
            </tr>
            <tr>
              <th>Pendidikan Terakhir ibu</th>
              <td>:</td>
              <td>{{optional($ibu)->pendidikan}}</td>
            </tr>
            <tr>
              <th>Penghasilan perbulan ibu</th>
              <td>:</td>
              <td>{{ $rupiah(optional($ibu)->penghasilan) }}</td>
            </tr>
            <tr>
              <th>Nomor Telepon</th>
              <td>:</td>
              <td>{{optional($ibu)->nomor_hp}}</td>
            </tr>
          </table>
        </div>
      </div>
      <div class="row mb-3 justify-content-center">
        <h3 class="mb-3">C. Keterangan Wali Siswa</h3>
        <div class="col-lg-5">
          <table class="table">
            <tr>
              <th>Nama Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->nama }}</td>
            </tr>
            <tr>
              <th>Tempat, Tanggal Lahir Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->tempat_lahir }}{{ optional($res->wali)->tempat_lahir && optional($res->wali)->tanggal_lahir ? ', ' : '' }}{{ optional($res->wali)->tanggal_lahir }}</td>
            </tr>
            <tr>
              <th>Agama Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->agama }}</td>
            </tr>
            <tr>
              <th>Kewarganegaraan Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->kewarganegaraan }}</td>
            </tr>
            <tr>
              <th>Pekerjaan Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->pekerjaan }}</td>
            </tr>
          </table>
        </div>
        <div class="col-lg-5">
          <table class="table">
            <tr>
              <th>Pendidikan Terakhir Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->pendidikan }}</td>
            </tr>
            <tr>
              <th>Penghasilan perbulan Wali</th>
              <td>:</td>
              <td>{{ $rupiah(optional($wali)->penghasilan) }}</td>
            </tr>
            <tr>
              <th>Alamat Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->alamat }}</td>
            </tr>
            <tr>
              <th>Nomor Telepon Wali</th>
              <td>:</td>
              <td>{{ optional($res->wali)->nomor_hp }}</td>
            </tr>
          </table>
        </div>
      </div>
      <div class="row mb-3 justify-content-center">
        <h3 class="mb-3">D. Mutasi Siswa</h3>
        <div class="col-lg-10">
          @if ($mutasi->isEmpty())
            <p class="text-muted">Belum ada data mutasi untuk siswa ini.</p>
          @else
            <table class="table table-bordered">
              <thead>
                <tr>
                  <th>No.</th>
                  <th>Tujuan Sekolah</th>
                  <th>Alasan</th>
                  <th>Tanggal Pindah</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($mutasi as $m)
                  <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $m->tujuan }}</td>
                    <td>{{ $m->alasan }}</td>
                    <td>{{ $m->tanggal_pindah }}</td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @endif
        </div>
      </div>
    </div>

@endsection