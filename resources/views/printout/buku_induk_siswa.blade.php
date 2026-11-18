<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>{{ $setelan['nama_sekolah'] }} - Buku Induk Siswa</title>
</head>
<body>
  <div style="margin: 34px 20px;">
    @include('printout.partials.kop_sekolah', ['judul' => 'Buku Induk Siswa'])
    @include('printout.partials.buku_induk', ['res' => $res])
  </div>
</body>
</html>
