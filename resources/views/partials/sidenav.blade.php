<aside class="app-sidebar" id="appSidebar">
      <div class="sidebar-head">
        <a class="brand" href="{{ auth()->user()->can('dashboard.view') ? route('dashboard') : (auth()->user()->can('master.examinations.view') ? route('master.examinations.index') : (auth()->user()->can('master.type_examinations.view') ? route('master.type_examinations.index') : (auth()->user()->can('master.users.view') ? route('master.users.index') : route('access.roles.index')))) }}">
          <span class="brand-mark"><i class="bi bi-flower1 fs-5"></i></span>
          <span
            ><span class="brand-title d-block">Dashboard</span
            ><span class="brand-subtitle"></span
            ></span>
        </a>
      </div>

      <div class="sidebar-scroll">
        @foreach (config('access.menus') as $menuGroup)
          @if (collect($menuGroup['items'])->contains(fn ($item) => auth()->user()->can($item['permission'])))
            <div class="nav-caption">{{ $menuGroup['label'] }}</div>
            <nav class="app-nav" aria-label="{{ $menuGroup['label'] }}">
              @foreach ($menuGroup['items'] as $item)
                @can($item['permission'])
                  <a class="{{ request()->routeIs($item['route']) || request()->routeIs($item['route'].'.*') ? 'active' : '' }}"
                     href="{{ route($item['route']) }}">
                    <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>{{ $item['label'] }}
                  </a>
                @endcan
              @endforeach
            </nav>
          @endif
        @endforeach
      </div>
      <div class="sidebar-foot">
        <span class="system-dot"></span>
        <span>
            <strong>Sistem aktif</strong>
        </span>
      </div>
    </aside>
