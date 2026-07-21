BUILD PROTOCOL - BANGUN APLIKASI DARI TEMPLATE
================================================

KONTEKS
-------
File ini dibaca oleh AI coding assistant (Claude Code CLI, Cursor, dll) untuk
membangun aplikasi dari template ini. Urutan bacaan WAJIB linear sebelum
mengerjakan apapun:

1. `CLAUDE.md` (root)   - constitution, aturan arsitektur, WAJIB lengkap
2. `docs/prd.md`        - definisi aplikasi spesifik (fitur, ekosistem A/B/C/D,
                          model bisnis)
3. `docs/branding.md`   - identitas visual (warna, font, logo)
4. `references/*.html`  - golden template struktur & styling per shell (lihat
                          CLAUDE.md Section 3e/12c - BASELINE STRUKTURAL,
                          bukan sumber teks final)

TUJUAN AKHIR: aplikasi bisa di-preview di browser lewat
`http://<nama-folder-project>.test/` (Laragon auto virtual host), dengan
fitur sesuai `docs/prd.md` dan tampilan sesuai `docs/branding.md`.

ATURAN KERJA (WAJIB, mengikuti CLAUDE.md Section 1)
-----------------------------------------------------
- Proses ini terbagi 3 TAHAP. Setiap tahap: selesaikan penuh -> laporkan hasil
  KONKRET (path file + cuplikan isi, bukan kesimpulan) -> TUNGGU approval
  eksplisit project owner sebelum lanjut tahap berikutnya. JANGAN lompat tahap
  dalam satu sesi yang sama.
- Klaim "sudah selesai" / "sudah jalan" WAJIB disertai bukti konkret (path
  file, isi fungsi, atau hasil `php -l`). CLI TIDAK punya browser/HTTP
  client - TIDAK BOLEH mengklaim "tampilan sudah benar secara visual" atau
  "preview berhasil diakses". Itu WAJIB diverifikasi manual oleh project
  owner sendiri di browser (lihat Tahap 3 poin 5).
- `references/*.html` HANYA dibaca sebagai acuan struktur/styling, JANGAN
  pernah dijalankan/dieksekusi sebagai kode, dan JANGAN disalin literal
  menjadi teks final di PHP (lihat CLAUDE.md Section 12c poin 4 - teks WAJIB
  diekstrak jadi key i18n di `locales/*.json`, bukan hardcode).
- Kalau ada gap/ambiguitas (references/*.html kosong untuk shell tertentu,
  fitur di prd.md tidak jelas cara implementasinya, dst) - JANGAN mengarang.
  Laporkan sebagai temuan di Tahap 1, tunggu keputusan project owner.

TAHAP 1 - AUDIT & RENCANA (read-only, TIDAK menulis kode)
-----------------------------------------------------------
1. Baca `CLAUDE.md`, `docs/prd.md`, `docs/branding.md` secara lengkap.
2. Baca seluruh isi `references/*.html` - untuk SETIAP file, catat: daftar
   section/komponen UI, dan teks yang perlu diekstrak jadi key i18n.
3. Cek kelengkapan struktur WAJIB sesuai CLAUDE.md Section 4 & 3f. Laporkan
   status ADA/BELUM ADA untuk masing-masing:
   - `include/config.php` (baca `.env`, define constants termasuk
     `APP_DISPLAY_NAME`, `APP_TAGLINE`, `APP_ENV`, `DB_MODE`, dst)
   - `core/router.php` (router asli) dan `public/core/router.php` (proxy,
     lihat Section 3f)
   - `modules/auth/login.php`, `register.php`, `logout.php`
   - `data/users.json` (bukan `.example`-nya)
   - `.env` (bukan `.env.example`-nya)
4. Buat SATU file output: `docs/build_plan.md`, berisi:
   - Tabel: Shell -> file `references/` acuan -> status (belum dibuat /
     draft / lengkap)
   - Daftar file WAJIB yang belum ada (dari poin 3) beserta rencana isinya
   - Daftar environment variable yang perlu diisi di `.env` (ambil nama
     app/tagline dari `docs/prd.md` Section 1)
   - Daftar warna/font yang perlu dipetakan dari `docs/branding.md` Section 4
     ke `public/assets/css/branding.css`

BERHENTI DI SINI. Tunggu project owner review dan approve `docs/build_plan.md`
sebelum lanjut TAHAP 2.

TAHAP 2 - EKSEKUSI ONE-SHOT (setelah build_plan.md disetujui)
------------------------------------------------------------------
1. `.env`: copy dari `.env.example`, isi `APP_DISPLAY_NAME` dan `APP_TAGLINE`
   sesuai `docs/prd.md` Section 1. Set `APP_ENV="development"`.
2. `public/assets/css/branding.css`: update CSS variables (`--brand-gold`,
   `--bg-primary`, dst) sesuai palet warna final di `docs/branding.md`
   Section 4.
3. Buat file WAJIB yang masih kosong dari hasil Tahap 1 poin 3, mengikuti
   konvensi CLAUDE.md (path resolution Section 12e, router proxy pattern
   Section 3f, template shell baru Section 12e).
4. `data/users.json`: copy dari `data/users.json.example`, generate hash
   Argon2ID ASLI untuk password `password123` (command ada di CLAUDE.md
   Section 6b). JANGAN pakai hash placeholder `$argon2id$...$...`.
5. Untuk SETIAP shell (`public/index.php`, `login/`, `register/`,
   `manajemen/`, `admin/`, `client/`):
   a. Copy struktur & styling dari `references/*.html` yang sesuai (mapping
      di CLAUDE.md Section 3e)
   b. Tambahkan PHP header standar (Section 12e): require `config.php`,
      `helper.php`, panggil `initSession()`
   c. GANTI semua teks statis jadi `<?= t('key') ?>` - tambahkan key barunya
      ke SEMUA `locales/*.json` (id, en, DAN ar - bukan cuma id.json)
   d. GANTI nama aplikasi hardcode jadi `<?= APP_DISPLAY_NAME ?>`
   e. Validasi session/role sesuai CLAUDE.md Section 3b
6. Validasi sintaks: jalankan `php -l` untuk SETIAP file `.php` yang dibuat
   atau diubah di langkah ini. Tempel hasilnya apa adanya di laporan. Error
   WAJIB diperbaiki dulu sebelum lanjut ke poin berikutnya.

BERHENTI DI SINI. Laporkan hasil TAHAP 2 (daftar file dibuat/diubah + hasil
`php -l` lengkap) dan tunggu approval sebelum lanjut TAHAP 3.

TAHAP 3 - PREVIEW LOKAL
--------------------------
1. Pastikan folder project berada di dalam `www/` Laragon (atau `htdocs/`
   XAMPP - sesuaikan langkah di bawah kalau pakai XAMPP).
2. **Langkah manual WAJIB dilakukan project owner sendiri lewat GUI Laragon**
   (CLI tidak bisa melakukan ini):
   a. Klik kanan tray icon Laragon -> pastikan "Auto Virtual Hosts" aktif
   b. Restart Apache/Nginx dari Laragon
3. **Document root harus mengarah ke folder `public/`**, bukan root project
   (lihat CLAUDE.md Section 3f). Kalau Laragon auto vhost default mengarah
   ke root project, project owner WAJIB set manual: klik kanan nama site di
   menu Laragon -> Edit sites-enabled/vhost -> ubah `root`/`DocumentRoot` ke
   path `.../public`. JANGAN klaim langkah ini otomatis selesai tanpa
   konfirmasi eksplisit dari project owner bahwa sudah dilakukan.
4. Informasikan ke project owner: URL preview yang seharusnya bisa diakses
   adalah `http://<nama-folder-project>.test/`
5. Laporkan checklist manual berikut - ini WAJIB diverifikasi project owner
   sendiri di browser, CLI TIDAK BOLEH mengklaim sudah lolos:
   - [ ] Landing page tampil sesuai struktur `references/landingpage.html`
   - [ ] Tombol quick-login demo (dev-only) berhasil masuk ke masing-masing
     role (manajemen/admin/client)
   - [ ] Ganti bahasa (id/en/ar) mengubah SEMUA teks, termasuk konten yang
     di-inject lewat JavaScript (lihat CLAUDE.md Section 12d)
   - [ ] Logout mengarah balik ke landing page tanpa render HTML apapun
     (Section 12f)

CATATAN
-------
Section CLAUDE.md yang relevan tapi tidak dibahas eksplisit di atas tetap
WAJIB diikuti selama proses ini (contoh: Section 8 keamanan, Section 12h
checklist validasi sintaks).
