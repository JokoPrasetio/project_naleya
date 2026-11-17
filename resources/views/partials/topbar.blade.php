<header class="app-topbar">
  <div class="d-flex align-items-center gap-3">
    <button class="icon-button sidebar-toggle" id="sidebarToggle" type="button"
      aria-label="Buka atau tutup menu" aria-expanded="true">
      <i class="bi bi-list"></i>
    </button>
    <div>
      <h2 class="topbar-title">@yield('page-title', 'Dashboard Pelayanan')</h2>
      <div class="topbar-date">{{ now()->locale('id')->translatedFormat('l, d F Y') }}</div>
    </div>
  </div>
  <div class="dropdown">
    <button class="profile-chip profile-button" type="button" data-bs-toggle="dropdown"
      aria-expanded="false">
      <span class="avatar">{{ strtoupper(substr(auth()->user()->name, 0, 2)) }}</span>
      
      <i class="bi bi-chevron-down small"></i>
    </button>
    <div class="dropdown-menu dropdown-menu-end profile-menu">
      <div class="dropdown-header">
        Akun pengguna<small>{{ auth()->user()->email }}</small>
      </div>
      <div class="dropdown-divider"></div>
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button class="dropdown-item text-danger" type="submit">
          <i class="bi bi-box-arrow-right"></i>Keluar
        </button>
      </form>
    </div>
  </div>
</header>
