# Addendum Audit: Kesesuaian Konsep & Paritas Keamanan Multi-Mode - Vibeforge

Versi: 3.3 (Generik, Terintegrasi Vibeforge Framework & 13 Pilar Software Architecture)
Status: Modul audit lanjutan opsional, dijalankan setelah Audit Dasar (`docs/audit_protocol.md`)

---

## 0. Prasyarat Pemakaian

Addendum ini HANYA dijalankan jika minimal satu dari dua kondisi berikut benar:
- Proyek memiliki dokumen governance/konsep/PRD tertulis (`CLAUDE.md`, `docs/prd.md`, `docs/branding.md`, `docs/install.md`).
- Proyek memiliki arsitektur penyimpanan data dual-mode / multi-mode yang menjalankan logika bisnis yang sama (misal Mode JSON vs Mode MySQL via `core/Repo.php`).

Jika kedua kondisi di atas tidak berlaku, HENTIKAN audit addendum ini. Cukup gunakan `docs/audit_protocol.md`.

---

## 0.1 Parameter Konteks (WAJIB diisi sebelum audit dimulai)

- Nama aplikasi: `[Isi dari APP_DISPLAY_NAME / docs/prd.md]`
- Nama file governance doc: `CLAUDE.md` (13 Pilar Software Architecture)
- Nama file konsep/bisnis tambahan: `docs/prd.md`, `docs/branding.md`, `docs/install.md`
- Arsitektur database multi-mode berlaku? [Ya - Dual Mode Repo: JSON & MySQL via `core/Repo.php`]
- Jika Ya, sebutkan nama tiap mode: Mode 1 = `json`, Mode 2 = `mysql`, Mode 3 = `auto`
- Apakah Bagian A (kesesuaian governance 13 Pilar) berlaku? [Ya]
- Apakah Bagian B (paritas multi-mode & i18n GeoIP) berlaku? [Ya]
- Apakah proyek memakai Median.co (WebView-to-APK wrapper)? [Ya/Tidak - Cek `docs/prd.md`]
- Apakah proyek memakai OneSignal / FCM (push notification)? [Ya/Tidak - Cek `docs/prd.md`]
- Apakah Bagian C (integrasi pihak ketiga) berlaku? [Ya/Tidak]

---

## 1. Aturan Kerja

- READ-ONLY. Jangan mengubah kode apapun saat audit.
- Setiap klaim kesesuaian atau ketidaksesuaian WAJIB disertai bukti konkret: file, baris, cuplikan kode.
- Jika sebuah item tidak bisa diverifikasi dari kode saja, tulis "TIDAK DAPAT DIVERIFIKASI SECARA STATIS" dan jelaskan alasannya.
- BACA logika internal, jangan menyimpulkan hanya dari nama fungsi/file.

---

## 2. Bagian A: Audit Kesesuaian Konsep vs Governance Doc (13 Pilar)

*(Jalankan hanya jika Parameter 0.1 mengonfirmasi Bagian A berlaku)*

### Langkah 1: Ekstraksi Aturan

Sebelum membandingkan kode, baca seluruh isi `CLAUDE.md`, `docs/prd.md`, `docs/branding.md`, dan `docs/install.md`. Buat daftar Aturan Eksplisit yang mencakup:

1. Pemetaan 13 Pilar Software dalam 6 Lapisan (Inti, Keamanan, Tempat & Tenaga, Alur Kerja Aman, Performa & Skala, Keandalan).
2. Definisi Role & Shell Mapping (`manajemen` -> `/manajemen/`, `admin` -> `/admin/`, `client` -> `/client/`).
3. Aturan arsitektur SPA Shell, Router Proxy Pattern (`public/core/router.php`), dan Dual-Pattern Entry Guard (Pola 1 Entry `define()` vs Pola 2 Module strict check).
4. Ketentuan dual-mode Repo (`core/Repo.php`), i18n GeoIP + fallback + DB preference, dan variabel CSS branding.
5. Pembedaan `cache/debug.log` (dev debug) vs `data/audit_trail.json` (append-only audit log).

### Langkah 2: Verifikasi Kesesuaian Kode

Cocokkan setiap Aturan Eksplisit dengan implementasi aktual di kode. Tandai status untuk setiap aturan:
- ✅ SESUAI: Implementasi mengikuti aturan
- ❌ TIDAK SESUAI: Implementasi menyimpang dari aturan
- ⚠️ PERLU VERIFIKASI: Tidak dapat diverifikasi secara statis

### Langkah 3: Dokumentasikan Temuan

Untuk setiap ketidaksesuaian, dokumentasikan:
- File dan lokasi (path:line)
- Aturan yang dilanggar (referensi ke section CLAUDE.md)
- Kondisi aktual vs kondisi yang diharapkan
- Dampak dan risiko

---

## 3. Bagian B: Paritas Keamanan Dual-Mode Storage & Sistem i18n

*(Jalankan hanya jika Parameter 0.1 mengonfirmasi Bagian B berlaku)*

### B.1 Paritas Fungsi Keamanan & Persistensi Preferensi

- [ ] Apakah hash password (Argon2ID) konsisten di mode JSON dan SQL?
- [ ] Apakah validasi CSRF token sama kuat di kedua mode?
- [ ] Apakah rate limiting (fixed-window IP+Username) berfungsi identik untuk kedua mode?
- [ ] Apakah prepared statements wajib di mode SQL (tanpa fallback ke query mentah)?
- [ ] Apakah preferensi pengguna (`theme_preference` dan `language_preference`) tersimpan secara permanen di entitas `users` baik mode JSON maupun SQL via `Repo`?

### B.2 Paritas Audit Trail & Logging (Pilar 12)

- [ ] Apakah setiap aksi sensitif (login, ubah data, hapus) dicatat ke `data/audit_trail.json` di kedua mode?
- [ ] Apakah format log audit konsisten (`timestamp`, `user_id`/`email`, `action`, `ip_address`)?
- [ ] Apakah audit trail APPEND-ONLY di kedua mode dan DILARANG dihapus?
- [ ] Apakah `cache/debug.log` terisolasi hanya untuk PHP error/notice saat `APP_DEBUG=true`?

### B.3 Paritas Data Integrity & Atomic Write (Pilar 13)

- [ ] Apakah validasi input sama ketatnya untuk kedua mode?
- [ ] Apakah error handling konsisten (tidak membocorkan informasi sensitif)?
- [ ] Apakah transaksi atomic dijamin di kedua mode (file lock `.lock` + temporary write & `rename()` untuk JSON, PDO transaction untuk SQL)?

### B.4 Paritas Deteksi Bahasa GeoIP & Smart Fallback i18n

- [ ] Apakah `detectLanguage()` mengevaluasi 4 tingkatan prioritas:
  1. Parameter URL (`?lang=xx`) → update session & simpan `language_preference` ke DB.
  2. Active Session (`$_SESSION['language']`).
  3. Database preference dari user logged-in.
  4. IP Country Code GeoIP Mapping.
- [ ] Apakah pengunjung dari 22 negara Liga Arab terdeteksi otomatis dan diarahkan ke Bahasa Arab (`'ar'`)?
- [ ] Apakah IP negara terdaftar di manifest terarah ke kode bahasa yang sesuai (`id`, `ja`, `en`, `ar`)?
- [ ] Apakah IP negara tidak terdaftar secara cerdas fallback ke Bahasa Arab (`'ar'`) jika dari negara Arab, atau ke Bahasa Inggris (`'en'`) untuk negara lainnya?

---

## 4. Bagian C: Integrasi Pihak Ketiga (Opsional)

*(Jalankan hanya jika Parameter 0.1 mengonfirmasi integrasi pihak ketiga berlaku)*

### C.1 Median.co (WebView App)

- [ ] Pilihan plugin audio background sesuai spesifikasi (Native Media Player `median.backgroundMedia`).
- [ ] Bebas dari full-page reload pada flow utama agar media player tidak terputus.
- [ ] Pull-to-refresh dikonfigurasi / disabled.

### C.2 Push Notifications (OneSignal / FCM)

- [ ] Credentials & API Key disimpan di `.env` server-side, bukan hardcoded di client.
- [ ] User identifier (External ID) aman dan tidak membocorkan data sensitif.

---

## 5. Output Wajib Laporan Audit

Hasil audit addendum ini ditulis ke file laporan baru:
**`docs/AUDIT_CONFORMANCE.md`**

Struktur Laporan:

1. **Konfirmasi Parameter Konteks** (dari Section 0.1)
2. **Ringkasan Eksekutif Paritas Keamanan & Governance 13 Pilar**
3. **Tabel Audit Kesesuaian Governance** (Bagian A)
4. **Tabel Paritas Keamanan Dual-Mode Storage & i18n GeoIP** (Bagian B)
5. **Tabel Integrasi Pihak Ketiga** (Bagian C, jika ada)
6. **Daftar Temuan Kritis (Blocker) & Rekomendasi Prioritas**

---

## 6. Referensi

- Audit Dasar: `docs/audit_protocol.md`
- Konstitusi Teknis: `CLAUDE.md`
- Definisi Produk: `docs/prd.md`
- Identitas Visual: `docs/branding.md`
- Protokol Instalasi: `docs/install.md`
