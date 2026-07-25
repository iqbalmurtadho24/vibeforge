# Plan Implementasi: Perbaikan Branding & Role Management

## Konteks
Terdapat ketidakkonsistenan antara dokumentasi dan implementasi mengenai branding warna dan definisi role pengguna.
- Warna primary di `branding.md` (#F97316) berbeda dengan kode (#FF6B35).
- Definisi role `admin` di `CLAUDE.md` bertentangan antara istilah "Admin" dan "Creator", serta scope akses yang seharusnya tidak "Super Admin".

## Rencana Perubahan

### 1. Sinkronisasi Branding
- Update `public/assets/css/branding.css`:
  - Ubah `--brand-primary` dari `#FF6B35` menjadi `#F97316` (sesuai `docs/branding.md`).
  - Update `--brand-primary-hover` ke shade yang sesuai (misal `#EA580C`).
  - Update `--brand-primary-light` (rgba sesuai).
- Update `public/assets/css/branding.css`: Pastikan `--brand-primary-glow` menggunakan warna baru.
- Update `public/index.php` dan semua file di `references/` yang memiliki `tailwind.config` inline (terutama `brand: { primary: ... }`).

### 2. Penyesuaian Role (Admin != Super Admin)
- Update `CLAUDE.md`:
  - Revisi Section 3c: Perjelas role `admin` adalah Creator, bukan Super Admin.
  - Hapus referensi "Admin" sebagai Super Admin.
- Update `public/admin/index.php`:
  - Ubah teks "Admin Creator" / "Creator Studio" agar lebih konsisten merujuk ke "Creator".
  - Pastikan validasi role hanya mengizinkan `admin`.
- Update `references/modul_admin.html`:
  - Sesuaikan label dari "Admin Creator" menjadi "Creator".

### 3. Verifikasi
- Jalankan `php -l` untuk setiap file yang diubah.
- Test login demo untuk `manajemen`, `admin` (Creator), dan `client`.
- Cek perubahan warna di browser (setelah clear cache).

## File Terkait
- `docs/branding.md`
- `public/assets/css/branding.css`
- `public/*/index.php` (Semua shell)
- `references/*.html`
- `CLAUDE.md`
