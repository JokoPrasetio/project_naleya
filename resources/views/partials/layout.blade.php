<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>@yield('page-title', 'Dashboard Pelayanan') — Nama Fasilitas</title>
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
  <link rel="stylesheet" href="{{ asset('styles/app.css') }}?v={{ filemtime(public_path('styles/app.css')) }}">
</head>
<body>
  @include('partials.sidenav')
  <main class="app-main">
    @include('partials.topbar')
    <div class="content-wrap">
      @yield('content')
    </div>
    @include('partials.footer')
  </main>
  @if (session('success') || $errors->any())
    <div class="toast-container position-fixed top-0 end-0 p-3 app-toast-container">
      <div class="toast app-toast {{ $errors->any() ? 'is-error' : '' }}" id="feedbackToast"
        role="{{ $errors->any() ? 'alert' : 'status' }}" aria-live="{{ $errors->any() ? 'assertive' : 'polite' }}"
        aria-atomic="true" data-bs-delay="{{ $errors->any() ? '8000' : '5000' }}">
        <div class="toast-body">
          <i class="bi {{ $errors->any() ? 'bi-exclamation-circle' : 'bi-check-circle' }} app-toast-icon" aria-hidden="true"></i>
          <div class="flex-grow-1">
            <strong>{{ $errors->any() ? 'Gagal menyimpan' : 'Berhasil' }}</strong>
            <small>{{ $errors->any() ? $errors->first() : session('success') }}</small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup notifikasi"></button>
        </div>
      </div>
    </div>
  @endif
  @include('partials.page-loading')
  <script src="{{ asset('assets/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
  <script src="{{ asset('scripts/app.js') }}"></script>
  <script src="{{ asset('scripts/feedback-toast.js') }}"></script>
  <script src="{{ asset('scripts/page-loading.js') }}"></script>
  @stack('scripts')
</body>
</html>
