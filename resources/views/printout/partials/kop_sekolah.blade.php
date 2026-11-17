@php
  $logoPath = $setelan['has_logo']
      ? storage_path('app/public/' . $setelan['logo'])
      : public_path('images/tut wuri handayani.png');
@endphp
<table style="width: 100%; border-bottom: 3px double #000; margin-bottom: 15px;">
  <tr>
    <td style="width: 90px;">
      <img src="{{ $logoPath }}" alt="Logo" width="75px">
    </td>
    <td style="text-align: center;">
      <h2 style="margin: 0;">{{ $setelan['nama_sekolah'] }}</h2>
      @if ($setelan['alamat_sekolah'])
        <p style="margin: 4px 0 0;">{{ $setelan['alamat_sekolah'] }}</p>
      @endif
    </td>
    <td style="width: 90px;"></td>
  </tr>
</table>
