<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Akses Ditolak — Nama Fasilitas</title>
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('styles/app.css') }}?v={{ filemtime(public_path('styles/app.css')) }}">
</head>
<body class="access-error-page">
  <main class="access-error-shell">
    <div class="access-error-card">
      <span class="access-error-icon" aria-hidden="true"><i class="bi bi-shield-lock"></i></span>
      <span class="access-error-code">403 · Akses ditolak</span>
      <h1>Anda tidak memiliki izin untuk membuka halaman ini.</h1>
      <p>Jika halaman ini diperlukan untuk pekerjaan Anda, hubungi administrator agar hak akses akun diperiksa.</p>
      <div class="access-error-actions">
        @auth
          @can('dashboard.view')
            <a class="btn btn-naleya" href="{{ route('dashboard') }}"><i class="bi bi-house me-1"></i> Ke dashboard</a>
          @endcan
          @can('access.users.view')
            <a class="btn btn-outline-secondary" href="{{ route('access.users.index') }}">Ke halaman pengguna</a>
          @endcan
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button class="btn btn-outline-secondary" type="submit">Ganti akun</button>
          </form>
        @else
          <a class="btn btn-naleya" href="{{ route('login') }}">Ke halaman masuk</a>
        @endauth
      </div>
    </div>
    <small class="access-error-foot">Nama Fasilitas · Akses sesuai peran pengguna</small>
  </main>
</body>
</html>
