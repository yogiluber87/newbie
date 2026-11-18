@php
    /*
     * Menu sidebar untuk siswa: hanya data miliknya sendiri.
     */
    $u = auth()->user();
@endphp

<ul class="side-menu">
  <li class="side-menu-label">Menu Utama</li>

  <li>
    <a href="/home" class="{{ request()->is('home') ? 'active' : '' }}">
      <span class="side-menu-icon"><i class="fas fa-home"></i></span>
      <span class="side-menu-title">Beranda</span>
    </a>
  </li>

  <li class="side-menu-label">Data Saya</li>

  @if ($u->biodata)
    <li>
      <a href="/biodata/{{ $u->biodata->uri }}" class="{{ $title === 'Biodata Siswa' ? 'active' : '' }}">
        <span class="side-menu-icon"><i class="fas fa-id-card"></i></span>
        <span class="side-menu-title">Biodata Siswa</span>
      </a>
    </li>
  @else
    <li>
      <span class="text-muted" style="display:flex;gap:10px;padding:10px 14px;font-size:13px;" title="Data belum dibuat oleh petugas">
        <span class="side-menu-icon"><i class="fas fa-id-card"></i></span>
        <span class="side-menu-title">Biodata Siswa</span>
      </span>
    </li>
  @endif

  @if ($u->ibu)
    <li>
      <a href="/data-ibu/{{ $u->ibu->uri }}" class="{{ $title === 'Data Ibu Siswa' ? 'active' : '' }}">
        <span class="side-menu-icon"><i class="fas fa-female"></i></span>
        <span class="side-menu-title">Data Ibu</span>
      </a>
    </li>
  @endif

  @if ($u->ayah)
    <li>
      <a href="/data-ayah/{{ $u->ayah->uri }}" class="{{ $title === 'Data Ayah Siswa' ? 'active' : '' }}">
        <span class="side-menu-icon"><i class="fas fa-male"></i></span>
        <span class="side-menu-title">Data Ayah</span>
      </a>
    </li>
  @endif

  @if ($u->wali)
    <li>
      <a href="/data-wali/{{ $u->wali->uri }}" class="{{ $title === 'Data Wali Siswa' ? 'active' : '' }}">
        <span class="side-menu-icon"><i class="fas fa-user-friends"></i></span>
        <span class="side-menu-title">Data Wali</span>
      </a>
    </li>
  @endif

  <li class="side-menu-label">Akun</li>

  <li>
    <a href="/password/{{ $u->nisn }}" class="{{ $title === 'Ganti Password' ? 'active' : '' }}">
      <span class="side-menu-icon"><i class="fas fa-key"></i></span>
      <span class="side-menu-title">Ganti Password</span>
    </a>
  </li>

  <li>
    <a href="/admin/print/{{ $u->nisn }}/preview">
      <span class="side-menu-icon"><i class="fas fa-print"></i></span>
      <span class="side-menu-title">Cetak Buku Induk</span>
    </a>
  </li>
</ul>