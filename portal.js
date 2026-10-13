(() => {
  const STORAGE_KEY = "jarvis:selected_project";
  const DESC_MAX = 2000;
  const ITEMS_PER_PAGE = 5;

  const state = {
    projects: [],
    currentProject: null,
    changelogs: [],
    currentPage: 1,
    tickets: [],
    features: [],
    timetable: [],
    filter: "all",
    calendar: new Date(),
    requestId: 0,
  };

  const $ = (id) => document.getElementById(id);
  const els = {
    switcher: $("project-switcher"),
    greeting: $("greeting"),
    greetingSub: $("greeting-sub"),
    featureList: $("client-feature-list"),
    timetableGrid: $("client-timetable-grid"),
    timetableSummaryChip: $("timetable-summary-chip"),
    timetablePercentLabel: $("timetable-percent-label"),
    timetableProgressFill: $("timetable-progress-fill"),
    timetableProgressMeta: $("timetable-progress-meta"),
    changelog: $("changelog-list"),
    changelogPagination: $("changelog-pagination"),
    changelogPrev: $("changelog-prev"),
    changelogNext: $("changelog-next"),
    changelogPageInfo: $("changelog-page-info"),
    tickets: $("ticket-list"),
    assignments: $("assignment-list"),
    ticketCount: $("ticket-badge"),
    ticketStatusChip: $("ticket-status-chip"),
    featuresChip: $("features-chip"),
    form: $("ticket-form"),
    formProject: $("form-project"),
    reporter: $("reporter_name"),
    description: $("issue_description"),
    counter: $("desc-counter"),
    feedback: $("form-feedback"),
    submit: $("submit-ticket"),
    categoryBug: $("label-cat-bug"),
    categoryIde: $("label-cat-ide"),
    issueDescLabel: $("label-issue-desc"),
    ticketImage: $("ticket_image"),
    fileCta: $("file-cta"),
    filePreviewCard: $("file-preview-card"),
    filePreviewImg: $("file-preview-img"),
    filePreviewName: $("file-preview-name"),
    filePreviewSize: $("file-preview-size"),
    btnRemoveFile: $("btn-remove-file"),
    calendarLabel: $("calendar-label"),
    calendarGrid: $("calendar-grid"),
    menuBtn: $("menu-btn"),
    sidebar: $("sidebar"),
    backdrop: $("backdrop"),
    jumpReport: $("jump-report"),
  };

  /* ----------------------------- Utils ----------------------------- */

  function icon(name) {
    const paths = {
      git: '<path d="M6 3v12"/><circle cx="6" cy="6" r="2"/><circle cx="6" cy="18" r="2"/><circle cx="18" cy="12" r="2"/><path d="M8 6h4a4 4 0 0 1 4 4v2"/>',
      bug: '<path d="M8 14s1.5 2 4 2 4-2 4-2"/><path d="M9 9h.01"/><path d="M15 9h.01"/><path d="M16 5c0-1.7-1.3-3-3-3h-2C9.3 2 8 3.3 8 5"/><path d="m19 7-3 2"/><path d="m5 7 3 2"/><path d="M20 13h-4"/><path d="M8 13H4"/><rect x="8" y="7" width="8" height="13" rx="4"/>',
      doc: '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10 9 9 9 8 9"/>',
      check: '<polyline points="20 6 9 17 4 12"/>',
      folder: '<path d="M4 20h16a2 2 0 0 0 2-2V8a2 2 0 0 0-2-2h-7.93a2 2 0 0 1-1.66-.9l-.82-1.2A2 2 0 0 0 7.93 3H4a2 2 0 0 0-2 2v13c0 1.1.9 2 2 2Z"/>',
      calendar: '<rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>',
    };
    return `<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">${paths[name] || paths.git}</svg>`;
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

  function renderFeatures() {
    if (!state.currentProject) {
      els.featureList.innerHTML = '<div class="empty">Pilih sistem yayasan terlebih dahulu.</div>';
      return;
    }

    if (!state.features.length) {
      els.featureList.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("doc")}</div>
          <h3>Belum ada modul fitur terdaftar</h3>
          <p>Tim pengembang belum menambahkan daftar modul atau tautan panduan Google Docs untuk sistem ini.</p>
        </div>`;
      els.featuresChip.textContent = "0 Modul";
      return;
    }

    els.featuresChip.textContent = `${state.features.length} Modul Tersedia`;

    els.featureList.innerHTML = state.features
      .map((f) => {
        const isDone = f.status === "Done";
        return `<article class="feature-card client-feature-card">
          <div class="feature-card-header">
            <div class="feature-title-group">
              <div class="icon-box ${isDone ? "icon-mint" : "icon-lavender"}">${icon("doc")}</div>
              <div>
                <h3>${escapeHtml(f.feature_name)}</h3>
                <span class="badge ${isDone ? "badge-completed" : "badge-progress"}">
                  ${isDone ? "✓ Siap Digunakan" : "Dalam Pengerjaan"}
                </span>
              </div>
            </div>
          </div>
          ${f.description ? `<p class="feature-card-desc">${escapeHtml(f.description)}</p>` : ""}
          <div class="feature-card-foot">
            ${
              f.doc_url
                ? `<a class="btn btn-primary btn-sm btn-doc-action" href="${escapeHtml(f.doc_url)}" target="_blank" rel="noopener">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
                    <span>Buka Panduan (Google Docs) ↗</span>
                  </a>`
                : '<span class="text-muted text-xs">Panduan belum ditautkan</span>'
            }
            <span class="feature-date">Diperbarui: ${escapeHtml(formatTime(f.created_at))}</span>
          </div>
        </article>`;
      })
      .join("");
  }

  function renderTimetable() {
    if (!els.timetableGrid) return;
    if (!state.currentProject) {
      els.timetableGrid.innerHTML = '<div class="empty">Pilih sistem yayasan terlebih dahulu.</div>';
      return;
    }

    const total = state.timetable.length;
    const doneCount = state.timetable.filter((t) => t.status === "Done").length;
    const inProgressCount = state.timetable.filter((t) => t.status === "In Progress").length;
    const pct = total ? Math.round((doneCount / total) * 100) : 0;

    if (els.timetableSummaryChip) {
      els.timetableSummaryChip.textContent = `${total} Task Terjadwal`;
    }
    if (els.timetablePercentLabel) {
      els.timetablePercentLabel.textContent = `${pct}% Selesai`;
    }
    if (els.timetableProgressFill) {
      els.timetableProgressFill.style.width = `${pct}%`;
    }
    if (els.timetableProgressMeta) {
      els.timetableProgressMeta.textContent = `${doneCount} dari ${total} task selesai (${inProgressCount} sedang digarap)`;
    }

    if (!total) {
      els.timetableGrid.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("calendar")}</div>
          <h3>Belum ada agenda time table bulanan</h3>
          <p>Tim pengembang belum menambahkan roadmap bulanan untuk sistem ini.</p>
        </div>`;
      return;
    }

    // Kelompokkan task berdasarkan Bulan (Group by target_month)
    const months = [];
    state.timetable.forEach((t) => {
      const m = (t.target_month || t.target_week || "Bulan Ini").trim();
      if (!months.includes(m)) months.push(m);
    });

    els.timetableGrid.innerHTML = months
      .map((m) => {
        const monthTasks = state.timetable.filter(
          (t) => (t.target_month || t.target_week || "Bulan Ini").trim() === m
        );
        const mTotal = monthTasks.length;
        const mDone = monthTasks.filter((t) => t.status === "Done").length;
        const mPct = mTotal ? Math.round((mDone / mTotal) * 100) : 0;

        const taskRows = monthTasks
          .map((t) => {
            let badgeHtml = "";
            let dotClass = "";
            if (t.status === "Done") {
              badgeHtml = '<span class="badge-tt badge-tt-done">✓ Selesai</span>';
              dotClass = "is-done";
            } else if (t.status === "In Progress") {
              badgeHtml = '<span class="badge-tt badge-tt-progress">⚡ Sedang Digarap</span>';
              dotClass = "is-progress";
            } else {
              badgeHtml = '<span class="badge-tt badge-tt-planned">Rencana</span>';
              dotClass = "is-planned";
            }

            return `
              <div class="timetable-task-row">
                <div class="timetable-task-main">
                  <span class="timetable-dot ${dotClass}" aria-hidden="true"></span>
                  <h4 class="timetable-task-name">${escapeHtml(t.task_name)}</h4>
                </div>
                <div class="timetable-task-badge-box">
                  ${badgeHtml}
                </div>
              </div>
            `;
          })
          .join("");

        return `
          <article class="timetable-month-card">
            <div class="timetable-month-head">
              <div class="timetable-month-title-wrap">
                <div class="icon-box icon-sm icon-lavender">${icon("calendar")}</div>
                <div>
                  <h3>Plan ${escapeHtml(m)}</h3>
                  <span class="timetable-month-sub">${mDone} dari ${mTotal} task selesai • ${mPct}%</span>
                </div>
              </div>
              <div class="timetable-month-progress-wrap">
                <div class="timetable-mini-track">
                  <div class="timetable-mini-fill" style="width: ${mPct}%"></div>
                </div>
                <strong class="timetable-mini-pct">${mPct}%</strong>
              </div>
            </div>
            <div class="timetable-month-tasks">
              ${taskRows}
            </div>
          </article>
        `;
      })
      .join("");
  }

  function filteredLogs() {
    return state.changelogs.filter((item) => {
      if (state.filter !== "all" && item.commit_type !== state.filter) return false;
      return true;
    });
  }

  function renderChangelog() {
    if (!state.currentProject) {
      els.changelog.innerHTML = '<div class="empty">Pilih sistem yayasan terlebih dahulu.</div>';
      if (els.changelogPagination) els.changelogPagination.hidden = true;
      return;
    }

    const items = filteredLogs();
    if (!items.length) {
      els.changelog.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("git")}</div>
          <h3>Belum ada riwayat update</h3>
          <p>Catatan update dari tim pengembang akan tampil di sini saat ada pembaharuan sistem.</p>
        </div>`;
      if (els.changelogPagination) els.changelogPagination.hidden = true;
      return;
    }

    // Logika Pagination Frontend (Maksimal 5 commit per halaman)
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

  function renderTickets() {
    if (!state.currentProject) {
      els.tickets.innerHTML = '<div class="empty">Pilih sistem yayasan terlebih dahulu.</div>';
      els.assignments.innerHTML = '<div class="empty">Antrian kosong.</div>';
      return;
    }

    const openCount = state.tickets.filter((t) => t.status !== "Resolved").length;
    els.ticketCount.textContent = String(openCount);
    els.ticketCount.hidden = openCount === 0;
    els.ticketStatusChip.textContent = `${state.tickets.length} Laporan`;

    if (!state.tickets.length) {
      els.tickets.innerHTML = `
        <div class="empty-state">
          <div class="empty-icon">${icon("check")}</div>
          <h3>Tidak ada kendala aktif</h3>
          <p>Semua sistem berjalan lancar. Jika Anda menemukan bug atau kendala, silakan isi formulir di atas.</p>
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

        const statusText = isResolved
          ? "✓ Selesai (Resolved)"
          : ticket.status === "In Progress"
          ? "⏳ Sedang Dikerjakan"
          : "🚨 Menunggu Penanganan";

        const catBadge = isIdea
          ? `<span class="badge badge-category-idea">💡 Ide</span>`
          : `<span class="badge badge-category-bug">🔴 Bug</span>`;

        const imageHtml = ticket.image_url
          ? `<div class="ticket-attachment">
               <a href="${escapeHtml(ticket.image_url || "")}" target="_blank" rel="noopener" class="ticket-thumb-link" title="Buka screenshot ukuran penuh">
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
                <span class="badge ${badgeClass}">${statusText}</span>
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
            <button class="btn btn-sm btn-edit-ticket" data-ticket-id="${ticket.id}" type="button" title="Edit laporan tiket ini">
              <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4Z"/></svg>
              <span>Edit</span>
            </button>
          </div>
        </article>`;
      })
      .join("");

    // Listeners for edit ticket
    els.tickets.querySelectorAll(".btn-edit-ticket").forEach((btn) => {
      btn.addEventListener("click", () => {
        const id = Number(btn.getAttribute("data-ticket-id"));
        const ticket = state.tickets.find((t) => t.id === id);
        if (ticket) openEditModal(ticket);
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
    renderFeatures();
    renderTimetable();
    renderChangelog();
    renderTickets();
    renderCalendar();
  }

  /* ----------------------------- Data Operations ----------------------------- */

  async function loadProjects() {
    try {
      const res = await fetch("api.php?action=projects");
      const payload = await res.json();
      state.projects = payload.projects || [];

      if (state.projects.length === 0) {
        els.switcher.innerHTML = '<option value="" disabled selected>Belum ada sistem terdaftar</option>';
        state.currentProject = null;
        state.changelogs = [];
        state.tickets = [];
        state.features = [];
        state.timetable = [];
        els.greeting.textContent = "Portal Matla";
        els.greetingSub.textContent = "Belum ada sistem proyek yang terdaftar.";
        renderAll();
        return;
      }

      els.switcher.innerHTML = state.projects
        .map((p) => {
          const label = p.client_name ? `${p.client_name} (${p.name})` : p.name;
          return `<option value="${escapeHtml(p.name)}">${escapeHtml(label)}</option>`;
        })
        .join("");

      const cachedName = localStorage.getItem(STORAGE_KEY);
      const chosen = state.projects.find((p) => p.name === cachedName) || state.projects[0];
      setProject(chosen);
    } catch (error) {
      console.error("Load projects error:", error);
      els.switcher.innerHTML = '<option value="" disabled selected>Gagal memuat sistem</option>';
    }
  }

  async function loadData({ silent = false } = {}) {
    if (!state.currentProject) return;

    const requestId = ++state.requestId;
    if (!silent) {
      els.featureList.innerHTML = '<div class="skeleton"></div><div class="skeleton"></div>';
      if (els.timetableGrid) els.timetableGrid.innerHTML = '<div class="skeleton"></div>';
      els.changelog.innerHTML = '<div class="skeleton"></div>';
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
      renderAll();
    } catch (error) {
      if (requestId !== state.requestId || silent) return;
      const message = `<div class="empty">${escapeHtml(error.message)}.</div>`;
      els.featureList.innerHTML = message;
      if (els.timetableGrid) els.timetableGrid.innerHTML = message;
      els.changelog.innerHTML = message;
      els.tickets.innerHTML = message;
    }
  }

  function setProject(project) {
    if (!project) return;
    state.currentProject = project;
    state.currentPage = 1;
    localStorage.setItem(STORAGE_KEY, project.name);
    els.switcher.value = project.name;

    const displayName = project.client_name || project.name;
    els.greeting.textContent = `Portal ${displayName}`;
    els.greetingSub.textContent = `Pantau panduan fitur, time table bulanan, dan laporkan kendala operasional untuk ${displayName}.`;
    els.formProject.textContent = displayName;
    document.title = `Portal ${displayName} — Jarvis Yayasan`;
    setFeedback(els.feedback, "");
    loadData();
  }

  /* ----------------------------- Events ----------------------------- */

  els.switcher.addEventListener("change", (event) => {
    const chosen = state.projects.find((p) => p.name === event.target.value);
    if (chosen) setProject(chosen);
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

  els.description.addEventListener("input", () => {
    const len = els.description.value.length;
    els.counter.textContent = `${len} / ${DESC_MAX}`;
    els.counter.classList.toggle("is-near", len > DESC_MAX * 0.9);
  });

  [els.reporter, els.description].forEach((input) =>
    input.addEventListener("input", () => input.removeAttribute("aria-invalid"))
  );

  /* ----------------------------- Category & Image Preview ----------------------------- */

  function updateCategory(category) {
    const isIdea = category === "Ide";
    if (els.categoryBug) els.categoryBug.classList.toggle("is-active", !isIdea);
    if (els.categoryIde) els.categoryIde.classList.toggle("is-active", isIdea);
    if (els.issueDescLabel) {
      els.issueDescLabel.textContent = isIdea ? "Deskripsi Usulan Ide Fitur" : "Deskripsi Kendala";
    }
    if (els.description) {
      els.description.placeholder = isIdea
        ? "Jelaskan ide fitur baru yang Anda usulkan: apa fungsinya, siapa penggunanya, dan bagaimana alur kerjanya..."
        : "Jelaskan kendala: tombol apa yang error, di halaman mana, atau data apa yang tidak sesuai...";
    }
    if (els.submit) {
      els.submit.textContent = isIdea ? "Kirim Usulan Ide Fitur" : "Kirim Laporan Kendala";
    }
  }

  function getSelectedCategory() {
    const checked = document.querySelector('input[name="category"]:checked');
    return checked ? checked.value : "Bug";
  }

  document.querySelectorAll('input[name="category"]').forEach((radio) => {
    radio.addEventListener("change", (e) => {
      updateCategory(e.target.value);
    });
  });

  if (els.ticketImage) {
    els.ticketImage.addEventListener("change", (e) => {
      const file = e.target.files?.[0];
      if (!file) return;

      if (file.size > 5 * 1024 * 1024) {
        setFeedback(els.feedback, "Ukuran gambar melebihi batas maksimal 5MB.", "err");
        els.ticketImage.value = "";
        return;
      }

      if (els.filePreviewName) els.filePreviewName.textContent = file.name;
      if (els.filePreviewSize) {
        const sizeKb = Math.round(file.size / 1024);
        els.filePreviewSize.textContent = sizeKb > 1024 ? `${(sizeKb / 1024).toFixed(1)} MB` : `${sizeKb} KB`;
      }
      if (els.filePreviewCard) els.filePreviewCard.hidden = false;
      if (els.fileCta) els.fileCta.hidden = true;
    });
  }

  if (els.btnRemoveFile) {
    els.btnRemoveFile.addEventListener("click", () => {
      resetImagePreview();
    });
  }

  function resetImagePreview() {
    if (els.ticketImage) els.ticketImage.value = "";
    if (els.filePreviewCard) els.filePreviewCard.hidden = true;
    if (els.fileCta) els.fileCta.hidden = false;
  }

  // Submit tiket kendala / ide fitur
  els.form.addEventListener("submit", async (event) => {
    event.preventDefault();
    if (!state.currentProject) {
      setFeedback(els.feedback, "Pilih sistem yayasan terlebih dahulu.", "err");
      return;
    }

    const reporter = els.reporter.value.trim();
    const description = els.description.value.trim();
    const category = getSelectedCategory();

    const missing = [
      [!reporter, els.reporter],
      [!description, els.description],
    ].filter(([invalid]) => invalid);
    if (missing.length) {
      missing.forEach(([, input]) => input.setAttribute("aria-invalid", "true"));
      missing[0][1].focus();
      setFeedback(els.feedback, "Lengkapi nama & bagian pelapor serta deskripsi.", "err");
      return;
    }

    els.submit.disabled = true;
    els.submit.textContent = "Mengirimkan…";
    setFeedback(els.feedback, "");

    try {
      const formData = new FormData();
      formData.append("project_name", state.currentProject.name);
      formData.append("reporter_name", reporter);
      formData.append("category", category);
      formData.append("issue_description", description);

      const file = els.ticketImage?.files?.[0];
      if (file) {
        formData.append("image", file);
      }

      const res = await fetch("api.php?action=tickets", {
        method: "POST",
        body: formData,
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || "Gagal mengirim tiket");

      els.form.reset();
      resetImagePreview();
      updateCategory("Bug");
      els.counter.textContent = `0 / ${DESC_MAX}`;
      const notified = payload.telegram?.sent ? " Alert telah terkirim ke Telegram tim IT." : "";
      setFeedback(
        els.feedback,
        `✓ Laporan #${payload.ticket.id} (${payload.ticket.category}) berhasil dikirim dan dicatat.${notified}`,
        "ok"
      );
      await loadData({ silent: true });
      const row = els.tickets.querySelector(".ticket-row");
      if (row) row.classList.add("is-new");
    } catch (error) {
      setFeedback(els.feedback, error.message, "err");
    } finally {
      els.submit.disabled = false;
      updateCategory(getSelectedCategory());
    }
  });

  if (els.jumpReport) {
    els.jumpReport.addEventListener("click", () => {
      els.form.scrollIntoView({ behavior: "smooth", block: "center" });
      els.reporter.focus();
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
      closeEditModal();
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

  setInterval(() => {
    if (!document.hidden) loadData({ silent: true });
  }, 30000);

  /* ----------------------------- Edit Ticket Modal ----------------------------- */

  const editModal = $("modal-edit-ticket");
  const editForm = $("edit-ticket-form");
  const editIdInput = $("edit_ticket_id");
  const editReporterInput = $("edit_reporter_name");
  const editDescInput = $("edit_issue_description");
  const editImageInput = $("edit_ticket_image");
  const editFileCta = $("edit-file-cta");
  const editFilePreviewCard = $("edit-file-preview-card");
  const editFilePreviewImg = $("edit-file-preview-img");
  const editFilePreviewName = $("edit-file-preview-name");
  const editFilePreviewSize = $("edit-file-preview-size");
  const editExistingBox = $("edit-existing-image-box");
  const editExistingLink = $("edit-existing-image-link");
  const editRemoveFlag = $("edit_remove_image");
  const editFeedback = $("edit-ticket-feedback");
  const editSubmitBtn = $("btn-submit-edit-ticket");

  function setEditCategory(category) {
    const isIdea = category === "Ide";
    const radio = document.querySelector(`input[name="edit_category"][value="${isIdea ? "Ide" : "Bug"}"]`);
    if (radio) radio.checked = true;
    const catBug = $("label-edit-cat-bug");
    const catIde = $("label-edit-cat-ide");
    if (catBug) catBug.classList.toggle("is-active", !isIdea);
    if (catIde) catIde.classList.toggle("is-active", isIdea);
  }

  document.querySelectorAll('input[name="edit_category"]').forEach((radio) => {
    radio.addEventListener("change", (e) => {
      setEditCategory(e.target.value);
    });
  });

  function resetEditFile() {
    if (editImageInput) editImageInput.value = "";
    if (editFilePreviewCard) editFilePreviewCard.hidden = true;
    if (editFileCta) editFileCta.hidden = false;
  }

  if (editImageInput) {
    editImageInput.addEventListener("change", (e) => {
      const file = e.target.files?.[0];
      if (!file) return;

      if (file.size > 5 * 1024 * 1024) {
        setFeedback(editFeedback, "Ukuran gambar melebihi batas 5MB.", "err");
        editImageInput.value = "";
        return;
      }

      if (editFilePreviewName) editFilePreviewName.textContent = file.name;
      if (editFilePreviewSize) {
        const sizeKb = Math.round(file.size / 1024);
        editFilePreviewSize.textContent = sizeKb > 1024 ? `${(sizeKb / 1024).toFixed(1)} MB` : `${sizeKb} KB`;
      }
      if (editFilePreviewCard) editFilePreviewCard.hidden = false;
      if (editFileCta) editFileCta.hidden = true;
    });
  }

  $("btn-remove-edit-file")?.addEventListener("click", () => {
    resetEditFile();
  });

  $("btn-remove-existing-image")?.addEventListener("click", () => {
    if (editRemoveFlag) editRemoveFlag.value = "true";
    if (editExistingBox) editExistingBox.hidden = true;
  });

  function openEditModal(ticket) {
    if (!editModal) return;
    editIdInput.value = ticket.id;
    editReporterInput.value = ticket.reporter_name;
    editDescInput.value = ticket.issue_description;
    setEditCategory(ticket.category);
    resetEditFile();

    if (ticket.image_url) {
      if (editExistingBox) editExistingBox.hidden = false;
      if (editExistingLink) editExistingLink.href = ticket.image_url;
      if (editRemoveFlag) editRemoveFlag.value = "false";
    } else {
      if (editExistingBox) editExistingBox.hidden = true;
      if (editRemoveFlag) editRemoveFlag.value = "false";
    }

    setFeedback(editFeedback, "");
    editModal.hidden = false;
  }

  function closeEditModal() {
    if (editModal) editModal.hidden = true;
  }

  $("modal-edit-ticket-close")?.addEventListener("click", closeEditModal);
  $("modal-edit-ticket-cancel")?.addEventListener("click", closeEditModal);
  editModal?.addEventListener("click", (e) => {
    if (e.target === editModal) closeEditModal();
  });

  editForm?.addEventListener("submit", async (e) => {
    e.preventDefault();
    const id = editIdInput.value;
    const reporter = editReporterInput.value.trim();
    const description = editDescInput.value.trim();
    const checkedRadio = document.querySelector('input[name="edit_category"]:checked');
    const category = checkedRadio ? checkedRadio.value : "Bug";

    if (!reporter || !description) {
      setFeedback(editFeedback, "Nama pelapor dan deskripsi wajib diisi.", "err");
      return;
    }

    if (editSubmitBtn) {
      editSubmitBtn.disabled = true;
      editSubmitBtn.textContent = "Menyimpan…";
    }
    setFeedback(editFeedback, "");

    try {
      const formData = new FormData();
      formData.append("reporter_name", reporter);
      formData.append("category", category);
      formData.append("issue_description", description);
      if (editRemoveFlag && editRemoveFlag.value === "true") {
        formData.append("remove_image", "true");
      }
      const file = editImageInput?.files?.[0];
      if (file) {
        formData.append("image", file);
      }

      formData.append("_method", "PUT");
      const res = await fetch(`api.php?action=tickets&id=${id}&sub=edit`, {
        method: "POST",
        body: formData,
      });
      const payload = await res.json();
      if (!res.ok) throw new Error(payload.error || "Gagal memperbarui tiket");

      closeEditModal();
      await loadData({ silent: true });
    } catch (err) {
      setFeedback(editFeedback, err.message, "err");
    } finally {
      if (editSubmitBtn) {
        editSubmitBtn.disabled = false;
        editSubmitBtn.textContent = "Simpan Perubahan";
      }
    }
  });

  renderCalendar();
  loadProjects();
})();
