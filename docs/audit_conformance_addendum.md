# Addendum Audit: Kesesuaian Konsep & Paritas Keamanan Multi-Mode - Vibeforge
Versi: 2.0 (Generik & Terintegrasi Vibeforge Framework)
Status: Modul audit lanjutan opsional, dijalankan setelah Audit Dasar (`docs/audit_protocol.md`) jika kondisi di Bagian 0 terpenuhi.

---

## 0. Prasyarat Pemakaian

Addendum ini HANYA dijalankan jika minimal satu dari dua kondisi berikut benar:
- Proyek memiliki dokumen governance/konsep/PRD tertulis (`CLAUDE.md`, `docs/prd.md`, `docs/branding.md`, `docs/document.md`).
- Proyek memiliki arsitektur penyimpanan data dual-mode / multi-mode yang menjalankan logika bisnis yang sama (misal Mode JSON vs Mode MySQL via `core/Repo.php`).

Jika kedua kondisi di atas tidak berlaku, HENTIKAN audit addendum ini. Cukup gunakan `docs/audit_protocol.md`.

---

## 0.1 Parameter Konteks (WAJIB diisi sebelum audit dimulai)

- Nama aplikasi: `[Isi dari APP_DISPLAY_NAME / docs/prd.md]`
- Nama file governance doc: `CLAUDE.md`
- Nama file konsep/bisnis tambahan: `docs/prd.md`, `docs/branding.md`, `docs/document.md`
- Arsitektur database multi-mode berlaku? [Ya - Dual Mode Repo: JSON & MySQL]
- Jika Ya, sebutkan nama tiap mode: Mode 1 = `json`, Mode 2 = `mysql`, Mode 3 = `auto`
- Apakah Bagian A (kesesuaian governance) berlaku? [Ya]
- Apakah Bagian B (paritas multi-mode) berlaku? [Ya]
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

## 2. Bagian A: Audit Kesesuaian Konsep vs Governance Doc

*(Jalankan hanya jika Parameter 0.1 mengonfirmasi Bagian A berlaku)*

### Langkah 1: Ekstraksi Aturan
Sebelum membandingkan kode, baca seluruh isi `CLAUDE.md`, `docs/prd.md`, `docs/branding.md`, dan `docs/document.md`. Buat daftar Aturan Eksplisit yang mencakup:
1. Definisi Role & Shell Mapping (`manajemen` -> `/manajemen/`, `admin` -> `/admin/`, `client` -> `/client/`).
2. Aturan arsitektur SPA Shell, Router Proxy Pattern (`public/core/router.php`), dan Entry Guard dua pola.
3. Ketentuan dual-mode Repo (`core/Repo.php`), i18n multi-bahasa, dan variabel CSS branding.

### Langkah 2: Verifikasi Kesesuaian Kode
Cocokkan setiap Aturan Eksplisit dengan kode aktual. Buat tabel:
`Aturan | Status (Sesuai/Sebagian/Tidak Sesuai/Tidak Dapat Diverifikasi) | Bukti (file:baris) | Catatan`

---

## 3. Bagian B: Paritas Keamanan Antar Mode Penyimpanan (JSON vs MySQL)

*(Jalankan jika Mode Dual-Mode JSON & MySQL aktif)*

Periksa setara/tidaknya kontrol keamanan berikut di `core/Repo.php` dan modul pemanggil:

1. **Tenant / Scope Enforcement**: Apakah pembatasan akses data user/role konsisten baik saat data dibaca dari file JSON maupun tabel MySQL?
2. **Input Sanitization & Injection Protection**:
   - Mode SQL: Apakah selalu menggunakan PDO Prepared Statements?
   - Mode JSON: Apakah input tervalidasi tipe & kondisinya sebelum disimpan ke file JSON?
3. **Atomic Write & Concurrency Locking**:
   - Mode JSON: Apakah penulisan data menggunakan file lock terpisah (`{entitas}.json.lock`) dengan `flock(LOCK_EX)` serta atomic rename via temporary file untuk mencegah corrupt data?
   - Mode SQL: Apakah operasi multi-step menggunakan transaksi PDO?
4. **Rate Limiting & Counters**: Apakah counter percobaan login / rate limit disimpan dengan race-condition-safe di kedua mode?
5. **Lokasi File JSON**: Pastikan file `data/*.json` TIDAK dapat diakses langsung via browser HTTP GET (terlindung oleh `.htaccess` dan diluar webroot jika memungkinkan).
6. **Logging & Audit Trail**: Apakah audit trail (`data/audit_trail.json` atau tabel `audit_log`) mencatat transaksi POST secara konsisten tanpa peduli mode DB yang aktif?

---

## 4. Bagian C: Integrasi Pihak Ketiga (Opsional)

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
`docs/AUDIT_CONFORMANCE.md`

Struktur Laporan:
1. Konfirmasi Parameter Konteks (dari Section 0.1)
2. Ringkasan Eksekutif Paritas Keamanan & Governance
3. Tabel Audit Kesesuaian Governance (Bagian A)
4. Tabel Paritas Keamanan Dual-Mode Storage (Bagian B)
5. Tabel Integrasi Pihak Ketiga (Bagian C, jika ada)
6. Daftar Temuan Kritis (Blocker) & Rekomendasi Prioritas
