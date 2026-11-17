<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <title>Daftar Siswa - {{ $setelan['nama_sekolah'] }}</title>
</head>
<body>
  <div>
    @php
      $logoPath = $setelan['has_logo']
          ? storage_path('app/public/' . $setelan['logo'])
          : public_path('images/tut wuri handayani.png');
    @endphp
    <table style="width: 100%; border-bottom: 3px double #000; margin-bottom: 10px;">
      <tr>
        <td style="width: 80px;">
          <img src="{{ $logoPath }}" alt="Logo" width="65px">
        </td>
        <td style="text-align: center;">
          <h2 style="margin: 0;">{{ $setelan['nama_sekolah'] }}</h2>
          @if ($setelan['alamat_sekolah'])
            <p style="margin: 4px 0 0;">{{ $setelan['alamat_sekolah'] }}</p>
          @endif
        </td>
        <td style="width: 80px;"></td>
      </tr>
    </table>
    <h3 style="text-align: center">Daftar Siswa</h3>
    <hr>
    <table style="border: 1px solid black; border-collapse: collapse;">
      <thead>
        <th style="border: 1px solid black; border-collapse: collapse;">No.</th>
        <th style="border: 1px solid black; border-collapse: collapse;">Nama</th>
        <th style="border: 1px solid black; border-collapse: collapse;">NISN</th>
        <th style="border: 1px solid black; border-collapse: collapse;">NIS</th>
        <th style="border: 1px solid black; border-collapse: collapse;">Alamat</th>
        <th style="border: 1px solid black; border-collapse: collapse;">Nomor Hp</th>
      </thead>
      @foreach ($res as $item)
          <tr>
            <td style="border: 1px solid black; border-collapse: collapse;">{{$loop->iteration}}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{$item->nama_lengkap}}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{$item->nisn}}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{$item->nis}}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{optional($item->biodata)->alamat}}</td>
            <td style="border: 1px solid black; border-collapse: collapse;">{{optional($item->biodata)->nomor_hp}}</td>
          </tr>
        @endforeach
    </table>
  </div>
</body>
</html>