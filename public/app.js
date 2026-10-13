(() => {
  const STORAGE_KEY = "jarvis:selected_project";
  const ITEMS_PER_PAGE = 5;
  const TIMETABLE_PER_PAGE = 5;

  /* ==========================================================================
   * 1. CUSTOM TOAST NOTIFICATION SYSTEM
   * ========================================================================== */

  function showToast(message, type = "info", duration = 3500) {
    const container = document.getElementById("toast-container");
    if (!container) return;

    const toast = document.createElement("div");
    toast.className = `toast-item toast-${type}`;
    toast.setAttribute("role", "alert");

    const icons = {
      success: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>',
      error: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
      info: '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
    };

    toast.innerHTML = `
      <div class="toast-content">
        <span class="toast-icon">${icons[type] || icons.info}</span>
        <span class="toast-msg">${message}</span>
      </div>
      <button type="button" class="toast-close" aria-label="Tutup notifikasi">&times;</button>
    `;

    const closeBtn = toast.querySelector(".toast-close");
    let timer = null;

    const dismiss = () => {
      if (timer) clearTimeout(timer);
      toast.classList.add("is-hiding");
      setTimeout(() => toast.remove(), 220);
    };

    if (closeBtn) closeBtn.addEventListener("click", dismiss);
    timer = setTimeout(dismiss, duration);
    container.appendChild(toast);
  }

  /* ==========================================================================
   * 2. CUSTOM CONFIRMATION MODAL
   * ========================================================================== */

  function showConfirmModal({ title = "Konfirmasi Tindakan", message, confirmText = "Ya, Lanjutkan", cancelText = "Batal", isDanger = true }) {
    return new Promise((resolve) => {
      const modal = document.getElementById("modal-confirm");
      if (!modal) {
        resolve(window.confirm(message));
        return;
      }

      const titleEl = document.getElementById("confirm-modal-title");
      const descEl = document.getElementById("confirm-modal-desc");
      const okBtn = document.getElementById("confirm-modal-ok");
      const cancelBtn = document.getElementById("confirm-modal-cancel");

      if (titleEl) titleEl.textContent = title;
      if (descEl) descEl.textContent = message;
      if (okBtn) {
        okBtn.textContent = confirmText;
        okBtn.className = isDanger ? "btn btn-danger" : "btn btn-primary";
      }
      if (cancelBtn) cancelBtn.textContent = cancelText;

      modal.hidden = false;

      const cleanup = () => {
        modal.hidden = true;
        okBtn.removeEventListener("click", onOk);
        cancelBtn.removeEventListener("click", onCancel);
        modal.removeEventListener("click", onOverlay);
      };

      const onOk = () => {
        cleanup();
        resolve(true);
      };

      const onCancel = () => {
        cleanup();
        resolve(false);
      };

      const onOverlay = (e) => {
        if (e.target === modal) {
          cleanup();
          resolve(false);
        }
      };

      okBtn.addEventListener("click", onOk);
      cancelBtn.addEventListener("click", onCancel);
      modal.addEventListener("click", onOverlay);
    });
  }

  /* ==========================================================================
   * 3. KEYPAD PIN AUTHENTICATION (GATEKEEPER)
   * ========================================================================== */

  const pinScreen = document.getElementById("pin-screen");
  if (pinScreen) {
    initPinKeypad();
    return; // Berhenti jika dashboard terkunci di layar PIN
  }

  function initPinKeypad() {
    let currentPin = "";
    const maxPinLength = 6;
    const dotsContainer = document.getElementById("pin-dots");
    const dots = dotsContainer ? Array.from(dotsContainer.querySelectorAll(".pin-dot")) : [];
    const errorMsg = document.getElementById("pin-error-msg");
    const submitBtn = document.getElementById("btn-pin-submit");
    const backspaceBtn = document.getElementById("btn-pin-backspace");
    let isSubmitting = false;

    function updateDots() {
      dots.forEach((dot, idx) => {
        dot.classList.toggle("is-filled", idx < currentPin.length);
        dot.classList.remove("is-error");
      });
      if (errorMsg) errorMsg.textContent = "";
      if (dotsContainer) dotsContainer.classList.remove("shake");
    }

    function triggerError(msg) {
      if (errorMsg) errorMsg.textContent = msg;
      if (dotsContainer) {
        dotsContainer.classList.add("shake");
        dots.forEach((dot) => {
          dot.classList.remove("is-filled");
          dot.classList.add("is-error");
        });
        setTimeout(() => {
          currentPin = "";
          updateDots();
        }, 800);
      } else {
        currentPin = "";
        updateDots();
      }
      showToast(msg, "error");
    }

    async function submitPin() {
      if (isSubmitting) return;
      if (currentPin.length !== maxPinLength) {
        triggerError(`Masukkan ${maxPinLength} digit PIN.`);
        return;
      }

      isSubmitting = true;
      if (submitBtn) submitBtn.disabled = true;

      try {
        const res = await fetch("api.php?action=login_pin", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({ pin: currentPin }),
        });
        const data = await res.json();
        if (!res.ok) {
          throw new Error(data.error || "PIN salah, silakan coba lagi.");
        }

        showToast("PIN valid! Mengalihkan ke dashboard...", "success", 2000);
        setTimeout(() => {
          window.location.reload();
        }, 500);
      } catch (err) {
        triggerError(err.message);
      } finally {
        isSubmitting = false;
        if (submitBtn) submitBtn.disabled = false;
      }
    }

    function inputDigit(digit) {
      if (isSubmitting) return;
      if (currentPin.length < maxPinLength) {
        currentPin += digit;
        updateDots();
        if (currentPin.length === maxPinLength) {
          setTimeout(submitPin, 120);
        }
      }
    }

    function backspace() {
      if (isSubmitting) return;
      if (currentPin.length > 0) {
        currentPin = currentPin.slice(0, -1);
        updateDots();
      }
    }

    document.querySelectorAll("#pin-keypad .keypad-btn[data-key]").forEach((btn) => {
      btn.addEventListener("click", () => {
        inputDigit(btn.getAttribute("data-key"));
      });
    });

    if (backspaceBtn) {
      backspaceBtn.addEventListener("click", backspace);
    }

    if (submitBtn) {
      submitBtn.addEventListener("click", submitPin);
    }

    window.addEventListener("keydown", (e) => {
      if (e.key >= "0" && e.key <= "9") {
        inputDigit(e.key);
      } else if (e.key === "Backspace") {
        backspace();
      } else if (e.key === "Enter") {
        submitPin();
      }
    });
  }

  /* ==========================================================================
   * 4. MAIN DASHBOARD LOGIC (AUTHENTICATED)
   * ========================================================================== */

  const state = {
    projects: [],
    currentProject: null,
    changelogs: [],
    currentPage: 1,
    tickets: [],
    features: [],
    timetable: [],
    timetableCurrentPage: 1,
    activity: [],
    search: "",
    filter: "all",
    calendar: new Date(),
    requestId: 0,
  };

  const $ = (id) => document.getElementById(id);
  const els = {
    switcher: $("project-switcher"),
    btnOpenAddProject: $("btn-open-add-project"),
    btnDeleteProject: $("btn-delete-project"),
    greeting: $("greeting"),
    search: $("search"),
    projectMetaPill: $("project-meta-pill"),
    openCount: $("open-count"),
    commitCount: $("commit-count"),
    featureCount: $("feature-count"),
    openStatusLabel: $("open-status-label"),
    chartBars: $("chart-bars"),
    chartY: $("chart-y"),
    chartTable: $("chart-table-body"),
    trend: $("activity-trend"),
    changelog: $("changelog-list"),
    btnSyncGithub: $("btn-sync-github"),
    changelogPagination: $("changelog-pagination"),
    changelogPrev: $("changelog-prev"),
    changelogNext: $("changelog-next"),
    changelogPageInfo: $("changelog-page-info"),
    featureList: $("feature-list"),
    btnOpenAddFeature: $("btn-open-add-feature"),
    timetableAdminList: $("timetable-admin-list"),
    timetablePagination: $("timetable-pagination"),
    timetablePrev: $("timetable-prev"),
    timetableNext: $("timetable-next"),
    timetablePageInfo: $("timetable-page-info"),
    btnOpenAddTimetable: $("btn-open-add-timetable"),
    timetableBadge: $("timetable-badge"),
    tickets: $("ticket-list"),
    ticketTotalChip: $("ticket-total-chip"),
    assignments: $("assignment-list"),
    ticketCount: $("ticket-badge"),
    featuresBadge: $("features-badge"),
    calendarLabel: $("calendar-label"),
    calendarGrid: $("calendar-grid"),
    menuBtn: $("menu-btn"),
    sidebar: $("sidebar"),
    backdrop: $("backdrop"),
    sidebarTelegram: $("sidebar-telegram"),
    promoTitle: $("promo-title"),
    promoCopy: $("promo-copy"),

    // Modal Tambah Proyek
    modalProject: $("modal-project"),
    modalProjectClose: $("modal-project-close"),
    modalProjectCancel: $("modal-project-cancel"),
    projectForm: $("project-form"),
    newRepoUrl: $("new_repo_url"),
    newClientName: $("new_client_name"),
    previewRepoName: $("preview-repo-name"),
    projectFeedback: $("project-feedback"),
    btnSubmitProject: $("btn-submit-project"),

    // Modal Tambah Fitur
    modalFeature: $("modal-feature"),
    modalFeatureClose: $("modal-feature-close"),
    modalFeatureCancel: $("modal-feature-cancel"),
    featureForm: $("feature-form"),
    featureName: $("feature_name"),
    featureDocUrl: $("feature_doc_url"),
    featureStatus: $("feature_status"),
    featureDesc: $("feature_desc"),
    featureFeedback: $("feature-feedback"),
    btnSubmitFeature: $("btn-submit-feature"),

    // Modal Tambah Timetable
    modalTimetable: $("modal-timetable"),
    modalTimetableClose: $("modal-timetable-close"),
    modalTimetableCancel: $("modal-timetable-cancel"),
    timetableForm: $("timetable-form"),
    taskName: $("task_name"),
    targetMonth: $("target_month"),
    timetableStatus: $("timetable_status"),
    timetableFeedback: $("timetable-feedback"),
    btnSubmitTimetable: $("btn-submit-timetable"),
  };

  /* ----------------------------- Utils ----------------------------- */

  function icon(name) {
    const paths = {
      git: '<path d="M6 3v12"/><circle cx="6" cy="6" r="2"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="12" r="2"/><path d="M8 6h4a4 4 0 0 1 4 4v2"/>',
      bug: '<path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01"/><path d="M15 9h.01"/><path d="M16 5c0-1.7-1.3-3-3-3h-2C9.3 2 8 3.3 8 5"/><path d="m19 7-3 2"/><path d="m5 7 3 2"/><path d="M20 13h-4"/><path d="M8 13H4"/><rect x="8" y="7" width="8" height="13" rx="4"/>',
      doc: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
      check: '<polyline points="20 6 9 17 4 12"/>',
      trash: '<polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/>',
      folder: '<path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>',
    };
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[name] || paths.git}</svg>`;
  }

  function parseRepoName(urlOrName) {
    if (!urlOrName || typeof urlOrName !== "string") return "";
    let trimmed = urlOrName.trim();
    trimmed = trimmed.replace(/\/+$/, "");
    if (trimmed.endsWith(".git")) {
      trimmed = trimmed.slice(0, -4);
    }
    const match = trimmed.match(/[/:]+([^/:]+)$/);
    if (match && match[1]) {
      return match[1].trim();
    }
    return trimmed;
  }

  function parseUtc(value) {
    if (!value) return new Date(NaN);
    const text = String(value);
    return new Date(text.includes("T") ? text : `${text.replace(" ", "T")}Z`);
  }

  function formatTime(value) {
    const date = parseUtc(value);
    if (Number.isNaN(date.getTime())) return String(value || "");
    return new Intl.DateTimeFormat("id-ID", {
      day: "numeric",
      month: "short",
      hour: "2-digit",
      minute: "2-digit",
    }).format(date);
  }

  function relativeTime(value) {
    const date = parseUtc(value);
    if (Number.isNaN(date.getTime())) return "—";
    const diff = (date.getTime() - Date.now()) / 1000;
    const rtf = new Intl.RelativeTimeFormat("id-ID", { numeric: "auto" });
    const units = [
      ["day", 86400],
      ["hour", 3600],
      ["minute", 60],
    ];
    for (const [unit, seconds] of units) {
      if (Math.abs(diff) >= seconds) return rtf.format(Math.round(diff / seconds), unit);
    }
    return "baru saja";
  }

  function escapeHtml(value) {
    return String(value ?? "")
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#39;");
  }

  const TYPE_META = {
    fitur: { label: "Fitur", badge: "badge badge-fitur", box: "icon-mint" },
    bugfix: { label: "Bugfix", badge: "badge badge-bugfix", box: "icon-peach" },
    update: { label: "Update", badge: "badge badge-update", box: "icon-lavender" },
  };

  function setFeedback(el, message, kind) {
    el.textContent = message;
    el.className = `feedback${kind ? ` is-${kind}` : ""}`;
  }

  /* ----------------------------- Render ----------------------------- */

  function renderStats() {
    const openTickets = state.tickets.filter((t) => t.status === "Open" || t.status === "In Progress");
    els.openCount.textContent = String(openTickets.length);
    els.commitCount.textContent = String(state.changelogs.length);
    els.featureCount.textContent = String(state.features.length);
    els.openStatusLabel.textContent = openTickets.length === 0 ? "Bebas Isu" : "Perlu Aksi";

    els.ticketCount.textContent = String(openTickets.length);
    els.ticketCount.hidden = openTickets.length === 0;

    els.featuresBadge.textContent = String(state.features.length);
    els.featuresBadge.hidden = state.features.length === 0;

    els.ticketTotalChip.textContent = `${state.tickets.length} Tiket`;
  }

  function renderChart() {
    const days = state.activity.length ? state.activity : [];
    const max = Math.max(1, ...days.map((d) => d.count));
    const top = Math.max(4, Math.ceil((max * 1.25) / 4) * 4);
    const total = days.reduce((sum, d) => sum + d.count, 0);
    const hotIndex = days.reduce((best, d, i, arr) => (d.count > arr[best].count ? i : best), 0);
    const dayFmt = new Intl.DateTimeFormat("id-ID", { weekday: "short", timeZone: "UTC" });
    const fullFmt = new Intl.DateTimeFormat("id-ID", { weekday: "long", day: "numeric", month: "short", timeZone: "UTC" });

    els.chartY.innerHTML = [top, (top * 3) / 4, top / 2, top / 4].map((v) => `<span>${v}</span>`).join("");

    els.chartBars.innerHTML = days
      .map((d, i) => {
        const date = new Date(`${d.day}T00:00:00Z`);
        const pct = (d.count / top) * 100;
        const hot = total > 0 && i === hotIndex;
        return `<div class="bar-col" title="${escapeHtml(fullFmt.format(date))}: ${d.count} commit">
          <div class="bar-track">
            ${hot ? `<span class="bar-tip" style="bottom:calc(${Math.max(pct, 4)}% + 6px)">${d.count} commit</span>` : ""}
            <div class="bar ${hot ? "is-hot" : ""} ${d.count === 0 ? "is-zero" : ""}" style="height:${Math.max(pct, 4)}%"></div>
          </div>
          <span>${escapeHtml(dayFmt.format(date))}</span>
        </div>`;
      })
      .join("");

    els.chartTable.innerHTML = days
      .map((d) => `<tr><td>${escapeHtml(fullFmt.format(new Date(`${d.day}T00:00:00Z`)))}</td><td>${d.count}</td></tr>`)
      .join("");

    els.trend.innerHTML = total
      ? `<b>${total} push</b> dalam 7 hari terakhir`
      : "Belum ada push dalam 7 hari terakhir";
  }

  function filteredLogs() {
    const q = state.search.trim().toLowerCase();
    return state.changelogs.filter((item) => {
      if (state.filter !== "all" && item.commit_type !== state.filter) return false;
      return !q || item.commit_message.toLowerCase().includes(q);
    });
  }

  function renderChangelog() {
    if (!state.currentProject) {
      els.changelog.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("folder")}</div>
          <h3>Belum ada proyek dipilih</h3>
          <p>Tambahkan proyek baru melalui tombol <strong>+ Proyek</strong> di atas.</p>
        </div>`;
      if (els.changelogPagination) els.changelogPagination.hidden = true;
      return;
    }

    const items = filteredLogs();
    if (!items.length) {
      const isFiltered = state.search || state.filter !== "all";
      if (isFiltered) {
        els.changelog.innerHTML = '<div class="empty">Tidak ada commit yang cocok dengan filter ini.</div>';
      } else {
        els.changelog.innerHTML = `
          <div class="empty-state">
            <div class="empty-icon">${icon("git")}</div>
            <h3>Belum ada commit terdeteksi</h3>
            <p>Hubungkan webhook repositori <strong>${escapeHtml(state.currentProject.name)}</strong> ke endpoint Jarvis: <code>POST /api/webhook/github</code> atau klik tombol <strong>Sync</strong> di atas.</p>
          </div>`;
      }
      if (els.changelogPagination) els.changelogPagination.hidden = true;
      return;
    }

    // Logika Pagination Frontend
    const totalPages = Math.max(1, Math.ceil(items.length / ITEMS_PER_PAGE));
    if (state.currentPage > totalPages) state.currentPage = totalPages;
    if (state.currentPage < 1) state.currentPage = 1;

    const startIdx = (state.currentPage - 1) * ITEMS_PER_PAGE;
    const pagedItems = items.slice(startIdx, startIdx + ITEMS_PER_PAGE);

    els.changelog.innerHTML = pagedItems
      .map((item) => {
        const meta = TYPE_META[item.commit_type] || TYPE_META.update;
        return `<article class="feed-item">
          <div class="icon-box ${meta.box}">${icon("git")}</div>
          <div class="feed-copy">
            <h3 title="${escapeHtml(item.commit_message)}">${escapeHtml(item.commit_message)}</h3>
            <p><time datetime="${escapeHtml(item.created_at)}">${escapeHtml(formatTime(item.created_at))}</time></p>
          </div>
          <span class="${meta.badge}">${meta.label}</span>
        </article>`;
      })
      .join("");

    // Update Navigasi Pagination
    if (els.changelogPagination) {
      els.changelogPagination.hidden = false;
      if (els.changelogPrev) {
        els.changelogPrev.disabled = state.currentPage <= 1;
      }
      if (els.changelogNext) {
        els.changelogNext.disabled = state.currentPage >= totalPages;
      }
      if (els.changelogPageInfo) {
        els.changelogPageInfo.textContent = `Halaman ${state.currentPage} dari ${totalPages}`;
      }
    }
  }

  function renderFeatures() {
    if (!state.currentProject) {
      els.featureList.innerHTML = '<div class="empty">Pilih proyek terlebih dahulu.</div>';
      return;
    }

    if (!state.features.length) {
      els.featureList.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("doc")}</div>
          <h3>Belum ada fitur / panduan terdaftar</h3>
          <p>Tambahkan modul yang sudah selesai dikerjakan agar klien yayasan dapat membaca panduan Google Docs di Portal Klien.</p>
        </div>`;
      return;
    }

    els.featureList.innerHTML = state.features
      .map((f) => {
        const isDone = f.status === "Done";
        return `<article class="feature-card">
          <div class="feature-card-header">
            <div class="feature-title-group">
              <div class="icon-box ${isDone ? "icon-mint" : "icon-lavender"}">${icon("doc")}</div>
              <div>
                <h3>${escapeHtml(f.feature_name)}</h3>
                <span class="badge ${isDone ? "badge-completed" : "badge-progress"}">${escapeHtml(f.status || "Done")}</span>
              </div>
            </div>
            <button class="btn-icon-subtle btn-delete-feature" data-id="${f.id}" type="button" aria-label="Hapus fitur" title="Hapus fitur ini">
              ${icon("trash")}
            </button>
          </div>
          ${f.description ? `<p class="feature-card-desc">${escapeHtml(f.description)}</p>` : ""}
          <div class="feature-card-foot">
            ${
              f.doc_url
                ? `<a class="btn-doc-link" href="${escapeHtml(f.doc_url)}" target="_blank" rel="noopener">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>Buka Panduan (Google Docs)</span>
                  </a>`
                : '<span class="text-muted text-xs">Belum ada link panduan</span>'
            }
            <span class="feature-date">${escapeHtml(formatTime(f.created_at))}</span>
          </div>
        </article>`;
      })
      .join("");

    // Event listener untuk tombol hapus fitur
    els.featureList.querySelectorAll(".btn-delete-feature").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        e.stopPropagation();
        const id = btn.getAttribute("data-id");
        const ok = await showConfirmModal({
          title: "Hapus Modul Fitur",
          message: "Apakah Anda yakin ingin menghapus modul fitur ini?",
          confirmText: "Hapus Fitur",
          isDanger: true,
        });
        if (!ok) return;
        try {
          const res = await fetch(`api.php?action=features&id=${id}`, { method: "DELETE" });
          const payload = await res.json();
          if (!res.ok) throw new Error(payload.error || "Gagal menghapus fitur");
          showToast("Modul fitur berhasil dihapus.", "success");
          await loadData({ silent: true });
        } catch (err) {
          showToast(err.message, "error");
        }
      });
    });
  }

  function renderTimetable() {
    if (!els.timetableAdminList) return;
    if (els.timetableBadge) {
      els.timetableBadge.textContent = String(state.timetable.length);
    }

    if (!state.currentProject) {
      els.timetableAdminList.innerHTML = '<div class="empty">Pilih proyek terlebih dahulu.</div>';
      if (els.timetablePagination) els.timetablePagination.hidden = true;
      return;
    }

    if (!state.timetable.length) {
      els.timetableAdminList.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("calendar")}</div>
          <h3>Belum ada agenda time table bulanan</h3>
          <p>Tambahkan target pekerjaan mingguan agar tim dan klien yayasan dapat memantau roadmap bulanan secara transparan.</p>
        </div>`;
      if (els.timetablePagination) els.timetablePagination.hidden = true;
      return;
    }

    // Logika Pagination Timetable Admin (5 task per halaman)
    const totalPages = Math.max(1, Math.ceil(state.timetable.length / TIMETABLE_PER_PAGE));
    if (state.timetableCurrentPage > totalPages) state.timetableCurrentPage = totalPages;
    if (state.timetableCurrentPage < 1) state.timetableCurrentPage = 1;

    const startIdx = (state.timetableCurrentPage - 1) * TIMETABLE_PER_PAGE;
    const pagedTasks = state.timetable.slice(startIdx, startIdx + TIMETABLE_PER_PAGE);

    els.timetableAdminList.innerHTML = pagedTasks
      .map((t) => {
        let badgeClass = "badge-tt-planned";
        let badgeText = "Planned";
        if (t.status === "Done") {
          badgeClass = "badge-tt-done";
          badgeText = "✓ Selesai";
        } else if (t.status === "In Progress") {
          badgeClass = "badge-tt-progress";
          badgeText = "⚡ Sedang Digarap";
        }

        return `
          <article class="timetable-admin-row">
            <div class="timetable-month-chip">${escapeHtml(t.target_month || t.target_week || "Bulan Ini")}</div>
            <div class="timetable-admin-info">
              <h4>${escapeHtml(t.task_name)}</h4>
              <span class="text-muted text-xs">Diperbarui: ${escapeHtml(formatTime(t.updated_at))}</span>
            </div>
            <div class="timetable-admin-actions">
              <button class="btn btn-sm btn-status-toggle ${badgeClass}" data-id="${t.id}" data-status="${t.status}" type="button" title="Klik untuk ganti status (Planned ➔ In Progress ➔ Done)">
                ${badgeText}
              </button>
              <button class="btn-icon-subtle btn-delete-timetable" data-id="${t.id}" type="button" title="Hapus task ini" aria-label="Hapus task">
                ${icon("trash")}
              </button>
            </div>
          </article>
        `;
      })
      .join("");

    // Update Navigasi Pagination Timetable
    if (els.timetablePagination) {
      els.timetablePagination.hidden = false;
      if (els.timetablePrev) {
        els.timetablePrev.disabled = state.timetableCurrentPage <= 1;
      }
      if (els.timetableNext) {
        els.timetableNext.disabled = state.timetableCurrentPage >= totalPages;
      }
      if (els.timetablePageInfo) {
        els.timetablePageInfo.textContent = `Halaman ${state.timetableCurrentPage} dari ${totalPages}`;
      }
    }

    // Listeners toggle status
    els.timetableAdminList.querySelectorAll(".btn-status-toggle").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        e.stopPropagation();
        const id = btn.getAttribute("data-id");
        const curr = btn.getAttribute("data-status");
        const next = curr === "Planned" ? "In Progress" : curr === "In Progress" ? "Done" : "Planned";
        btn.disabled = true;
        btn.textContent = "…";
        try {
          const res = await fetch(`api.php?action=timetables&id=${id}&sub=status`, {
            method: "PATCH",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ status: next }),
          });
          const payload = await res.json();
          if (!res.ok) throw new Error(payload.error || "Gagal mengubah status task");
          showToast(`Status task diubah menjadi "${next}".`, "success");
          await loadData({ silent: true });
        } catch (err) {
          showToast(err.message, "error");
          btn.disabled = false;
        }
      });
    });

    // Listeners delete task
    els.timetableAdminList.querySelectorAll(".btn-delete-timetable").forEach((btn) => {
      btn.addEventListener("click", async (e) => {
        e.stopPropagation();
        const id = btn.getAttribute("data-id");
        const ok = await showConfirmModal({
          title: "Hapus Task Time Table",
          message: "Hapus agenda task time table bulanan ini?",
          confirmText: "Hapus Task",
          isDanger: true,
        });
        if (!ok) return;
        try {
          const res = await fetch(`api.php?action=timetables&id=${id}`, { method: "DELETE" });
          const payload = await res.json();
          if (!res.ok) throw new Error(payload.error || "Gagal menghapus task");
          showToast("Task time table berhasil dihapus.", "success");
          await loadData({ silent: true });
        } catch (err) {
          showToast(err.message, "error");
        }
      });
    });
  }

  function renderTickets() {
    if (!state.currentProject) {
      els.tickets.innerHTML = '<div class="empty">Belum ada proyek dipilih.</div>';
      els.assignments.innerHTML = '<div class="empty">Antrian kosong.</div>';
      return;
    }

    if (!state.tickets.length) {
      els.tickets.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("bug")}</div>
          <h3>Semua tiket tuntas</h3>
          <p>Belum ada tiket kendala masuk dari klien yayasan.</p>
        </div>`;
      els.assignments.innerHTML = '<div class="empty">Antrian kosong.</div>';
      return;
    }

    els.tickets.innerHTML = state.tickets
      .map((ticket) => {
        const isResolved = ticket.status === "Resolved";
        const isIdea = ticket.category === "Ide";
        const badgeClass = isResolved
          ? "badge-completed"
          : ticket.status === "In Progress"
          ? "badge-progress"
          : "badge-open";

        const catBadge = isIdea
          ? `<span class="badge badge-category-idea">💡 Ide</span>`
          : `<span class="badge badge-category-bug">🔴 Bug</span>`;

        const imageHtml = ticket.image_url
          ? `<div class="ticket-attachment">
               <a href="${escapeHtml(ticket.image_url || "")}" target="_blank" rel="noopener" class="ticket-thumb-link" title="Klik untuk membuka screenshot ukuran penuh">
                 <img src="${escapeHtml(ticket.image_url)}" alt="Screenshot #${ticket.id}" class="ticket-thumb" />
                 <span class="ticket-thumb-label">🔍 Lihat Screenshot</span>
               </a>
             </div>`
          : "";

        return `<article class="ticket-row ${isResolved ? "is-resolved" : ""}">
          <div class="icon-box ${isResolved ? "icon-mint" : isIdea ? "icon-sand" : "icon-peach"}">${icon(isResolved ? "check" : isIdea ? "doc" : "bug")}</div>
          <div class="ticket-copy">
            <div class="ticket-meta-line">
              <h3>#${ticket.id} · ${escapeHtml(ticket.reporter_name)}</h3>
              <div class="ticket-badges">
                ${catBadge}
                <span class="badge ${badgeClass}">${escapeHtml(ticket.status)}</span>
              </div>
            </div>
            <p>${escapeHtml(ticket.issue_description)}</p>
            ${imageHtml}
          </div>
          <div class="remaining">
            <span>Dilaporkan</span>
            <strong>${escapeHtml(relativeTime(ticket.created_at))}</strong>
          </div>
          <div class="ticket-actions">
            ${
              isResolved
                ? '<span class="status-done-tag">✓ Tuntas</span>'
                : `<button class="btn btn-sm btn-resolve" data-ticket-id="${ticket.id}" type="button">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                    <span>Tandai Resolved</span>
                  </button>`
            }
            <button class="btn btn-sm btn-delete-ticket" data-ticket-id="${ticket.id}" type="button" title="Hapus tiket">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
              <span>Hapus</span>
            </button>
          </div>
        </article>`;
      })
      .join("");

    // Listeners for resolve ticket
    els.tickets.querySelectorAll(".btn-resolve").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.getAttribute("data-ticket-id");
        btn.disabled = true;
        btn.textContent = "Menyimpan…";
        try {
          const res = await fetch(`api.php?action=tickets&id=${id}&sub=status`, {
            method: "PATCH",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ status: "Resolved" }),
          });
          const payload = await res.json();
          if (!res.ok) throw new Error(payload.error || "Gagal memperbarui tiket");
          showToast("Status tiket diperbarui menjadi Resolved.", "success");
          await loadData({ silent: true });
        } catch (err) {
          showToast(err.message, "error");
          btn.disabled = false;
        }
      });
    });

    // Listeners for delete ticket
    els.tickets.querySelectorAll(".btn-delete-ticket").forEach((btn) => {
      btn.addEventListener("click", async () => {
        const id = btn.getAttribute("data-ticket-id");
        const ok = await showConfirmModal({
          title: "Hapus Tiket Laporan",
          message: `Hapus tiket #${id}? Tindakan ini permanen dan tidak dapat dibatalkan.`,
          confirmText: "Hapus Tiket",
          isDanger: true,
        });
        if (!ok) return;

        btn.disabled = true;
        btn.textContent = "Menghapus…";
        try {
          const res = await fetch(`api.php?action=tickets&id=${id}`, { method: "DELETE" });
          const payload = await res.json();
          if (!res.ok) throw new Error(payload.error || "Gagal menghapus tiket");
          showToast("Tiket berhasil dihapus.", "success");
          await loadData({ silent: true });
        } catch (err) {
          showToast(err.message, "error");
          btn.disabled = false;
        }
      });
    });

    const openOnly = state.tickets.filter((t) => t.status !== "Resolved");
    els.assignments.innerHTML = openOnly.length
      ? openOnly
          .slice(0, 3)
          .map(
            (ticket) => `<article class="assign-item">
              <div class="icon-box icon-sm icon-peach">${icon("bug")}</div>
              <div>
                <h3>#${ticket.id} ${escapeHtml(ticket.reporter_name)}</h3>
                <p>${escapeHtml(formatTime(ticket.created_at))}</p>
                <span class="badge badge-open">${escapeHtml(ticket.status)}</span>
              </div>
            </article>`
          )
          .join("")
      : '<div class="empty">Tidak ada tiket aktif.</div>';
  }

  function renderCalendar() {
    const year = state.calendar.getFullYear();
    const month = state.calendar.getMonth();
    els.calendarLabel.textContent = new Intl.DateTimeFormat("id-ID", { month: "long", year: "numeric" }).format(
      state.calendar
    );

    const startDay = new Date(year, month, 1).getDay();
    const daysInMonth = new Date(year, month + 1, 0).getDate();
    const prevDays = new Date(year, month, 0).getDate();
    const today = new Date();
    const activeDays = new Set(
      [...state.changelogs, ...state.tickets]
        .map((item) => parseUtc(item.created_at))
        .filter((d) => d.getFullYear() === year && d.getMonth() === month)
        .map((d) => d.getDate())
    );

    const cells = ["M", "S", "S", "R", "K", "J", "S"].map((d) => `<span class="dow" aria-hidden="true">${d}</span>`);
    for (let i = 0; i < 42; i += 1) {
      const dayNum = i - startDay + 1;
      const muted = dayNum < 1 || dayNum > daysInMonth;
      const text = dayNum < 1 ? prevDays + dayNum : dayNum > daysInMonth ? dayNum - daysInMonth : dayNum;
      const isToday =
        !muted && dayNum === today.getDate() && month === today.getMonth() && year === today.getFullYear();
      const hasActivity = !muted && activeDays.has(dayNum);
      const classes = [muted && "muted", isToday && "is-today", hasActivity && "has-activity"].filter(Boolean).join(" ");
      cells.push(
        `<span class="cal-day ${classes}" aria-label="Tanggal ${text}${hasActivity ? ", ada aktivitas" : ""}">${text}</span>`
      );
    }
    els.calendarGrid.innerHTML = cells.join("");
  }

  function renderAll() {
    renderStats();
    renderChart();
    renderChangelog();
    renderFeatures();
    renderTimetable();
    renderTickets();
    renderCalendar();
  }

  /* ----------------------------- Data Operations ----------------------------- */

  async function loadProjects(selectProjectName) {
    try {
      const res = await fetch("api.php?action=projects");
      const payload = await res.json();
      state.projects = payload.projects || [];

      if (state.projects.length === 0) {
        els.switcher.innerHTML = '<option value="" disabled selected>Belum ada proyek</option>';
        els.btnDeleteProject.hidden = true;
        els.btnOpenAddFeature.disabled = true;
        if (els.btnOpenAddTimetable) els.btnOpenAddTimetable.disabled = true;
        state.currentProject = null;
        state.changelogs = [];
        state.tickets = [];
        state.features = [];
        state.timetable = [];
        state.activity = [];
        els.greeting.textContent = "Developer Console 👋";
        els.projectMetaPill.textContent = "Belum ada proyek";
        renderAll();
        return;
      }

      els.btnOpenAddFeature.disabled = false;
      if (els.btnOpenAddTimetable) els.btnOpenAddTimetable.disabled = false;
      els.switcher.innerHTML = state.projects
        .map((p) => {
          const label = p.client_name ? `${p.client_name} (${p.name})` : p.name;
          return `<option value="${escapeHtml(p.name)}">${escapeHtml(label)}</option>`;
        })
        .join("");

      const targetName =
        selectProjectName ||
        localStorage.getItem(STORAGE_KEY) ||
        state.projects[0].name;

      const chosen = state.projects.find((p) => p.name === targetName) || state.projects[0];
      setProject(chosen);
    } catch (error) {
      console.error("Load projects error:", error);
      els.switcher.innerHTML = '<option value="" disabled selected>Gagal memuat proyek</option>';
    }
  }

  async function loadData({ silent = false } = {}) {
    if (!state.currentProject) return;

    const requestId = ++state.requestId;
    if (!silent) {
      els.changelog.innerHTML = '<div class="skeleton"></div><div class="skeleton"></div>';
      els.featureList.innerHTML = '<div class="skeleton"></div>';
      if (els.timetableAdminList) els.timetableAdminList.innerHTML = '<div class="skeleton"></div>';
      els.tickets.innerHTML = '<div class="skeleton"></div>';
    }
    try {
      const res = await fetch(`api.php?action=project_data&project=${encodeURIComponent(state.currentProject.name)}`);
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || "Gagal memuat data");
      if (requestId !== state.requestId) return;

      state.changelogs = payload.changelogs || [];
      state.tickets = payload.tickets || [];
      state.features = payload.features || [];
      state.timetable = payload.timetable || [];
      state.activity = payload.activity || [];
      renderAll();
    } catch (error) {
      if (requestId !== state.requestId || silent) return;
      const message = `<div class="empty">${escapeHtml(error.message)}.</div>`;
      els.changelog.innerHTML = message;
      els.featureList.innerHTML = message;
      if (els.timetableAdminList) els.timetableAdminList.innerHTML = message;
      els.tickets.innerHTML = message;
    }
  }

  async function loadHealth() {
    try {
      const res = await fetch("api.php?action=health");
      const { telegram } = await res.json();
      els.sidebarTelegram.textContent = telegram ? "Telegram Jarvis terhubung" : "Telegram belum terhubung";
      els.promoTitle.textContent = telegram ? "Jarvis Siaga" : "Mode Lokal";
      els.promoCopy.textContent = telegram
        ? "Push commit & alert tiket darurat otomatis dikirim ke bot Telegram."
        : "Isi token Telegram di .env agar notifikasi terkirim.";
    } catch {
      els.sidebarTelegram.textContent = "Status Telegram tidak diketahui";
    }
  }

  function setProject(project) {
    if (!project) return;
    state.currentProject = project;
    state.currentPage = 1;
    state.timetableCurrentPage = 1;
    localStorage.setItem(STORAGE_KEY, project.name);
    els.switcher.value = project.name;
    els.btnDeleteProject.hidden = false;

    const displayName = project.client_name || project.name;
    els.greeting.textContent = `${displayName} · Dev Admin 👋`;
    els.projectMetaPill.textContent = `${project.name} (GitHub)`;
    document.title = `${displayName} — Developer Console`;
    loadData();
  }

  /* ----------------------------- Modals Logic ----------------------------- */

  // Modal Tambah Proyek
  function openProjectModal() {
    els.modalProject.hidden = false;
    els.projectForm.reset();
    els.previewRepoName.textContent = "—";
    setFeedback(els.projectFeedback, "");
    setTimeout(() => els.newRepoUrl.focus(), 50);
  }

  function closeProjectModal() {
    els.modalProject.hidden = true;
  }

  els.btnOpenAddProject.addEventListener("click", openProjectModal);
  els.modalProjectClose.addEventListener("click", closeProjectModal);
  els.modalProjectCancel.addEventListener("click", closeProjectModal);
  els.modalProject.addEventListener("click", (e) => {
    if (e.target === els.modalProject) closeProjectModal();
  });

  els.newRepoUrl.addEventListener("input", () => {
    const raw = els.newRepoUrl.value;
    const detected = parseRepoName(raw);
    els.previewRepoName.textContent = detected || "—";
    if (detected && !els.newClientName.value) {
      els.newClientName.placeholder = `Contoh: Yayasan ${detected}`;
    }
  });

  els.projectForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    const repoUrl = els.newRepoUrl.value.trim();
    const clientName = els.newClientName.value.trim();

    if (!repoUrl) {
      setFeedback(els.projectFeedback, "Link repositori GitHub wajib diisi.", "err");
      return;
    }

    els.btnSubmitProject.disabled = true;
    els.btnSubmitProject.textContent = "Menyimpan…";
    setFeedback(els.projectFeedback, "");

    try {
      const res = await fetch("api.php?action=projects", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({ repo_url: repoUrl, client_name: clientName }),
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || "Gagal menambahkan proyek");

      closeProjectModal();
      await loadProjects(payload.project.name);
    } catch (error) {
      setFeedback(els.projectFeedback, error.message, "err");
    } finally {
      els.btnSubmitProject.disabled = false;
      els.btnSubmitProject.textContent = "Simpan Proyek";
    }
  });

  // Delete project
  els.btnDeleteProject.addEventListener("click", async () => {
    if (!state.currentProject) return;
    const ok = await showConfirmModal({
      title: "Hapus Proyek",
      message: `Hapus proyek "${state.currentProject.name}"? Semua modul fitur, tiket, dan riwayat commit proyek ini akan terhapus permanen.`,
      confirmText: "Ya, Hapus Proyek",
      isDanger: true,
    });
    if (!ok) return;

    try {
      const res = await fetch(`api.php?action=projects&id=${state.currentProject.id}`, { method: "DELETE" });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || "Gagal menghapus proyek");

      showToast(`Proyek "${state.currentProject.name}" berhasil dihapus.`, "success");
      localStorage.removeItem(STORAGE_KEY);
      await loadProjects();
    } catch (error) {
      showToast(error.message, "error");
    }
  });

  // Modal Tambah Fitur
  function openFeatureModal() {
    if (!state.currentProject) {
      showToast("Pilih atau buat proyek terlebih dahulu.", "info");
      return;
    }
    els.modalFeature.hidden = false;
    els.featureForm.reset();
    setFeedback(els.featureFeedback, "");
    setTimeout(() => els.featureName.focus(), 50);
  }

  function closeFeatureModal() {
    els.modalFeature.hidden = true;
  }

  els.btnOpenAddFeature.addEventListener("click", openFeatureModal);
  els.modalFeatureClose.addEventListener("click", closeFeatureModal);
  els.modalFeatureCancel.addEventListener("click", closeFeatureModal);
  els.modalFeature.addEventListener("click", (e) => {
    if (e.target === els.modalFeature) closeFeatureModal();
  });

  els.featureForm.addEventListener("submit", async (e) => {
    e.preventDefault();
    if (!state.currentProject) return;

    const name = els.featureName.value.trim();
    const docUrl = els.featureDocUrl.value.trim();
    const status = els.featureStatus.value;
    const desc = els.featureDesc.value.trim();

    if (!name) {
      setFeedback(els.featureFeedback, "Nama fitur wajib diisi.", "err");
      return;
    }

    els.btnSubmitFeature.disabled = true;
    els.btnSubmitFeature.textContent = "Menyimpan…";
    setFeedback(els.featureFeedback, "");

    try {
      const res = await fetch("api.php?action=features", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify({
          project_name: state.currentProject.name,
          feature_name: name,
          doc_url: docUrl,
          status,
          description: desc,
        }),
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || "Gagal menyimpan fitur");

      closeFeatureModal();
      await loadData({ silent: true });
    } catch (error) {
      setFeedback(els.featureFeedback, error.message, "err");
    } finally {
      els.btnSubmitFeature.disabled = false;
      els.btnSubmitFeature.textContent = "Simpan Fitur";
    }
  });

  // Modal Tambah Timetable
  function openTimetableModal() {
    if (!state.currentProject) {
      showToast("Pilih atau buat proyek terlebih dahulu.", "info");
      return;
    }
    if (els.modalTimetable) {
      els.modalTimetable.hidden = false;
      els.timetableForm.reset();
      setFeedback(els.timetableFeedback, "");
      setTimeout(() => els.taskName && els.taskName.focus(), 50);
    }
  }

  function closeTimetableModal() {
    if (els.modalTimetable) els.modalTimetable.hidden = true;
  }

  if (els.btnOpenAddTimetable) els.btnOpenAddTimetable.addEventListener("click", openTimetableModal);
  if (els.modalTimetableClose) els.modalTimetableClose.addEventListener("click", closeTimetableModal);
  if (els.modalTimetableCancel) els.modalTimetableCancel.addEventListener("click", closeTimetableModal);
  if (els.modalTimetable) {
    els.modalTimetable.addEventListener("click", (e) => {
      if (e.target === els.modalTimetable) closeTimetableModal();
    });
  }

  if (els.timetableForm) {
    els.timetableForm.addEventListener("submit", async (e) => {
      e.preventDefault();
      if (!state.currentProject) return;

      const name = els.taskName.value.trim();
      const month = els.targetMonth ? els.targetMonth.value : "Oktober 2026";
      const status = els.timetableStatus.value;

      if (!name) {
        setFeedback(els.timetableFeedback, "Nama task wajib diisi.", "err");
        return;
      }

      els.btnSubmitTimetable.disabled = true;
      els.btnSubmitTimetable.textContent = "Menyimpan…";
      setFeedback(els.timetableFeedback, "");

      try {
        const res = await fetch("api.php?action=timetables", {
          method: "POST",
          headers: { "Content-Type": "application/json" },
          body: JSON.stringify({
            project_name: state.currentProject.name,
            task_name: name,
            target_month: month,
            status,
          }),
        });
        const payload = await res.json();
        if (!res.ok) throw new Error(payload.error || "Gagal menyimpan task");

        closeTimetableModal();
        await loadData({ silent: true });
      } catch (error) {
        setFeedback(els.timetableFeedback, error.message, "err");
      } finally {
        els.btnSubmitTimetable.disabled = false;
        els.btnSubmitTimetable.textContent = "Simpan Task";
      }
    });
  }

  /* ----------------------------- Events ----------------------------- */

  els.switcher.addEventListener("change", (event) => {
    const chosen = state.projects.find((p) => p.name === event.target.value);
    if (chosen) setProject(chosen);
  });

  els.search.addEventListener("input", (event) => {
    state.search = event.target.value;
    state.currentPage = 1;
    renderChangelog();
  });

  document.querySelectorAll("[data-filter]").forEach((btn) => {
    btn.addEventListener("click", () => {
      state.filter = btn.dataset.filter;
      state.currentPage = 1;
      document.querySelectorAll("[data-filter]").forEach((b) => {
        const active = b === btn;
        b.classList.toggle("is-active", active);
        b.setAttribute("aria-pressed", String(active));
      });
      renderChangelog();
    });
  });

  if (els.changelogPrev) {
    els.changelogPrev.addEventListener("click", () => {
      if (state.currentPage > 1) {
        state.currentPage--;
        renderChangelog();
      }
    });
  }

  if (els.changelogNext) {
    els.changelogNext.addEventListener("click", () => {
      const items = filteredLogs();
      const totalPages = Math.ceil(items.length / ITEMS_PER_PAGE);
      if (state.currentPage < totalPages) {
        state.currentPage++;
        renderChangelog();
      }
    });
  }

  if (els.timetablePrev) {
    els.timetablePrev.addEventListener("click", () => {
      if (state.timetableCurrentPage > 1) {
        state.timetableCurrentPage--;
        renderTimetable();
      }
    });
  }

  if (els.timetableNext) {
    els.timetableNext.addEventListener("click", () => {
      const totalPages = Math.ceil(state.timetable.length / TIMETABLE_PER_PAGE);
      if (state.timetableCurrentPage < totalPages) {
        state.timetableCurrentPage++;
        renderTimetable();
      }
    });
  }

  if (els.btnSyncGithub) {
    els.btnSyncGithub.addEventListener("click", async () => {
      if (!state.currentProject) {
        showToast("Pilih proyek terlebih dahulu.", "info");
        return;
      }

      els.btnSyncGithub.disabled = true;
      els.btnSyncGithub.classList.add("is-syncing");
      showToast("Menyinkronkan commit dari GitHub...", "info", 2000);

      try {
        const res = await fetch(`api.php?action=sync_github&project=${encodeURIComponent(state.currentProject.name)}`);
        const data = await res.json();
        if (!res.ok) {
          throw new Error(data.error || "Gagal menyinkronkan commit GitHub.");
        }
        showToast(data.message || "Sinkronisasi commit GitHub berhasil!", "success");
        state.currentPage = 1;
        await loadData({ silent: true });
      } catch (err) {
        showToast("Gagal sinkronisasi: " + err.message, "error");
      } finally {
        els.btnSyncGithub.classList.remove("is-syncing");
        els.btnSyncGithub.disabled = false;
      }
    });
  }

  $("cal-prev").addEventListener("click", () => {
    state.calendar = new Date(state.calendar.getFullYear(), state.calendar.getMonth() - 1, 1);
    renderCalendar();
  });
  $("cal-next").addEventListener("click", () => {
    state.calendar = new Date(state.calendar.getFullYear(), state.calendar.getMonth() + 1, 1);
    renderCalendar();
  });

  const mobileMenuBtn = $("mobile-menu-btn");
  const sidebarCloseBtn = $("sidebar-close-btn");

  function openSidebar() {
    els.sidebar.classList.add("is-open", "sidebar-open");
    els.backdrop.classList.add("is-open", "sidebar-open");
    document.body.classList.add("drawer-open");
    if (mobileMenuBtn) mobileMenuBtn.setAttribute("aria-expanded", "true");
  }

  function closeSidebar() {
    els.sidebar.classList.remove("is-open", "sidebar-open");
    els.backdrop.classList.remove("is-open", "sidebar-open");
    document.body.classList.remove("drawer-open");
    if (mobileMenuBtn) mobileMenuBtn.setAttribute("aria-expanded", "false");
  }

  if (mobileMenuBtn) mobileMenuBtn.addEventListener("click", openSidebar);
  if (els.menuBtn) els.menuBtn.addEventListener("click", openSidebar);
  if (sidebarCloseBtn) sidebarCloseBtn.addEventListener("click", closeSidebar);
  if (els.backdrop) els.backdrop.addEventListener("click", closeSidebar);

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") {
      closeSidebar();
      closeProjectModal();
      closeFeatureModal();
      closeTimetableModal();
    }
  });

  document.querySelectorAll("[data-nav]").forEach((btn) => {
    btn.addEventListener("click", () => {
      document.querySelectorAll("[data-nav]").forEach((item) => item.classList.remove("is-active"));
      btn.classList.add("is-active");
      const target = $(btn.getAttribute("data-nav"));
      if (target) target.scrollIntoView({ behavior: "smooth", block: "start" });
      closeSidebar();
    });
  });

  document.querySelectorAll(".sidebar .nav-item, .sidebar a").forEach((item) => {
    item.addEventListener("click", closeSidebar);
  });

  // Handler Logout PIN
  const btnLogout = $("btn-logout");
  if (btnLogout) {
    btnLogout.addEventListener("click", async () => {
      const ok = await showConfirmModal({
        title: "Keluar dari Console",
        message: "Kunci sesi developer dan kembali ke layar autentikasi PIN?",
        confirmText: "Ya, Logout",
        cancelText: "Batal",
        isDanger: false,
      });
      if (!ok) return;

      try {
        await fetch("api.php?action=logout", { method: "POST" });
        showToast("Sesi berhasil diakhiri.", "info", 1500);
        setTimeout(() => window.location.reload(), 400);
      } catch {
        window.location.reload();
      }
    });
  }

  renderCalendar();
  loadHealth();
  loadProjects();
})();
