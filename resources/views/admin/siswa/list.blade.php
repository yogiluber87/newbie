@extends('layouts.base')

@section('content')

    <div class="container card shadow border-info mt-3 mb-3">
      @php
        $filterAktif = collect($filters)->filter(function ($v) {
            return trim((string) $v) !== '';
        });
      @endphp
      <div class="row mt-3 mb-3 justify-content-center">
        <div class="col-lg-3">
          
          <h3>Daftar Siswa</h3>
        </div>
        <div class="col-lg-6">
          <form action="/admin/siswa" method="get" id="formCariSiswa">
            <div class="input-group mb-2">
              <input type="text" class="form-control" placeholder="Cari nama, NISN, atau NIS.." name="cari" value="{{ $filters['cari'] ?? '' }}">
              <button class="btn btn-outline-success" type="submit" title="Cari"><i class="bi bi-search"></i></button>
              <button class="btn btn-outline-secondary" type="button" data-bs-toggle="collapse" data-bs-target="#filterLanjutan" aria-expanded="{{ $adaFilter ? 'true' : 'false' }}" aria-controls="filterLanjutan" title="Filter lanjutan">
                <i class="bi bi-funnel"></i> Filter
              </button>
            </div>

            <div class="collapse {{ $adaFilter ? 'show' : '' }}" id="filterLanjutan">
              <div class="card card-body bg-light border-secondary">
                <div class="row g-2">
                  <div class="col-sm-6">
                    <label class="form-label small mb-1" for="f_nisn">NISN</label>
                    <input type="text" class="form-control form-control-sm" id="f_nisn" name="nisn" value="{{ $filters['nisn'] ?? '' }}" placeholder="Contoh: 0120568897">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1" for="f_nis">NIS</label>
                    <input type="text" class="form-control form-control-sm" id="f_nis" name="nis" value="{{ $filters['nis'] ?? '' }}" placeholder="Nomor induk sekolah">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1" for="f_tempat">Tempat tinggal</label>
                    <input type="text" class="form-control form-control-sm" id="f_tempat" name="tempat_tinggal" value="{{ $filters['tempat_tinggal'] ?? '' }}" placeholder="Kabupaten/Kota / kecamatan / alamat">
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1" for="f_kelas">Kelas</label>
                    <select class="form-select form-select-sm" id="f_kelas" name="group_id">
                      <option value="">Semua kelas</option>
                      @foreach ($kelas as $k)
                        <option value="{{ $k->id }}" {{ (string) ($filters['group_id'] ?? '') === (string) $k->id ? 'selected' : '' }}>{{ $k->nama }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1" for="f_jk">Jenis kelamin</label>
                    <select class="form-select form-select-sm" id="f_jk" name="jenis_kelamin">
                      <option value="">Semua</option>
                      <option value="L" {{ ($filters['jenis_kelamin'] ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                      <option value="P" {{ ($filters['jenis_kelamin'] ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                    </select>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1" for="f_mutasi">Status mutasi</label>
                    <select class="form-select form-select-sm" id="f_mutasi" name="status_mutasi">
                      <option value="">Semua siswa</option>
                      <option value="belum" {{ ($filters['status_mutasi'] ?? '') === 'belum' ? 'selected' : '' }}>Belum mutasi</option>
                      <option value="sudah" {{ ($filters['status_mutasi'] ?? '') === 'sudah' ? 'selected' : '' }}>Sudah mutasi / pindah</option>
                    </select>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1">Tanggal mutasi (rentang)</label>
                    <div class="input-group input-group-sm">
                      <input type="date" class="form-control" name="mutasi_dari" value="{{ $filters['mutasi_dari'] ?? '' }}" title="Dari tanggal">
                      <span class="input-group-text">s.d.</span>
                      <input type="date" class="form-control" name="mutasi_sampai" value="{{ $filters['mutasi_sampai'] ?? '' }}" title="Sampai tanggal">
                    </div>
                    @if ((int) ($filters['penghasilan_min'] ?? 0) > 0 && (int) ($filters['penghasilan_max'] ?? 0) > 0 && (int) $filters['penghasilan_min'] > (int) $filters['penghasilan_max'])
                      <div class="small text-danger mt-1">Penghasilan minimum lebih besar dari maksimum, hasil akan kosong.</div>
                    @endif
                    @if (($filters['status_mutasi'] ?? '') === 'belum' && (!empty($filters['mutasi_dari']) || !empty($filters['mutasi_sampai'])))
                      <div class="small text-danger mt-1">Status “Belum mutasi” tidak cocok dengan rentang tanggal mutasi. Pilih “Sudah mutasi” atau kosongkan salah satunya.</div>
                    @endif
                    @if (!empty($filters['mutasi_dari']) && !empty($filters['mutasi_sampai']) && $filters['mutasi_dari'] > $filters['mutasi_sampai'])
                      <div class="small text-danger mt-1">Tanggal “dari” lebih besar dari “sampai”, hasil akan kosong.</div>
                    @endif
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1" for="f_tahun">Tahun masuk / tahun ajaran</label>
                    <select class="form-select form-select-sm" id="f_tahun" name="year_id">
                      <option value="">Semua tahun masuk</option>
                      @foreach ($tahun as $t)
                        <option value="{{ $t->id }}" {{ (string) ($filters['year_id'] ?? '') === (string) $t->id ? 'selected' : '' }}>{{ $t->tahun_ajaran }}</option>
                      @endforeach
                    </select>
                  </div>
                  <div class="col-sm-6">
                    <label class="form-label small mb-1">Penghasilan orang tua per bulan</label>
                    <div class="input-group input-group-sm">
                      <span class="input-group-text">Rp</span>
                      <input type="number" min="0" step="50000" class="form-control" name="penghasilan_min" value="{{ $filters['penghasilan_min'] ?? '' }}" placeholder="Min">
                      <span class="input-group-text">s.d.</span>
                      <input type="number" min="0" step="50000" class="form-control" name="penghasilan_max" value="{{ $filters['penghasilan_max'] ?? '' }}" placeholder="Max">
                    </div>
                  </div>
                </div>
                <div class="d-flex flex-wrap gap-2 mt-3">
                  <button class="btn btn-success btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Terapkan Filter</button>
                  <a href="/admin/siswa?reset=1" class="btn btn-outline-secondary btn-sm"><i class="bi bi-arrow-counterclockwise me-1"></i>Reset</a>
                  <span class="small text-muted align-self-center">Filter bisa dikombinasikan dan disimpan otomatis sampai di-reset. Penghasilan dicek pada data ayah atau ibu; rentang tanggal mutasi otomatis hanya mencari siswa yang sudah mutasi.</span>
                </div>
              </div>
            </div>
          </form>
          @if ($filterAktif->isNotEmpty())
            <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
              <span class="small text-muted">Filter aktif:</span>
              @if (!empty($filters['cari']))
                <span class="badge bg-success">Kata kunci: {{ $filters['cari'] }}</span>
              @endif
              @if (!empty($filters['nisn']))
                <span class="badge bg-success">NISN: {{ $filters['nisn'] }}</span>
              @endif
              @if (!empty($filters['nis']))
                <span class="badge bg-success">NIS: {{ $filters['nis'] }}</span>
              @endif
              @if (!empty($filters['tempat_tinggal']))
                <span class="badge bg-success">Tempat tinggal: {{ $filters['tempat_tinggal'] }}</span>
              @endif
              @if (!empty($filters['group_id']))
                <span class="badge bg-success">Kelas: {{ optional($kelas->firstWhere('id', (int) $filters['group_id']))->nama ?? $filters['group_id'] }}</span>
              @endif
              @if (!empty($filters['jenis_kelamin']))
                <span class="badge bg-success">Jenis kelamin: {{ $filters['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' }}</span>
              @endif
              @if (!empty($filters['status_mutasi']))
                <span class="badge bg-success">Mutasi: {{ $filters['status_mutasi'] === 'sudah' ? 'sudah mutasi' : 'belum mutasi' }}</span>
              @endif
              @if (!empty($filters['mutasi_dari']) || !empty($filters['mutasi_sampai']))
                <span class="badge bg-success">Tanggal mutasi: {{ \App\Models\User::formatTanggalFilter($filters['mutasi_dari'] ?? null) ?? 'awal' }} s.d. {{ \App\Models\User::formatTanggalFilter($filters['mutasi_sampai'] ?? null) ?? 'akhir' }}</span>
              @endif
              @if (!empty($filters['year_id']))
                <span class="badge bg-success">Tahun masuk: {{ optional($tahun->firstWhere('id', (int) $filters['year_id']))->tahun_ajaran ?? $filters['year_id'] }}</span>
              @endif
              @if (!empty($filters['penghasilan_min']) || !empty($filters['penghasilan_max']))
                <span class="badge bg-success">Penghasilan: Rp {{ number_format((int) ($filters['penghasilan_min'] ?? 0), 0, ',', '.') }} s.d. {{ !empty($filters['penghasilan_max']) ? 'Rp ' . number_format((int) $filters['penghasilan_max'], 0, ',', '.') : 'tanpa batas' }}</span>
              @endif
            </div>
          @endif
        </div>
        <div class="col-lg-2">
          <a href="/admin/print" class="btn btn-warning"><i class="bi bi-printer me-2"></i>Cetak</a>
          <div class="dropdown mt-2">
            <button class="btn btn-success w-100 dropdown-toggle" type="button" id="imporMenu" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="bi bi-upload me-2"></i>Impor
            </button>
            <ul class="dropdown-menu" aria-labelledby="imporMenu">
              <li><a class="dropdown-item" href="/admin/siswa/import"><i class="bi bi-person-plus me-2"></i>CSV Biasa (siswa baru)</a></li>
              <li><a class="dropdown-item" href="/admin/siswa/import-lengkap"><i class="bi bi-database-add me-2"></i>CSV Lengkap (biodata, ortu, wali, mutasi)</a></li>
            </ul>
          </div>
        </div>
      </div>
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
      @if ($errors->any())
          <div class="alert alert-danger alert-dismissible fade show" role="alert" style="z-index: 1">
            <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
          </div>
      @endif
      @if (session()->has('import_detail'))
          @php
            $punyaMasalah = collect(session('import_detail'))->whereIn('status', ['dilewati', 'gagal']);
          @endphp
          @if ($punyaMasalah->count())
          <div class="alert alert-warning alert-dismissible fade show" role="alert" style="z-index: 1">
            <strong><i class="bi bi-exclamation-triangle me-2"></i>Baris yang tidak diimpor ({{ $punyaMasalah->count() }})</strong>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            <div class="table-responsive mt-2" style="max-height: 220px; overflow-y: auto;">
              <table class="table table-sm table-bordered mb-0 bg-white">
                <thead>
                  <tr><th>Baris</th><th>Nama</th><th>Status</th><th>Keterangan</th></tr>
                </thead>
                <tbody>
                  @foreach ($punyaMasalah as $d)
                    <tr>
                      <td>{{ $d['baris'] }}</td>
                      <td>{{ $d['nama'] }}</td>
                      <td>{{ ucfirst($d['status']) }}</td>
                      <td>{{ $d['pesan'] }}</td>
                    </tr>
                  @endforeach
                </tbody>
              </table>
            </div>
          </div>
          @endif
      @endif
      @if($res->count())
      <div class="row justify-content-center">
        <div class="col-lg-11">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2">
            <div class="d-flex flex-wrap align-items-center gap-2">
              <span class="badge bg-secondary" id="jumlahPilih">0 dipilih</span>
              <button type="button" class="btn btn-link btn-sm text-decoration-none p-0" id="kosongkanPilihan">
                Kosongkan pilihan
              </button>
            </div>
            <div class="d-flex flex-wrap gap-2">
              <button type="button" class="btn btn-dark btn-sm" onclick="eksporTerpilih()">
                <i class="bi bi-filetype-csv me-1"></i>Ekspor Terpilih (CSV)
              </button>
              <button type="button" class="btn btn-warning btn-sm" onclick="cetakTerpilih()">
                <i class="bi bi-printer me-1"></i>Cetak Terpilih (PDF)
              </button>
              @if ($adaFilter)
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="cetakSemuaCari()">
                  <i class="bi bi-printer me-1"></i>Cetak Hasil Pencarian
                </button>
              @endif
              @if ($adaFilter)
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="eksporSemuaCari()">
                  <i class="bi bi-search me-1"></i>Ekspor Hasil Pencarian (CSV)
                </button>
              @endif
              <a href="/admin/siswa/ekspor-semua-csv{{ $filterAktif->isNotEmpty() ? '?' . http_build_query($filterAktif->toArray()) : '' }}" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-download me-1"></i>{{ $filterAktif->isNotEmpty() ? 'Ekspor Hasil Filter (CSV)' : 'Ekspor Semua (CSV)' }}
              </a>
            </div>
          </div>
          <table class="table">
            <thead>
              <th style="width: 40px;"><input type="checkbox" id="pilihSemua" title="Pilih semua di halaman ini"></th>
              <th>No</th>
              <th>Nama Siswa</th>
              <th>Kelas</th>
              <th>JK</th>
              <th>NISN</th>
              <th>Data Ayah Siswa</th>
              <th>Data Ibu Siswa</th>
              <th>Aksi</th>
            </thead>
            @foreach ($res as $item)
                <tr>
                  <td><input type="checkbox" class="pilih-siswa" value="{{$item->nisn}}"></td>
                  <td>{{$loop->iteration}}</td>
                  <td>
                    {{$item->nama_lengkap}}
                    @if ($item->status === 'mutasi')
                      <span class="badge bg-warning text-dark" title="Siswa sudah pindah / termutasi"><i class="bi bi-arrow-left-right me-1"></i>Mutasi</span>
                    @endif
                  </td>
                  <td>{{ optional($item->kelas)->nama ?? '-' }}</td>
                  <td>
                    @if ($item->jenis_kelamin === 'L')
                      <span class="badge bg-primary">L</span>
                    @elseif ($item->jenis_kelamin === 'P')
                      <span class="badge bg-danger">P</span>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>{{$item->nisn}}</td>
                  <td>
                    @if ($item->ayah)
                      <a href="/admin/ayah/{{$item->ayah->uri}}" class="text-decoration-none">{{$item->ayah->nama}}</a>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td>
                    @if ($item->ibu)
                      <a href="/admin/ibu/{{$item->ibu->uri}}" class="text-decoration-none">{{$item->ibu->nama}}</a>
                    @else
                      <span class="text-muted">-</span>
                    @endif
                  </td>
                  <td class="d-flex">
                    <form action="/admin/siswa/{{$item->nisn}}" method="post">
                      @csrf
                      @method('delete')
                      <button type="submit" class="btn btn-sm btn-danger me-1" onclick="return confirm('Anda yakin akan menghapus siswa ini?')"><i class="bi bi-trash"></i></button>
                    </form>
                    <a href="/admin/siswa/{{$item->nisn}}" class="btn btn-sm btn-info me-1"><i class="bi bi-eye"></i></a> 
                    <a href="/admin/siswa/{{$item->nisn}}/edit" class="btn btn-sm btn-warning"><i class="bi bi-pencil"></i></a>
                  </td>
                </tr>
            @endforeach
          </table>
          <div class="d-flex justify-content-center">
            {{ $res->links() }}
          </div>
        </div>
      </div>
      @else
        <div class="row justify-content-center mb-3 p-3">
          <div class="col-lg-9 text-center">
            <img src="/images/empty.jpg" alt="" class="img-fluid" width="200px">
            <h2 class="text-uppercase text-warning fw-bold mb-3">data siswa tidak ditemukan</h2>
            @if ($filterAktif->isNotEmpty())
              <p class="text-muted">Tidak ada siswa yang cocok dengan filter yang dipakai.
                <a href="/admin/siswa?reset=1" class="fw-bold">Hapus semua filter</a>
              </p>
            @endif
            <a href="/admin/siswa/create" class="btn btn-primary btn-lg"><i class="bi bi-plus me-2"></i> Tambah Siswa</a>
          </div>
        </div>
    </div>
    @endif

    <script>
      (function () {
        var KUNCI = 'buku-induk-siswa-terpilih';
        var FILTER = @json($filterAktif->toArray());
        var pilihSemua = document.getElementById('pilihSemua');
        var label = document.getElementById('jumlahPilih');
        var tombolKosongkan = document.getElementById('kosongkanPilihan');

        function baca() {
          try {
            var isi = JSON.parse(localStorage.getItem(KUNCI) || '[]');
            return Array.isArray(isi) ? isi : [];
          } catch (e) {
            return [];
          }
        }

        function simpan(daftar) {
          try {
            localStorage.setItem(KUNCI, JSON.stringify(daftar));
          } catch (e) {}
        }

        function kotak() {
          return Array.prototype.slice.call(document.querySelectorAll('.pilih-siswa'));
        }

        function sinkronHalaman() {
          var terpilih = baca();

          kotak().forEach(function (c) {
            var i = terpilih.indexOf(c.value);
            if (c.checked && i === -1) { terpilih.push(c.value); }
            if (!c.checked && i !== -1) { terpilih.splice(i, 1); }
          });

          simpan(terpilih);
        }

        function perbarui() {
          var terpilih = baca();
          var halaman = kotak();
          var diHalaman = halaman.filter(function (c) { return c.checked; }).length;

          if (label) {
            label.textContent = terpilih.length + ' dipilih' + (terpilih.length > diHalaman ? ' (termasuk halaman lain)' : '');
          }

          if (pilihSemua) {
            pilihSemua.checked = halaman.length > 0 && diHalaman === halaman.length;
            pilihSemua.indeterminate = diHalaman > 0 && diHalaman < halaman.length;
          }
        }

        function peringatan(judul, teks) {
          if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: judul, text: teks });
          } else {
            alert(teks);
          }
        }

        function kirim(aksi, daftarNisn, filters) {
          var form = document.createElement('form');
          form.method = 'POST';
          form.action = aksi;
          form.style.display = 'none';

          var tambah = function (nama, nilai) {
            var el = document.createElement('input');
            el.type = 'hidden';
            el.name = nama;
            el.value = nilai;
            form.appendChild(el);
          };

          tambah('_token', '{{ csrf_token() }}');

          if (filters) {
            Object.keys(filters).forEach(function (kunci) {
              tambah(kunci, filters[kunci]);
            });
          }

          daftarNisn.forEach(function (nisn) { tambah('nisn[]', nisn); });

          document.body.appendChild(form);
          form.submit();
          document.body.removeChild(form);
        }

        // Pulihkan centang dari halaman sebelumnya (pilihan disimpan di browser)
        var tersimpan = baca();
        kotak().forEach(function (c) { c.checked = tersimpan.indexOf(c.value) !== -1; });

        kotak().forEach(function (c) {
          c.addEventListener('change', function () {
            sinkronHalaman();
            perbarui();
          });
        });

        if (pilihSemua) {
          pilihSemua.addEventListener('change', function () {
            kotak().forEach(function (c) { c.checked = pilihSemua.checked; });
            sinkronHalaman();
            perbarui();
          });
        }

        if (tombolKosongkan) {
          tombolKosongkan.addEventListener('click', function () {
            simpan([]);
            kotak().forEach(function (c) { c.checked = false; });
            perbarui();
          });
        }

        window.eksporTerpilih = function () {
          var daftar = baca();

          if (daftar.length === 0) {
            peringatan('Belum ada siswa dipilih', 'Centang dulu siswa yang ingin diekspor.');
            return;
          }

          kirim('/admin/siswa/ekspor-pilihan', daftar, null);
        };

        window.eksporSemuaCari = function () {
          if (Object.keys(FILTER).length === 0) {
            peringatan('Belum ada filter', 'Isi dulu kata kunci atau filter pencarian.');
            return;
          }

          kirim('/admin/siswa/ekspor-pilihan', [], FILTER);
        };

        window.cetakTerpilih = function () {
          var daftar = baca();

          if (daftar.length === 0) {
            peringatan('Belum ada siswa dipilih', 'Centang dulu siswa yang ingin dicetak.');
            return;
          }

          kirim('/admin/print/terpilih', daftar, null);
        };

        window.cetakSemuaCari = function () {
          if (Object.keys(FILTER).length === 0) {
            peringatan('Belum ada filter', 'Isi dulu kata kunci atau filter pencarian.');
            return;
          }

          kirim('/admin/print/terpilih', [], FILTER);
        };

        perbarui();
      })();

      // Jangan kirim parameter filter yang kosong supaya URL dan tautan halaman tetap bersih
      (function () {
        var form = document.getElementById('formCariSiswa');
        if (!form) { return; }

        form.addEventListener('submit', function () {
          Array.prototype.slice.call(form.querySelectorAll('input, select')).forEach(function (el) {
            if (!el.name || el.name === 'cari') { return; }
            el.disabled = String(el.value).trim() === '';
          });
        });
      })();
    </script>
@endsection