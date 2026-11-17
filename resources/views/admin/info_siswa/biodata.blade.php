@extends('layouts.base')

@section('content')
    <div class="container mt-3 card shadow">
      @if (($lanjut ?? false) && !empty($siswa))
        <div class="mt-3">
          @include('admin.info_siswa._langkah', ['siswa' => $siswa, 'langkah' => 'biodata'])
        </div>
      @endif
      <div class="row justify-content-center mb-3">
        <div class="col-lg-9">
          <form action="/admin/biodata/{{$res->uri}}" method="post">
            @method('put')
            @csrf
            <div class="container">
              @php
                $kelengkapan = $kelengkapan ?? $res->kelengkapan();
                $labelStatus = [
                    'kosong' => 'belum diisi',
                    'sebagian' => 'sebagian',
                    'lengkap' => 'lengkap',
                ];
                $badgeStatus = [
                    'kosong' => 'bg-danger',
                    'sebagian' => 'bg-warning text-dark',
                    'lengkap' => 'bg-success',
                ];
              @endphp
              <style>
                /* Tab biodata: nomor besar berwarna sesuai kelengkapan */
                #ex1 .nav-link.tab-tahap { position: relative; padding: .6rem .5rem; border-bottom-width: 2px; }
                #ex1 .nav-link.tab-tahap.tab-kosong { color: #dc3545; }
                #ex1 .nav-link.tab-tahap.tab-sebagian { color: #b8860b; }
                #ex1 .nav-link.tab-tahap.tab-lengkap { color: #198754; }
                #ex1 .angka-tahap {
                  display: inline-flex; align-items: center; justify-content: center;
                  width: 2.75rem; height: 2.75rem; border-radius: 50%;
                  border: 2px solid currentColor; background: #fff; color: inherit;
                  font-size: 1.4rem; font-weight: 700; line-height: 1;
                }
                #ex1 .nav-link.tab-tahap.active { background: #fff; border-bottom-color: #fff; }
                #ex1 .nav-link.tab-tahap.active::after {
                  content: ''; position: absolute; left: 0; right: 0; bottom: -2px; height: 3px;
                  background: currentColor; border-radius: 3px;
                }
                /* Tab aktif: lingkaran diberi cincin, angka tetap berwarna */
                #ex1 .nav-link.tab-tahap.active .angka-tahap { box-shadow: 0 0 0 4px rgba(0, 0, 0, .07); }
                #ex1 .jumlah-terisi { font-variant-numeric: tabular-nums; font-weight: 600; }
              </style>
              <!-- Tabs navs -->
                  <ul class="nav nav-tabs nav-fill mb-2" id="ex1" role="tablist">
                    @foreach ([1, 2, 3] as $nomor)
                      @php
                        $info = $kelengkapan[$nomor];
                      @endphp
                      <li class="nav-item" role="presentation">
                        <a
                          class="nav-link tab-tahap tab-{{ $info['status'] }}{{ $nomor === 1 ? ' active' : '' }}"
                          id="ex2-tab-{{ $nomor }}"
                          data-bs-toggle="tab"
                          href="#ex2-tabs-{{ $nomor }}"
                          role="tab"
                          aria-controls="ex2-tabs-{{ $nomor }}"
                          aria-selected="{{ $nomor === 1 ? 'true' : 'false' }}"
                          >
                          <span class="angka-tahap">{{ $nomor }}</span>
                          <span class="d-block small mt-1 text-body">
                            <span class="jumlah-terisi">{{ $info['terisi'] }}</span>/{{ $info['total'] }} terisi
                          </span>
                          <span class="badge rounded-pill mt-1 status-tahap {{ $badgeStatus[$info['status']] }}">{{ $labelStatus[$info['status']] }}</span>
                        </a>
                      </li>
                    @endforeach
                  </ul>
                  <p class="small text-muted text-center mb-3">
                    <span class="badge bg-danger">Merah</span> belum diisi &middot;
                    <span class="badge bg-warning text-dark">Kuning</span> sebagian &middot;
                    <span class="badge bg-success">Hijau</span> lengkap
                  </p>
                  <!-- Tabs navs -->

                  <!-- Tabs content -->
                  <div class="tab-content" id="ex2-content" style="height: fit-content">
                    <div
                      class="tab-pane fade show active"
                      id="ex2-tabs-1"
                      role="tabpanel"
                      aria-labelledby="ex2-tab-1"
                    >
                    <h3 class="text-uppercase text-center">{{$title}}</h3>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Alamat</span>
                        <input type="text" class="form-control @error('alamat') is-invalid @enderror" placeholder="Contoh : Jl. Kemuning No.7" name="alamat" value="{{$res->alamat}}">
                        @error('alamat')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Kabupaten/Kota</span>
                        <input type="text" class="form-control @error('kota') is-invalid @enderror" placeholder="Contoh : Kab. Tanah Datar" name="kota" value="{{$res->kota}}">
                        @error('kota')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Kecamatan</span>
                        <input type="text" class="form-control @error('kecamatan') is-invalid @enderror" placeholder="Contoh : Sidorejo" name="kecamatan" value="{{$res->kecamatan}}">
                        @error('kecamatan')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Tempat dan Tanggal Lahir</span>
                        <input type="text" class="form-control @error('tempat_lahir') is-invalid @enderror" placeholder="Contoh : Kab. Tanah Datar" name="tempat_lahir" value="{{$res->tempat_lahir}}">
                        <input type="date" class="form-control @error('tanggal_lahir') is-invalid @enderror" name="tanggal_lahir" value="{{$res->tanggal_lahir}}">
                        @error('tempat_lahir' || 'tanggal_lahir')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Anak ke</span>
                        <input type="number" class="form-control @error('anak_ke') is-invalid @enderror" placeholder="Contoh : 1" name="anak_ke" value="{{$res->anak_ke}}">
                        @error('anak_ke')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Jumlah Saudara Kandung</span>
                        <input type="number" class="form-control @error('jlh_saudara') is-invalid @enderror" placeholder="Contoh : 2" name="jlh_saudara" value="{{$res->jlh_saudara}}">
                        @error('jlh_saudara')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Jumlah Saudara Tiri</span>
                        <input type="number" class="form-control @error('saudara_tiri') is-invalid @enderror" placeholder="Contoh : 2" name="saudara_tiri" value="{{$res->saudara_tiri}}">
                        @error('saudara_tiri')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Jumlah Saudara Angkat</span>
                        <input type="number" class="form-control @error('saudara_angkat') is-invalid @enderror" placeholder="Contoh : 2" name="saudara_angkat" value="{{$res->saudara_angkat}}">
                        @error('saudara_angkat')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                    </div>
                    <div
                      class="tab-pane fade"
                      id="ex2-tabs-2"
                      role="tabpanel"
                      aria-labelledby="ex2-tab-2"
                    >
                      <h3 class="text-uppercase text-center">{{$title}}</h3>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Bahasa sehari-hari</span>
                        <input type="text" class="form-control @error('bahasa') is-invalid @enderror" placeholder="Contoh : Bahasa Indonesia" name="bahasa" value="{{$res->bahasa}}">
                        @error('bahasa')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Agama</span>
                        <select name="agama" class="form-select">
                          <option value="" @if (empty($res->agama)) selected @endif>-- Pilih agama --</option>
                          <option value="Islam" @if ($res->agama === 'Islam') selected @endif>Islam</option>
                          <option value="Katholik"  @if ($res->agama === 'Katholik') selected @endif>Katholik</option>
                          <option value="Protestan"  @if ($res->agama === 'Protestan') selected @endif>Protestan</option>
                          <option value="Buddha"  @if ($res->agama === "Buddha") selected @endif>Buddha</option>
                          <option value="Hindu"  @if ($res->agama === 'Hindu') selected @endif>Hindu</option>
                        </select>
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Jarak dari rumah (KM)</span>
                        <input type="number" class="form-control @error('jarak') is-invalid @enderror" placeholder="Contoh : 7" name="jarak" value="{{$res->jarak}}">
                        @error('jarak')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Nomor telefon siswa</span>
                        <input type="text" class="form-control @error('nomor_hp') is-invalid @enderror" placeholder="Contoh : 081234567891" name="nomor_hp" value="{{$res->nomor_hp}}">
                        @error('nomor_hp')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Golongan Darah</span>
                        <select name="goldar" class="form-select">
                          <option value="" @if (empty($res->goldar)) selected @endif>-- Pilih golongan darah --</option>
                          <option value="O" @if ($res->goldar === 'O') selected @endif>O</option>
                          <option value="A"  @if ($res->goldar === 'A') selected @endif>A</option>
                          <option value="B"  @if ($res->goldar === 'B') selected @endif>B</option>
                          <option value="AB"  @if ($res->goldar === "AB") selected @endif>AB</option>
                        </select>
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Tinggi badan (CM)</span>
                        <input type="number" class="form-control @error('tinggi') is-invalid @enderror" placeholder="Contoh : 172" name="tinggi" value="{{$res->tinggi}}">
                        @error('tinggi')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Berat badan (KG)</span>
                        <input type="number" class="form-control @error('berat') is-invalid @enderror" placeholder="Contoh : 55" name="berat" value="{{$res->berat}}">
                        @error('berat')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                    </div>
                    <div
                      class="tab-pane fade"
                      id="ex2-tabs-3"
                      role="tabpanel"
                      aria-labelledby="ex2-tab-3"
                    > 
                      <h3 class="text-uppercase text-center">{{$title}}</h3>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Riwayat penyakit</span>
                        <input type="text" class="form-control @error('penyakit') is-invalid @enderror" placeholder="Contoh : Asma" name="penyakit" value="{{$res->penyakit}}">
                        @error('penyakit')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Kegemaran</span>
                        <input type="text" class="form-control @error('hobi') is-invalid @enderror" placeholder="Contoh : Bulu tangkis" name="hobi" value="{{$res->hobi}}">
                        @error('hobi')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Kewarganegaraan</span>
                        <input type="text" class="form-control @error('kewarganegaraan') is-invalid @enderror" placeholder="Contoh : Indonesia" name="kewarganegaraan" value="{{$res->kewarganegaraan}}">
                        @error('kewarganegaraan')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Sekolah Asal</span>
                        <input type="text" class="form-control @error('sekolah_asal') is-invalid @enderror" placeholder="Contoh : SMPN 1 Kota Medan" name="sekolah_asal" value="{{$res->sekolah_asal}}">
                        @error('sekolah_asal')
                          <div class="invalid-feedback">
                            {{ $message }}  
                          </div>                   
                        @enderror
                      </div>
                      </div>
                    </div>
                  </div>
                  <!-- Tabs content -->

                  <!-- Tombol simpan selalu terlihat, di luar tab, menempel di bawah layar -->
                  <div class="d-flex flex-wrap align-items-center gap-2 mt-3">
                    <span class="small text-muted">
                      <i class="bi bi-info-circle me-1"></i>Tab yang sedang dibuka: <span id="tabAktif">1</span> dari 3.
                      Tekan <strong>Simpan</strong> kapan saja, data dari semua tab akan ikut tersimpan.
                    </span>
                  </div>
                  @include('admin.info_siswa._tombol', ['siswa' => $siswa, 'langkah' => 'biodata', 'lanjut' => $lanjut ?? false])
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
      // Perbarui keterangan tab yang sedang dibuka pada bilah tombol simpan
      (function () {
        var label = document.getElementById('tabAktif');
        if (!label) { return; }

        document.querySelectorAll('#ex1 [data-bs-toggle="tab"]').forEach(function (el, i) {
          el.addEventListener('shown.bs.tab', function () {
            label.textContent = i + 1;
          });
        });
      })();

      // Hitung ulang kelengkapan tiap tab saat isian diubah, supaya admin
      // langsung tahu tab mana yang masih merah (belum diisi).
      (function () {
        var gaya = {
          kosong: { kelas: 'tab-kosong', badge: 'bg-danger', label: 'belum diisi' },
          sebagian: { kelas: 'tab-sebagian', badge: 'bg-warning text-dark', label: 'sebagian' },
          lengkap: { kelas: 'tab-lengkap', badge: 'bg-success', label: 'lengkap' }
        };

        document.querySelectorAll('.tab-content .tab-pane').forEach(function (pane) {
          var nomor = pane.id.replace('ex2-tabs-', '');
          var tab = document.getElementById('ex2-tab-' + nomor);

          if (!tab) { return; }

          var kolom = pane.querySelectorAll('input:not([type="hidden"]), select, textarea');

          function hitung() {
            var terisi = 0;

            kolom.forEach(function (f) {
              if (String(f.value === undefined ? '' : f.value).trim() !== '') { terisi++; }
            });

            var status = terisi === 0 ? 'kosong' : (terisi === kolom.length ? 'lengkap' : 'sebagian');
            var warna = gaya[status];

            tab.classList.remove('tab-kosong', 'tab-sebagian', 'tab-lengkap');
            tab.classList.add(warna.kelas);

            var jumlah = tab.querySelector('.jumlah-terisi');
            if (jumlah) { jumlah.textContent = terisi; }

            var badge = tab.querySelector('.status-tahap');
            if (badge) {
              badge.className = 'badge rounded-pill mt-1 status-tahap ' + warna.badge;
              badge.textContent = warna.label;
            }
          }

          pane.addEventListener('input', hitung);
          pane.addEventListener('change', hitung);
          hitung();
        });
      })();
    </script>
@endsection