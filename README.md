<img width="2816" height="1536" alt="Gemini_Generated_Image_6v8hra6v8hra6v8h" src="https://github.com/user-attachments/assets/97316596-8376-43b5-b5d3-c4bf913841e4" />

# 🔥 Vibeforge

*Tempa aplikasi Anda dari dokumen ke kode jadi — dengan bantuan AI.*

Vibeforge adalah template starter untuk membangun aplikasi web (PHP) dengan pendekatan *vibe coding*: Anda menjelaskan aplikasi yang diinginkan lewat dokumen (`docs/prd.md`, `docs/branding.md`), lalu AI coding assistant (Claude Code, Cursor, Copilot CLI, dst) yang menempanya secara terstruktur mengikuti `docs/install.md`.

Cocok dipakai dengan *XAMPP* (`htdocs/`) atau *Laragon* (`www/`).

---

## Prasyarat

Sebelum mulai, pastikan sudah terpasang:

- *Node.js* — hanya dipakai untuk mengunduh template lewat `npx`, bukan untuk menjalankan aplikasinya
- *XAMPP* atau *Laragon* — sudah terpasang dan bisa dijalankan
- Salah satu *AI coding CLI*: [Claude Code](https://code.claude.com), Cursor, GitHub Copilot CLI, atau sejenisnya
- *VS Code* (opsional, tapi disarankan agar terminal & editor jadi satu tempat)

---

## Langkah 1 — Download template

*Arahkan terminal ke folder `htdocs`/`www` terlebih dahulu*, karena command di bawah akan membuat folder project persis di lokasi terminal Anda saat itu.

```bash
# Laragon
cd C:\laragon\www

# atau XAMPP
cd C:\xampp\htdocs
```

> 💡 *Path di atas cuma contoh default.* Laragon/XAMPP bisa saja terinstal di drive lain (`D:\laragon\www`, `E:\xampp\htdocs`, dst), tergantung pilihan Anda saat instalasi. Kalau tidak yakin lokasinya, klik kanan *ikon tray Laragon* → klik *"www"* (atau buka shortcut XAMPP Control Panel → cek path instalasi) untuk melihat folder aslinya di File Explorer, lalu sesuaikan drive di command `cd` Anda.
>
> Di PowerShell, pindah drive cukup dengan `cd D:\laragon\www` langsung (beda dengan Command Prompt/`cmd.exe` yang butuh `cd /d D:\laragon\www`).

Lalu unduh Vibeforge — command ini bisa langsung di-copy-paste apa adanya, *cukup ganti `<nama-aplikasi-anda>`* dengan nama project Anda sendiri (contoh: `toko-online-saya`):

```bash
npx -y degit iqbalmurtadho24/vibeforge <nama-aplikasi-anda>
cd <nama-aplikasi-anda>
```

> 💡 Flag `-y` membuat `npx` langsung setuju mengunduh package kecil `degit` tanpa menampilkan prompt konfirmasi (`Ok to proceed? (y)`) — ini hanya muncul sekali di komputer yang belum pernah pakai `npx degit` sebelumnya. Tanpa `-y`, kalau Anda paste beberapa baris command sekaligus, baris berikutnya bisa "termakan" jadi jawaban untuk prompt itu alih-alih dijalankan sebagai command terpisah.

> ⚠️ Bagian `iqbalmurtadho24/vibeforge` *JANGAN diubah* — itu alamat resmi repo Vibeforge. Yang perlu Anda ganti hanya `<nama-aplikasi-anda>` (termasuk tanda `<` `>`-nya, jangan ikut disalin).

Folder yang muncul adalah salinan bersih Vibeforge — *tanpa riwayat git*, siap dipakai sebagai project baru.

---

## Langkah 2 — Siapkan Identitas & Konsep Aplikasi (WAJIB, sebelum lanjut)

Jawaban atas pertanyaan ini menentukan alur yang diambil:

### A. Jika MEMBUAT aplikasi baru

Isi 3 hal berikut supaya AI tahu persis aplikasi yang Anda maksud:

1. *`docs/prd.md`* — jelaskan aplikasi Anda: nama, fitur utama, peran
   pengguna (siapa saja aktornya), model bisnis.
2. *`docs/branding.md`* — identitas visual: nama brand, warna, font, logo.
3. *`references/*.html`* — 6 file HTML ini adalah *contoh pola struktur &
   styling* dari aplikasi referensi. Kalau aplikasi Anda beda topik/konten,
   sesuaikan teks dan komponen di dalamnya agar mencerminkan aplikasi Anda.
   AI membaca file ini sebagai *acuan struktur*, bukan konten final yang
   disalin mentah.

> Jangan lewati langkah ini. Kalau `prd.md`/`branding.md` dibiarkan
> kosong, AI akan berhenti dan balik bertanya ke Anda — itu memang
> disengaja, supaya AI tidak menebak-nebak konsep aplikasi Anda.

### B. Jika MEREDESAIN aplikasi yang sudah ada

Aplikasi sudah pernah dibuat dan Anda ingin meredesain. AI perlu memahami
konsep lama terlebih dahulu:

1. Pastikan folder `references/*.html` berisi salinan/concept dokumen
   aplikasi lama Anda (struktur HTML, styling, navigasi) — ini yang akan
   dibaca AI sebagai acuan.
2. AI akan **menulis ulang** `docs/prd.md` dan `docs/branding.md`
   berdasarkan apa yang dibaca di `references/*.html`, mengikuti format
   placeholder `[...]` yang sudah ada di masing-masing file.
3. Sesuaikan `references/*.html` agar mencerminkan versi baru setelah
   `prd.md`/`branding.md` terisi.

> **Catatan penting:** `docs/prd.md` dan `docs/branding.md` adalah
> **satu-satunya** sumber kebenaran untuk konsep dan identitas aplikasi.
> `references/*.html` hanya menggambarkan struktur dan styling — bukan
> teks final. Semua isi aplikasi (nama, warna, deskripsi) WAJIB
> didefinisikan di `prd.md`/`branding.md`.

---

## Langkah 3 — Jalankan AI coding assistant

Di terminal (masih di dalam folder project), jalankan AI CLI pilihan Anda, misalnya:

```bash
claude
```

Setelah sesi terbuka, ketik:

```
Baca dan jalankan docs/install.md
```

---

## ⚠️ PENTING — proses ini BUKAN "tekan yes sampai selesai"

`docs/install.md` sengaja dirancang berhenti di *3 tahap*, masing-masing menunggu keputusan Anda:

| Tahap | Yang dilakukan AI | Yang Anda lakukan |
|---|---|---|
| *1 — Audit & Rencana* | Baca `CLAUDE.md`/`prd.md`/`branding.md`/`references/`, cek kelengkapan struktur, tulis `docs/build_plan.md` | *Review* `build_plan.md`, approve sebelum lanjut |
| *2 — Eksekusi Kode* | Buat `.env`, `data/users.json`, seluruh shell PHP, jalankan `php -l` | *Cek* hasil validasi `php -l`, approve sebelum lanjut |
| *3 — Preview Lokal* | Hanya memberi instruksi — tidak bisa eksekusi apa pun di sini | *Wajib dilakukan manual*: buka Laragon/XAMPP, aktifkan Auto Virtual Host, restart Apache, arahkan document root ke folder `public/`, lalu cek sendiri di browser |

Baca laporan tiap tahap sebelum lanjut — jangan asal setuju tanpa dicek, terutama Tahap 1 (rencana file yang akan dibuat) dan Tahap 2 (hasil validasi sintaks). *Tahap 3 tidak bisa "di-yes"* sama sekali karena isinya langkah GUI yang hanya bisa Anda lakukan sendiri.

### Tips: kurangi gangguan approval per-file (opsional)

Supaya AI tidak berhenti minta izin untuk setiap file kecil *di dalam* satu tahap, aktifkan mode auto-approve edit:

```bash
claude --permission-mode acceptEdits
```

atau tekan `Shift+Tab` di dalam sesi yang sedang berjalan. Ini hanya mengurangi prompt per-file — tetap lakukan review Anda sendiri di setiap *batas tahap* seperti tabel di atas.

---

## Troubleshooting (Windows)

Tiga error paling umum yang biasa muncul di Windows, beserta solusinya:

*❌ `git : The term 'git' is not recognized...`*
Git belum ada di PATH sistem. Kalau pakai Laragon: klik kanan ikon tray Laragon → *Tools* → *PATH* → *Add Laragon to PATH*, lalu tutup dan buka ulang terminal/VS Code. Kalau masih gagal, install [Git for Windows](https://git-scm.com/download/win) langsung (pilih opsi default saat instalasi, itu sudah otomatis menambahkan ke PATH).

*❌ `npx : File ...npx.ps1 cannot be loaded because running scripts is disabled on this system`*

Ini *khusus terjadi di PowerShell* — bukan di Command Prompt (`cmd.exe`). Cek dulu terminal mana yang Anda pakai: kalau prompt-nya diawali `PS C:\...>` berarti PowerShell (kemungkinan ini yang kena error di atas); kalau cuma `C:\...>` tanpa `PS`, berarti Command Prompt, dan error ini *tidak akan muncul* di sana sama sekali (jadi tidak perlu langkah di bawah ini).

Kalau Anda memang di PowerShell, jalankan perintah berikut *di PowerShell itu sendiri* (perintah ini adalah cmdlet PowerShell — tidak akan dikenali kalau dicoba di Command Prompt):
```powershell
Set-ExecutionPolicy -Scope CurrentUser -ExecutionPolicy RemoteSigned
```
Ketik `Y` saat diminta konfirmasi. Setelah ini, `npx`, `npm`, dan tool berbasis Node.js lain akan berjalan normal di PowerShell.

> 💡 Kalau tidak mau ubah setting apa pun, alternatifnya: pakai Command Prompt saja untuk perintah `npx degit` (buka lewat Start Menu → ketik `cmd`), atau di dalam PowerShell jalankan `cmd /c "npx degit ..."` untuk satu baris itu saja.

*❌ `Cannot find path 'C:\laragon\www' because it does not exist`*
Laragon/XAMPP Anda mungkin terinstal di drive lain (bukan `C:`). Klik kanan ikon tray Laragon → klik *"www"* untuk membuka folder aslinya di File Explorer dan lihat drive yang benar di address bar (misal `D:\laragon\www`), lalu sesuaikan command `cd` Anda.

---

## Setelah selesai

Buka aplikasi Anda di:

```
http://<nama-aplikasi-anda>.test/
```

(ganti `<nama-aplikasi-anda>` dengan nama folder project yang Anda pakai di Langkah 1 — mengikuti konvensi Auto Virtual Host Laragon, atau URL sesuai konfigurasi XAMPP Anda).

---

## Struktur penting

```
docs/
  install.md      ← protokol kerja untuk AI (3 tahap, lihat di atas)
  prd.md           ← WAJIB diisi (Langkah 2)
  branding.md      ← WAJIB diisi (Langkah 2)
references/        ← 6 file HTML, pola struktur — sesuaikan isinya (Langkah 2)
CLAUDE.md          ← aturan arsitektur & konvensi kode (dibaca AI di awal Tahap 1)
```

---

*Selamat menempa aplikasi Anda dengan Vibeforge.* 🔥
