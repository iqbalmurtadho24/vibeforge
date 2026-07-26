<?php
/**
 * Vibeforge Landing Page
 * Full informational page with installation guide - IT Professional Edition
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
    <title><?= APP_DISPLAY_NAME ?> - Developer-Grade Vibe Coding Architecture</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F97316'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
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
                    colors: {
                        brand: {
                            primary: '#F97316',
                            hover: '#EA580C',
                            dark: '#0B0F17',
                            card: '#111726',
                            border: '#1E293B'
                        }
                    },
                    fontFamily: {
                        sans: ['Plus Jakarta Sans', 'Inter', 'sans-serif'],
                        heading: ['Plus Jakarta Sans', 'sans-serif'],
                        mono: ['JetBrains Mono', 'Fira Code', 'monospace']
                    }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: var(--bg-primary); color: var(--text-primary); }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .tech-grid {
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .glow-mesh {
            background: radial-gradient(circle at 50% 20%, rgba(249, 115, 22, 0.15) 0%, rgba(13, 17, 23, 0) 65%);
        }
        .text-gradient {
            background: linear-gradient(135deg, #F97316 0%, #FBBF24 50%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .bg-gradient-brand { background: linear-gradient(135deg, #F97316 0%, #EA580C 100%); }
        .glow-orange { box-shadow: 0 0 35px rgba(249, 115, 22, 0.25); }
        .glow-orange-sm { box-shadow: 0 0 20px rgba(249, 115, 22, 0.15); }
        .glow-box-cyber { box-shadow: 0 0 0 1px rgba(249, 115, 22, 0.2), 0 10px 30px -10px rgba(0, 0, 0, 0.8); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased tech-grid bg-[var(--bg-primary)]">

    <!-- Top Status Bar (IT Professional Touch) -->
    <div class="bg-[var(--bg-secondary)] border-b border-[var(--border-default)] py-1 px-4 text-[11px] font-mono text-[var(--text-secondary)] hidden sm:flex items-center justify-between z-50">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1.5 text-emerald-400">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                SYSTEM: ONLINE
            </span>
            <span class="text-[var(--border-default)]">|</span>
            <span>PHP 8.3+ Native</span>
            <span class="text-[var(--border-default)]">|</span>
            <span>Architecture: SPA Shell Proxy</span>
        </div>
        <div class="flex items-center gap-4">
            <span>Repo Mode: Dual-Engine (Auto SQL/JSON)</span>
            <span class="text-[var(--border-default)]">|</span>
            <span class="text-[var(--brand-primary)]">v3.2.0-STABLE</span>
        </div>
    </div>

    <!-- Navbar -->
    <nav class="sticky top-0 w-full z-50 bg-[var(--bg-primary)]/85 backdrop-blur-xl border-b border-[var(--border-default)]">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                <a href="/" class="flex items-center gap-3 group">
                    <div class="w-9 h-9 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center group-hover:border-orange-500 transition-colors shadow-[0_0_15px_rgba(249,115,22,0.2)]">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="#F97316">
                            <path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/>
                        </svg>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-heading font-extrabold text-lg tracking-tight leading-none">
                            <span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span>
                        </span>
                        <span class="font-mono text-[9px] text-[var(--text-muted)] tracking-wider">DEV_ENGINE</span>
                    </div>
                </a>

                <div class="hidden md:flex items-center gap-8 font-medium text-sm">
                    <a href="#fitur" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5">
                        <i class="ph ph-cpu text-base text-[var(--brand-primary)]"></i> Arsitektur
                    </a>
                    <a href="#cara-pasang" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5">
                        <i class="ph ph-terminal-window text-base text-[var(--brand-primary)]"></i> Installer
                    </a>
                    <a href="#demo" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5">
                        <i class="ph ph-shield-check text-base text-[var(--brand-primary)]"></i> Demo Roles
                    </a>
                    <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1">
                        <i class="ph ph-github-logo text-base"></i> GitHub
                    </a>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Language Selector -->
                    <div class="relative group" x-data="{ open: false }">
                        <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[var(--bg-card)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors text-xs font-mono" aria-label="Change Language">
                            <img src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" alt="<?= $currentLang ?>" class="w-4 h-3 rounded-sm shadow-sm">
                            <span class="uppercase font-bold text-[var(--text-secondary)]"><?= escape($currentLang) ?></span>
                            <i class="ph ph-caret-down text-xs text-[var(--text-muted)]"></i>
                        </button>
                        <div x-show="open" x-transition class="absolute right-0 mt-1.5 bg-[var(--bg-card)] rounded-xl shadow-2xl border border-[var(--border-default)] py-1 min-w-[160px] z-50 font-mono text-xs">
                            <?php foreach (getAvailableLanguages() as $code => $lang): ?>
                            <a href="?lang=<?= $code ?>" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-[var(--bg-hover)] transition-colors <?= $currentLang === $code ? 'text-[var(--brand-primary)] font-bold bg-orange-500/10' : 'text-[var(--text-secondary)]' ?>">
                                <img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-4 h-3 rounded-sm">
                                <span><?= escape($lang['name']) ?></span>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Theme Toggle -->
                    <button id="themeToggle" class="w-9 h-9 rounded-lg bg-[var(--bg-card)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] flex items-center justify-center transition-colors text-[var(--text-muted)] hover:text-amber-400" aria-label="Toggle theme">
                        <i class="ph ph-moon text-lg dark:text-amber-400"></i>
                    </button>

                    <?php if ($isLoggedIn): ?>
                    <a href="<?= $dashboardUrl ?>" class="px-4 py-2 bg-[var(--brand-primary)] hover:bg-[var(--brand-primary-hover)] text-white text-xs font-bold rounded-lg transition-all shadow-md flex items-center gap-1.5 font-mono">
                        <i class="ph ph-squares-four text-base"></i> DASHBOARD
                    </a>
                    <?php else: ?>
                    <a href="/login/" class="px-3.5 py-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] text-xs font-semibold rounded-lg hover:bg-[var(--bg-hover)] transition-colors">Masuk</a>
                    <a href="/register/" class="px-4 py-2 bg-gradient-brand text-white text-xs font-bold rounded-lg hover:opacity-95 transition-all shadow-md glow-orange-sm flex items-center gap-1">
                        <i class="ph ph-user-plus text-sm"></i> Daftar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <main class="flex-grow relative glow-mesh">

        <!-- Hero Section -->
        <section class="py-16 sm:py-24 relative overflow-hidden">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-4xl mx-auto space-y-6">

                    <!-- Tech Status Badge -->
                    <div class="inline-flex items-center gap-2.5 px-4 py-1.5 rounded-full bg-orange-500/10 border border-orange-500/30 text-orange-400 font-mono text-xs shadow-[0_0_20px_rgba(249,115,22,0.15)]">
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-orange-500"></span>
                        </span>
                        <span>AI-POWERED VIBE CODING TEMPLATE // ARCHITECTURE READY</span>
                    </div>

                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-heading font-extrabold tracking-tight leading-[1.1]">
                        Tempakan Aplikasi Anda<br>
                        <span class="text-gradient">dari Dokumen ke Kode Jadi</span>
                    </h1>

                    <p class="text-base sm:text-xl text-[var(--text-secondary)] max-w-3xl mx-auto leading-relaxed font-normal">
                        <strong class="text-[var(--text-primary)]"><?= APP_DISPLAY_NAME ?></strong> adalah template starter professional untuk membangun aplikasi web dengan metode <span class="text-[var(--brand-primary)] font-semibold">vibe coding</span>: definisikan kebutuhan di dokumen, biarkan AI coding assistant menempa seluruh struktur kode fungsional.
                    </p>

                    <!-- Quick Terminal Command Bar in Hero -->
                    <div class="pt-2 max-w-3xl mx-auto">
                        <div class="bg-gray-950/90 backdrop-blur-md border border-[var(--border-default)] rounded-xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 font-mono text-xs text-emerald-400 shadow-2xl group hover:border-orange-500/50 transition-all glow-box-cyber">
                            <div class="flex items-center gap-2.5 overflow-x-auto hide-scrollbar py-0.5 min-w-0">
                                <span class="text-xs font-bold text-orange-400 bg-orange-500/10 border border-orange-500/20 px-2 py-0.5 rounded select-none shrink-0">PS</span>
                                <span class="text-gray-500 select-none font-bold">&gt;</span>
                                <span class="truncate sm:whitespace-normal font-mono text-emerald-400 font-medium tracking-tight">irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex</span>
                            </div>
                            <button onclick="copySnippet(this, 'irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex')" class="self-end sm:self-auto px-4 py-2 bg-gradient-brand hover:opacity-90 text-white rounded-lg text-xs font-sans font-bold transition-all shrink-0 flex items-center gap-1.5 shadow-md glow-orange-sm">
                                <i class="ph ph-copy text-sm"></i> Copy Command
                            </button>
                        </div>
                    </div>

                    <!-- Call To Actions -->
                    <div class="flex flex-col sm:flex-row gap-4 justify-center pt-4">
                        <a href="#cara-pasang" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-gradient-brand text-white font-bold rounded-xl hover:opacity-95 transition-all shadow-xl glow-orange text-sm font-mono tracking-wide">
                            <i class="ph ph-rocket-launch text-xl"></i> SETUP APPLICATION
                        </a>
                        <a href="/install/" class="inline-flex items-center justify-center gap-2 px-8 py-4 bg-[var(--bg-card)] text-[var(--text-primary)] font-bold rounded-xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] hover:bg-orange-500/5 transition-all shadow-lg text-sm font-mono tracking-wide">
                            <i class="ph ph-magic-wand text-xl text-[var(--brand-primary)]"></i> WIZARD (12 STEPS)
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Technical Specification Bar -->
        <section class="py-10 bg-[var(--bg-secondary)]/70 border-y border-[var(--border-default)] backdrop-blur-md">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">

                    <div class="p-4 rounded-xl bg-[var(--bg-card)] border border-[var(--border-default)] flex items-center gap-4 hover:border-orange-500/40 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-[var(--brand-primary)] shrink-0">
                            <i class="ph ph-code text-2xl"></i>
                        </div>
                        <div>
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]">Native PHP 8.3+</div>
                            <div class="text-[11px] text-[var(--text-muted)]">Zero Heavy Framework Overhead</div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-[var(--bg-card)] border border-[var(--border-default)] flex items-center gap-4 hover:border-orange-500/40 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                            <i class="ph ph-database text-2xl"></i>
                        </div>
                        <div>
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]">Dual-Engine Repo</div>
                            <div class="text-[11px] text-[var(--text-muted)]">Auto Switch SQL / JSON Storage</div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-[var(--bg-card)] border border-[var(--border-default)] flex items-center gap-4 hover:border-orange-500/40 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                            <i class="ph ph-shield-check text-2xl"></i>
                        </div>
                        <div>
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]">OWASP ASVS Guarded</div>
                            <div class="text-[11px] text-[var(--text-muted)]">Argon2ID + CSRF + Rate Limiter</div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-[var(--bg-card)] border border-[var(--border-default)] flex items-center gap-4 hover:border-orange-500/40 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                            <i class="ph ph-browser text-2xl"></i>
                        </div>
                        <div>
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]">SPA Shell Architecture</div>
                            <div class="text-[11px] text-[var(--text-muted)]">Router Proxy Pattern No-Reload</div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Feature Grid (IT Pro Bento-style) -->
        <section id="fitur" class="py-20 sm:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <span class="font-mono text-xs text-[var(--brand-primary)] tracking-widest uppercase font-bold">// KEY ARCHITECTURE //</span>
                    <h2 class="text-3xl sm:text-5xl font-heading font-extrabold tracking-tight">Kenapa Harus Vibeforge?</h2>
                    <p class="text-[var(--text-secondary)] text-base">Arsitektur bersih yang dirancang khusus untuk mempermudah AI CLI (Claude Code, Cursor, Copilot) bekerja tanpa kebingungan.</p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)] group-hover:scale-110 transition-transform">
                            <i class="ph ph-file-doc text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-[var(--text-primary)]">Dokumen ke Kode</h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed">Cukup update <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">docs/prd.md</code> & <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">docs/branding.md</code>. AI akan mengeksekusi fitur secara presisi.</p>
                    </div>

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                            <i class="ph ph-layout text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-[var(--text-primary)]">Shell SPA Clean-URL</h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed">Landing page, login, register, manajemen, admin, client — terisolasi via Shell tipis dengan router proxy AJAX.</p>
                    </div>

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <i class="ph ph-shield-check text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-[var(--text-primary)]">Enterprise Security</h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed">Password hashing Argon2ID, proteksi CSRF otomatis, rate-limiting IP/Username, serta RBAC middleware.</p>
                    </div>

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                            <i class="ph ph-translate text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-semibold text-lg text-[var(--text-primary)]">Native i18n & RTL</h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed">Sistem terjemahan multi-bahasa terpusat dengan deteksi GeoIP dan dukungan penuh layout RTL (Arabic).</p>
                    </div>

                </div>
            </div>
        </section>

        <!-- Interactive Setup Console Component -->
        <section id="cara-pasang" class="py-20 sm:py-28 bg-[var(--bg-secondary)]/50 border-y border-[var(--border-default)]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="text-center max-w-3xl mx-auto mb-12 space-y-3">
                    <span class="font-mono text-xs text-[var(--brand-primary)] tracking-widest uppercase font-bold">// AUTOMATED DEPLOYMENT CONSOLE //</span>
                    <h2 class="text-3xl sm:text-5xl font-heading font-extrabold tracking-tight">Unduh & Konfigurasi Aplikasi</h2>
                    <p class="text-[var(--text-secondary)] text-sm sm:text-base">Gunakan console interaktif di bawah untuk membuat Virtual Host & workspace project baru di server lokal Anda.</p>
                </div>

                <!-- Notice Banner -->
                <div class="mb-8 p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-start gap-3.5 text-xs font-sans">
                    <i class="ph ph-warning-circle text-amber-400 text-2xl shrink-0 mt-0.5"></i>
                    <div class="space-y-1">
                        <p class="font-bold text-amber-300">Penting: Pembuatan Aplikasi Baru Terpisah</p>
                        <p class="text-[var(--text-secondary)] leading-relaxed">
                            Form console di bawah ini mengunduh template Vibeforge dan mengonfigurasi Virtual Host untuk <strong>aplikasi baru terpisah</strong> di server lokal.<br>
                            Jika Anda ingin <strong>mengedit aplikasi ini</strong> atau melakukan <strong>redesain proyek</strong>, silakan ikuti alur pada tab <a href="#alur-aplikasi" class="text-[var(--brand-primary)] underline font-semibold hover:opacity-80">Alur Redesain Aplikasi di bawah</a>.
                        </p>
                    </div>
                </div>

                <!-- IDE Terminal-Style Window Downloader Component -->
                <div id="appDownloaderComponent" x-data="appDownloader()" class="bg-gray-950 rounded-2xl border border-[var(--border-default)] shadow-2xl overflow-hidden mb-16 glow-box-cyber">

                    <!-- IDE Window Title Bar -->
                    <div class="px-5 py-3 bg-gray-900 border-b border-gray-800 flex items-center justify-between font-mono text-xs text-gray-400">
                        <div class="flex items-center gap-2">
                            <span class="w-3 h-3 rounded-full bg-red-500 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-yellow-500 inline-block"></span>
                            <span class="w-3 h-3 rounded-full bg-green-500 inline-block"></span>
                            <span class="ml-2 font-semibold text-gray-300">vibeforge-cli ~ automated-setup</span>
                        </div>
                        <div class="flex items-center gap-3 text-[11px] text-gray-500">
                            <span>ENV: <strong class="text-orange-400"><?= escape(APP_ENV) ?></strong></span>
                            <span>PHP: <strong class="text-emerald-400">8.3</strong></span>
                        </div>
                    </div>

                    <div class="p-6 sm:p-8 space-y-6">
                        <form @submit.prevent="startFullSetup()" class="grid grid-cols-1 md:grid-cols-3 gap-6">

                            <!-- Server Type Selector -->
                            <div>
                                <div class="flex items-center justify-between mb-3 font-mono">
                                    <label class="block text-xs font-bold uppercase text-gray-400">1. Web Server</label>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                        Auto: <?= strtoupper($detectedServer) ?>
                                    </span>
                                </div>
                                <div class="grid grid-cols-2 gap-2.5">
                                    <button type="button" @click="server = 'laragon'" :class="server === 'laragon' ? 'bg-[var(--brand-primary)] text-white font-bold border-[var(--brand-primary)]' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-850 hover:text-gray-200'" class="py-3 px-3 rounded-xl border text-xs font-mono transition-all flex items-center justify-center gap-2">
                                        <i class="ph ph-bug text-lg"></i> Laragon
                                    </button>
                                    <button type="button" @click="server = 'xampp'" :class="server === 'xampp' ? 'bg-[var(--brand-primary)] text-white font-bold border-[var(--brand-primary)]' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-850 hover:text-gray-200'" class="py-3 px-3 rounded-xl border text-xs font-mono transition-all flex items-center justify-center gap-2">
                                        <i class="ph ph-file-css text-lg"></i> XAMPP
                                    </button>
                                </div>
                            </div>

                            <!-- Disk Drive Selector -->
                            <div>
                                <div class="flex items-center justify-between mb-3 font-mono">
                                    <label class="block text-xs font-bold uppercase text-gray-400">2. Local Disk</label>
                                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                        Disk <?= $detectedDrive ?>:
                                    </span>
                                </div>
                                <div class="flex items-center gap-2 overflow-x-auto pb-1 hide-scrollbar">
                                    <template x-for="d in drives" :key="d">
                                        <button type="button" @click="drive = d" :class="drive === d ? 'bg-[var(--brand-primary)] text-white font-bold border-[var(--brand-primary)]' : 'bg-gray-900 text-gray-400 border-gray-800 hover:bg-gray-850 hover:text-gray-200'" class="px-3.5 py-3 rounded-xl border text-xs font-mono transition-all flex items-center gap-1.5 shrink-0">
                                            <i class="ph ph-hard-drive"></i> (<span x-text="d"></span>:)
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- App Name Input & Trigger -->
                            <div>
                                <label class="block text-xs font-mono font-bold uppercase text-gray-400 mb-3">3. Nama Aplikasi <span class="text-red-400">*</span></label>
                                <div class="flex items-center gap-2">
                                    <div class="relative flex-1">
                                        <input type="text" x-model="appName" @input="sanitizeAppName()" required placeholder="e.g. my_app" pattern="^[a-zA-Z0-9][a-zA-Z0-9_-]*$" class="w-full bg-gray-900 border border-gray-800 rounded-xl px-4 py-3 text-xs text-gray-200 focus:outline-none focus:border-[var(--brand-primary)] transition-colors font-mono" :class="{ 'border-red-500': appNameError }">
                                        <template x-if="appNameError">
                                            <div class="absolute bottom-full left-0 mb-1 px-2 py-1 bg-red-500/90 text-white text-[10px] font-sans rounded whitespace-nowrap">Format: huruf, angka, _, - (tanpa spasi)</div>
                                        </template>
                                    </div>
                                    <button type="submit" :disabled="!appName.trim() || isSettingUp || appNameError" class="px-5 py-3 bg-gradient-brand text-white text-xs font-mono font-bold rounded-xl hover:opacity-90 transition-opacity disabled:opacity-50 flex items-center gap-2 shrink-0 whitespace-nowrap shadow-lg glow-orange-sm">
                                        <template x-if="!isSettingUp">
                                            <span class="flex items-center gap-1.5"><i class="ph ph-rocket-launch text-base"></i> EXECUTE SETUP</span>
                                        </template>
                                        <template x-if="isSettingUp">
                                            <span class="flex items-center gap-1.5"><i class="ph ph-circle-notch animate-spin text-base"></i> RUNNING...</span>
                                        </template>
                                    </button>
                                </div>
                            </div>
                        </form>

                        <!-- Terminal Overlay Dialog -->
                        <div id="setupTerminal" x-show="showTerminal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/85 backdrop-blur-md" x-cloak>
                            <div class="bg-gray-950 rounded-2xl w-full max-w-2xl border border-gray-800 shadow-2xl overflow-hidden">
                                <div class="flex items-center justify-between px-5 py-3.5 bg-gray-900 border-b border-gray-800">
                                    <span class="text-xs font-mono font-bold text-white flex items-center gap-2">
                                        <i class="ph ph-terminal-window text-[var(--brand-primary)] text-lg"></i> Vibeforge Automated Installer
                                    </span>
                                    <button type="button" @click="cancelSetup()" :disabled="!isSettingUp" class="text-gray-400 hover:text-red-400 transition-colors disabled:opacity-30">
                                        <i class="ph ph-x text-lg"></i>
                                    </button>
                                </div>
                                <div class="p-6 font-mono text-xs text-emerald-400 h-96 overflow-y-auto space-y-1.5 bg-black">
                                    <template x-for="line in terminalLines">
                                        <div x-text="line" class="leading-relaxed"></div>
                                    </template>
                                    <div x-show="isSettingUp" class="flex items-center gap-2 text-gray-500 pt-2">
                                        <i class="ph ph-circle-notch animate-spin text-sm"></i>
                                        <span>Menjalankan skrip Powershell & Virtual Host auto-config...</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Workflow Step Tabs -->
                <div id="alur-aplikasi" class="space-y-8">

                    <div class="flex justify-center border-b border-[var(--border-default)] max-w-md mx-auto" x-data="{ tab: 'new' }">
                        <button @click="tab = 'new'; $dispatch('tab-change', 'new')" :class="tab === 'new' ? 'border-[var(--brand-primary)] text-[var(--brand-primary)] border-b-2 font-bold' : 'text-[var(--text-muted)]'" class="flex-1 py-3 text-xs font-mono uppercase tracking-wider transition-colors focus:outline-none">Alur Aplikasi Baru (12 Steps)</button>
                        <button @click="tab = 'redesign'; $dispatch('tab-change', 'redesign')" :class="tab === 'redesign' ? 'border-purple-500 text-purple-400 border-b-2 font-bold' : 'text-[var(--text-muted)]'" class="flex-1 py-3 text-xs font-mono uppercase tracking-wider transition-colors focus:outline-none">Alur Redesain (5 Steps)</button>
                    </div>

                    <div x-data="{ mode: 'new' }" @tab-change.window="mode = $event.detail">

                        <!-- NEW MODE TUTORIAL (12 Steps) -->
                        <div x-show="mode === 'new'" class="space-y-6">
                            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 sm:p-8 space-y-6">
                                <div class="flex items-center justify-between border-b border-[var(--border-default)] pb-4">
                                    <h3 class="font-heading font-extrabold text-xl">12 Langkah Setup Wizard — Aplikasi Baru</h3>
                                    <span class="px-3 py-1 bg-orange-500/10 border border-orange-500/30 text-[var(--brand-primary)] font-mono text-xs rounded-full">MODE: GREENFIELD</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_01</span>
                                            <i class="ph ph-compass text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Overview Mode</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Pemilihan mode instalasi & penjelasan arsitektur Vibeforge.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_02</span>
                                            <i class="ph ph-file-text text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Definisi PRD</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Spesifikasi aplikasi & alur fitur di <code class="px-1 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">docs/prd.md</code>.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_03</span>
                                            <i class="ph ph-palette text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Branding Tokens</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Konfigurasi token warna & font di <code class="px-1 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">docs/branding.md</code>.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_04</span>
                                            <i class="ph ph-image text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Logo Assets</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Upload asset gambar logo ke <code class="px-1 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">docs/logo.png</code>.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_05</span>
                                            <i class="ph ph-browsers text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Template Landing</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Kustomisasi halaman depan di <code class="px-1 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">references/landingpage.html</code>.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_06-07</span>
                                            <i class="ph ph-lock-key text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Auth Forms</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Desain form Login & Register di folder <code class="px-1 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">references/</code>.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_08-10</span>
                                            <i class="ph ph-squares-four text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Role Modules</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Dashboard Manajemen, Admin/Creator Studio, & Client Player.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_11</span>
                                            <i class="ph ph-gear text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Config & Environment</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Penyesuaian variabel <code class="px-1 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">.env</code> & database settings.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <div class="flex items-center justify-between">
                                            <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">STEP_12</span>
                                            <i class="ph ph-terminal text-lg text-[var(--text-muted)]"></i>
                                        </div>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">AI Execution</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Buka CLI dan eksekusi <code class="px-1 bg-[var(--bg-surface)] text-[var(--brand-primary)] font-mono rounded">baca @docs/install.md</code>.</p>
                                    </div>

                                </div>

                                <div class="text-center pt-4">
                                    <a href="/install/" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-brand text-white font-mono font-bold text-xs rounded-xl hover:opacity-90 transition-opacity glow-orange-sm shadow-lg">
                                        <i class="ph ph-magic-wand text-base"></i> LAUNCH 12-STEP WIZARD
                                    </a>
                                </div>
                            </div>
                        </div>

                        <!-- REDESIGN MODE TUTORIAL (5 Steps) -->
                        <div x-show="mode === 'redesign'" class="space-y-6">
                            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 sm:p-8 space-y-6">
                                <div class="flex items-center justify-between border-b border-[var(--border-default)] pb-4">
                                    <h3 class="font-heading font-extrabold text-xl">5 Langkah Setup Wizard — Redesain Aplikasi</h3>
                                    <span class="px-3 py-1 bg-purple-500/10 border border-purple-500/30 text-purple-400 font-mono text-xs rounded-full">MODE: REFIT</span>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <span class="font-mono text-xs text-purple-400 font-bold">STEP_01</span>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Overview</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Pilih Mode Redesain pada Wizard UI.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <span class="font-mono text-xs text-purple-400 font-bold">STEP_02</span>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Upload References</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Letakkan codebase lama ke folder <code class="px-1 bg-[var(--bg-surface)] text-purple-400 font-mono rounded">references/</code>.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <span class="font-mono text-xs text-purple-400 font-bold">STEP_03</span>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Logo Assets</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Upload asset logo baru ke <code class="px-1 bg-[var(--bg-surface)] text-purple-400 font-mono rounded">docs/logo.png</code>.</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <span class="font-mono text-xs text-purple-400 font-bold">STEP_04</span>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">Target Host</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Tentukan jenis web server lokal (Laragon/XAMPP).</p>
                                    </div>

                                    <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                        <span class="font-mono text-xs text-purple-400 font-bold">STEP_05</span>
                                        <h4 class="font-bold text-sm text-[var(--text-primary)]">AI Re-architecting</h4>
                                        <p class="text-xs text-[var(--text-secondary)]">Jalankan AI CLI untuk auto-generate PRD & menyerap struktur lama.</p>
                                    </div>

                                </div>

                                <div class="text-center pt-4">
                                    <a href="/install/?mode=redesign" class="inline-flex items-center gap-2 px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white font-mono font-bold text-xs rounded-xl transition-colors shadow-lg">
                                        <i class="ph ph-folder-open text-base"></i> LAUNCH 5-STEP REDESIGN WIZARD
                                    </a>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

            </div>
        </section>

        <!-- Role Access Matrix / Demo Portals Section -->
        <section id="demo" class="py-20 sm:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <span class="font-mono text-xs text-[var(--brand-primary)] tracking-widest uppercase font-bold">// ACCESS CONTROL MATRIX //</span>
                    <h2 class="text-3xl sm:text-5xl font-heading font-extrabold tracking-tight">Coba Demo Instant Roles</h2>
                    <p class="text-[var(--text-secondary)] text-base">Satu klik untuk menguji autentikasi & otorisasi role-based access control (RBAC) tanpa perlu mengetik kredensial.</p>
                </div>

                <div class="grid sm:grid-cols-3 gap-8 max-w-5xl mx-auto">

                    <!-- Role Card 1: Manajemen -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 hover:border-purple-500/50 transition-all duration-300 flex flex-col justify-between space-y-6 glow-box-cyber">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                                    <i class="ph ph-crown text-2xl"></i>
                                </div>
                                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20">ROLE: MANAJEMEN</span>
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-xl text-[var(--text-primary)]">Super Admin Portal</h3>
                                <p class="text-xs text-[var(--text-secondary)] mt-1">Akses penuh ke overview sistem, audit trail, keuangan & persetujuan kreator.</p>
                            </div>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="admin@app.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/manajemen/')" class="w-full py-3 bg-purple-600 hover:bg-purple-500 text-white font-mono font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                                <i class="ph ph-sign-in text-base"></i> LOGIN SUPER ADMIN
                            </button>
                        </form>
                    </div>

                    <!-- Role Card 2: Creator -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--brand-primary)]/50 p-6 transition-all duration-300 flex flex-col justify-between space-y-6 relative overflow-hidden glow-box-cyber">
                        <div class="absolute -top-12 -right-12 w-28 h-28 bg-orange-500/10 rounded-full blur-xl pointer-events-none"></div>
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)]">
                                    <i class="ph ph-rocket-launch text-2xl"></i>
                                </div>
                                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-500/20">ROLE: ADMIN (CREATOR)</span>
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-xl text-[var(--text-primary)]">Creator Studio</h3>
                                <p class="text-xs text-[var(--text-secondary)] mt-1">Dashboard upload konten, statistik performa karya, & manajemen royalti.</p>
                            </div>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="admin@app.id">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/admin/')" class="w-full py-3 bg-gradient-brand text-white font-mono font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2 glow-orange-sm">
                                <i class="ph ph-sign-in text-base"></i> LOGIN CREATOR
                            </button>
                        </form>
                    </div>

                    <!-- Role Card 3: Client -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 hover:border-emerald-500/50 transition-all duration-300 flex flex-col justify-between space-y-6 glow-box-cyber">
                        <div class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                                    <i class="ph ph-headphones text-2xl"></i>
                                </div>
                                <span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">ROLE: CLIENT</span>
                            </div>
                            <div>
                                <h3 class="font-heading font-bold text-xl text-[var(--text-primary)]">Client Application</h3>
                                <p class="text-xs text-[var(--text-secondary)] mt-1">Interface pengguna/pendengar untuk eksplorasi, playlist, & player audio.</p>
                            </div>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="client@app.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/client/')" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-mono font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2">
                                <i class="ph ph-sign-in text-base"></i> LOGIN CLIENT
                            </button>
                        </form>
                    </div>

                </div>
            </div>
        </section>

        <!-- Open Source CTA Banner -->
        <section class="py-16 bg-gradient-brand border-t border-orange-500/30 text-white">
            <div class="max-w-4xl mx-auto px-4 text-center space-y-4">
                <span class="font-mono text-xs uppercase tracking-widest text-orange-200 font-bold">// OPEN SOURCE REPOSITORY //</span>
                <h2 class="text-3xl sm:text-4xl font-heading font-extrabold">Siap Mengembangkan Vibeforge?</h2>
                <p class="text-white/80 max-w-xl mx-auto text-sm">Proyek ini 100% open-source di bawah lisensi Apache 2.0. Bebas digunakan untuk aplikasi komersial maupun pribadi.</p>
                <div class="pt-2">
                    <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="inline-flex items-center gap-2 px-8 py-3.5 bg-gray-950 text-white font-mono font-bold text-xs rounded-xl hover:bg-gray-900 transition-colors shadow-2xl">
                        <i class="ph ph-github-logo text-lg text-orange-400"></i> github.com/iqbalmurtadho24/vibeforge
                    </a>
                </div>
            </div>
        </section>
    </main>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-[var(--bg-card)]/95 backdrop-blur-md border-t border-[var(--border-default)] z-50 shadow-lg font-mono">
        <div class="flex justify-around items-center h-16 px-2">
            <a href="#fitur" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-cpu text-xl"></i>
                <span class="text-[9px] font-semibold">Fitur</span>
            </a>
            <a href="#cara-pasang" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-terminal-window text-xl"></i>
                <span class="text-[9px] font-semibold">Pasang</span>
            </a>
            <a href="#demo" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-shield-check text-xl"></i>
                <span class="text-[9px] font-semibold">Demo</span>
            </a>
            <a href="/install/" class="flex flex-col items-center gap-1 text-[var(--brand-primary)]">
                <i class="ph ph-magic-wand text-xl"></i>
                <span class="text-[9px] font-bold">Wizard</span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <footer class="bg-[var(--bg-secondary)] border-t border-[var(--border-default)] py-8 pb-24 md:pb-8 font-mono text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[var(--text-muted)]">
            <p>&copy; 2026 <?= APP_DISPLAY_NAME ?>. Developer-Grade Vibe Coding Architecture.</p>
            <div class="flex gap-6">
                <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="hover:text-[var(--brand-primary)] transition-colors">GitHub</a>
                <a href="#" class="hover:text-[var(--brand-primary)] transition-colors">Docs</a>
                <a href="#" class="hover:text-[var(--brand-primary)] transition-colors">Apache-2.0 License</a>
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
                setupCancelled: false,
                subPath() {
                    return this.server === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs';
                },
                fullTargetDir() {
                    return this.drive + ':\\' + this.subPath() + '\\' + (this.appName.trim() || 'myapp');
                },
                fullCommand() {
                    return `irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex`;
                },
                wizardUrl() {
                    const name = this.appName.trim() || 'myapp';
                    return 'http://localhost/' + name + '/public/install/';
                },
                sanitizeAppName() {
                    this.appName = this.appName.replace(/[^a-zA-Z0-9_-]/g, '');
                    this.appNameError = /[^a-zA-Z0-9_-]/.test(this.appName);
                },
                init() {
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

        // Copy Snippet Helper
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
                btn.innerHTML = '<i class="ph ph-check text-emerald-400"></i> Copied!';
            } catch(e) {
                btn.innerHTML = '<i class="ph ph-warning text-red-400"></i> Failed!';
            } fontFinally: {
                setTimeout(() => { btn.innerHTML = originalHtml; }, 2000);
            }
        }

        // Start Full Setup Workflow
        async function startFullSetup() {
            const dataEl = document.getElementById('appDownloaderComponent');
            if (!dataEl) return;
            const state = Alpine.$data(dataEl);
            const appName = state.appName.trim() || 'myapp';
            const targetUrl = 'http://' + appName + '.test/install/';
            const fallbackUrl = 'http://localhost/' + appName + '/public/install/';

            state.showTerminal = true;
            state.isSettingUp = true;
            state.setupCancelled = false;
            state.terminalLines = [
                '═══════════════════════════════════════════════════════',
                '  Vibeforge Automated CLI Deployment Engine',
                '  Target App:  ' + appName,
                '  Server:      ' + state.server.toUpperCase(),
                '  Target Disk: ' + state.drive + ':',
                '  VHost URL:   http://' + appName + '.test/install/',
                '═══════════════════════════════════════════════════════',
                ''
            ];

            const addLog = (msg, delay = 400) => {
                return new Promise(resolve => {
                    setTimeout(() => {
                        if (state.setupCancelled) return resolve();
                        state.terminalLines.push(msg);
                        resolve();
                    }, delay);
                });
            };

            await addLog('[1/3] Mengunduh template Vibeforge via degit...', 300);
            await addLog('[CMD] ' + state.fullCommand(), 200);

            try {
                const res1 = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        module: 'install',
                        action: 'execute',
                        drive: state.drive,
                        serverType: state.server,
                        projectName: appName,
                        command: state.fullCommand(),
                        csrf_token: '<?= $csrfToken ?>'
                    })
                });
                const data1 = await res1.json();

                if (data1.success) {
                    await addLog('[OK] Terminal PowerShell berhasil dibuka — proses download berjalan...', 600);
                    await addLog('[INFO] Menunggu pengerjaan degit...', 800);
                    let found = false;
                    for (let i = 0; i < 10; i++) {
                        await new Promise(r => setTimeout(r, 1000));
                        if (state.setupCancelled) return;
                        try {
                            const cr = await fetch('/core/router.php', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                body: JSON.stringify({
                                    module: 'install',
                                    action: 'check_folder',
                                    drive: state.drive,
                                    serverType: state.server,
                                    projectName: appName,
                                    csrf_token: '<?= $csrfToken ?>'
                                })
                            });
                            const cd = await cr.json();
                            if (cd.exists) {
                                found = true;
                                state.folderExists = true;
                                break;
                            }
                        } catch(e) {}
                    }
                    if (found) {
                        await addLog('[OK] Folder target terdeteksi — download template selesai.', 200);
                    } else {
                        await addLog('[WARN] Folder belum terdeteksi, melanjutkan proses setup...', 200);
                    }
                } else {
                    await addLog('[WARN] ' + (data1.error || 'Gagal membuka terminal download.'), 0);
                    await addLog('[INFO] Mencoba lanjut ke Virtual Host setup...', 600);
                }
            } catch(e) {
                await addLog('[WARN] Error: ' + e.message, 0);
            }

            if (state.setupCancelled) { state.isSettingUp = false; return; }

            await addLog('', 300);
            await addLog('[2/3] Konfigurasi Virtual Host & file hosts Windows...', 600);
            await addLog('[INFO] Domain: http://' + appName + '.test/', 300);

            try {
                const res2 = await fetch('/core/router.php', {
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
                const data2 = await res2.json();

                if (data2.success) {
                    await addLog('[OK] Virtual Host berhasil dibuat!', 300);
                    await addLog('[OK] Host local DNS (127.0.0.1 ' + appName + '.test) ditambahkan.', 300);
                    await addLog('[OK] Web Server Apache di-restart.', 300);
                } else {
                    await addLog('[WARN] ' + (data2.error || 'VirtualHost setup gagal.'), 0);
                }
            } catch(e) {
                await addLog('[WARN] Error koneksi saat setup VirtualHost.', 0);
            }

            if (state.setupCancelled) { state.isSettingUp = false; return; }

            await addLog('', 300);
            await addLog('═══════════════════════════════════════════════════════', 200);
            await addLog('[3/3] Deployment selesai! Membuka Setup Wizard...', 400);
            await addLog('[→] ' + targetUrl, 200);
            await addLog('═══════════════════════════════════════════════════════', 400);

            setTimeout(() => {
                state.isSettingUp = false;
                window.location.href = targetUrl;
                setTimeout(() => {
                    if (window.location.href !== targetUrl) return;
                    window.location.href = fallbackUrl;
                }, 4000);
            }, 1500);
        }

        function cancelSetup() {
            const dataEl = document.getElementById('appDownloaderComponent');
            if (!dataEl) return;
            const state = Alpine.$data(dataEl);
            state.setupCancelled = true;
            state.isSettingUp = false;
            state.showTerminal = false;
            setTimeout(() => { state.setupCancelled = false; }, 300);
        }

        async function directLogin(event, form, targetUrl) {
            event.preventDefault();
            const btn = event.currentTarget;
            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<i class="ph ph-circle-notch animate-spin text-base"></i> MEMPROSES...';

            try {
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
