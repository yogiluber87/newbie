@extends('layouts.base')

@section('content')
    <div class="container card shadow my-3 p-3">
      <div class="row justify-content-center">
        <div class="col-lg-10">
        </div>
      </div>
      <form action="/data-ayah/{{ $res->uri }}" method="post" class="mx-3 my-3">
        @method('put')
        @csrf
        <div class="row">
          {{-- Kolom 1 --}}
          <div class="col-lg-4">
            <div class="mb-3">
              <label for="nama" class="form-label">Nama lengkap</label>
              <input type="text" 
                     class="form-control @error('nama') is-invalid @enderror" 
                     name="nama" 
                     value="{{ old('nama', $res->nama) }}" 
                     placeholder="Contoh: Agus Rahman">
              @error('nama')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="tempat_lahir" class="form-label">Kabupaten/Kota Kelahiran</label>
              <input type="text" 
                     class="form-control @error('tempat_lahir') is-invalid @enderror" 
                     name="tempat_lahir" 
                     value="{{ old('tempat_lahir', $res->tempat_lahir) }}" 
                     placeholder="Contoh : Kab. Tanah Datar">
              @error('tempat_lahir')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="tanggal_lahir" class="form-label">Tanggal Lahir</label>
              <input type="date" 
                     class="form-control @error('tanggal_lahir') is-invalid @enderror" 
                     name="tanggal_lahir" 
                     value="{{ old('tanggal_lahir', $res->tanggal_lahir) }}">
              @error('tanggal_lahir')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="agama" class="form-label">Agama</label>
              <select class="form-select @error('agama') is-invalid @enderror" name="agama">
                <option value="Islam" @if (old('agama', $res->agama) === 'Islam') selected @endif>Islam</option>
                <option value="Protestan" @if (old('agama', $res->agama) === 'Protestan') selected @endif>Protestan</option>
                <option value="Katholik" @if (old('agama', $res->agama) === 'Katholik') selected @endif>Katholik</option>
                <option value="Buddha" @if (old('agama', $res->agama) === 'Buddha') selected @endif>Buddha</option>
                <option value="Hindu" @if (old('agama', $res->agama) === 'Hindu') selected @endif>Hindu</option>
              </select>
              @error('agama')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Kolom 2 --}}
          <div class="col-lg-4">
            <div class="mb-3">
              <label for="kewarganegaraan" class="form-label">Kewarganegaraan</label>
              <input type="text" 
                     class="form-control @error('kewarganegaraan') is-invalid @enderror" 
                     name="kewarganegaraan" 
                     value="{{ old('kewarganegaraan', $res->kewarganegaraan) }}" 
                     placeholder="Contoh: Indonesia">
              @error('kewarganegaraan')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="pekerjaan" class="form-label">Pekerjaan</label>
              <input type="text" 
                     class="form-control @error('pekerjaan') is-invalid @enderror" 
                     name="pekerjaan" 
                     value="{{ old('pekerjaan', $res->pekerjaan) }}" 
                     placeholder="Contoh: Juru masak">
              @error('pekerjaan')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="pendidikan" class="form-label">Pendidikan Terakhir</label>
              <select class="form-select @error('pendidikan') is-invalid @enderror" name="pendidikan">
                <option value="SD/Sederajat" @if (old('pendidikan', $res->pendidikan) === 'SD/Sederajat') selected @endif>SD/Sederajat</option>
                <option value="SMP/Sederajat" @if (old('pendidikan', $res->pendidikan) === 'SMP/Sederajat') selected @endif>SMP/Sederajat</option>
                <option value="SMA/Sederajat" @if (old('pendidikan', $res->pendidikan) === 'SMA/Sederajat') selected @endif>SMA/Sederajat</option>
                <option value="Diploma" @if (old('pendidikan', $res->pendidikan) === 'Diploma') selected @endif>Diploma</option>
                <option value="Strata-1" @if (old('pendidikan', $res->pendidikan) === 'Strata-1') selected @endif>Strata-1</option>
                <option value="Strata-2" @if (old('pendidikan', $res->pendidikan) === 'Strata-2') selected @endif>Strata-2</option>
                <option value="Strata-3" @if (old('pendidikan', $res->pendidikan) === 'Strata-3') selected @endif>Strata-3</option>
              </select>
              @error('pendidikan')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="penghasilan" class="form-label">Penghasilan perbulan</label>
              <input type="number" 
                     class="form-control @error('penghasilan') is-invalid @enderror" 
                     name="penghasilan" 
                     value="{{ old('penghasilan', $res->penghasilan) }}" 
                     placeholder="Contoh: 3000000">
              @error('penghasilan')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>

          {{-- Kolom 3 --}}
          <div class="col-lg-4">
            <div class="mb-3">
              <label for="alamat" class="form-label">Alamat sesuai KTP</label>
              <input type="text" 
                     class="form-control @error('alamat') is-invalid @enderror" 
                     name="alamat" 
                     value="{{ old('alamat', $res->alamat) }}" 
                     placeholder="Jl. Bata No.7, Medan Tembung, Medan">
              @error('alamat')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <label for="nomor_hp" class="form-label">Nomor Telefon</label>
              <input type="text" 
                     class="form-control @error('nomor_hp') is-invalid @enderror" 
                     name="nomor_hp" 
                     value="{{ old('nomor_hp', $res->nomor_hp) }}" 
                     placeholder="Contoh: 081234567891">
              @error('nomor_hp')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>

            <div class="mb-3">
              <p>Status Orang Tua</p>
              <div class="form-check form-check-inline">
                <input class="form-check-input @error('status') is-invalid @enderror" 
                       type="radio" 
                       name="status" 
                       value="Masih Hidup" 
                       @if (old('status', $res->status) == 'Masih Hidup') checked @endif>
                <label class="form-check-label">Masih hidup</label>
              </div>
              <div class="form-check form-check-inline">
                <input class="form-check-input @error('status') is-invalid @enderror" 
                       type="radio" 
                       name="status" 
                       value="Telah Meninggal" 
                       @if (old('status', $res->status) == 'Telah Meninggal') checked @endif>
                <label class="form-check-label">Telah Meninggal</label>
              </div>
              @error('status')
                <div class="invalid-feedback">{{ $message }}</div>
              @enderror
            </div>
          </div>
        </div>

        <div class="mb-3">
          <p class="fw-light mb-2">
            <span class="text-danger fw-bold">*</span> pastikan data yang kamu masukkan benar dan sesuai ketentuan
          </p>
          <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-primary btn-sm px-3">
              <i class="bi bi-cloud-check me-1"></i> Simpan
            </button>
          </div>
        </div>
      </form>
    </div>
@endsection
