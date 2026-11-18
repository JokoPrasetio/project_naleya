<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Daftar Online — HealTech</title>
  <link rel="stylesheet" href="{{ asset('assets/bootstrap/css/bootstrap.min.css') }}">
  <link rel="stylesheet" href="{{ asset('styles/app.css') }}">
</head>
<body class="bg-light">
  <main class="container py-5" style="max-width: 880px">
    <div class="mb-4">
      <h1>Daftar Online</h1>
      <p class="text-muted">Isi data sesuai identitas. Jika Anda pernah terdaftar, gunakan NIK, nama, dan tanggal lahir yang sama.</p>
    </div>
    @if(session('registration_number'))
      <div class="alert alert-success" role="status">Pendaftaran berhasil. Simpan nomor pendaftaran Anda: <strong>{{ session('registration_number') }}</strong>.</div>
    @endif
    @if($errors->any())
      <div class="alert alert-danger" role="alert">Periksa kembali data yang diisi. {{ $errors->first() }}</div>
    @endif
    <section class="card border-0 shadow-sm"><div class="card-body p-4">
      @include('registrations.form', ['online' => true])
    </div></section>
    <p class="text-muted small mt-3">Data Anda hanya digunakan untuk pendaftaran dan verifikasi oleh petugas.</p>
  </main>
</body>
</html>
