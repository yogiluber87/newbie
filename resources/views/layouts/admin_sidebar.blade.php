@php
    /*
     * Menu sidebar untuk petugas (superadmin & tata usaha).
     * Gaya mengikuti portal TKA: label bagian, ikon, caret, dan sub-menu
     * dengan garis pohon. Pengaturan & Akun Pengguna hanya untuk superadmin.
     */
    $u = auth()->user();

    $grupKesiswaan = [
        ['label' => 'Tambah Siswa',   'url' => '/admin/siswa/create',   'aktif' => request()->is('admin/siswa/create')],
        ['label' => 'Daftar Siswa',   'url' => '/admin/siswa',          'aktif' => request()->is('admin/siswa') || request()->is('admin/siswa/*/edit') || request()->is('admin/siswa/*') && !request()->is('admin/siswa/create', 'admin/siswa/import*')],
        ['label' => 'Data Mutasi',    'url' => '/admin/mutasi',         'aktif' => request()->is('admin/mutasi*')],
        ['label' => 'Impor Biodata',  'url' => '/admin/siswa/import',   'aktif' => request()->is('admin/siswa/import')],
        ['label' => 'Impor Lengkap',  'url' => '/admin/siswa/import-lengkap', 'aktif' => request()->is('admin/siswa/import-lengkap')],
        ['label' => 'Cetak / Ekspor PDF', 'url' => '/admin/print/daftar/preview', 'aktif' => request()->is('admin/print*')],
    ];

    $grupMaster = [
        ['label' => 'Daftar Kelas',        'url' => '/admin/grup',        'aktif' => request()->is('admin/grup') || request()->is('admin/grup/*/edit')],
        ['label' => 'Tambah Kelas',        'url' => '/admin/grup/create', 'aktif' => request()->is('admin/grup/create')],
        ['label' => 'Daftar Tahun Ajaran', 'url' => '/admin/tahun',       'aktif' => request()->is('admin/tahun') || request()->is('admin/tahun/*/edit')],
        ['label' => 'Tambah Tahun Ajaran', 'url' => '/admin/tahun/create','aktif' => request()->is('admin/tahun/create')],
    ];

    $grupAkun = [
        ['label' => 'Daftar Akun', 'url' => '/admin/akun',        'aktif' => request()->is('admin/akun') || request()->is('admin/akun/*/edit')],
        ['label' => 'Tambah Akun', 'url' => '/admin/akun/create', 'aktif' => request()->is('admin/akun/create')],
    ];

    $bukaKesiswaan = collect($grupKesiswaan)->contains('aktif', true)
        || request()->is('admin/ayah*', 'admin/ibu*', 'admin/wali*', 'admin/biodata*');
    $bukaMaster = collect($grupMaster)->contains('aktif', true);
    $bukaAkun = collect($grupAkun)->contains('aktif', true);
@endphp

<ul class="side-menu">
  <li class="side-menu-label">Menu Utama</li>

  <li>
    <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}">
      <span class="side-menu-icon"><i class="fas fa-tachometer-alt"></i></span>
      <span class="side-menu-title">Dashboard</span>
    </a>
  </li>

  <li>
    <a href="#collapse-kesiswaan" data-bs-toggle="collapse" role="button"
       aria-expanded="{{ $bukaKesiswaan ? 'true' : 'false' }}" aria-controls="collapse-kesiswaan">
      <span class="side-menu-icon"><i class="fas fa-user-graduate"></i></span>
      <span class="side-menu-title">Kesiswaan</span>
      <span class="caret"></span>
    </a>
    <ul class="sub-menu collapse {{ $bukaKesiswaan ? 'show' : '' }}" id="collapse-kesiswaan">
      @foreach ($grupKesiswaan as $item)
        <li><a href="{{ $item['url'] }}" class="{{ $item['aktif'] ? 'active' : '' }}">{{ $item['label'] }}</a></li>
      @endforeach
    </ul>
  </li>

  <li>
    <a href="#collapse-master" data-bs-toggle="collapse" role="button"
       aria-expanded="{{ $bukaMaster ? 'true' : 'false' }}" aria-controls="collapse-master">
      <span class="side-menu-icon"><i class="fas fa-school"></i></span>
      <span class="side-menu-title">Data Master</span>
      <span class="caret"></span>
    </a>
    <ul class="sub-menu collapse {{ $bukaMaster ? 'show' : '' }}" id="collapse-master">
      @foreach ($grupMaster as $item)
        <li><a href="{{ $item['url'] }}" class="{{ $item['aktif'] ? 'active' : '' }}">{{ $item['label'] }}</a></li>
      @endforeach
    </ul>
  </li>

  @if ($u->isSuperadmin())
  <li class="side-menu-label">Manajemen Pengguna</li>

  <li>
    <a href="#collapse-akun" data-bs-toggle="collapse" role="button"
       aria-expanded="{{ $bukaAkun ? 'true' : 'false' }}" aria-controls="collapse-akun">
      <span class="side-menu-icon"><i class="fas fa-user-cog"></i></span>
      <span class="side-menu-title">Akun Pengguna</span>
      <span class="caret"></span>
    </a>
    <ul class="sub-menu collapse {{ $bukaAkun ? 'show' : '' }}" id="collapse-akun">
      @foreach ($grupAkun as $item)
        <li><a href="{{ $item['url'] }}" class="{{ $item['aktif'] ? 'active' : '' }}">{{ $item['label'] }}</a></li>
      @endforeach
    </ul>
  </li>

  <li>
    <a href="/admin/pengaturan" class="{{ request()->is('admin/pengaturan*') ? 'active' : '' }}">
      <span class="side-menu-icon"><i class="fas fa-cog"></i></span>
      <span class="side-menu-title">Pengaturan</span>
    </a>
  </li>
  @endif
</ul>