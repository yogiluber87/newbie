@extends('layouts.base')

@section('content')

    <div class="container card shadow border-info mt-3 mb-3">
      <div class="row mt-3 mb-3 justify-content-center">
        <div class="col-lg-3">
          
          <h3>Daftar Siswa</h3>
        </div>
        <div class="col-lg-6">
          <form action="/admin/siswa">
            <div class="input-group mb-3">
              <input type="text" class="form-control" placeholder="Cari.." name="cari" value="{{ request('cari')}}">
              <button class="btn btn-outline-success" type="submit"><i class="bi bi-search"></i></button>
            </div>
          </form>
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
          <a href="/admin/siswa/ekspor-semua-csv{{ request('cari') ? '?cari=' . urlencode(request('cari')) : '' }}" class="btn btn-dark w-100 mt-2">
            <i class="bi bi-filetype-csv me-2"></i>{{ request('cari') ? 'Ekspor Hasil Pencarian (CSV)' : 'Ekspor Semua (CSV)' }}
          </a>
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
              @if (request('cari'))
                <button type="button" class="btn btn-outline-warning btn-sm" onclick="cetakSemuaCari()">
                  <i class="bi bi-printer me-1"></i>Cetak Hasil Pencarian
                </button>
              @endif
              @if (request('cari'))
                <button type="button" class="btn btn-outline-dark btn-sm" onclick="eksporSemuaCari()">
                  <i class="bi bi-search me-1"></i>Ekspor Hasil Pencarian (CSV)
                </button>
              @endif
              <a href="/admin/siswa/ekspor-semua-csv" class="btn btn-outline-dark btn-sm">
                <i class="bi bi-download me-1"></i>Ekspor Semua (CSV)
              </a>
            </div>
          </div>
          <table class="table">
            <thead>
              <th style="width: 40px;"><input type="checkbox" id="pilihSemua" title="Pilih semua di halaman ini"></th>
              <th>No</th>
              <th>Nama Siswa</th>
              <th>NISN</th>
              <th>Data Ayah Siswa</th>
              <th>Data Ibu Siswa</th>
              <th>Aksi</th>
            </thead>
            @foreach ($res as $item)
                <tr>
                  <td><input type="checkbox" class="pilih-siswa" value="{{$item->nisn}}"></td>
                  <td>{{$loop->iteration}}</td>
                  <td>{{$item->nama_lengkap}}</td>
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
            <a href="/admin/siswa/create" class="btn btn-primary btn-lg"><i class="bi bi-plus me-2"></i> Tambah Siswa</a>
          </div>
        </div>
    </div>
    @endif

    <script>
      (function () {
        var KUNCI = 'buku-induk-siswa-terpilih';
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

        function kirim(aksi, daftarNisn, cari) {
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

          if (cari) {
            tambah('cari', cari);
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
          var cari = @json(request('cari'));

          if (!cari) {
            peringatan('Tidak ada kata kunci', 'Kotak pencarian masih kosong.');
            return;
          }

          kirim('/admin/siswa/ekspor-pilihan', [], cari);
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
          var cari = @json(request('cari'));

          if (!cari) {
            peringatan('Tidak ada kata kunci', 'Kotak pencarian masih kosong.');
            return;
          }

          kirim('/admin/print/terpilih', [], cari);
        };

        perbarui();
      })();
    </script>
@endsection