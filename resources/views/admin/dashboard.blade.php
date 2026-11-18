@extends('layouts.base')

@section('subtitle', 'Ringkasan data buku induk siswa')

@section('content')
  @if (session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
      <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
      <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
  @endif

  {{-- Kartu ringkasan --}}
  <div class="row g-3 mb-1">
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon aqua"><i class="fas fa-users"></i></div>
        <div>
          <h4>{{ $jumlah_siswa }}</h4>
          <span>Siswa aktif</span>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon green"><i class="fas fa-school"></i></div>
        <div>
          <h4>{{ $jumlah_kelas }}</h4>
          <span>Kelas / rombel</span>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon yellow"><i class="fas fa-exchange-alt"></i></div>
        <div>
          <h4>{{ $jumlah_mutasi }}</h4>
          <span>Data mutasi</span>
        </div>
      </div>
    </div>
    <div class="col-md-6 col-xl-3">
      <div class="stat-card">
        <div class="stat-icon blue"><i class="fas fa-calendar-alt"></i></div>
        <div>
          <h4 style="font-size: 15px">{{ optional($tahun->first())->tahun_ajaran ?? '-' }}</h4>
          <span>Tahun ajaran aktif</span>
        </div>
      </div>
    </div>
  </div>

  <div class="row g-3">
    {{-- Sambutan + pintasan --}}
    <div class="col-lg-8">
      <div class="panel h-100">
        <div class="panel-heading">
          <span><i class="fas fa-bullhorn me-2"></i>Selamat Datang</span>
          <span class="tools">
            <a href="/admin/print/daftar/preview" class="btn btn-xs bg-aqua text-white"><i class="fas fa-print me-1"></i>Preview Cetak</a>
            <a href="/admin/siswa/create" class="btn btn-xs btn-success"><i class="fas fa-plus me-1"></i>Tambah Siswa</a>
          </span>
        </div>
        <div class="panel-body">
          <h5 class="mb-2">Halo, {{ auth()->user()->nama_lengkap }}</h5>
          <p class="text-muted mb-3" style="max-width: 620px">
            Sistem ini dipakai untuk mendigitalkan administrasi data induk siswa: biodata, data ayah/ibu/wali,
            kelas, tahun ajaran, mutasi, serta pencetakan buku induk.
          </p>

          <div class="table-responsive">
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th>Ringkasan siswa</th>
                  <th class="text-center">Jumlah</th>
                </tr>
              </thead>
              <tbody>
                <tr><td>Siswa laki-laki</td><td class="text-center">{{ $murid_laki }}</td></tr>
                <tr><td>Siswa perempuan</td><td class="text-center">{{ $murid_perempuan }}</td></tr>
                <tr><td>Siswa yatim / piatu</td><td class="text-center">{{ $siswa_yatim_piatu }}</td></tr>
                <tr><td>Siswa kurang mampu</td><td class="text-center">{{ $siswa_kurang_mampu }}</td></tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Mutasi terakhir --}}
    <div class="col-lg-4">
      <div class="panel h-100">
        <div class="panel-heading">
          <span><i class="fas fa-history me-2"></i>Mutasi Terakhir</span>
          <span class="tools"><a href="/admin/mutasi" class="btn btn-xs bg-aqua text-white">Lihat semua</a></span>
        </div>
        <div class="panel-body p-0">
          @if ($mutasi->isNotEmpty())
            <ul class="list-group list-group-flush">
              @foreach ($mutasi->take(6) as $m)
                <li class="list-group-item d-flex justify-content-between align-items-start" style="font-size: 12.5px">
                  <div>
                    <div class="fw-semibold text-dark">{{ optional($m->user)->nama_lengkap ?? '-' }}</div>
                    <small class="text-muted">
                      {{ $m->tanggal_pindah ? \Carbon\Carbon::parse($m->tanggal_pindah)->format('d/m/Y') : '-' }}
                      @if ($m->sekolah_tujuan) &middot; {{ $m->sekolah_tujuan }} @endif
                    </small>
                  </div>
                  <a href="/admin/mutasi/{{ optional($m->user)->nisn }}" class="btn btn-xs btn-warning"><i class="fas fa-pen"></i></a>
                </li>
              @endforeach
            </ul>
          @else
            <div class="p-4 text-center text-muted small">Belum ada data mutasi.</div>
          @endif
        </div>
      </div>
    </div>
  </div>

  {{-- Kelas terbaru --}}
  <div class="row g-3">
    <div class="col-12">
      <div class="panel">
        <div class="panel-heading">
          <span><i class="fas fa-house-user me-2"></i>Kelas Terbaru</span>
          <span class="tools"><a href="/admin/grup" class="btn btn-xs bg-aqua text-white">Daftar kelas</a></span>
        </div>
        <div class="panel-body p-0">
          @if ($kelas->isNotEmpty())
            <table class="table table-striped mb-0">
              <thead>
                <tr>
                  <th>Nama kelas</th>
                  <th class="text-center">Jumlah siswa</th>
                  <th class="text-center" style="width: 120px">Aksi</th>
                </tr>
              </thead>
              <tbody>
                @foreach ($kelas->take(8) as $k)
                  <tr>
                    <td>{{ $k->nama }}</td>
                    <td class="text-center">{{ count($k->siswa) }}</td>
                    <td class="text-center">
                      <a href="/admin/grup/{{ $k->uri }}" class="btn btn-xs btn-info" title="Detail"><i class="fas fa-eye"></i></a>
                      <a href="/admin/grup/{{ $k->uri }}/edit" class="btn btn-xs btn-warning" title="Ubah"><i class="fas fa-pen"></i></a>
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          @else
            <div class="p-4 text-center text-muted small">Belum ada kelas. <a href="/admin/grup/create">Tambah kelas</a>.</div>
          @endif
        </div>
      </div>
    </div>
  </div>
@endsection