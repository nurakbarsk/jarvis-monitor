<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Jarvis Monitor — Developer Admin Console</title>
    <meta
      name="description"
      content="Developer admin dashboard untuk memantau push GitHub, mengelola fitur proyek, dan menindaklanjuti tiket kendala klien yayasan."
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="stylesheet" href="style.css?v=2.1" />
  </head>
  <body>
    <div class="backdrop" id="backdrop"></div>

    <!-- Mobile / Tablet Sticky Topbar (< 1024px) -->
    <header class="mobile-topbar" id="mobile-topbar">
      <button class="hamburger-btn" id="mobile-menu-btn" type="button" aria-label="Buka menu navigasi" aria-expanded="false">
        <span class="hamburger-icon" aria-hidden="true">
          <span></span>
          <span></span>
          <span></span>
        </span>
      </button>
      <div class="mobile-brand">
        <div class="brand-mark brand-mark-sm" aria-hidden="true">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="M12 3v3" />
            <circle cx="12" cy="14" r="7" />
            <path d="M9 14h6" />
          </svg>
        </div>
        <span class="mobile-brand-title">Jarvis Admin</span>
      </div>
      <div class="mobile-topbar-spacer" aria-hidden="true"></div>
    </header>

    <div class="app-shell">
      <aside class="sidebar" id="sidebar">
        <div class="brand">
          <div class="brand-mark" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 3v3" />
              <circle cx="12" cy="14" r="7" />
              <path d="M9 14h6" />
            </svg>
          </div>
          <div>
            <span class="brand-name">Jarvis Admin</span>
            <span class="brand-tag">Developer Console</span>
          </div>
          <button class="sidebar-close-btn" id="sidebar-close-btn" type="button" aria-label="Tutup menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
        <nav class="nav" aria-label="Navigasi admin">
          <button class="nav-item is-active" type="button" data-nav="roadmap-section">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect x="3" y="3" width="7" height="9" rx="2" />
              <rect x="14" y="3" width="7" height="5" rx="2" />
              <rect x="14" y="12" width="7" height="9" rx="2" />
              <rect x="3" y="16" width="7" height="5" rx="2" />
            </svg>
            <span>Ringkasan</span>
          </button>
          <button class="nav-item" type="button" data-nav="features-section">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
            <span>Daftar Fitur</span>
            <span class="badge-count" id="features-badge">0</span>
          </button>
          <button class="nav-item" type="button" data-nav="timetable-section">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
              <path d="m9 16 2 2 4-4" />
            </svg>
            <span>Time Table</span>
            <span class="badge-count" id="timetable-badge">0</span>
          </button>
          <button class="nav-item" type="button" data-nav="changelog-card">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
            </svg>
            <span>Changelog</span>
          </button>
          <button class="nav-item" type="button" data-nav="tickets-section">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M15 5H5a2 2 0 0 0-2 2v4a2 2 0 0 1 0 4v4a2 2 0 0 0 2 2h10" />
              <path d="M19 5h-4v14h4a2 2 0 0 0 2-2v-4a2 2 0 0 1 0-4V7a2 2 0 0 0-2-2z" />
            </svg>
            <span>Tiket Masuk</span>
            <span class="badge-count" id="ticket-badge">0</span>
          </button>
          <a class="nav-item nav-link-portal" href="portal.php" target="_blank" rel="noopener">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6" />
              <polyline points="15 3 21 3 21 9" />
              <line x1="10" y1="14" x2="21" y2="3" />
            </svg>
            <span>Portal Klien ↗</span>
          </a>
        </nav>
        <div class="sidebar-spacer"></div>
        <div class="app-card">
          <div class="app-card-arrow" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="M5 12h14" />
              <path d="m12 5 7 7-7 7" />
            </svg>
          </div>
          <p id="sidebar-telegram">Memeriksa status Telegram…</p>
        </div>
      </aside>

      <div class="workspace">
        <header class="topbar">
          <button class="menu-btn" id="menu-btn" type="button" aria-label="Buka menu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
          </button>
          <h1 class="greeting" id="greeting">Developer Console 👋</h1>
          <div class="topbar-actions">
            <div class="project-selector-wrapper">
              <label class="sr-only" for="project-switcher">Pilih proyek</label>
              <select class="project-switch" id="project-switcher">
                <option value="" disabled selected>Memuat proyek…</option>
              </select>
              <button class="icon-btn-danger" id="btn-delete-project" type="button" aria-label="Hapus proyek" title="Hapus proyek terpilih" hidden>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                  <path d="M3 6h18M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2" />
                </svg>
              </button>
            </div>
            <button class="btn btn-primary btn-add-project" id="btn-open-add-project" type="button">
              <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path d="M12 5v14M5 12h14" />
              </svg>
              <span>Proyek</span>
            </button>
            <a class="btn btn-ghost" href="portal.php" target="_blank" rel="noopener" title="Buka Portal Klien / Yayasan">
              <span>Portal Klien ↗</span>
            </a>
            <div class="search">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <circle cx="11" cy="11" r="7" />
                <path d="m20 20-3-3" />
              </svg>
              <input id="search" type="search" placeholder="Cari commit..." />
            </div>
            <div class="avatar" id="user-avatar" aria-label="Operator Developer">DEV</div>
          </div>
        </header>

        <div class="layout">
          <div class="main-col">
            <section id="roadmap-section" aria-labelledby="roadmap-title">
              <div class="section-head">
                <h2 id="roadmap-title">Ringkasan Developer</h2>
                <div class="project-pill" id="project-meta-pill">Belum ada proyek</div>
              </div>
              <div class="stat-grid">
                <article class="card stat-card">
                  <div class="stat-top">
                    <div class="icon-box icon-peach">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M8 14s1.5 2 4 2 4-2 4-2" />
                        <rect x="8" y="7" width="8" height="13" rx="4" />
                      </svg>
                    </div>
                    <div class="stat-copy">
                      <h3>Tiket Terbuka</h3>
                      <p>Perlu ditindaklanjuti</p>
                    </div>
                  </div>
                  <div class="stat-meta">
                    <div>
                      <span>Antrian</span>
                      <strong id="open-count">0</strong>
                    </div>
                    <div>
                      <span>Status</span>
                      <strong id="open-status-label">Open</strong>
                    </div>
                  </div>
                </article>
                <article class="card stat-card">
                  <div class="stat-top">
                    <div class="icon-box icon-lime">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M6 3v12" />
                        <circle cx="6" cy="18" r="2" />
                        <circle cx="18" cy="12" r="2" />
                      </svg>
                    </div>
                    <div class="stat-copy">
                      <h3>Commit GitHub</h3>
                      <p>Catatan push otomatis</p>
                    </div>
                  </div>
                  <div class="stat-meta">
                    <div>
                      <span>Jumlah</span>
                      <strong id="commit-count">0</strong>
                    </div>
                    <div>
                      <span>Sumber</span>
                      <strong>Webhook</strong>
                    </div>
                  </div>
                </article>
                <article class="card stat-card">
                  <div class="stat-top">
                    <div class="icon-box icon-mint">
                      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                        <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14" />
                        <polyline points="22 4 12 14.01 9 11.01" />
                      </svg>
                    </div>
                    <div class="stat-copy">
                      <h3>Fitur Selesai</h3>
                      <p>Terdokumentasi di docs</p>
                    </div>
                  </div>
                  <div class="stat-meta">
                    <div>
                      <span>Total Fitur</span>
                      <strong id="feature-count">0</strong>
                    </div>
                    <div>
                      <span>Dokumentasi</span>
                      <strong>GDocs</strong>
                    </div>
                  </div>
                </article>
              </div>
            </section>

            <div class="split">
              <section class="card" aria-labelledby="activity-title">
                <div class="card-header">
                  <div>
                    <h2 id="activity-title">Aktivitas Push</h2>
                    <div class="trend" id="activity-trend">Memuat…</div>
                  </div>
                  <span class="chip">7 hari</span>
                </div>
                <div class="chart" role="img" aria-labelledby="activity-title" aria-describedby="chart-table">
                  <div class="chart-y" id="chart-y" aria-hidden="true"></div>
                  <div class="chart-bars" id="chart-bars" aria-hidden="true"></div>
                </div>
                <table class="sr-only" id="chart-table">
                  <caption>Jumlah commit per hari, 7 hari terakhir</caption>
                  <thead><tr><th scope="col">Hari</th><th scope="col">Commit</th></tr></thead>
                  <tbody id="chart-table-body"></tbody>
                </table>
              </section>

              <section class="card" id="changelog-card" aria-labelledby="changelog-title">
                <div class="card-header">
                  <h2 id="changelog-title">Riwayat Push GitHub</h2>
                  <div class="filter-group" role="group" aria-label="Filter tipe commit">
                    <button class="filter-btn is-active" type="button" data-filter="all" aria-pressed="true">Semua</button>
                    <button class="filter-btn" type="button" data-filter="fitur" aria-pressed="false">Fitur</button>
                    <button class="filter-btn" type="button" data-filter="bugfix" aria-pressed="false">Bugfix</button>
                    <button class="filter-btn" type="button" data-filter="update" aria-pressed="false">Update</button>
                  </div>
                </div>
                <div class="feed" id="changelog-list"></div>
              </section>
            </div>

            <!-- Bagian CRUD Fitur & Dokumen -->
            <section class="card" id="features-section" aria-labelledby="features-title">
              <div class="section-head">
                <div>
                  <h2 id="features-title">Daftar Fitur &amp; Dokumen Panduan</h2>
                  <p class="section-sub">Daftar modul yang dapat diakses klien melalui Portal Yayasan.</p>
                </div>
                <button class="btn btn-primary" id="btn-open-add-feature" type="button">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                  <span>+ Tambah Fitur &amp; Dokumen</span>
                </button>
              </div>
              <div class="feature-list" id="feature-list"></div>
            </section>

            <!-- Bagian Time Table / Roadmap Bulanan IT -->
            <section class="card" id="timetable-section" aria-labelledby="timetable-title">
              <div class="section-head">
                <div>
                  <h2 id="timetable-title">Time Table &amp; Roadmap Bulanan IT</h2>
                  <p class="section-sub">Atur target mingguan proyek. Klik tombol status untuk toggle (Planned ➔ In Progress ➔ Done).</p>
                </div>
                <button class="btn btn-primary" id="btn-open-add-timetable" type="button">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path d="M12 5v14M5 12h14" />
                  </svg>
                  <span>+ Tambah Task Time Table</span>
                </button>
              </div>
              <div class="timetable-admin-list" id="timetable-admin-list"></div>
            </section>

            <!-- Bagian Tiket Masuk Dari Klien -->
            <section class="card" id="tickets-section" aria-labelledby="tickets-title">
              <div class="section-head">
                <div>
                  <h2 id="tickets-title">Tiket Masuk Dari Klien</h2>
                  <p class="section-sub">Laporan kendala masuk dari Portal Yayasan. Klik "Tandai Resolved" bila tuntas.</p>
                </div>
                <span class="chip" id="ticket-total-chip">0 Tiket</span>
              </div>
              <div class="ticket-list" id="ticket-list"></div>
            </section>
          </div>

          <aside class="rail">
            <section class="promo">
              <div class="icon-box icon-lime" style="background: var(--color-primary)">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                  <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z" />
                </svg>
              </div>
              <h2 id="promo-title">Jarvis Siaga</h2>
              <p id="promo-copy">Memeriksa status bot Telegram…</p>
              <a class="btn btn-primary" href="portal.php" target="_blank" rel="noopener">Buka Portal Klien ↗</a>
            </section>

            <section class="card calendar">
              <div class="cal-head">
                <h2 id="calendar-label">Oktober 2026</h2>
                <div class="cal-nav">
                  <button class="icon-btn" id="cal-prev" type="button" aria-label="Bulan sebelumnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m15 6-6 6 6 6" /></svg>
                  </button>
                  <button class="icon-btn" id="cal-next" type="button" aria-label="Bulan berikutnya">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="m9 6 6 6-6 6" /></svg>
                  </button>
                </div>
              </div>
              <div class="cal-grid" id="calendar-grid"></div>
            </section>

            <section class="card assignments">
              <div class="assign-head">
                <h2>Antrian Tiket</h2>
                <span class="chip" id="assign-chip">Aktif</span>
              </div>
              <div id="assignment-list"></div>
            </section>
          </aside>
        </div>
      </div>
    </div>

    <!-- Modal Tambah Proyek -->
    <div class="modal-backdrop" id="modal-project" hidden>
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-project-title">
        <div class="modal-header">
          <div>
            <h2 id="modal-project-title">Tambah Proyek Baru</h2>
            <p class="modal-sub">Daftarkan repositori GitHub untuk memantau commit &amp; tiket.</p>
          </div>
          <button class="icon-btn" id="modal-project-close" type="button" aria-label="Tutup modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <form class="form-grid" id="project-form" novalidate>
          <div class="field">
            <label for="new_repo_url">Link Repositori GitHub</label>
            <input id="new_repo_url" name="repo_url" type="url" placeholder="https://github.com/organisasi/nama-repo" required />
            <div class="repo-preview-bar">
              <span class="preview-tag">Auto-detect:</span>
              <code class="preview-repo-name" id="preview-repo-name">—</code>
            </div>
          </div>
          <div class="field">
            <label for="new_client_name">Nama Klien / Yayasan</label>
            <input id="new_client_name" name="client_name" placeholder="Contoh: Yayasan Kampus A (opsional)" />
          </div>
          <div class="modal-foot">
            <p class="feedback" id="project-feedback" role="status" aria-live="polite"></p>
            <div class="modal-actions">
              <button class="btn btn-ghost" id="modal-project-cancel" type="button">Batal</button>
              <button class="btn btn-primary" id="btn-submit-project" type="submit">Simpan Proyek</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tambah Fitur & Dokumen Google Docs -->
    <div class="modal-backdrop" id="modal-feature" hidden>
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-feature-title">
        <div class="modal-header">
          <div>
            <h2 id="modal-feature-title">Tambah Fitur &amp; Dokumen Panduan</h2>
            <p class="modal-sub">Dokumentasi ini akan ditampilkan pada Portal Klien Yayasan.</p>
          </div>
          <button class="icon-btn" id="modal-feature-close" type="button" aria-label="Tutup modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <form class="form-grid" id="feature-form" novalidate>
          <div class="field">
            <label for="feature_name">Nama Fitur</label>
            <input id="feature_name" name="feature_name" placeholder="Contoh: Modul KRS Online &amp; Transkrip" required />
          </div>
          <div class="field">
            <label for="feature_doc_url">Link Google Docs (Panduan)</label>
            <input id="feature_doc_url" name="doc_url" type="url" placeholder="https://docs.google.com/document/d/..." />
          </div>
          <div class="field">
            <label for="feature_status">Status Fitur</label>
            <select id="feature_status" name="status">
              <option value="Done" selected>Done (Selesai)</option>
              <option value="In Progress">In Progress (Sedang Dikerjakan)</option>
            </select>
          </div>
          <div class="field">
            <label for="feature_desc">Deskripsi Fitur</label>
            <textarea id="feature_desc" name="description" placeholder="Jelaskan fungsi fitur ini untuk pengguna yayasan..."></textarea>
          </div>
          <div class="modal-foot">
            <p class="feedback" id="feature-feedback" role="status" aria-live="polite"></p>
            <div class="modal-actions">
              <button class="btn btn-ghost" id="modal-feature-cancel" type="button">Batal</button>
              <button class="btn btn-primary" id="btn-submit-feature" type="submit">Simpan Fitur</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <!-- Modal Tambah Task Time Table -->
    <div class="modal-backdrop" id="modal-timetable" hidden>
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-timetable-title">
        <div class="modal-header">
          <div>
            <h2 id="modal-timetable-title">Tambah Task Time Table</h2>
            <p class="modal-sub">Rencanakan target pengerjaan IT untuk ditampilkan di Portal Klien.</p>
          </div>
          <button class="icon-btn" id="modal-timetable-close" type="button" aria-label="Tutup modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <form class="form-grid" id="timetable-form" novalidate>
          <div class="field">
            <label for="task_name">Nama Task / Pekerjaan</label>
            <input id="task_name" name="task_name" placeholder="Contoh: Integrasi Payment Gateway SPP" required />
          </div>
          <div class="field">
            <label for="target_month">Target Bulan</label>
            <select id="target_month" name="target_month">
              <option value="Oktober 2026" selected>Oktober 2026</option>
              <option value="November 2026">November 2026</option>
              <option value="Desember 2026">Desember 2026</option>
              <option value="Januari 2027">Januari 2027</option>
              <option value="Februari 2027">Februari 2027</option>
              <option value="Maret 2027">Maret 2027</option>
            </select>
          </div>
          <div class="field">
            <label for="timetable_status">Status Awal</label>
            <select id="timetable_status" name="status">
              <option value="Planned" selected>Planned (Rencana)</option>
              <option value="In Progress">In Progress (Sedang Digarap)</option>
              <option value="Done">Done (Selesai)</option>
            </select>
          </div>
          <div class="modal-foot">
            <p class="feedback" id="timetable-feedback" role="status" aria-live="polite"></p>
            <div class="modal-actions">
              <button class="btn btn-ghost" id="modal-timetable-cancel" type="button">Batal</button>
              <button class="btn btn-primary" id="btn-submit-timetable" type="submit">Simpan Task</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script src="app.js"></script>
  </body>
</html>
