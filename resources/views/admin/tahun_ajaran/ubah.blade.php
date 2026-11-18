@extends('layouts.base')

@section('content')
    <div class="container card shadow w-100 mb-3 mt-3 p-3">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <form action="/admin/tahun/{{ $res->id }}" method="post">
                    @method('PUT')
                    @csrf

                    {{-- Input Tahun Ajaran --}}
                    <div class="input-group mb-3">
                        <span class="input-group-text bg-light">Tahun Ajaran</span>
                        <input type="text"
                               name="tahun_ajaran"
                               class="form-control @error('tahun_ajaran') is-invalid @enderror"
                               placeholder="Contoh : 2022/2023"
                               value="{{ old('tahun_ajaran', $res->tahun_ajaran) }}">
                        @error('tahun_ajaran')
                            <div class="invalid-feedback">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Input Status --}}
                    <div class="mb-3">
                        <p>Status Tahun Ajaran</p>
                        <div class="form-check form-check-inline">
                            <input id="statusAktif"
                                   class="form-check-input @error('status') is-invalid @enderror"
                                   type="radio"
                                   name="status"
                                   value="aktif"
                                   {{ old('status', $res->status) == 'aktif' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusAktif">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input id="statusNonAktif"
                                   class="form-check-input @error('status') is-invalid @enderror"
                                   type="radio"
                                   name="status"
                                   value="tidak aktif"
                                   {{ old('status', $res->status) == 'tidak aktif' ? 'checked' : '' }}>
                            <label class="form-check-label" for="statusNonAktif">Tidak Aktif</label>
                        </div>
                        @error('status')
                            <div class="invalid-feedback d-block">
                                {{ $message }}
                            </div>
                        @enderror
                    </div>

                    {{-- Submit --}}
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
        </div>
    </div>
@endsection
