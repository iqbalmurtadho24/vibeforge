## [Nama Aplikasi] - Master Application Concept (Product Document)
Dokumen ini mendeskripsikan secara komprehensif arsitektur produk, fitur inti, model bisnis, dan alur kerja untuk aplikasi ini. Ganti seluruh bagian dalam [ ] sebelum eksekusi/reuse template.

> **Mode Redesain** (lihat `docs/install.md` alur B): kalau dokumen ini
> ditulis ulang dari aplikasi existing, sumber acuannya adalah SELURUH isi
> folder `references/` — *tidak terbatas pada file `.html`*. Folder
> tersebut bisa berisi beberapa subfolder dengan campuran file PHP, JS,
> HTML, bahkan CSS template lengkap dari codebase lama. Baca semuanya
> secara lengkap sebelum menyusun konsep di bawah ini, supaya hasilnya
> mencerminkan aplikasi lama secara utuh, bukan cuma dari potongan HTML.

## 1. Ringkasan Eksekutif (Executive Summary)
[Aplikasi ini adalah ... (jenis produk) yang berfokus pada ... (domain/masalah). Platform ini menjembatani ... (target pengguna) dengan ... (target kreator/mitra/pihak kedua, jika ada).]

## 2. Model Bisnis & Monetisasi (Business Model)
[Sebutkan model bisnis: Freemium, Subscription, Marketplace commission, dll]

Arus Pendapatan (Revenue Streams)
- [Stream 1, misal: Langganan Premium]
- [Stream 2, misal: Iklan/Komisi transaksi]

Distribusi Keuangan (jika relevan, misal ada revenue share ke pihak ketiga)
[Jelaskan skema distribusi/pembayaran ke pihak lain jika model bisnis melibatkan revenue share]

## 3. Ekosistem Platform & Fitur Inti
Aplikasi ini dibagi menjadi 4 ekosistem utama sesuai role mapping di CLAUDE.md Section 3c, untuk memisahkan fokus dan keamanan (Separation of Concerns).

## A. Public Landing Page (Akusisi)
Target: Calon Pengguna.
Tujuan: Edukasi produk, konversi pendaftaran, dan branding.
Fitur Utama:
- [Fitur 1, misal: Hero section & value proposition]
- [Fitur 2, misal: Preview konten/produk populer]
- Form Registrasi/Login

## B. Client App (Konsumsi) — role `client`
Target: [Pengguna akhir/pelanggan]
Tujuan: [Pengalaman inti produk bagi pengguna]
Fitur Utama:
- [Fitur inti 1]
- [Fitur inti 2]
- Manajemen Akun

## C. Admin/Creator Studio (Produksi) — role `admin`
Target: [Kreator/mitra/operator konten, sesuai konteks aplikasi]
Tujuan: Dasbor swalayan untuk [distribusi konten/produk dan analitik]
Fitur Utama:
- [Fitur upload/manajemen konten atau produk]
- Analitik Performa
- [Manajemen keuangan/komisi, jika relevan]

## D. Super Admin / Manajemen (Tata Kelola) — role `manajemen`
Target: Staf Internal.
Tujuan: Kontrol total atas operasional platform.
Fitur Utama:
- Dashboard Overview (KPI bisnis)
- Moderasi Konten
- Approval [kreator/mitra/pengguna]
- User Management

## 4. Alur Kerja Pengguna (User Lifecycles)
[Jelaskan alur utama pengguna langkah-demi-langkah, misal:]

Alur Utama (Pengguna)
1. [Langkah 1]
2. [Langkah 2]
3. [Langkah 3]

Alur [Kreator/Admin, jika relevan]
1. [Langkah 1]
2. [Langkah 2]
