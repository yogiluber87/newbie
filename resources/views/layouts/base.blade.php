<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">
  <link rel="icon" href="{{ $setelan['has_logo'] ? $setelan['logo_url'] : asset('images/tut wuri handayani.ico') }}">

  {{-- Font & Ikon ala portal TKA --}}
  <link href="https://fonts.googleapis.com/css?family=Poppins:300,400,500,600,700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  {{-- Bootstrap --}}
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.9.1/font/bootstrap-icons.css">

  {{-- Tema portal --}}
  <link rel="stylesheet" href="{{ asset('css/portal.css') }}">

  <title>{{ $title ?? 'Buku Induk Siswa' }}</title>
</head>
<body>
  @php
    $staff = auth()->user()->isStaff();
    $judulHalaman = $title ?? ($staff ? 'Dashboard' : 'Beranda');
    $avatar = auth()->user()->foto
        ? asset('storage/' . auth()->user()->foto)
        : (auth()->user()->jenis_kelamin === 'P' ? '/images/female.png' : '/images/male.png');
  @endphp

  <div class="main">
    {{-- Sidebar --}}
    <aside class="menu" id="portal-menu">
      <div class="menu-header">
        <img src="{{ $setelan['logo_url'] }}" alt="Logo" class="menu-logo">
        <span class="menu-brand">
          {{ $setelan['nama_sekolah'] }}
          <small>Buku Induk Siswa</small>
        </span>
        @if ($staff)
        <div class="menu-quick">
          <a href="/dashboard" class="{{ request()->is('dashboard') ? 'active' : '' }}" title="Dashboard">
            <i class="fas fa-home"></i>Dashboard
          </a>
          @if (auth()->user()->isSuperadmin())
          <a href="/admin/pengaturan" class="{{ request()->is('admin/pengaturan*') ? 'active' : '' }}" title="Pengaturan">
            <i class="fas fa-user-cog"></i>Pengaturan
          </a>
          @endif
          <a href="#" onclick="event.preventDefault(); document.getElementById('form-keluar').submit();" title="Keluar">
            <i class="fas fa-sign-out-alt"></i>Keluar
          </a>
        </div>
        @endif
      </div>

      <div class="menu-scroll">
        @if ($staff)
          @include('layouts.admin_sidebar')
        @else
          @include('layouts.siswa_sidebar')
        @endif
      </div>

      <div class="menu-footer">
        <span class="role-tag"><i class="fas fa-user-circle me-1"></i>{{ auth()->user()->roleLabel() }}</span>
      </div>
    </aside>

    <div class="menu-overlay" id="portal-overlay"></div>

    {{-- Header & Konten --}}
    <div class="wrapper-main">
      <header class="header" id="portal-header">
        <a href="#" class="toggle-menu" id="portal-toggle" title="Tampilkan / sembunyikan menu">
          <i class="fas fa-bars"></i>
        </a>
        <ul class="header-right">
          <li class="user-menu">
            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
              <img class="avatar" src="{{ $avatar }}" alt="{{ auth()->user()->nama_lengkap }}">
              <span class="d-none d-sm-inline">{{ Str::limit(auth()->user()->nama_lengkap, 22) }}</span>
              <i class="fas fa-caret-down small"></i>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <div class="photo">
                <img src="{{ $avatar }}" alt="" width="72">
              </div>
              <li><span class="dropdown-item-text text-center text-muted small">{{ auth()->user()->email }}</span></li>
              @if (auth()->user()->isSuperadmin())
              <li><a class="dropdown-item" href="/admin/pengaturan"><i class="fas fa-user-cog"></i> Pengaturan Akun</a></li>
              @endif
              @if (!$staff)
              <li><a class="dropdown-item" href="/password/{{ auth()->user()->nisn }}"><i class="fas fa-key"></i> Ganti Password</a></li>
              @endif
              <li><hr class="dropdown-divider"></li>
              <li>
                <form id="form-keluar" action="/keluar" method="post">
                  @csrf
                  <button type="submit" class="dropdown-item"><i class="fas fa-sign-out-alt"></i> Keluar</button>
                </form>
              </li>
            </ul>
          </li>
        </ul>
      </header>

      <div class="wrapper-content" id="portal-wrapper">
        <div class="page-band">
          <h1>
            {{ $judulHalaman }}
            @hasSection('subtitle')
              <small>@yield('subtitle')</small>
            @endif
          </h1>
          <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
              <li class="breadcrumb-item"><a href="{{ $staff ? '/dashboard' : '/home' }}">{{ $staff ? 'Dashboard' : 'Beranda' }}</a></li>
              <li class="breadcrumb-item active" aria-current="page">{{ $judulHalaman }}</li>
            </ol>
          </nav>
        </div>
        <div class="content">
          @yield('content')
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="{{ asset('js/portal.js') }}"></script>
  <script src="{{ asset('js/sidebars.js') }}"></script>
  @stack('scripts')
</body>
</html>