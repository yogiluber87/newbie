<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{ $setelan['nama_sekolah'] }} - Buku Induk Siswa ({{ $res->count() }} siswa)</title>
</head>
<body>
  @foreach ($res as $siswa)
    <div style="margin: 34px 20px; width: 100%; {{ $loop->last ? '' : 'page-break-after: always;' }}">
      @include('printout.partials.kop_sekolah')
      @include('printout.partials.buku_induk', ['res' => $siswa])
    </div>
  @endforeach
</body>
</html>
