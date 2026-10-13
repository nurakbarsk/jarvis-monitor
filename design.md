# Design System — Eduplex Dashboard

> Panduan desain untuk AI coding agent. Semua nilai warna, ukuran, dan radius adalah **estimasi** dari screenshot. Gunakan sebagai design token (CSS variables / Tailwind theme) dan pertahankan konsistensinya di seluruh UI.

---

## 1. Gaya Visual & Mood

**Nama gaya:** Modern EdTech Dashboard — soft, friendly, clean.

**Karakter utama**
- **Kontras tinggi di sidebar:** sidebar gelap (near-black navy) berdampingan dengan area konten terang (off-white).
- **Satu warna aksen dominan:** lime/chartreuse neon sebagai penanda "aktif / penting / aksi utama".
- **Pastel sebagai pembeda kategori:** ikon dan badge memakai tint pastel (peach, lavender, mint, kuning muda) agar kartu mudah dibedakan tanpa terasa ramai.
- **Sudut sangat membulat** (rounded 16–24 px) di hampir semua elemen → kesan ramah dan playful.
- **Datar dan ringan:** hampir tanpa shadow; pemisahan kartu memakai perbedaan warna latar (kartu putih di atas canvas abu-krem) dan border tipis.
- **Whitespace lega**, informasi dikelompokkan dalam kartu modular.
- **Ilustrasi flat** dan emoji kecil (👋) memberi sentuhan personal.

**Mood:** ramah, energik, terpercaya, fokus pada belajar. Cocok untuk produk edukasi, LMS, atau dashboard produktivitas.

**Layout tingkat tinggi**

```
┌────────┬──────────────────────────────────────┬──────────────┐
│        │ Welcome + Search + Avatar            │              │
│ Side-  ├──────────────────────────┬───────────┤  Right rail  │
│ bar    │ New Courses (3 cards)    │ Promo     │  - Promo     │
│ (dark) ├────────────┬─────────────┤ (dark)    │  - Calendar  │
│        │ Hours      │ Daily       │           │  - Assign-   │
│        │ Activity   │ Schedule    │           │    ments     │
│        ├────────────┴─────────────┤           │              │
│        │ Course You're Taking     │           │              │
└────────┴──────────────────────────┴───────────┴──────────────┘
```

---

## 2. Palet Warna

### 2.1 Design Tokens

```css
:root {
  /* Brand / Primary */
  --color-primary:        #D4F25C; /* lime — aksen utama, item aktif, CTA */
  --color-primary-hover:  #C6E74A;
  --color-primary-soft:   #EEF8C4; /* tint untuk badge / highlight lembut */

  /* Neutral gelap (sidebar, kartu promo) */
  --color-dark:           #1B1B2A; /* near-black navy */
  --color-dark-elevated:  #26263A; /* hover / elemen di atas dark */
  --color-on-dark:        #FFFFFF;
  --color-on-dark-muted:  #B8B8C7;

  /* Background */
  --color-bg-canvas:      #F4F3EF; /* latar halaman (off-white hangat) */
  --color-bg-surface:     #FFFFFF; /* kartu */
  --color-bg-subtle:      #F6F6F3; /* input, chip, track progress */

  /* Text */
  --color-text-primary:   #17172A; /* judul, angka penting */
  --color-text-secondary: #5E5E6E; /* body, label */
  --color-text-muted:     #9A9AA8; /* caption, placeholder, meta */

  /* Border */
  --color-border:         #ECECE6; /* border kartu / divider */
  --color-border-strong:  #DADAD2; /* border input, chip */

  /* Secondary (pastel kategori) */
  --color-peach:          #FFE4D6; /* bg ikon: Design System, Content Writing */
  --color-peach-ink:      #F2733B;
  --color-lavender:       #E4E2FB; /* bg ikon: Typography, Photography */
  --color-lavender-ink:   #6C63E0;
  --color-mint:           #DDF6E8;
  --color-mint-ink:       #2FA66A;
  --color-lime-tint:      #EAF7BE; /* bg ikon: Color Style, Usability Testing */
  --color-sand:           #FBEBD2; /* bg ikon: Visual Design */
  --color-sand-ink:       #D9902B;

  /* State */
  --color-success:        #2FA66A;
  --color-success-bg:     #DFF5E8; /* badge "Completed" */
  --color-info-bg:        #E4E2FB; /* badge "In progress" */
  --color-warning-bg:     #FFE6D2; /* badge "Upcoming" */
  --color-warning:        #F2733B;
  --color-danger:         #E5484D;
  --color-star:           #F5B82E; /* ikon rating bintang */
  --color-notif-badge-bg: #FFE4D6; /* bulatan notifikasi (angka 2) */
  --color-notif-badge-fg: #E5602B;
}
```

### 2.2 Pemakaian

| Peran | Token | Dipakai untuk |
|---|---|---|
| Primary | `--color-primary` | Menu aktif sidebar, tombol "+" , tombol Get Access, bar chart yang disorot, tanggal terpilih di kalender, progress ring |
| Secondary | pastel (peach, lavender, mint, sand) | Latar ikon kursus/jadwal/assignment |
| Dark | `--color-dark` | Sidebar, kartu promo "Go Premium", tooltip chart |
| Background | `--color-bg-canvas` / `--color-bg-surface` | Halaman / kartu |
| Text | `--color-text-*` | Hirarki tulisan |
| Border | `--color-border*` | Garis tipis kartu, input, dropdown |
| State | `success / info / warning / danger` | Badge status, indikator tren |

### 2.3 Aturan Warna
- Lime **selalu** memakai teks gelap (`--color-text-primary` / `--color-dark`), jangan teks putih.
- Di atas dark, aksen lime dipakai untuk satu fokus per area (item aktif, CTA).
- Warna pastel hanya sebagai **latar**; ikonnya memakai ink yang lebih pekat atau hitam.
- Kontras teks minimal WCAG AA (4.5:1 untuk body).

---

## 3. Tipografi

**Font utama:** geometric/humanist sans-serif modern dengan karakter ramah dan bentuk bulat, mirip **Plus Jakarta Sans** atau **DM Sans** (alternatif: Manrope, Inter).

```css
--font-sans: "Plus Jakarta Sans", "DM Sans", "Inter", system-ui, -apple-system, "Segoe UI", sans-serif;
```

### Skala Tipografi

| Peran | Ukuran | Weight | Line-height | Letter-spacing | Contoh |
|---|---|---|---|---|---|
| Page title | 24–26 px | 700 | 1.25 | -0.01em | "Welcome back Taylor 👋" |
| Promo title | 18–20 px | 700 | 1.3 | 0 | "Go Premium" |
| Section title | 16–18 px | 700 | 1.3 | 0 | "New Courses", "Hours Activity", "Daily Schedule" |
| Card title | 13–14 px | 600 | 1.35 | 0 | "Design System", "Content Writing" |
| Body | 13–14 px | 500 | 1.5 | 0 | Nama kursus, menu sidebar |
| Body secondary | 12–13 px | 400–500 | 1.5 | 0 | "Lecture - Class", "Micheal Andrew" |
| Caption / meta | 10–11 px | 400–500 | 1.4 | 0 | "12 Lessons", "Rate", "Type", "Remaining" |
| Value / stat | 12–13 px | 700 | 1.3 | 0 | "4.8", "8h 45 min", "45%" |
| Badge / chip | 10–11 px | 600 | 1 | 0 | "In progress", "Completed" |
| Link kecil | 11–12 px | 500 | 1.3 | 0 | "View All" (underline) |
| Button | 12–13 px | 600 | 1 | 0 | "Get Access" |

**Aturan**
- Maksimal 3 weight: 400/500 (body), 600 (label/emphasis), 700 (heading/angka).
- Gunakan **sentence/Title Case** seperti di UI asli; hindari ALL CAPS.
- Label meta (Rate, Type, Remaining) memakai warna `--color-text-muted`, nilainya memakai `--color-text-primary` dengan weight lebih berat.
- Angka di chart/axis memakai ukuran 10–11 px, warna muted.

---

## 4. Spacing & Layout

### 4.1 Skala Spacing (basis 4 px)

`4 · 8 · 12 · 16 · 20 · 24 · 32 · 40`

### 4.2 Struktur Halaman

| Elemen | Nilai |
|---|---|
| Frame luar | Padding 12–16 px, canvas `--color-bg-canvas` |
| Sidebar | Lebar ±180–190 px (fixed), tinggi penuh, radius 24 px |
| Area konten utama | Fleksibel (`1fr`), padding horizontal 20–24 px |
| Right rail | Lebar ±150–170 px (fixed) |
| Gap antar kartu | 16 px |
| Padding dalam kartu | 16–20 px |
| Gap antar item list | 12 px |
| Max-width keseluruhan | 1280–1440 px, terpusat |

### 4.3 Grid

```
Desktop (≥1200px): grid-template-columns: 188px minmax(0, 1fr) 168px;  gap: 16px
Konten tengah:
  Baris 1: "New Courses"  → 3 kolom sama lebar (repeat(3, 1fr)), gap 16px
  Baris 2: "Hours Activity" (±55%) + "Daily Schedule" (±45%)
  Baris 3: "Course You're Taking" → 1 kolom penuh (list row)
Right rail: Promo → Kalender → Assignments (stack vertikal, gap 16px)
```

### 4.4 Responsif (saran)
- **≥1200 px:** 3 kolom (sidebar + konten + right rail).
- **768–1199 px:** right rail turun ke bawah konten; sidebar tetap atau menjadi ikon saja (±72 px).
- **<768 px:** sidebar menjadi drawer / bottom nav; kartu 1 kolom; "New Courses" bisa scroll horizontal.

---

## 5. Border Radius, Shadow, dan Border

### Radius

| Token | Nilai | Dipakai untuk |
|---|---|---|
| `--radius-xs` | 6 px | Tooltip chart, chip kecil |
| `--radius-sm` | 10 px | Kotak ikon (36–40 px), dropdown kecil |
| `--radius-md` | 14 px | Input pencarian (pill sebagian), baris list item |
| `--radius-lg` | 20 px | Kartu konten |
| `--radius-xl` | 24–28 px | Sidebar, kartu promo, kartu "Download mobile app" |
| `--radius-full` | 9999 px | Menu aktif sidebar, tombol, badge, avatar, search bar, progress ring |

### Shadow
- **Default: tanpa shadow** pada kartu. Pemisahan dari canvas cukup lewat warna latar + border tipis.
- Elemen mengambang (tooltip chart, dropdown terbuka): `0 6px 18px rgba(23, 23, 42, 0.12)`.
- Tombol lingkaran dark (ikon panah di kartu mobile app): `0 4px 12px rgba(0,0,0,0.20)`.

### Border
- Kartu: `1px solid var(--color-border)` (opsional; boleh tanpa border jika kontras latar cukup).
- Input / dropdown / chip: `1px solid var(--color-border-strong)`.
- Divider internal: `1px solid var(--color-border)`.
- Fokus keyboard: `2px solid var(--color-primary)` + offset 2 px (atau ring `0 0 0 3px rgba(212,242,92,.5)`).

---

## 6. Komponen

### 6.1 Sidebar Navigation

- Latar `--color-dark`, radius 24 px, padding 16 px, tinggi penuh.
- **Logo** di atas: ikon bulat lime + wordmark "Eduplex" putih (bold ±18 px).
- **Menu item** (Dashboard, My Courses, My Classes, Messages, Notifications, Calendars, Community, Settings):
  - Tinggi ±36–40 px, padding `8px 12px`, ikon outline 18–20 px + label 13 px, gap 12 px.
  - Jarak antar item 6–8 px.

| State | Latar | Teks/Ikon |
|---|---|---|
| Default | transparan | `--color-on-dark-muted` / putih 80% |
| Hover | `--color-dark-elevated` | putih |
| **Active** | `--color-primary`, radius full | `--color-dark`, weight 600 |
| Focus | ring lime 2 px | — |

- **Badge notifikasi** ("2"): lingkaran 18 px, latar `--color-notif-badge-bg`, teks `--color-notif-badge-fg`, 10 px bold, di sisi kanan item.
- **Kartu "Download our mobile app"** di dasar sidebar: latar lime, radius 24 px, padding 16 px, ikon panah dalam lingkaran dark 40 px di atas, teks bold 13 px di bawah.

### 6.2 Header (Top Bar)
- Kiri: sapaan `Welcome back {name} 👋` (Page title).
- Kanan: **search input** (pill, lebar ±220 px, tinggi 36–40 px, latar putih, border tipis, ikon kaca pembesar kiri, placeholder muted) + **avatar** bulat 36 px dengan ring tipis lavender.

### 6.3 Button

| Varian | Latar | Teks | Radius | Padding | Dipakai |
|---|---|---|---|---|---|
| **Primary** | `--color-primary` | `--color-dark` 600 | full | `8px 16px` | "Get Access" |
| **Icon primary** | `--color-primary` | `--color-dark` | full / 8 px | 24–28 px persegi | Tombol "+" di Assignments & Course You're Taking |
| **Icon dark** | `--color-dark` | putih | full | 40 px | Panah di kartu mobile app |
| **Ghost/outline** | transparan | `--color-text-primary` | 10 px | `6px 12px` | Dropdown "Weekly", "Active" |
| **Text link** | transparan | `--color-text-secondary`, underline | — | — | "View All" |

State:
- Hover: primary → `--color-primary-hover`; ghost → latar `--color-bg-subtle`.
- Active/pressed: scale 0.98.
- Disabled: opacity 0.5, `cursor: not-allowed`.
- Focus-visible: ring lime 2–3 px.
- Transisi: `150ms ease-out` (warna & transform).

### 6.4 Input & Select
- **Search:** tinggi 40 px, radius full, latar `#FFFFFF`, border `--color-border-strong`, ikon 16 px muted, teks 13 px. Focus → border `--color-primary` + ring lime lembut.
- **Dropdown kecil** ("Weekly ▾", "Active ▾"): tinggi 28–32 px, radius 10 px, border tipis, teks 12 px, chevron 12 px.
- Placeholder memakai `--color-text-muted`.

### 6.5 Card

**a. Course Card (New Courses)**
- Latar putih, radius 20 px, padding 16 px, tinggi ±105 px.
- Baris atas: kotak ikon pastel 36 px (radius 10 px) + judul (14 px/600) + "12 Lessons" (11 px muted).
- Baris bawah: dua kolom meta — **Rate** (bintang kuning + angka bold) dan **Type** (nilai teks) dengan label muted 10–11 px di atasnya.
- Variasi warna ikon: peach, lime-tint, lavender (berputar).
- Hover: border `--color-border-strong` dan naik 1–2 px (`translateY(-2px)`), tanpa shadow berat.

**b. Promo Card ("Go Premium")**
- Latar `--color-dark`, radius 24 px, padding 16 px.
- Logo kecil + judul putih bold 18 px, deskripsi 11–12 px putih 70%.
- Tombol primary lime ("Get Access") di kiri bawah; ilustrasi flat karakter di kanan.

**c. Chart Card (Hours Activity)**
- Header: judul + chip tren (ikon panah + `+3%` hijau + "Increase than last week" muted 11 px) + dropdown "Weekly" di kanan.
- Bar chart vertikal: 7 bar (Su–Sa), lebar ±5 px, ujung membulat, warna default `--color-dark`, **bar terpilih** lime.
- Y-axis (1h–8h) dan X-axis berwarna muted 10 px, garis grid putus-putus sangat tipis.
- **Tooltip:** latar `--color-dark`, teks putih 11 px, radius 8 px, ikon emoji kecil + durasi bold ("6h 45 min") + tanggal muted.

**d. Schedule Card (Daily Schedule)**
- Daftar 4 baris; tiap baris: kotak ikon pastel 36 px + judul (13 px/600) + sub (11 px muted, mis. "Lecture - Class") + chevron bulat di kanan.
- Latar baris `--color-bg-subtle` / pastel sangat muda, radius 14 px, padding 8–10 px, gap 8–12 px.
- Hover: latar sedikit lebih gelap, chevron bergeser 2 px.

**e. Course Progress Row (Course You're Taking)**
- Satu baris per kursus: avatar/ikon 36–40 px kotak membulat + judul 13 px/600 + avatar mentor kecil 16 px + nama (muted) | kolom "Remaining" (label muted + nilai bold) | **progress ring** 28–32 px (stroke lime, track `--color-bg-subtle`) + persentase bold.
- Antar baris dipisah divider tipis atau jarak 8–12 px.

**f. Calendar Card**
- Header: bulan-tahun bold 13 px + panah kiri/kanan (tombol lingkaran 24 px, border tipis).
- Grid 7 kolom (S M T W T F S), label hari muted 10 px, tanggal 11–12 px.
- Tanggal biasa: teks `--color-text-primary`; tanggal di luar bulan: muted.
- **Tanggal terpilih:** lingkaran lime 24 px, teks dark bold.
- Latar kartu putih, radius 20 px, padding 12–16 px.

**g. Assignment Item**
- Header section: "Assignments" bold + tombol "+" lime.
- Item: kotak ikon pastel 32 px + judul 12–13 px/600 + tanggal 10–11 px muted + **status badge** di kanan/bawah.
- Latar putih, radius 14–16 px, padding 10–12 px, gap 8 px.

### 6.6 Badge / Status Chip

| Status | Latar | Teks |
|---|---|---|
| In progress | `--color-info-bg` (lavender) | `#5B52D6` |
| Completed | `--color-success-bg` (mint) | `#1F8A55` |
| Upcoming | `--color-warning-bg` (peach) | `#D8571F` |
| Notifikasi (angka) | `--color-notif-badge-bg` | `--color-notif-badge-fg` |

Spesifikasi: tinggi 22–24 px, padding `4px 10px`, radius full, teks 10–11 px/600, tanpa border.

### 6.7 Ikon
- Gaya **outline**, stroke 1.5 px, sudut membulat, ukuran 16–20 px (sidebar 18–20, dalam kartu 16–18).
- Gunakan satu library konsisten (rekomendasi: Lucide / Phosphor / Iconsax outline).
- Ikon di kotak pastel memakai warna ink gelap/berwarna dengan kontras cukup.

### 6.8 Avatar
- Bulat; ukuran 36 px (header), 16–20 px (mentor di list). Foto dengan `object-fit: cover`.
- Ring tipis 2 px lavender/putih untuk avatar utama.

### 6.9 Progress Ring
- Diameter 28–32 px, stroke 3 px, `stroke-linecap: round`, progres `--color-primary` (bisa `--color-success` jika 100%), track `--color-bg-subtle`.
- Angka persen di sampingnya (bukan di dalam ring), 12 px bold.

---

## 7. Interaksi & Motion
- Durasi 150–200 ms, easing `ease-out`.
- Hover kartu: naik 1–2 px + border lebih tegas; **hindari** shadow besar.
- Item sidebar aktif berpindah dengan transisi latar (opsional slide 200 ms).
- Chart: bar tumbuh dari bawah saat pertama kali muncul (satu kali, 400–600 ms); hormati `prefers-reduced-motion`.
- Fokus keyboard harus selalu terlihat.

---

## 8. Aksesibilitas
- Semua ikon-saja (tombol "+", panah, chevron) wajib punya `aria-label`.
- Kontras teks di atas lime & pastel harus ≥ 4.5:1 (gunakan teks gelap).
- Status tidak hanya dibedakan dengan warna: sertakan teks ("Completed", "In progress").
- Target sentuh minimal 40×40 px untuk tombol ikon di mobile (perbesar area klik walau ikon kecil).
- Chart harus punya alternatif teks/tabel ringkas untuk screen reader.

---

## 9. Do & Don't

### ✅ Do
- Pakai **satu** aksen utama (lime) untuk state aktif dan aksi utama saja.
- Pertahankan kontras gelap-terang: sidebar dan promo gelap, konten terang.
- Gunakan radius besar dan konsisten sesuai tingkat hirarki (kartu 20 px, ikon 10 px, pill = full).
- Kelompokkan konten dalam kartu modular dengan gap 16 px.
- Pakai warna pastel berbeda untuk membedakan kategori kursus/jadwal/assignment.
- Tampilkan angka penting (rating, durasi, persen) dengan weight 700 dan label di atasnya memakai muted.
- Beri ruang napas: padding kartu 16–20 px, jarak antar section 16–24 px.
- Gunakan ikon outline satu keluarga dan teks sentence/Title Case.
- Sorot hanya satu data point di chart (bar lime) agar fokus jelas.

### ❌ Don't
- Jangan memakai teks putih di atas lime, atau lime sebagai warna teks di latar putih.
- Jangan menambah warna aksen baru (biru, merah, ungu pekat) di luar token; pastel hanya untuk latar.
- Jangan memakai shadow tebal/gelap pada kartu — tampilan harus tetap flat dan ringan.
- Jangan mencampur radius kecil (≤4 px) / sudut tajam dengan radius besar dalam satu layar.
- Jangan memakai lebih dari 3 weight font atau lebih dari 2 font family.
- Jangan memakai ALL CAPS, garis tepi tebal, atau gradient dekoratif.
- Jangan memadatkan kartu: hindari padding < 12 px dan gap < 8 px.
- Jangan memakai ikon filled/duotone bercampur dengan outline.
- Jangan menaruh lebih dari satu CTA lime utama dalam satu kartu.
- Jangan menyembunyikan status hanya lewat warna tanpa label teks.

---

## 10. Contoh Implementasi Cepat (Tailwind)

```js
// tailwind.config.js (potongan)
theme: {
  extend: {
    colors: {
      primary: { DEFAULT: "#D4F25C", hover: "#C6E74A", soft: "#EEF8C4" },
      dark: { DEFAULT: "#1B1B2A", elevated: "#26263A" },
      canvas: "#F4F3EF",
      subtle: "#F6F6F3",
      line: { DEFAULT: "#ECECE6", strong: "#DADAD2" },
      ink: { DEFAULT: "#17172A", 2: "#5E5E6E", muted: "#9A9AA8" },
      peach: "#FFE4D6", lavender: "#E4E2FB", mint: "#DDF6E8", sand: "#FBEBD2",
    },
    borderRadius: { sm: "10px", md: "14px", lg: "20px", xl: "24px" },
    fontFamily: { sans: ['"Plus Jakarta Sans"', "DM Sans", "Inter", "system-ui", "sans-serif"] },
  },
}
```

```html
<!-- Contoh: menu sidebar aktif -->
<a class="flex items-center gap-3 rounded-full bg-primary px-3 py-2 text-[13px] font-semibold text-dark">
  <svg class="h-5 w-5" aria-hidden="true">…</svg> Dashboard
</a>

<!-- Contoh: badge status -->
<span class="rounded-full bg-lavender px-2.5 py-1 text-[11px] font-semibold text-[#5B52D6]">In progress</span>

<!-- Contoh: kartu -->
<section class="rounded-lg border border-line bg-white p-4">…</section>
```

---

## 11. Catatan untuk AI Agent
1. Selalu gunakan **token** di atas, jangan hardcode hex baru.
2. Bangun komponen kecil yang bisa dipakai ulang: `Card`, `IconBox`, `Badge`, `ProgressRing`, `NavItem`, `StatMeta`.
3. Pertahankan hirarki: **gelap (navigasi/promo) → putih (kartu) → pastel (kategori) → lime (aksi/aktif)**.
4. Ikuti skala spacing 4 px dan skala radius di atas agar tampilan konsisten.
5. Jika ragu pada ukuran, pilih opsi yang lebih lega dan lebih membulat.
