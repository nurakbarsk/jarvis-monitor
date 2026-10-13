<!DOCTYPE html>
<html lang="id">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Portal Klien — Jarvis Yayasan Kampus</title>
    <meta
      name="description"
      content="Portal panduan fitur sistem, roadmap time table bulanan, dan layanan pelaporan kendala untuk tim yayasan dan operasional kampus."
    />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap"
      rel="stylesheet"
    />
    <link rel="icon" type="image/png" href="logo.png" />
    <link rel="stylesheet" href="style.css?v=2.2" />
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
        <img src="logo.png" alt="Portal Yayasan" class="brand-logo brand-logo-sm" />
        <span class="mobile-brand-title">Portal Yayasan</span>
      </div>
      <div class="mobile-topbar-spacer" aria-hidden="true"></div>
    </header>

    <div class="app-shell">
      <aside class="sidebar" id="sidebar">
        <div class="brand">
          <img src="logo.png" alt="Portal Yayasan" class="brand-logo" />
          <div>
            <span class="brand-name">Portal Yayasan</span>
            <span class="brand-tag">Client &amp; Campus</span>
          </div>
          <button class="sidebar-close-btn" id="sidebar-close-btn" type="button" aria-label="Tutup menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <line x1="18" y1="6" x2="6" y2="18" />
              <line x1="6" y1="6" x2="18" y2="18" />
            </svg>
          </button>
        </div>
        <div class="sidebar-project-card">
          <label class="sidebar-project-label" for="project-switcher">PROYEK YANG DIPILIH</label>
          <div class="sidebar-project-field">
            <span class="sidebar-project-icon" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>
              </svg>
            </span>
            <select class="sidebar-project-select" id="project-switcher" aria-label="Pilih sistem atau proyek">
              <option value="" disabled selected>Memuat sistem…</option>
            </select>
            <span class="sidebar-project-chevron" aria-hidden="true">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round">
                <path d="m6 9 6 6 6-6"/>
              </svg>
            </span>
          </div>
        </div>
        <nav class="nav" aria-label="Navigasi portal klien">
          <button class="nav-item is-active" type="button" data-nav="client-features">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
            </svg>
            <span>Fitur &amp; Panduan</span>
          </button>
          <button class="nav-item" type="button" data-nav="client-timetable">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <rect x="3" y="4" width="18" height="18" rx="2" ry="2" />
              <line x1="16" y1="2" x2="16" y2="6" />
              <line x1="8" y1="2" x2="8" y2="6" />
              <line x1="3" y1="10" x2="21" y2="10" />
              <path d="m9 16 2 2 4-4" />
            </svg>
            <span>Time Table / Plan</span>
          </button>
          <button class="nav-item" type="button" data-nav="client-changelog">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20" />
              <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z" />
            </svg>
            <span>Update Terbaru</span>
          </button>
          <button class="nav-item" type="button" data-nav="client-report">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M12 20h9" />
              <path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z" />
            </svg>
            <span>Lapor Kendala</span>
          </button>
          <button class="nav-item" type="button" data-nav="client-tickets">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
              <path d="M15 5H5a2 2 0 0 0-2 2v4a2 2 0 0 1 0 4v4a2 2 0 0 0 2 2h10" />
              <path d="M19 5h-4v14h4a2 2 0 0 0 2-2v-4a2 2 0 0 1 0-4V7a2 2 0 0 0-2-2z" />
            </svg>
            <span>Status Tiket</span>
            <span class="badge-count" id="ticket-badge">0</span>
          </button>
        </nav>
        <div class="sidebar-spacer"></div>
      </aside>

      <div class="workspace">
        <header class="topbar">
          <button class="menu-btn" id="menu-btn" type="button" aria-label="Buka menu">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
              <path d="M4 7h16M4 12h16M4 17h16" />
            </svg>
          </button>
          <div class="topbar-heading">
            <div class="topbar-title-wrap">
              <img src="logo.png" alt="Portal Yayasan" class="brand-logo" />
              <h1 class="greeting" id="greeting">Portal Matla</h1>
            </div>
            <p class="greeting-sub" id="greeting-sub">Pantau panduan fitur, time table bulanan, dan laporkan kendala operasional.</p>
          </div>
        </header>

        <div class="layout">
          <div class="main-col">
            <!-- Bagian 1: Daftar Fitur Sistem & Google Docs -->
            <section class="card" id="client-features" aria-labelledby="features-title">
              <div class="section-head">
                <div>
                  <h2 id="features-title">Panduan &amp; Fitur Sistem</h2>
                  <p class="section-sub">Daftar modul yang telah selesai dikembangkan beserta tautan panduan resmi Google Docs.</p>
                </div>
                <span class="chip" id="features-chip">Fitur Siap Pakai</span>
              </div>
              <div class="feature-grid" id="client-feature-list"></div>
            </section>

            <!-- Bagian 2: Time Table / Plan Bulan Ini (Roadmap Bulanan IT) -->
            <section class="card" id="client-timetable" aria-labelledby="timetable-title">
              <div class="section-head">
                <div>
                  <h2 id="timetable-title">Time Table / Plan Bulan Ini</h2>
                  <p class="section-sub">Roadmap pengerjaan teknis tim IT yang dikelompokkan per bulan (Read-Only).</p>
                </div>
                <span class="chip" id="timetable-summary-chip">0 Task Terjadwal</span>
              </div>

              <!-- Kartu Time Table Per Bulan -->
              <div class="timetable-monthly-list" id="client-timetable-grid"></div>
              <div class="pagination-bar" id="timetable-pagination" hidden>
                <button class="pagination-btn" id="timetable-prev" type="button" aria-label="Halaman sebelumnya">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"></polyline>
                  </svg>
                  <span>Prev</span>
                </button>
                <span class="pagination-info" id="timetable-page-info">Halaman 1 dari 1</span>
                <button class="pagination-btn" id="timetable-next" type="button" aria-label="Halaman berikutnya">
                  <span>Next</span>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </button>
              </div>
            </section>

            <!-- Bagian 3: Riwayat Update Terakhir (Changelog GitHub) -->
            <section class="card" id="client-changelog" aria-labelledby="changelog-title">
              <div class="section-head">
                <div>
                  <h2 id="changelog-title">Riwayat Update Terakhir</h2>
                  <p class="section-sub">Catatan perbaikan dan penambahan fitur terkini dari tim developer.</p>
                </div>
                <div class="filter-group" role="group" aria-label="Filter tipe update">
                  <button class="filter-btn is-active" type="button" data-filter="all" aria-pressed="true">Semua</button>
                  <button class="filter-btn" type="button" data-filter="fitur" aria-pressed="false">Fitur</button>
                  <button class="filter-btn" type="button" data-filter="bugfix" aria-pressed="false">Bugfix</button>
                  <button class="filter-btn" type="button" data-filter="update" aria-pressed="false">Update</button>
                </div>
              </div>
              <div class="feed" id="changelog-list"></div>
              <div class="pagination-bar" id="changelog-pagination" hidden>
                <button class="pagination-btn" id="changelog-prev" type="button" aria-label="Halaman sebelumnya">
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="15 18 9 12 15 6"></polyline>
                  </svg>
                  <span>Prev</span>
                </button>
                <span class="pagination-info" id="changelog-page-info">Halaman 1 dari 1</span>
                <button class="pagination-btn" id="changelog-next" type="button" aria-label="Halaman berikutnya">
                  <span>Next</span>
                  <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <polyline points="9 18 15 12 9 6"></polyline>
                  </svg>
                </button>
              </div>
            </section>

            <!-- Bagian 4: Formulir Lapor Bug / Ide Fitur Yayasan -->
            <section class="card" id="client-report" aria-labelledby="form-title">
              <div class="section-head">
                <div>
                  <h2 id="form-title">Lapor Kendala / Berikan Ide Fitur</h2>
                  <p class="section-sub">Laporkan bug atau sampaikan ide pengembangan fitur baru langsung ke tim developer.</p>
                </div>
                <span class="badge badge-progress" id="form-project">Memuat...</span>
              </div>
              <form class="form-grid" id="ticket-form" novalidate enctype="multipart/form-data">
                <div class="field">
                  <label class="field-title">Kategori Laporan</label>
                  <div class="category-toggle" role="radiogroup" aria-label="Kategori Laporan">
                    <label class="category-pill is-active" id="label-cat-bug">
                      <input type="radio" name="category" value="Bug" checked />
                      <span>🔴 Kendala / Bug Sistem</span>
                    </label>
                    <label class="category-pill" id="label-cat-ide">
                      <input type="radio" name="category" value="Ide" />
                      <span>💡 Usulan Ide Fitur Baru</span>
                    </label>
                  </div>
                </div>
                <div class="field">
                  <label for="reporter_name">Nama &amp; Bagian Pelapor</label>
                  <input id="reporter_name" name="reporter_name" autocomplete="name" maxlength="120" placeholder="Contoh: Ibu Rina — Bagian Keuangan / Akademik" required />
                </div>
                <div class="field">
                  <label for="issue_description" id="label-issue-desc">Deskripsi Kendala</label>
                  <textarea id="issue_description" name="issue_description" maxlength="2000" placeholder="Jelaskan kendala: tombol apa yang error, di halaman mana, atau data apa yang tidak sesuai..." required></textarea>
                  <span class="field-hint" id="desc-counter">0 / 2000</span>
                </div>
                <div class="field field-attachment">
                  <label for="ticket-image">Lampiran</label>
                  <div class="attachment-input-row">
                    <button type="button" class="btn-add-attachment" id="btn-trigger-attachment">
                      + Tambah Lampiran
                    </button>
                    <input type="file" id="ticket-image" name="image" accept=".jpg,.jpeg,.png" style="display: none;" />
                    <span class="attachment-hint" id="attachment-hint">(jpeg, jpg, png)</span>
                    <span class="attachment-filename" id="attachment-filename" style="display: none;"></span>
                    <button type="button" class="btn-clear-attachment" id="btn-clear-attachment" title="Batalkan lampiran" style="display: none;">✕</button>
                  </div>
                </div>
                <div class="form-foot">
                  <p class="feedback" id="form-feedback" role="status" aria-live="polite"></p>
                  <button class="btn btn-primary btn-submit-ticket" id="submit-ticket" type="submit">Kirim Laporan Kendala</button>
                </div>
              </form>
            </section>

            <!-- Bagian 5: Status Tiket Laporan Klien -->
            <section class="card" id="client-tickets" aria-labelledby="tickets-title">
              <div class="section-head">
                <div>
                  <h2 id="tickets-title">Status Tiket Laporan Anda</h2>
                  <p class="section-sub">Pantau proses tindak lanjut kendala yang sudah dilaporkan.</p>
                </div>
                <span class="chip" id="ticket-status-chip">Tiket Terkirim</span>
              </div>
              <div class="ticket-list" id="ticket-list"></div>
            </section>
          </div>

          <aside class="rail">
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
                <h2>Tiket Terakhir</h2>
                <span class="chip">Laporan</span>
              </div>
              <div id="assignment-list"></div>
            </section>
          </aside>
        </div>
      </div>
    <!-- Modal Edit Tiket Klien -->
    <div class="modal-backdrop" id="modal-edit-ticket" hidden>
      <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modal-edit-ticket-title">
        <div class="modal-header">
          <div>
            <h2 id="modal-edit-ticket-title">Edit Laporan Tiket</h2>
            <p class="modal-sub">Perbarui informasi pelapor, kategori kendala/ide, deskripsi, atau bukti screenshot.</p>
          </div>
          <button class="icon-btn" id="modal-edit-ticket-close" type="button" aria-label="Tutup modal">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><path d="M18 6 6 18M6 6l12 12"/></svg>
          </button>
        </div>
        <form class="form-grid" id="edit-ticket-form" novalidate enctype="multipart/form-data">
          <input type="hidden" id="edit_ticket_id" name="id" />
          <div class="field">
            <label class="field-title">Kategori Laporan</label>
            <div class="category-toggle" role="radiogroup" aria-label="Kategori Laporan">
              <label class="category-pill is-active" id="label-edit-cat-bug">
                <input type="radio" name="edit_category" value="Bug" checked />
                <span>🔴 Kendala / Bug Sistem</span>
              </label>
              <label class="category-pill" id="label-edit-cat-ide">
                <input type="radio" name="edit_category" value="Ide" />
                <span>💡 Usulan Ide Fitur Baru</span>
              </label>
            </div>
          </div>
          <div class="field">
            <label for="edit_reporter_name">Nama &amp; Bagian Pelapor</label>
            <input id="edit_reporter_name" name="reporter_name" maxlength="120" required />
          </div>
          <div class="field">
            <label for="edit_issue_description" id="label-edit-issue-desc">Deskripsi</label>
            <textarea id="edit_issue_description" name="issue_description" maxlength="2000" rows="4" required></textarea>
          </div>
          <div class="field field-attachment">
            <label for="edit-ticket-image">Lampiran</label>
            <div class="attachment-input-row">
              <button type="button" class="btn-add-attachment" id="btn-edit-trigger-attachment">
                + Tambah Lampiran
              </button>
              <input type="file" id="edit-ticket-image" name="image" accept=".jpg,.jpeg,.png" style="display: none;" />
              <span class="attachment-hint" id="edit-attachment-hint">(jpeg, jpg, png)</span>
              <span class="attachment-filename" id="edit-attachment-filename" style="display: none;"></span>
              <button type="button" class="btn-clear-attachment" id="btn-clear-edit-attachment" title="Batalkan lampiran" style="display: none;">✕</button>
            </div>
            <div id="edit-existing-image-box" class="existing-image-box" hidden>
              <span>Screenshot tersimpan:</span>
              <a href="#" id="edit-existing-image-link" target="_blank" rel="noopener">Lihat</a>
              <button type="button" class="btn-text-danger" id="btn-remove-existing-image">Hapus Lampiran Ini</button>
            </div>
            <input type="hidden" id="edit_remove_image" name="remove_image" value="false" />
          </div>
          <div class="modal-foot">
            <p class="feedback" id="edit-ticket-feedback" role="status" aria-live="polite"></p>
            <div class="modal-actions">
              <button class="btn btn-ghost" id="modal-edit-ticket-cancel" type="button">Batal</button>
              <button class="btn btn-primary" id="btn-submit-edit-ticket" type="submit">Simpan Perubahan</button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <script src="portal.js?v=2.2"></script>
  </body>
</html>
