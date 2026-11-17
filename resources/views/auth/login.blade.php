<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Masuk — HealTech</title>
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('styles/app.css') }}">
</head>
<body class="login-page">
  <main class="login-shell">
    <section class="login-visual" aria-labelledby="login-visual-title">
      <div class="login-visual-brand">
        <span class="login-visual-mark">H</span>
        <span>Heal <strong>Tech</strong></span>
      </div>
      <div class="login-visual-copy">
        <span class="login-visual-emblem" aria-hidden="true"><i class="bi bi-heart-pulse"></i></span>
        <span class="login-visual-eyebrow"><span class="login-visual-dot"></span> Healtech</span>
        <h2 id="login-visual-title">integrated digital health solutions.</h2>
        <div class="login-visual-pills">
          <div class="login-benefit">
            <i class="bi bi-shield-check" aria-hidden="true"></i>
            <strong>Akses</strong>
            <small>	Masuk dengan akun yang terdaftar.</small>
          </div>
          <div class="login-benefit">
            <i class="bi bi-grid-1x2" aria-hidden="true"></i>
            <strong>Pemeriksaan</strong>
            <small>Kelola data dan hasil pemeriksaan.</small>
          </div>
        </div>
      </div>
      <p class="login-visual-foot">HealTech</p>
    </section>
    <section class="login-card" aria-labelledby="login-title">
      <div class="login-form-inner">
        <h1 id="login-title">Selamat datang</h1>
        <p class="login-intro">Gunakan akun Anda untuk melanjutkan.</p>
        @if ($errors->any())
          <div class="alert alert-danger" role="alert">{{ $errors->first() }}</div>
        @endif
        <form method="POST" action="{{ route('login.store') }}">
          @csrf
          <div class="mb-3">
            <label class="form-label" for="email">Alamat email</label>
            <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email"
              value="{{ old('email') }}" placeholder="nama@contoh.com"
              autocomplete="username" required autofocus>
          </div>
          <div class="mb-3">
            <label class="form-label" for="password">Kata sandi</label>
            <div class="login-password-field">
              <input class="form-control @error('password') is-invalid @enderror" id="password" name="password"
                type="password" placeholder="Masukkan kata sandi" autocomplete="current-password" required>
              <button class="login-password-toggle" id="passwordToggle" type="button"
                aria-controls="password" aria-label="Tampilkan kata sandi" aria-pressed="false">
                <i class="bi bi-eye" aria-hidden="true"></i>
              </button>
            </div>
          </div>
          <button class="btn btn-naleya login-submit" type="submit">
            Masuk <i class="bi bi-arrow-right" aria-hidden="true"></i>
          </button>
        </form>
      </div>
      <p class="login-card-footer">&copy; {{ date('Y') }} Healtech.</p>
    </section>
  </main>
  @include('partials.page-loading')
  <script src="{{ asset('scripts/pages/login.js') }}"></script>
  <script src="{{ asset('scripts/page-loading.js') }}"></script>
</body>
</html>
