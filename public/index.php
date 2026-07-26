<?php
/**
 * Vibeforge Landing Page
 * Full informational page with installation guide
 */

defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/helper.php';
require_once __DIR__ . '/../core/session.php';
require_once __DIR__ . '/../core/csrf.php';

initSession();

if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
    $_SESSION['language'] = $_GET['lang'];
}

$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$isRtl = isRtlLanguage();

$csrfToken = generateCsrfToken();
$isLoggedIn = isLoggedIn();
$user = getCurrentUser();
$dashboardUrl = getDashboardUrl();
$themePreference = $user['theme_preference'] ?? 'dark';
$isDev = APP_ENV !== 'production';

$projectRoot = dirname(__DIR__);

// Auto-detect drive letter and server type for interactive installer
$detectedDrive = strtoupper(substr($projectRoot, 0, 1));
if (!preg_match('/^[A-Z]$/', $detectedDrive)) {
    $detectedDrive = 'C';
}

$detectedServer = 'laragon';
$normalizedProjectRoot = str_replace('\\', '/', strtolower($projectRoot));

if (str_contains($normalizedProjectRoot, 'xampp')) {
    $detectedServer = 'xampp';
} elseif (str_contains($normalizedProjectRoot, 'laragon')) {
    $detectedServer = 'laragon';
} else {
    $drivePrefix = $detectedDrive . ':/';
    if (is_dir($drivePrefix . 'xampp/htdocs')) {
        $detectedServer = 'xampp';
    } elseif (is_dir($drivePrefix . 'laragon/www')) {
        $detectedServer = 'laragon';
    }
}

$availableDrives = [];
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
    foreach (range('A', 'Z') as $letter) {
        if (is_dir($letter . ':\\')) {
            $availableDrives[] = $letter;
        }
    }
}
if (empty($availableDrives)) {
    $availableDrives = ['C', 'D', 'E', 'F', 'G', 'H'];
}
if (!in_array($detectedDrive, $availableDrives, true)) {
    $availableDrives[] = $detectedDrive;
    sort($availableDrives);
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_DISPLAY_NAME ?> - <?= escape(APP_TAGLINE) ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FF6B35'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Suppress Tailwind CDN production warning
        const origWarn = console.warn;
        console.warn = function(...args) {
            if (args[0] && typeof args[0] === 'string' && args[0].includes('cdn.tailwindcss.com should not be used in production')) return;
            origWarn.apply(console, args);
        };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/branding.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: { brand: { primary: '#F97316', dark: '#0D1117', card: '#161B22' } },
                    fontFamily: { sans: ['Inter', 'sans-serif'], heading: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-primary); color: var(--text-primary); }
        h1, h2, h3 { font-family: 'Poppins', sans-serif; }
        .text-gradient { background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .bg-gradient-brand { background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%); }
        .glow-orange { box-shadow: 0 0 40px rgba(255, 107, 53, 0.3); }
        .glow-orange-sm { box-shadow: 0 0 20px rgba(255, 107, 53, 0.2); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased">

    <!-- Navbar -->
    <nav class="fixed top-0 w-full z-50 bg-[var(--bg-secondary)]/90 backdrop-blur-md border-b border-[var(--border-default)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-2">
                    <svg width="28" height="28" viewBox="0 0 24 24" fill="#F97316">
                        <path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/>
                    </svg>
                    <span class="font-heading font-bold text-xl">
                        <span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span>
                    </span>
                </a>

                <div class="hidden md:flex items-center gap-8">
                    <a href="#fitur" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">Fitur</a>
                    <a href="#cara-pasang" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">Cara Pasang</a>
                    <a href="#demo" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">Demo</a>
                    <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1">
                        <i class="ph ph-github-logo"></i> GitHub
                    </a>
                    <a href="/install/" class="px-4 py-2 bg-[var(--brand-primary)] text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity shadow-lg glow-orange-sm">
                        <i class="ph ph-magic-wand mr-1"></i> Setup Wizard
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Language Selector -->
                    <div class="relative group" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg bg-[var(--bg-card)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors text-xs font-medium" aria-label="Change Language">
                            <img src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" alt="<?= $currentLang ?>" class="w-5 h-3.5 rounded-sm shadow-sm">
                            <span class="hidden sm:inline uppercase font-bold text-[var(--text-secondary)]"><?= escape($currentLang) ?></span>
                            <i class="ph ph-caret-down text-xs text-[var(--text-muted)]"></i>
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 mt-1 bg-[var(--bg-card)] rounded-xl shadow-2xl border border-[var(--border-default)] py-1 min-w-[150px] z-50">
                            <?php foreach (getAvailableLanguages() as $code => $lang): ?>
                            <a href="?lang=<?= $code ?>" class="flex items-center gap-2.5 px-3 py-2 text-xs hover:bg-[var(--bg-hover)] transition-colors <?= $currentLang === $code ? 'text-[var(--brand-primary)] font-bold bg-[var(--brand-primary-light)]/10' : 'text-[var(--text-secondary)]' ?>">
                                <img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-5 h-3.5 rounded-sm">
                                <span><?= escape($lang['name']) ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="w-9 h-9 rounded-lg bg-[var(--bg-card)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] flex items-center justify-center transition-colors" aria-label="Toggle theme">
                        <i class="ph ph-moon text-base text-[var(--text-muted)] dark:text-yellow-400"></i>
                    </button>
                    <?php if ($isLoggedIn): ?>
                    <a href="<?= $dashboardUrl ?>" class="px-4 py-2 bg-[var(--brand-primary)] text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">Dashboard</a>
                    <?php else: ?>
                    <a href="/login/" class="px-3.5 py-1.5 text-[var(--text-secondary)] text-sm font-medium rounded-lg hover:bg-[var(--bg-hover)] transition-colors">Masuk</a>
                    <a href="/register/" class="px-4 py-2 bg-gradient-brand text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity shadow-lg glow-orange-sm">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow pt-20">

        <!-- Hero -->
        <section class="py-16 sm:py-24 bg-[var(--bg-primary)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-[var(--brand-primary-light)] border border-[var(--brand-primary)]/20 mb-6">
                        <i class="ph ph-robot text-[var(--brand-primary)]"></i>
                        <span class="text-sm font-medium text-[var(--brand-primary)]">AI-Powered Web Template</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-heading font-bold mb-6 leading-tight">
                        Tempakan Aplikasi Anda<br>
                        <span class="text-gradient">dari Dokumen ke Kode Jadi</span>
                    </h1>

                    <p class="text-lg sm:text-xl text-[var(--text-secondary)] mb-10 max-w-2xl mx-auto">
                        <?= APP_DISPLAY_NAME ?> adalah template starter untuk membangun aplikasi web dengan pendekatan vibe coding: jelaskan aplikasi lewat dokumen, AI coding assistant yang mengubahnya jadi kode fungsional lengkap.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-4 justify-center">
                        <a href="#cara-pasang" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-brand text-white font-bold rounded-xl hover:opacity-90 transition-opacity shadow-lg glow-orange">
                            <i class="ph ph-rocket-launch text-xl"></i> Mulai Pasang Sekarang
                        </a>
                        <a href="/install/" class="inline-flex items-center gap-2 px-8 py-4 bg-[var(--bg-card)] text-[var(--text-primary)] font-medium rounded-xl border border-[var(--brand-primary)] hover:bg-[var(--brand-primary-light)]/10 transition-colors shadow-lg glow-orange-sm">
                            <i class="ph ph-magic-wand text-xl text-[var(--brand-primary)]"></i> Mulai Menyiapkan Instalasi
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Prerequisites -->
        <section class="py-12 bg-[var(--bg-surface)] border-y border-[var(--border-default)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-lg font-heading font-semibold mb-6 text-center">Prasyarat</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-center">
                    <div class="p-4">
                        <i class="ph ph-file-code text-3xl text-[var(--brand-primary)] mb-2 block"></i>
                        <p class="text-sm font-medium">AI Coding CLI</p>
                        <p class="text-xs text-[var(--text-muted)]">Claude/Cursor/Copilot</p>
                    </div>
                    <div class="p-4">
                        <i class="ph ph-browser text-3xl text-[var(--brand-primary)] mb-2 block"></i>
                        <p class="text-sm font-medium">XAMPP / Laragon</p>
                        <p class="text-xs text-[var(--text-muted)]">PHP 8.x runtime</p>
                    </div>
                    <div class="p-4">
                        <i class="ph ph-git-branch text-3xl text-[var(--brand-primary)] mb-2 block"></i>
                        <p class="text-sm font-medium">Git (opsional)</p>
                        <p class="text-xs text-[var(--text-muted)]">Clone repo</p>
                    </div>
                    <div class="p-4">
                        <i class="ph ph-folder-simple text-3xl text-[var(--brand-primary)] mb-2 block"></i>
                        <p class="text-sm font-medium">Folder project</p>
                        <p class="text-xs text-[var(--text-muted)]">htdocs/ atau www/</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features -->
        <section id="fitur" class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <h2 class="text-3xl sm:text-4xl font-heading font-bold text-center mb-4">Kenapa <?= APP_DISPLAY_NAME ?>?</h2>
                <p class="text-center text-[var(--text-secondary)] max-w-2xl mx-auto mb-12">Template yang dirancang untuk developer modern yang ingin bangun aplikasi cepat dengan AI coding assistant.</p>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-all group">
                        <div class="w-14 h-14 rounded-xl bg-[var(--brand-primary-light)] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <i class="ph ph-file-doc text-2xl text-[var(--brand-primary)]"></i>
                        </div>
                        <h3 class="font-heading font-semibold text-lg mb-2">Dokumen ke Kode</h3>
                        <p class="text-sm text-[var(--text-secondary)]">Jelaskan aplikasi lewat docs/prd.md, AI yang mengubahnya jadi kode fungsional lengkap.</p>
                    </div>
                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-all group">
                        <div class="w-14 h-14 rounded-xl bg-[var(--brand-primary-light)] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <i class="ph ph-layout text-2xl text-[var(--brand-primary)]"></i>
                        </div>
                        <h3 class="font-heading font-semibold text-lg mb-2">Template Siap Pakai</h3>
                        <p class="text-sm text-[var(--text-secondary)]">Landing page, login, register, dashboard — semua sudah ada dan tinggal konfigurasi.</p>
                    </div>
                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-all group">
                        <div class="w-14 h-14 rounded-xl bg-[var(--brand-primary-light)] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <i class="ph ph-shield-check text-2xl text-[var(--brand-primary)]"></i>
                        </div>
                        <h3 class="font-heading font-semibold text-lg mb-2">Auth & Security</h3>
                        <p class="text-sm text-[var(--text-secondary)]">Argon2ID, CSRF, rate limiting, remember-me, role-based access.</p>
                    </div>
                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-all group">
                        <div class="w-14 h-14 rounded-xl bg-[var(--brand-primary-light)] flex items-center justify-center mb-5 group-hover:scale-110 transition-transform">
                            <i class="ph ph-palette text-2xl text-[var(--brand-primary)]"></i>
                        </div>
                        <h3 class="font-heading font-semibold text-lg mb-2">Dark/Light Theme</h3>
                        <p class="text-sm text-[var(--text-secondary)]">Tema modern dengan switcher dan CSS variables yang mudah dikustomisasi.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Installation Steps -->
        <section id="cara-pasang" class="py-16 sm:py-24 bg-[var(--bg-surface)] border-y border-[var(--border-default)]">
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <span class="px-3 py-1 bg-[var(--brand-primary-light)] text-[var(--brand-primary)] text-xs font-semibold uppercase tracking-wider rounded-full">Panduan Instalasi & Eksekusi Interaktif</span>
                    <h2 class="text-3xl sm:text-4xl font-heading font-bold mt-3 mb-4">Unduh & Konfigurasi Aplikasi</h2>
                    <p class="text-[var(--text-secondary)] max-w-2xl mx-auto">Pilih jenis server, tentukan nama aplikasi Anda, lalu unduh dan jalankan setup wizard otomatis.</p>
                </div>

                <!-- Interactive App Downloader Component -->
                <div id="appDownloaderComponent" x-data="appDownloader()" class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 sm:p-8 space-y-6 shadow-xl glow-orange-sm mb-12">

                    <form @submit.prevent="checkFolder()" class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <!-- Server Type Selector -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-xs font-bold uppercase text-[var(--text-muted)] tracking-wider">1. Web Server</label>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-green-500/10 text-green-400 border border-green-500/20" title="Terdeteksi Otomatis">
                                    <i class="ph ph-check-circle"></i> Auto: <?= strtoupper($detectedServer) ?>
                                </span>
                            </div>
                            <div class="grid grid-cols-2 gap-2">
                                <button type="button" @click="server = 'laragon'; isSubmitted = false" :class="server === 'laragon' ? 'bg-[var(--brand-primary)] text-white font-bold border-[var(--brand-primary)]' : 'bg-[var(--bg-surface)] text-[var(--text-secondary)] border-[var(--border-default)] hover:bg-[var(--bg-hover)]'" class="py-2.5 px-3 rounded-xl border text-xs font-medium transition-all flex items-center justify-center gap-1.5">
                                    <i class="ph ph-bug text-base"></i> Laragon
                                </button>
                                <button type="button" @click="server = 'xampp'; isSubmitted = false" :class="server === 'xampp' ? 'bg-[var(--brand-primary)] text-white font-bold border-[var(--brand-primary)]' : 'bg-[var(--bg-surface)] text-[var(--text-secondary)] border-[var(--border-default)] hover:bg-[var(--bg-hover)]'" class="py-2.5 px-3 rounded-xl border text-xs font-medium transition-all flex items-center justify-center gap-1.5">
                                    <i class="ph ph-file-css text-base"></i> XAMPP
                                </button>
                            </div>
                        </div>

                        <!-- Disk Drive Selector -->
                        <div>
                            <div class="flex items-center justify-between mb-3">
                                <label class="block text-xs font-bold uppercase text-[var(--text-muted)] tracking-wider">2. Local Disk</label>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20" title="Terdeteksi Otomatis">
                                    <i class="ph ph-hard-drive"></i> Auto: Disk <?= $detectedDrive ?>:
                                </span>
                            </div>
                            <div class="flex items-center gap-1.5 overflow-x-auto pb-1 hide-scrollbar">
                                <template x-for="d in drives" :key="d">
                                    <button type="button" @click="drive = d; isSubmitted = false" :class="drive === d ? 'bg-[var(--brand-primary)] text-white font-bold border-[var(--brand-primary)]' : 'bg-[var(--bg-surface)] text-[var(--text-secondary)] border-[var(--border-default)] hover:bg-[var(--bg-hover)]'" class="px-3 py-2.5 rounded-xl border text-xs font-medium transition-all flex items-center gap-1 shrink-0">
                                        <i class="ph ph-hard-drive"></i> (<span x-text="d"></span>:)
                                    </button>
                                </template>
                            </div>
                        </div>

                        <!-- App Name Input + Submit Button -->
                        <div>
                            <label class="block text-xs font-bold uppercase text-[var(--text-muted)] tracking-wider mb-3">3. Nama Aplikasi <span class="text-red-400">*</span></label>
                            <div class="flex items-center gap-2">
                                <div class="relative flex-1">
                                    <input type="text" x-model="appName" @input="sanitizeAppName(); isSubmitted = false" required placeholder="Tulis nama aplikasimu" pattern="^[a-zA-Z0-9][a-zA-Z0-9_-]*$" title="Hanya huruf, angka, underscore (_), dan hyphen (-). Tidak boleh spasi, titik, koma, atau simbol lain." class="w-full bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl px-4 py-2.5 text-xs text-[var(--text-primary)] focus:outline-none focus:border-[var(--brand-primary)] transition-colors font-mono" :class="{ 'border-red-400': appNameError }">
                                    <i class="ph ph-folder-simple absolute right-3 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"></i>
                                    <template x-if="appNameError">
                                        <div class="absolute bottom-full left-0 mb-1 px-2 py-1 bg-red-500/90 text-white text-[10px] rounded whitespace-nowrap">Gunakan _ atau - sebagai pemisah, tanpa spasi/simbol</div>
                                    </template>
                                </div>
                                <button type="submit" :disabled="!appName.trim() || isChecking || appNameError" class="px-4 py-2.5 bg-gradient-brand text-white text-xs font-bold rounded-xl hover:opacity-90 transition-opacity shadow-md disabled:opacity-50 flex items-center gap-1.5 shrink-0">
                                    <template x-if="!isChecking">
                                        <span class="flex items-center gap-1"><i class="ph ph-paper-plane-right"></i> Proses</span>
                                    </template>
                                    <template x-if="isChecking">
                                        <span class="flex items-center gap-1"><i class="ph ph-circle-notch animate-spin"></i> Mengecek...</span>
                                    </template>
                                </button>
                            </div>
                        </div>
                    </form>

                    <!-- Computed Terminal Box & Next Step CTA (tampil setelah Submit) -->
                    <div x-show="isSubmitted" x-transition class="space-y-4 pt-2">
                        <div class="bg-[var(--bg-primary)] rounded-xl p-4 sm:p-5 border border-[var(--border-default)] space-y-3 relative group shadow-inner">
                            <div class="flex items-center justify-between border-b border-[var(--border-default)] pb-2 text-xs text-[var(--text-muted)] font-mono">
                                <span class="flex items-center gap-1.5"><i class="ph ph-terminal text-sm text-[var(--brand-primary)]"></i> Perintah PowerShell Download (GitHub)</span>
                                <span class="text-[10px] text-green-400 font-sans" x-text="'Target Path: ' + fullTargetDir()"></span>
                            </div>
                            <div class="font-mono text-xs sm:text-sm text-[var(--text-primary)] space-y-1 select-all break-all" x-text="fullCommand()"></div>

                            <div class="flex items-center gap-2 pt-2 flex-wrap">
                                <button type="button" @click="copySnippet($el, fullCommand())" class="px-4 py-2 bg-[var(--bg-surface)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] text-xs font-medium rounded-xl transition-colors flex items-center gap-1.5">
                                    <i class="ph ph-copy"></i> Salin Perintah
                                </button>
                                <?php if ($isDev): ?>
                                <button type="button" @click="executeInteractiveTerminal($el)" class="px-4 py-2 bg-green-500/10 border border-green-500/30 text-green-400 hover:bg-green-500/20 text-xs font-bold rounded-xl transition-colors flex items-center gap-1.5">
                                    <i class="ph ph-download-simple"></i> Unduh & Buka Terminal Otomatis
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Status Folder & Button Setup Wizard -->
                        <div class="p-4 rounded-xl border flex flex-col sm:flex-row items-center justify-between gap-3 transition-all"
                            :class="folderExists ? 'bg-green-500/10 border-green-500/30' : 'bg-amber-500/10 border-amber-500/30'">
                            <div class="flex items-center gap-3">
                                <template x-if="folderExists">
                                    <i class="ph ph-check-circle text-2xl text-green-400 shrink-0"></i>
                                </template>
                                <template x-if="!folderExists">
                                    <i class="ph ph-info text-2xl text-amber-400 shrink-0"></i>
                                </template>
                                <div>
                                    <p class="text-xs font-bold" x-text="folderExists ? 'Folder Aplikasi Sudah Ada!' : 'Folder Belum Dibuat / Belum Diunduh'"></p>
                                    <p class="text-[11px] text-[var(--text-muted)]" x-text="folderExists ? 'Folder target sudah siap. Silakan klik Setup Wizard untuk melanjutkan.' : 'Jalankan perintah di atas / klik Unduh Otomatis untuk mengunduh template ke folder target.'"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <template x-if="!folderExists">
                                    <button type="button" @click="checkFolder()" :disabled="isChecking" class="px-3 py-1.5 bg-[var(--bg-surface)] border border-[var(--border-default)] text-[var(--text-secondary)] rounded-lg text-xs font-medium hover:bg-[var(--bg-hover)] transition-colors disabled:opacity-50 flex items-center gap-1">
                                        <template x-if="!isChecking">
                                            <i class="ph ph-arrows-clockwise"></i> Refresh Status
                                        </template>
                                        <template x-if="isChecking">
                                            <i class="ph ph-circle-notch animate-spin"></i> Mengecek...
                                        </template>
                                    </button>
                                </template>
                                <template x-if="folderExists">
                                    <button type="button" @click="startSetupWizard($el)" class="px-5 py-2.5 bg-gradient-brand text-white text-xs font-bold rounded-xl hover:opacity-90 transition-opacity flex items-center gap-1.5 whitespace-nowrap shadow-md glow-orange-sm">
                                        <i class="ph ph-magic-wand"></i> Masuk Setup Wizard <i class="ph ph-arrow-right"></i>
                                    </button>
                                </template>

<div id="setupTerminal" x-show="showTerminal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/80 backdrop-blur-sm" x-cloak>
    <div class="bg-gray-900 rounded-2xl w-full max-w-2xl border border-gray-700 shadow-2xl overflow-hidden">
        <div class="flex items-center justify-between px-4 py-3 bg-gray-800 border-b border-gray-700">
            <span class="text-xs font-mono text-gray-400">Setup Terminal</span>
        </div>
        <div class="p-6 font-mono text-sm text-green-400 h-64 overflow-y-auto space-y-1">
            <template x-for="line in terminalLines">
                <div x-text="line"></div>
            </template>
        </div>
    </div>
</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tab Menu for Workflows -->
                <div class="flex justify-center mb-8 border-b border-[var(--border-default)] max-w-md mx-auto" x-data="{ tab: 'new' }">
                    <button @click="tab = 'new'; $dispatch('tab-change', 'new')" :class="tab === 'new' ? 'border-[var(--brand-primary)] text-[var(--brand-primary)] border-b-2 font-bold' : 'text-[var(--text-muted)]'" class="flex-1 py-3 text-sm font-semibold transition-colors focus:outline-none">Alur Aplikasi Baru (New)</button>
                    <button @click="tab = 'redesign'; $dispatch('tab-change', 'redesign')" :class="tab === 'redesign' ? 'border-[var(--brand-primary)] text-[var(--brand-primary)] border-b-2 font-bold' : 'text-[var(--text-muted)]'" class="flex-1 py-3 text-sm font-semibold transition-colors focus:outline-none">Alur Redesain Aplikasi (Redesign)</button>
                </div>

                <div x-data="{ mode: 'new' }" @tab-change.window="mode = $event.detail">
                    <!-- NEW MODE TUTORIAL (12 Langkah) -->
                    <div x-show="mode === 'new'" class="space-y-6">
                        <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 shadow-sm">
                            <h3 class="font-heading font-semibold text-lg mb-4 text-center">12 Langkah Setup Wizard - Aplikasi Baru</h3>
                            <div class="space-y-4">
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">1</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Overview</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Halaman sambutan untuk memilih mode instalasi dan memahami alur kerja Vibeforge.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">2</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">PRD (Product Requirements Document)</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Tulis spesifikasi aplikasi: nama, fitur, target pengguna, user flow, dan kebutuhan bisnis di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">docs/prd.md</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">3</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Branding Identity</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Konfigurasi warna, font, logo, dan identitas visual aplikasi di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">docs/branding.md</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">4</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Logo</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Upload logo aplikasi berformat PNG (max 2MB) yang disimpan ke <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">docs/logo.png</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">5</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Landing Page</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Kustomisasi struktur & styling halaman landing di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">references/landingpage.html</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">6</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Login Page</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Atur tampilan halaman login di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">references/login.html</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">7</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Register Page</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Kustomisasi halaman registrasi di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">references/register.html</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">8</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Modul Manajemen</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Dashboard Super Admin di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">references/modul_manajemen.html</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">9</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Modul Admin/Creator</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Studio creator di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">references/modul_admin.html</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">10</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Modul Client</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Dashboard pendengar di <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">references/modul_client.html</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">11</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Server Configuration</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Pilih server lokal (Laragon/XAMPP) dan disk tujuan instalasi.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-gradient-brand flex items-center justify-center text-white font-heading font-bold text-base shrink-0">12</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Install Path & Execute</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Konfirmasi lokasi project dan buka terminal untuk menjalankan <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-xs">baca dan jalankan @docs/install.md</code>.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-6">
                                <a href="/install/" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-brand text-white font-bold rounded-xl hover:opacity-90 transition-opacity glow-orange shadow-lg">
                                    <i class="ph ph-magic-wand"></i> Mulai Wizard Baru (12 Langkah)
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- REDESIGN MODE TUTORIAL (5 Langkah) -->
                    <div x-show="mode === 'redesign'" class="space-y-6">
                        <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 shadow-sm">
                            <h3 class="font-heading font-semibold text-lg mb-4 text-center">5 Langkah Setup Wizard - Redesain Aplikasi</h3>
                            <div class="space-y-4">
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-heading font-bold text-base shrink-0">1</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Overview</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Halaman sambutan untuk memilih mode Redesain dan memahami alur cepat.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-heading font-bold text-base shrink-0">2</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Kelola Folder References</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Upload file/folder codebase lama aplikasi Anda ke folder <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-purple-400 font-mono text-xs">references/</code> via Web UI atau buka File Explorer langsung.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-heading font-bold text-base shrink-0">3</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Logo</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Upload logo aplikasi PNG (max 2MB) ke <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-purple-400 font-mono text-xs">docs/logo.png</code>.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-heading font-bold text-base shrink-0">4</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Server Configuration</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Pilih server lokal (Laragon/XAMPP) dan disk tujuan instalasi.</p>
                                    </div>
                                </div>
                                <div class="flex gap-4 p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)]">
                                    <div class="w-10 h-10 rounded-full bg-purple-500 flex items-center justify-center text-white font-heading font-bold text-base shrink-0">5</div>
                                    <div class="flex-1">
                                        <h4 class="font-semibold text-[var(--text-primary)] mb-1">Install Path & Execute</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Konfirmasi lokasi project dan buka terminal. PRD & Branding akan otomatis dibuat oleh AI saat menjalankan <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-purple-400 font-mono text-xs">baca dan jalankan @docs/install.md</code>.</p>
                                    </div>
                                </div>
                            </div>
                            <div class="text-center mt-6">
                                <a href="/install/?mode=redesign" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white font-bold rounded-xl transition-colors shadow-lg">
                                    <i class="ph ph-folder-open"></i> Mulai Wizard Redesain (5 Langkah)
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Demo / Try It -->
        <section id="demo" class="py-16 sm:py-24">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl sm:text-4xl font-heading font-bold mb-4">Coba Sekarang</h2>
                <p class="text-[var(--text-secondary)] mb-10">Klik tombol di bawah ini untuk langsung masuk ke dashboard masing-masing role tanpa perlu mengetik credential.</p>

                <div class="grid sm:grid-cols-3 gap-6 mb-8 max-w-4xl mx-auto">
                    <!-- Demo Card 1: Manajemen -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 text-left flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-purple-500/20 flex items-center justify-center mb-4">
                                <i class="ph ph-users text-2xl text-purple-400"></i>
                            </div>
                            <h3 class="font-heading font-semibold mb-1">Manajemen</h3>
                            <p class="text-sm text-[var(--text-muted)] mb-4">Super Admin dashboard penuh</p>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="manajemen@example.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/manajemen/')" class="w-full py-2.5 bg-purple-500 text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-1.5 shadow-md">
                                <i class="ph ph-sign-in text-base"></i> Masuk Manajemen
                            </button>
                        </form>
                    </div>

                    <!-- Demo Card 2: Admin/Creator -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--brand-primary)] p-6 text-left relative overflow-hidden flex flex-col justify-between">
                        <div class="absolute top-3 right-3 px-2 py-1 bg-[var(--brand-primary)] text-white text-xs font-bold rounded">Creator</div>
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-[var(--brand-primary-light)] flex items-center justify-center mb-4">
                                <i class="ph ph-rocket-launch text-2xl text-[var(--brand-primary)]"></i>
                            </div>
                            <h3 class="font-heading font-semibold mb-1">Admin / Creator</h3>
                            <p class="text-sm text-[var(--text-muted)] mb-4">Studio upload & analitik</p>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="admin@example.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/admin/')" class="w-full py-2.5 bg-gradient-brand text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-1.5 shadow-md glow-orange-sm">
                                <i class="ph ph-sign-in text-base"></i> Masuk Creator
                            </button>
                        </form>
                    </div>

                    <!-- Demo Card 3: Client -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 text-left flex flex-col justify-between">
                        <div>
                            <div class="w-12 h-12 rounded-xl bg-green-500/20 flex items-center justify-center mb-4">
                                <i class="ph ph-user text-2xl text-green-400"></i>
                            </div>
                            <h3 class="font-heading font-semibold mb-1">Client / Pengguna</h3>
                            <p class="text-sm text-[var(--text-muted)] mb-4">Dashboard eksplorasi konten</p>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="client@example.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/client/')" class="w-full py-2.5 bg-[var(--brand-primary)] text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity flex items-center justify-center gap-1.5 shadow-md">
                                <i class="ph ph-sign-in text-base"></i> Masuk Client
                            </button>
                        </form>
                    </div>
                </div>

            </div>
        </section>

        <!-- GitHub CTA -->
        <section class="py-12 bg-gradient-brand">
            <div class="max-w-4xl mx-auto px-4 text-center">
                <h2 class="text-2xl font-heading font-bold text-white mb-4">Source Code Terbuka</h2>
                <p class="text-white/80 mb-6">Lihat, fork, dan kontribusi di GitHub</p>
                <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-white text-[var(--brand-primary)] font-bold rounded-xl hover:opacity-90 transition-opacity shadow-lg">
                    <i class="ph ph-github-logo text-xl"></i> github.com/iqbalmurtadho24/vibeforge
                </a>
            </div>
        </section>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-[var(--bg-card)]/95 backdrop-blur-md border-t border-[var(--border-default)] z-50 shadow-lg">
        <div class="flex justify-around items-center h-16 px-2">
            <a href="#fitur" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-lightning text-xl"></i>
                <span class="text-[10px] font-medium">Fitur</span>
            </a>
            <a href="#cara-pasang" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-wrench text-xl"></i>
                <span class="text-[10px] font-medium">Pasang</span>
            </a>
            <a href="#demo" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-play-circle text-xl"></i>
                <span class="text-[10px] font-medium">Demo</span>
            </a>
            <a href="/install/" class="flex flex-col items-center gap-1 text-[var(--brand-primary)]">
                <i class="ph ph-magic-wand text-xl"></i>
                <span class="text-[10px] font-semibold">Wizard</span>
            </a>
        </div>
    </nav>

    <footer class="bg-[var(--bg-secondary)] border-t border-[var(--border-default)] py-8 pb-24 md:pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-sm text-[var(--text-muted)]">
            <p>&copy; 2026 <?= APP_DISPLAY_NAME ?>. All rights reserved.</p>
            <div class="flex gap-6">
                <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="hover:text-[var(--brand-primary)] transition-colors">GitHub</a>
                <a href="#" class="hover:text-[var(--brand-primary)] transition-colors">Dokumentasi</a>
                <a href="#" class="hover:text-[var(--brand-primary)] transition-colors">Lisensi Apache 2.0</a>
            </div>
        </div>
    </footer>

    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.data('appDownloader', () => ({
                appName: '',
                appNameError: false,
                server: '<?= $detectedServer ?>',
                drive: '<?= $detectedDrive ?>',
                drives: <?= json_encode(array_values($availableDrives)) ?>,
                isSubmitted: false,
                isChecking: false,
                folderExists: false,
                showTerminal: false,
                terminalLines: [],
                isSettingUp: false,
                subPath() {
                    return this.server === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs';
                },
                fullTargetDir() {
                    return this.drive + ':\\' + this.subPath() + '\\' + (this.appName.trim() || 'myapp');
                },
                fullCommand() {
                    const targetDir = this.drive + ':\\' + this.subPath();
                    const appName = this.appName.trim() || 'myapp';
                    return `Set-Location -Path '${targetDir}'; npx -y degit iqbalmurtadho24/vibeforge ${appName}; if ($?) { Set-Location -Path '.\\${appName}'; Copy-Item -Path '.env.example' -Destination '.env'; Exit }`;
                },
                wizardUrl() {
                    const name = this.appName.trim() || 'myapp';
                    return 'http://localhost/' + name + '/public/install/';
                },
                sanitizeAppName() {
                    // Hanya izinkan huruf, angka, underscore, hyphen
                    // Hapus karakter yang tidak diizinkan
                    this.appName = this.appName.replace(/[^a-zA-Z0-9_-]/g, '');
                    // Cek apakah ada karakter yang dihapus (misal spasi, titik, koma)
                    this.appNameError = /[^a-zA-Z0-9_-]/.test(this.appName);
                },
                init() {
                    // Auto-refresh status folder setiap 4 detik jika belum ada
                    setInterval(() => {
                        if (this.isSubmitted && !this.folderExists && !this.isChecking && this.appName.trim()) {
                            this.checkFolder();
                        }
                    }, 4000);
                },
                async checkFolder() {
                    if (!this.appName.trim()) return;
                    this.isChecking = true;
                    try {
                        const res = await fetch('/core/router.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                module: 'install',
                                action: 'check_folder',
                                drive: this.drive,
                                serverType: this.server,
                                projectName: this.appName.trim(),
                                csrf_token: '<?= $csrfToken ?>'
                            })
                        });
                        const data = await res.json();
                        this.folderExists = data.exists || false;
                    } catch(e) {
                        this.folderExists = false;
                    } finally {
                        this.isChecking = false;
                        this.isSubmitted = true;
                    }
                }
            }));
        });

        const html = document.documentElement;
        function initTheme() {
            const saved = localStorage.getItem('theme') || 'dark';
            html.classList.toggle('dark', saved === 'dark');
        }
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const isDark = html.classList.toggle('dark');
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
        initTheme();

        // Copy Snippet Helper with fallback for non-secure contexts
        async function copySnippet(btn, codeText) {
            const originalHtml = btn.innerHTML;
            try {
                if (navigator.clipboard && window.isSecureContext) {
                    await navigator.clipboard.writeText(codeText);
                } else {
                    const textarea = document.createElement('textarea');
                    textarea.value = codeText;
                    textarea.style.position = 'fixed';
                    textarea.style.opacity = '0';
                    document.body.appendChild(textarea);
                    textarea.select();
                    document.execCommand('copy');
                    document.body.removeChild(textarea);
                }
                btn.innerHTML = '<i class="ph ph-check text-green-400"></i> Tersalin!';
            } catch(e) {
                btn.innerHTML = '<i class="ph ph-warning text-red-400"></i> Gagal!';
            } finally {
                setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
            }
        }

        // Execute Terminal Helper via AJAX (downloads via npx degit & opens PowerShell inside project folder)
        async function executeInteractiveTerminal(btn) {
            const dataEl = document.getElementById('appDownloaderComponent');
            if (!dataEl) return;
            const state = Alpine.$data(dataEl);
            const cmdText = state.fullCommand();

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-circle-notch animate-spin"></i> Mengeksekusi...';

            try {
                const res = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        module: 'install',
                        action: 'execute',
                        drive: state.drive,
                        serverType: state.server,
                        projectName: state.appName.trim(),
                        command: cmdText, // Gunakan fullCommand() yang berisi perintah download lengkap
                        csrf_token: '<?= $csrfToken ?>'
                    })
                });
                const data = await res.json();
                if (data.success) {
                    btn.innerHTML = '<i class="ph ph-check-circle text-green-400"></i> Berhasil Dijalankan!';
                } else {
                    alert(data.error || 'Gagal membuka terminal');
                    btn.innerHTML = originalHtml;
                }
            } catch(e) {
                alert('Gagal membuka terminal.');
                btn.innerHTML = originalHtml;
            } finally {
                setTimeout(() => { btn.disabled = false; btn.innerHTML = originalHtml; }, 3000);
            }
        }

        // Setup Virtual Host Helper via AJAX
        async function startSetupWizard(btn) {
            const dataEl = document.getElementById('appDownloaderComponent');
            if (!dataEl) return;
            const state = Alpine.$data(dataEl);
            const appName = state.appName.trim() || 'myapp';
            const targetUrl = 'http://' + appName + '.test/install/';

            state.showTerminal = true;
            state.isSettingUp = true;
            state.terminalLines = [
                '[INFO] Initializing setup process for ' + appName + '...',
                '[INFO] Target Domain: http://' + appName + '.test/install/'
            ];

            const addLog = (msg, delay = 600) => {
                return new Promise(resolve => {
                    setTimeout(() => {
                        state.terminalLines.push(msg);
                        resolve();
                    }, delay);
                });
            };

            await addLog('[INFO] Checking virtual host configuration...');
            await addLog('[INFO] Triggering PowerShell elevation for Apache VHost & Hosts setup...');

            try {
                const res = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        module: 'install',
                        action: 'setup_vhost',
                        projectName: appName,
                        drive: state.drive,
                        serverType: state.server,
                        csrf_token: '<?= $csrfToken ?>'
                    })
                });
                const data = await res.json();

                if (data.success) {
                    await addLog('[SUCCESS] Virtual host setup command executed successfully!');
                    await addLog('[INFO] Reloading Apache Web Server configuration...');
                    await addLog('[SUCCESS] Setup Complete!');
                    await addLog('[INFO] Redirecting to installation wizard in 2 seconds...', 1000);

                    setTimeout(() => {
                        state.isSettingUp = false;
                        window.location.href = targetUrl;
                    }, 2000);
                } else {
                    await addLog('[ERROR] ' + (data.error || 'Failed to setup Virtual Host.'), 0);
                    await addLog('[WARNING] Falling back to direct URL: ' + state.wizardUrl(), 1000);
                    setTimeout(() => {
                        state.isSettingUp = false;
                        window.location.href = state.wizardUrl();
                    }, 3000);
                }
            } catch(e) {
                await addLog('[ERROR] Connection error during setup.', 0);
                await addLog('[WARNING] Falling back to direct URL...', 1000);
                setTimeout(() => {
                    state.isSettingUp = false;
                    window.location.href = state.wizardUrl();
                }, 3000);
            }
        }

        async function setupVirtualHost(btn) {
            startSetupWizard(btn);
        }

        // Open Folder Helper via AJAX
        async function openFolderExplorer(btn) {
            try {
                const res = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        module: 'install',
                        action: 'open_folder',
                        folder: 'references',
                        csrf_token: '<?= $csrfToken ?>'
                    })
                });
                const data = await res.json();
                if (!data.success) alert(data.error || 'Gagal membuka folder');
            } catch(e) {
                alert('Gagal membuka folder.');
            }
        }

        // Direct Login without redirection manually typing credentials
        async function directLogin(event, form, targetUrl) {
            event.preventDefault();
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-circle-notch animate-spin text-base"></i> Memproses...';

            try {
                // Post request to router proxy /core/router.php
                const response = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: new URLSearchParams(new FormData(form)).toString()
                });

                const data = await response.json();
                if (data.success) {
                    window.location.href = targetUrl;
                } else {
                    alert(data.error || 'Login gagal');
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (err) {
                console.error(err);
                alert('Terjadi kesalahan jaringan atau server.');
                btn.disabled = false;
                btn.innerHTML = originalHtml;
            }
        }
    </script>
</body>
</html>
