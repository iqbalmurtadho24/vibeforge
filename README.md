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

Lalu unduh Vibeforge (ganti `username` dengan akun GitHub Anda sendiri):

```bash
npx degit username/vibeforge nama-aplikasi-saya
cd nama-aplikasi-saya
code .
```

Folder yang muncul adalah salinan bersih Vibeforge — *tanpa riwayat git*, siap dipakai sebagai project baru.

---

## Langkah 2 — Isi identitas aplikasi Anda (WAJIB, sebelum lanjut)

Vibeforge ini generik. Sebelum AI mulai membangun apa pun, isi dulu 3 hal berikut supaya AI tahu persis aplikasi apa yang Anda maksud:

1. *`docs/prd.md`* — jelaskan aplikasi Anda: nama, fitur utama, peran pengguna (siapa saja aktornya), model bisnis.
2. *`docs/branding.md`* — identitas visual: nama brand, warna, font, logo.
3. *`references/*.html`* — 6 file HTML ini adalah *contoh pola struktur & styling* dari aplikasi referensi. Kalau aplikasi Anda beda topik/konten, sesuaikan teks dan komponen di dalamnya agar mencerminkan aplikasi Anda. AI membaca file ini sebagai *acuan struktur*, bukan konten final yang disalin mentah.

> Jangan lewati langkah ini. Kalau `prd.md`/`branding.md` dibiarkan kosong, AI akan berhenti dan balik bertanya ke Anda — itu memang disengaja, supaya AI tidak menebak-nebak konsep aplikasi Anda.

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

## Setelah selesai

Buka aplikasi Anda di:

```
http://nama-aplikasi-saya.test/
```

(sesuai nama folder project, mengikuti konvensi Auto Virtual Host Laragon — atau URL sesuai konfigurasi XAMPP Anda).

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
