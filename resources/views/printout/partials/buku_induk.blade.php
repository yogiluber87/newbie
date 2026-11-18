    <table style="width: 100%">
      <tr>
        <td style="width:500px;">
          <h5>Nama : {{$res->nama_lengkap}}</h5>
          <h6>NISN : {{$res->nisn}}</h6>
          <h6>NIS : {{$res->nis}}</h6>
        </td>
        <td style="width: 130px; text-align: center;">
          @if ($res->foto && file_exists(storage_path('app/public/' . $res->foto)))
            <img src="{{ storage_path('app/public/' . $res->foto) }}" alt="Foto {{ $res->nama_lengkap }}" style="width: 120px; border: 1px solid #666; padding: 2px;">
          @else
            <div style="width: 120px; height: 160px; border: 1px solid #666; text-align: center; line-height: 160px;">Foto 3 x 4</div>
          @endif
        </td>
      </tr>
    </table>
    <div style="margin-bottom: 30px; width: 100%;">
      <h3 style="margin-bottom: 20px;">A. Keterangan Siswa</h3>
      <table style="text-align: left;">
        <tr>
          <td style="width:350px;">Alamat</td>
          <td>:</td>
          <td>{{optional($res->biodata)->alamat}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kabupaten/Kota</td>
          <td>:</td>
          <td>{{optional($res->biodata)->kota}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Sekolah Asal</td>
          <td>:</td>
          <td>{{optional($res->biodata)->sekolah_asal}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kecamatan</td>
          <td>:</td>
          <td>{{optional($res->biodata)->kecamatan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kelahiran</td>
          <td>:</td>
          <td>{{optional($res->biodata)->tempat_lahir}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Tanggal Lahir</td>
          <td>:</td>
          <td>{{optional($res->biodata)->tanggal_lahir}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Anak ke</td>
          <td>:</td>
          <td>{{optional($res->biodata)->anak_ke}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Saudara Kandung</td>
          <td>:</td>
          <td>{{optional($res->biodata)->jlh_saudara}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Saudara Angkat</td>
          <td>:</td>
          <td>{{optional($res->biodata)->saudara_angkat}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Saudara Tiri</td>
          <td>:</td>
          <td>{{optional($res->biodata)->saudara_tiri}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Bahasa</td>
          <td>:</td>
          <td>{{optional($res->biodata)->bahasa}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Agama</td>
          <td>:</td>
          <td>{{optional($res->biodata)->agama}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kewarganegaraan</td>
          <td>:</td>
          <td>{{optional($res->biodata)->kewarganegaraan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kegemaran</td>
          <td>:</td>
          <td>{{optional($res->biodata)->hobi}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Jarak ke sekolah</td>
          <td>:</td>
          <td>{{optional($res->biodata)->jarak.' Km'}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Nomor HP</td>
          <td>:</td>
          <td>{{optional($res->biodata)->nomor_hp}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Alamat Email</td>
          <td>:</td>
          <td>{{$res->email}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Golongan Darah</td>
          <td>:</td>
          <td>{{optional($res->biodata)->goldar}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Tinggi Badan</td>
          <td>:</td>
          <td>{{optional($res->biodata)->tinggi.' Cm'}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Berat Badan</td>
          <td>:</td>
          <td>{{optional($res->biodata)->berat.' Kg'}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Riwayat Penyakit</td>
          <td>:</td>
          <td>{{optional($res->biodata)->penyakit}}</td>
        </tr>
      </table>
    </div>
    <div style="margin-bottom: 30px; width: 100%; page-break-inside: avoid;">
      <h3 style="margin-top: 10px;">B. Keterangan Ayah Siswa</h3>
      <table style="text-align: left;">
        <tr>
          <td style="width:350px;">Nama Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->nama}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Alamat Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->alamat}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kabupaten/Kota Kelahiran Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->tempat_lahir}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Tanggal Lahir Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->tanggal_lahir}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Agama Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->agama}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kewarganegaraan Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->kewarganegaraan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Pekerjaan Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->pekerjaan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Pendidikan Terakhir Ayah</td>
          <td>:</td>
          <td>{{optional($res->ayah)->pendidikan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Penghasilan perbulan Ayah</td>
          <td>:</td>
          <td>{{'Rp. '.number_format(optional($res->ayah)->penghasilan, 2,',', '.')}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Nomor Telefon</td>
          <td>:</td>
          <td>{{optional($res->ayah)->nomor_hp}}</td>
        </tr>
      </table>
    </div>
    <div style="margin-bottom: 30px; width: 100%; page-break-inside: avoid;">
      <h3 style="margin-top: 10px;">C. Keterangan Ibu Siswa</h3>
      <table style="text-align: left;">
        <tr>
          <td style="width:350px;">Nama ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->nama}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Alamat ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->alamat}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kabupaten/Kota Kelahiran Ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->tempat_lahir}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Tanggal Lahir ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->tanggal_lahir}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Agama ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->agama}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Kewarganegaraan ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->kewarganegaraan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Pekerjaan ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->pekerjaan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Pendidikan Terakhir ibu</td>
          <td>:</td>
          <td>{{optional($res->ibu)->pendidikan}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Penghasilan perbulan ibu</td>
          <td>:</td>
          <td>{{'Rp. '.number_format(optional($res->ibu)->penghasilan, 2,',', '.')}}</td>
        </tr>
        <tr>
          <td style="width:350px;">Nomor Telefon</td>
          <td>:</td>
          <td>{{optional($res->ibu)->nomor_hp}}</td>
        </tr>
      </table>
    </div>
