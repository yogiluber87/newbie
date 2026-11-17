@extends('layouts.base')

@section('content')
    <div class="container card shadow mt-3 mb-3 p-3">
      <div class="row justify-content-center mb-3">
        <h4 class="text-uppercase text-center">Tambah Siswa Baru</h4>
      </div>
      <div class="row justify-content-center">
        <div class="col-lg-9">
          <form action="/admin/siswa" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="mb-3">
              <label for="nama_lengkap" class="form-label">Nama Lengkap Siswa</label>
              <input type="text" class="form-control @error('nama_lengkap') is-invalid @enderror" name="nama_lengkap">
              @error('nama_lengkap')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
            </div>
            <div class="mb-3">
              <label for="nisn" class="form-label">Nomor Induk Siswa Nasional</label>
              <input type="text" class="form-control @error('nisn') is-invalid @enderror" name="nisn" autocomplete="off">
              @error('nisn')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
              <div class="form-text text-danger d-none" id="pesanNisn"></div>
            </div>
            <div class="mb-3">
              <label for="nis" class="form-label">Nomor Induk Siswa</label>
              <input type="text" class="form-control @error('nis') is-invalid @enderror" name="nis" autocomplete="off">
              @error('nis')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
              <div class="form-text text-danger d-none" id="pesanNis"></div>
            </div>
            <div class="mb-3">
              <label for="email" class="form-label">Alamat Email Siswa (Gmail)</label>
              <input type="email" class="form-control @error('email') is-invalid @enderror" name="email">
              @error('email')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
            </div>
            <div class="mb-3">
              <label for="foto" class="form-label">Pas Foto Siswa</label>
              <input class="form-control @error('foto') is-invalid @enderror" type="file" name="foto">
              @error('foto')
                <div class="invalid-feedback">
                  {{ $message }}  
                </div>                   
              @enderror
            </div>
            <div class="mb-3">
              <label for="group_id" class="form-label">Kelas Siswa</label>
              <select class="form-select @error('group_id') is-invalid @enderror" name="group_id">
                <option value="" disabled {{ old('group_id') ? '' : 'selected' }}>-- Pilih Kelas --</option>
                @foreach ($kelas as $item)
                    <option value="{{$item->id}}" {{ old('group_id') == $item->id ? 'selected' : '' }}>{{$item->nama}}</option>
                @endforeach
              </select>
              @error('group_id')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
            </div>
            <div class="mb-3">
              <label for="year_id" class="form-label">Tahun Ajaran siswa Didaftarkan</label>
              <select class="form-select @error('year_id') is-invalid @enderror" name="year_id">
                <option value="" disabled {{ old('year_id') ? '' : 'selected' }}>-- Pilih Tahun Ajaran --</option>
                @foreach ($tahun as $item)
                    <option value="{{$item->id}}" {{ old('year_id') == $item->id ? 'selected' : '' }}>{{$item->tahun_ajaran}}</option>
                @endforeach
              </select>
              @error('year_id')
                  <div class="invalid-feedback">
                    {{$message}}
                  </div>
              @enderror
            </div>
            <div class="mb-3">
              <p>Jenis Kelamin</p>
              <div class="form-check form-check-inline">
                <input class="form-check-input @error('jenis_kelamin') is-invalid @enderror" type="radio" name="jenis_kelamin" value="L">
                <label class="form-check-label" for="inlineRadio1">Laki-laki</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input @error('jenis_kelamin') is-invalid @enderror" type="radio" name="jenis_kelamin" value="P">
                <label class="form-check-label" for="inlineRadio2">Perempuan</label>
              </div>
              @error('jenis_kelamin')
                <div class="invalid-feedback">
                  {{ $message }}  
                </div>                   
              @enderror
            </div>
            <div class="d-grip gap-2 mb-3 w-100">
              <p class="fw-light"><span class="text-danger fw-bold">*</span> pastikan data yang kamu masukkan benar dan sesuai ketentuan</p>
              <p class="small text-muted mb-2">
                Setelah data siswa disimpan, pengisian otomatis lanjut ke <strong>data ayah</strong>, lalu <strong>ibu</strong>,
                <strong>biodata</strong>, dan <strong>wali</strong>. Langkah tersebut boleh dilewati dan bisa dilengkapi kapan saja.
              </p>
              <button type="submit" class="btn btn-primary w-100"><i class="bi bi-cloud-check me-2"></i> Simpan & Lanjut Isi Data Ayah</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <script>
    (function () {
        var token = document.querySelector('input[name="_token"]').value;
        var inputNisn = document.querySelector('input[name="nisn"]');
        var inputNis  = document.querySelector('input[name="nis"]');

        function tampilkan(el, pesan) {
            var kotak = document.getElementById(el === inputNisn ? 'pesanNisn' : 'pesanNis');
            if (pesan) {
                kotak.textContent = pesan;
                kotak.classList.remove('d-none');
                el.classList.add('is-invalid');
            } else {
                kotak.textContent = '';
                kotak.classList.add('d-none');
                el.classList.remove('is-invalid');
            }
        }

        function cekDuplikat(el) {
            var nisn = inputNisn.value.trim();
            var nis  = inputNis.value.trim();
            if (!nisn && !nis) return;

            fetch('/admin/siswa/cek-duplikat', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ nisn: nisn, nis: nis })
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (el === inputNisn) {
                    tampilkan(inputNisn, data.nisn_dipakai ? 'NISN ini sudah terdaftar. Gunakan NISN lain.' : '');
                } else {
                    tampilkan(inputNis, data.nis_dipakai ? 'NIS ini sudah terdaftar. Gunakan NIS lain.' : '');
                }
            })
            .catch(function () { /* diamkan bila gagal */ });
        }

        if (inputNisn) inputNisn.addEventListener('blur', function () { cekDuplikat(inputNisn); });
        if (inputNis)  inputNis.addEventListener('blur', function () { cekDuplikat(inputNis); });
    })();
    </script>
@endsection