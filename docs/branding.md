## [Nama Brand] - Brand Identity & UI/UX Guidelines
Dokumen ini merangkum identitas merek, pedoman visual, dan konsep antarmuka (UI/UX) untuk aplikasi ini. Ganti seluruh bagian dalam [ ] dengan detail brand kamu sebelum reuse template ini.

> **Mode Redesain** (lihat `docs/install.md` alur B): kalau dokumen ini
> ditulis ulang dari aplikasi existing, sumber acuannya adalah SELURUH isi
> folder `references/` — *tidak terbatas pada file `.html`*. Folder
> tersebut bisa berisi beberapa subfolder dengan campuran file PHP, JS,
> HTML, bahkan CSS template lengkap dari codebase lama. Baca semuanya
> secara lengkap (termasuk file CSS untuk warna/tipografi asli) sebelum
> menyusun identitas visual di bawah ini.

## 1. Identitas Inti (Core Identity)
- Nama Brand: [NAMA BRAND]
- Tagline Utama: "[Tagline singkat brand]"
- Tipe Aplikasi: [misal: platform streaming, marketplace, sistem informasi, dst]

## 2. Logo & Ikonografi
- Logo berada di file `docs/logo.png`
- [Deskripsikan elemen visual logo: bentuk, gaya, makna filosofis jika ada]
- Logotype: [Deskripsikan gaya teks nama brand — kapital/lowercase, warna, font]
- Orientasi: [Landscape/Portrait, sebutkan file referensi jika ada]
- Gaya Ikon Sistem: [bersih & minimalis / outline / filled / dll], disesuaikan dengan skema warna utama.

## 3. Tipografi (Typography)
- Font Utama (Brand & Headings): [misal Poppins, Inter, dst]
- Penggunaan: logotype, judul halaman (H1, H2, H3), elemen teks yang membutuhkan penekanan kuat.
- Font Sekunder (Body Text & UI): [misal Inter, Roboto]
- Penggunaan: teks paragraf, deskripsi, tabel data, elemen antarmuka aplikasi.

## 4. Palet Warna (Color Palette)
Selaras dengan CSS variables di `public/assets/css/branding.css` (`--brand-gold`, `--bg-primary`, dst) — update file itu jika warna berubah.
- Main Color: [#HEXCODE] — Penggunaan: header, navbar, tombol aksi primer (CTA)
- Secondary/Accent Color: [#HEXCODE] — Penggunaan: highlight, hover state, elemen pembeda
- Neutral Dark: [#HEXCODE] — Penggunaan: teks sekunder, footer
- Neutral Light/White: [#HEXCODE] — Penggunaan: latar belakang utama, card, kontainer konten

## 5. Konsep Tata Letak & Antarmuka (Layout & UI Concepts)
A. Tampilan Desktop
- Sidebar/Navbar: [warna utama untuk bilah navigasi], teks/ikon [warna], aksen [warna] saat active state.
- Area Konten (Cards/Surfaces): [deskripsi background & elevasi].
- Tipografi: judul section pakai [font utama], isi tabel/paragraf pakai [font sekunder].

B. Tampilan Mobile
- Desain Responsif: [catatan khusus, misal tabel jadi card list di layar kecil]
- Navigasi Bawah (Bottom Navigation): [warna, ikon aktif]

C. Estetika Visual Tambahan
- Radius Sudut (Border Radius): [misal rounded-md/rounded-lg/rounded-full]
- Shadow: [soft/hard drop shadow, kapan dipakai]
- Gaya Tombol: [warna tombol primer/sekunder dan aturan kontras teks]

## 6. Hierarki Platform (Asumsi Implementasi Web)
[Jelaskan level akses/halaman utama aplikasi dan gaya visual dominan di masing-masing, misal: landing page publik vs dashboard internal]
