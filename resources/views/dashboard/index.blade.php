@extends('partials.layout')

@section('content')
        <section class="welcome-panel mb-4">
          <div class="position-relative" style="z-index: 1">
            <span class="eyebrow">Pusat kontrol hari ini</span>
            <h1>Selamat pagi, operasional berjalan dengan baik.</h1>
            <p>
              Pantau antrean, jadwal dokter, dan pendaftaran pasien dalam satu
              tampilan yang lebih ringkas.
            </p>
            <div class="d-flex flex-wrap gap-2 mt-4">
              <a
                class="btn btn-light btn-sm px-3 py-2 rounded-3"
                href="../public/online-registration.html"
                ><i class="bi bi-plus-lg me-2"></i>Daftar Pasien</a
              >
              <button class="btn btn-outline-light btn-sm px-3 py-2 rounded-3">
                <i class="bi bi-download me-2"></i>Unduh Laporan
              </button>
            </div>
          </div>
        </section>

        <section class="row g-3 mb-4" aria-label="Ringkasan statistik">
          <div class="col-6 col-xl-3">
            <article class="metric-card">
              <div class="metric-head">
                <span class="metric-icon"><i class="bi bi-people"></i></span
                ><span class="trend"><i class="bi bi-arrow-up"></i> 12,5%</span>
              </div>
              <div class="metric-label">Pasien hari ini</div>
              <div class="metric-value">284</div>
            </article>
          </div>
          <div class="col-6 col-xl-3">
            <article class="metric-card">
              <div class="metric-head">
                <span class="metric-icon"
                  ><i class="bi bi-hourglass-split"></i></span
                ><span class="trend warning">18 menunggu</span>
              </div>
              <div class="metric-label">Antrean aktif</div>
              <div class="metric-value">46</div>
            </article>
          </div>
          <div class="col-6 col-xl-3">
            <article class="metric-card">
              <div class="metric-head">
                <span class="metric-icon"
                  ><i class="bi bi-person-check"></i></span
                ><span class="trend">92% hadir</span>
              </div>
              <div class="metric-label">Dokter bertugas</div>
              <div class="metric-value">18</div>
            </article>
          </div>
          <div class="col-6 col-xl-3">
            <article class="metric-card">
              <div class="metric-head">
                <span class="metric-icon"><i class="bi bi-laptop"></i></span
                ><span class="trend"><i class="bi bi-arrow-up"></i> 8,4%</span>
              </div>
              <div class="metric-label">Daftar online</div>
              <div class="metric-value">72</div>
            </article>
          </div>
        </section>

        <div class="row g-4">
          <div class="col-xl-8" id="kunjungan">
            <section class="section-card h-100">
              <div
                class="d-flex align-items-start justify-content-between gap-3 mb-3"
              >
                <div>
                  <h2 class="section-title">Pendaftaran terbaru</h2>
                  <p class="section-subtitle mb-0">
                    Daftar pasien yang masuk hari ini
                  </p>
                </div>
                <a
                  class="btn btn-sm btn-outline-secondary rounded-3"
                  href="../public/online-registration.html"
                  >Lihat semua</a
                >
              </div>
              <div class="table-responsive">
                <table class="table table-modern align-middle">
                  <thead>
                    <tr>
                      <th>Pasien</th>
                      <th>Poliklinik</th>
                      <th>Jadwal</th>
                      <th>Status</th>
                      <th></th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>
                        <strong>Andi Pratama</strong>
                        <div class="text-muted">RM-001842</div>
                      </td>
                      <td>Poli Jantung</td>
                      <td>08.30 WIB</td>
                      <td>
                        <span class="status confirmed">Terkonfirmasi</span>
                      </td>
                      <td>
                        <button class="icon-button">
                          <i class="bi bi-three-dots"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>Siti Rahma</strong>
                        <div class="text-muted">Pasien baru</div>
                      </td>
                      <td>Poli Anak</td>
                      <td>09.15 WIB</td>
                      <td><span class="status new">Baru</span></td>
                      <td>
                        <button class="icon-button">
                          <i class="bi bi-three-dots"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>Budi Santoso</strong>
                        <div class="text-muted">RM-000927</div>
                      </td>
                      <td>Poli Saraf</td>
                      <td>10.00 WIB</td>
                      <td><span class="status waiting">Menunggu</span></td>
                      <td>
                        <button class="icon-button">
                          <i class="bi bi-three-dots"></i>
                        </button>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <strong>Nabila Putri</strong>
                        <div class="text-muted">RM-002116</div>
                      </td>
                      <td>Poli Gigi</td>
                      <td>10.30 WIB</td>
                      <td>
                        <span class="status confirmed">Terkonfirmasi</span>
                      </td>
                      <td>
                        <button class="icon-button">
                          <i class="bi bi-three-dots"></i>
                        </button>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </section>
          </div>
          <div class="col-xl-4">
            <section class="section-card mb-4">
              <h2 class="section-title">Akses cepat</h2>
              <p class="section-subtitle">Pekerjaan yang sering digunakan</p>
              <div class="d-grid gap-2 mt-3">
                <a
                  class="quick-action"
                  href="../public/online-registration.html"
                  ><span class="metric-icon"
                    ><i class="bi bi-person-plus"></i></span
                  ><span
                    ><strong>Daftarkan pasien</strong
                    ><small>Buat pendaftaran kunjungan baru</small></span
                  ><i class="bi bi-chevron-right ms-auto"></i
                ></a>
                <a class="quick-action" href="#jadwal"
                  ><span class="metric-icon"
                    ><i class="bi bi-calendar-week"></i></span
                  ><span
                    ><strong>Kelola jadwal</strong
                    ><small>Atur jadwal praktik dokter</small></span
                  ><i class="bi bi-chevron-right ms-auto"></i
                ></a>
              </div>
            </section>
            <section class="section-card" id="jadwal">
              <div class="d-flex justify-content-between">
                <div>
                  <h2 class="section-title">Kapasitas poli</h2>
                  <p class="section-subtitle">Keterisian jadwal hari ini</p>
                </div>
                <i class="bi bi-activity text-success"></i>
              </div>
              <div class="doctor-row">
                <div>
                  <div class="doctor-name">Poli Jantung</div>
                  <div class="progress mt-2">
                    <div class="progress-bar" style="width: 82%"></div>
                  </div>
                </div>
                <span class="capacity">41/50</span>
              </div>
              <div class="doctor-row">
                <div>
                  <div class="doctor-name">Poli Anak</div>
                  <div class="progress mt-2">
                    <div class="progress-bar" style="width: 64%"></div>
                  </div>
                </div>
                <span class="capacity">32/50</span>
              </div>
              <div class="doctor-row">
                <div>
                  <div class="doctor-name">Poli Saraf</div>
                  <div class="progress mt-2">
                    <div class="progress-bar" style="width: 45%"></div>
                  </div>
                </div>
                <span class="capacity">18/40</span>
              </div>
            </section>
          </div>
        </div>
@endsection
