<?php
/**
 * Vibeforge - Admin Studio (Creator Studio)
 * Cyber-Tech & 13 Pillars Architecture Edition
 */
defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';

initSession();

if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
    $_SESSION['language'] = $_GET['lang'];
}

$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$isRtl = isRtlLanguage();

$isLoggedIn = isLoggedIn();
$user = getCurrentUser();

if (!$isLoggedIn || ($user['role'] ?? null) !== 'admin') {
    header('Location: /login/');
    exit;
}

$themePreference = $user['theme_preference'] ?? 'dark';
$userName = escape($user['name'] ?? 'Creator');
$userInitial = strtoupper(substr($userName, 0, 2));
$userEmail = escape($user['email'] ?? '');

// Load Data via Data Access Layer (Repo)
$allUsers = Repo::table('users')->all();
$totalUsers = count($allUsers);
$auditTrailCount = count(Repo::table('audit_trail')->all());
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $userName ?> - <?= escape(APP_DISPLAY_NAME) ?> Creator Studio</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F97316'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
        .text-gradient {
            background: linear-gradient(135deg, #F97316 0%, #FBBF24 50%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .bg-gradient-brand { background: linear-gradient(135deg, #F97316 0%, #EA580C 100%); }
        .glow-box-cyber { box-shadow: 0 0 0 1px rgba(249, 115, 22, 0.2), 0 10px 30px -10px rgba(0, 0, 0, 0.8); }
    </style>
</head>
<body class="antialiased h-screen flex flex-col overflow-hidden tech-grid bg-[var(--bg-primary)]">

<div class="flex flex-1 overflow-hidden">
    <!-- Sidebar -->
    <aside class="hidden md:flex flex-col w-64 bg-gray-950 border-r border-[var(--border-default)] shrink-0 font-mono">
        <div class="h-16 flex items-center px-6 border-b border-[var(--border-default)]">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center group-hover:border-orange-500 transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#F97316"><path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base tracking-tight leading-none"><span class="text-white">Vibe</span><span class="text-gradient">forge</span></span>
                    <span class="text-[9px] text-orange-400 font-bold tracking-wider uppercase">CREATOR_STUDIO</span>
                </div>
            </a>
        </div>

        <nav class="flex-1 py-4 px-3 space-y-1 text-xs">
            <button onclick="nav('overview')" id="s-overview" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold bg-orange-500/10 text-[var(--brand-primary)] border border-orange-500/20 transition-all"><i class="ph-fill ph-squares-four text-lg"></i> <?= t('studio.overview') ?></button>
            <button onclick="nav('howto')" id="s-howto" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-400 hover:bg-gray-900 hover:text-white transition-colors"><i class="ph ph-book-open text-lg"></i> <?= t('nav_how_it_works') ?></button>
            <button onclick="nav('structure')" id="s-structure" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-400 hover:bg-gray-900 hover:text-white transition-colors"><i class="ph ph-tree-structure text-lg"></i> <?= t('index.arch_link') ?></button>
            <button onclick="nav('profile')" id="s-profile" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-400 hover:bg-gray-900 hover:text-white transition-colors"><i class="ph ph-user text-lg"></i> <?= t('client.profile') ?></button>
        </nav>

        <div class="p-4 border-t border-[var(--border-default)] bg-gray-900/50">
            <div class="flex items-center gap-3 px-2 py-1.5">
                <div class="w-9 h-9 rounded-xl bg-orange-500/20 border border-orange-500/40 flex items-center justify-center text-orange-400 font-bold text-xs"><?= $userInitial ?></div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate"><?= $userName ?></p>
                    <p class="text-[10px] text-orange-400 uppercase font-semibold">Creator Role</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 flex items-center justify-between px-6 bg-gray-950/80 border-b border-[var(--border-default)] shrink-0 font-mono text-xs">
            <div class="flex items-center gap-3">
                <h1 id="pageTitle" class="font-heading font-extrabold text-sm text-white tracking-wide uppercase">// OVERVIEW</h1>
                <span class="px-2.5 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-500/20 text-[10px] flex items-center gap-1.5 font-bold">
                    <span class="w-1.5 h-1.5 rounded-full bg-orange-400 animate-pulse"></span> BUILD_ACTIVE
                </span>
            </div>
            <div class="flex items-center gap-3">
                <!-- Language Selector -->
                <div class="relative group" x-data="{ open: false }">
                    <button @click="open = !open" @click.away="open = false" class="flex items-center gap-2 px-2.5 py-1.5 rounded-lg bg-gray-900 border border-gray-800 hover:border-orange-500 transition-colors text-xs font-mono" aria-label="Change Language">
                        <img src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" alt="<?= $currentLang ?>" class="w-4 h-3 rounded-sm">
                        <span class="hidden sm:inline uppercase font-bold text-gray-300"><?= escape($currentLang) ?></span>
                        <i class="ph ph-caret-down text-xs text-gray-500"></i>
                    </button>
                    <div x-show="open" x-transition class="absolute right-0 mt-1 bg-gray-900 rounded-xl shadow-2xl border border-gray-800 py-1 min-w-[160px] z-50 text-xs font-mono">
                        <?php foreach (getAvailableLanguages() as $code => $lang): ?>
                        <a href="<?= escape(buildLangUrl($code)) ?>" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-gray-800 transition-colors <?= $currentLang === $code ? 'text-orange-400 font-bold bg-orange-500/10' : 'text-gray-300' ?>">
                            <img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-4 h-3 rounded-sm">
                            <span><?= escape($lang['name']) ?></span>
                        </a>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button id="themeToggle" class="p-2 hover:bg-gray-800 rounded-lg text-amber-400 transition-colors" aria-label="Toggle theme"><i class="ph ph-moon text-base"></i></button>
                <a href="/logout/" class="p-2 hover:bg-red-500/10 rounded-lg text-red-400 transition-colors" title="<?= t('auth_logout') ?>"><i class="ph ph-sign-out text-base"></i></a>
            </div>
        </header>

        <div id="content" class="flex-1 overflow-y-auto p-6 pb-24 md:pb-6 font-sans"></div>
    </main>
</div>

<!-- Mobile Nav -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-950 border-t border-gray-800 z-50 font-mono text-xs">
    <div class="flex justify-around items-center h-16">
        <button onclick="nav('overview')" class="flex flex-col items-center gap-1 text-orange-400" id="m-overview"><i class="ph-fill ph-squares-four text-xl"></i><span class="text-[9px]">Overview</span></button>
        <button onclick="nav('howto')" class="flex flex-col items-center gap-1 text-gray-400" id="m-howto"><i class="ph ph-book-open text-xl"></i><span class="text-[9px]">Workflow</span></button>
        <button onclick="nav('structure')" class="flex flex-col items-center gap-1 text-gray-400" id="m-structure"><i class="ph ph-tree-structure text-xl"></i><span class="text-[9px]">Tree</span></button>
        <button onclick="nav('profile')" class="flex flex-col items-center gap-1 text-gray-400" id="m-profile"><i class="ph ph-user text-xl"></i><span class="text-[9px]">Profil</span></button>
    </div>
</nav>

<script>
window._i18n = {
    overview: <?= json_encode(t('studio.overview')) ?>,
    howto: <?= json_encode(t('nav_how_it_works')) ?>,
    structure: <?= json_encode(t('index.arch_link')) ?>,
    profile: <?= json_encode(t('client.profile')) ?>
};

const V = {
    overview: `
        <div class="space-y-6">
            <!-- Bento Grid Metrics -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 font-mono">
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span class="text-xs uppercase font-bold">Workspace Role</span>
                        <i class="ph ph-code text-orange-400 text-xl"></i>
                    </div>
                    <p class="text-2xl font-extrabold text-orange-400">Creator</p>
                    <span class="text-[10px] text-gray-400">Full Template Auth</span>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span class="text-xs uppercase font-bold">Active Shells</span>
                        <i class="ph ph-layout text-blue-400 text-xl"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-white">6</p>
                    <span class="text-[10px] text-emerald-400">Clean URL Proxied</span>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span class="text-xs uppercase font-bold">System Users</span>
                        <i class="ph ph-users text-purple-400 text-xl"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-purple-400"><?= $totalUsers ?></p>
                    <span class="text-[10px] text-gray-400">Active Accounts</span>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span class="text-xs uppercase font-bold">Audit Events</span>
                        <i class="ph ph-shield-check text-emerald-400 text-xl"></i>
                    </div>
                    <p class="text-3xl font-extrabold text-emerald-400"><?= $auditTrailCount ?></p>
                    <span class="text-[10px] text-gray-400">Recorded Log Events</span>
                </div>
            </div>

            <!-- Creator Setup Wizard Banner -->
            <div class="bg-gradient-brand rounded-2xl p-6 text-white shadow-xl glow-box-cyber font-mono">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center shrink-0">
                            <i class="ph ph-rocket-launch text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-lg">Vibeforge Creator Studio</h3>
                            <p class="text-white/80 text-xs font-sans">Kembangkan aplikasi bisnis Anda berbasis template dan AI Assistant.</p>
                        </div>
                    </div>
                    <a href="/install/" class="px-6 py-3 bg-gray-950 text-orange-400 font-bold text-xs rounded-xl hover:bg-gray-900 transition-colors shadow-lg flex items-center gap-2 shrink-0">
                        <i class="ph ph-magic-wand text-base"></i> BUKA WIZARD
                    </a>
                </div>
            </div>

            <!-- Architecture Guidelines for Creator -->
            <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-6 space-y-4 glow-box-cyber font-mono">
                <h3 class="font-bold text-xs uppercase text-orange-400 flex items-center gap-2">
                    <i class="ph ph-tree-structure text-base"></i> Pedoman Pengembang Creator (13 Pilar)
                </h3>
                <div class="grid md:grid-cols-2 gap-4 text-xs font-sans">
                    <div class="p-4 bg-gray-900/60 rounded-xl border border-gray-800">
                        <p class="font-bold font-mono text-orange-400 mb-1">SPA Shell Architecture (Pilar 1)</p>
                        <p class="text-gray-400 text-xs leading-relaxed">Navigasi antar tab menggunakan AJAX ke core/router.php tanpa reload halaman penuh.</p>
                    </div>
                    <div class="p-4 bg-gray-900/60 rounded-xl border border-gray-800">
                        <p class="font-bold font-mono text-orange-400 mb-1">Centralized Data Repo (Pilar 3)</p>
                        <p class="text-gray-400 text-xs leading-relaxed">Akses data wajib via Repo::table() tanpa query PDO atau manipulasi JSON langsung.</p>
                    </div>
                </div>
            </div>
        </div>
    `,
    howto: `
        <div class="space-y-6 font-mono text-xs">
            <h2 class="text-xl font-bold uppercase text-white">// ALUR PENGEMBANGAN CREATOR</h2>
            <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-6 space-y-4 glow-box-cyber font-sans">
                <p class="text-gray-300 text-sm">Sebagai Creator/Admin, Anda menggunakan <strong>Vibeforge</strong> untuk membangun aplikasi apapun dalam 3 langkah mudah:</p>
                <div class="grid md:grid-cols-3 gap-4 font-mono text-xs">
                    <div class="p-4 bg-gray-900/60 rounded-xl border border-gray-800 space-y-2">
                        <span class="text-orange-400 font-bold">01. TULIS DOKUMEN</span>
                        <p class="text-gray-400 font-sans text-xs">Isi <code class="text-orange-400">docs/prd.md</code> dengan ide aplikasi Anda.</p>
                    </div>
                    <div class="p-4 bg-gray-900/60 rounded-xl border border-gray-800 space-y-2">
                        <span class="text-orange-400 font-bold">02. SET BRANDING</span>
                        <p class="text-gray-400 font-sans text-xs">Atur warna & font di <code class="text-orange-400">docs/branding.md</code>.</p>
                    </div>
                    <div class="p-4 bg-gray-900/60 rounded-xl border border-gray-800 space-y-2">
                        <span class="text-orange-400 font-bold">03. JALANKAN AI</span>
                        <p class="text-gray-400 font-sans text-xs">Jalankan CLI Claude Code / Cursor AI untuk membuat kodenya.</p>
                    </div>
                </div>
            </div>
        </div>
    `,
    structure: `
        <div class="space-y-6 font-mono text-xs">
            <h2 class="text-xl font-bold uppercase text-white">// STRUKTUR DIREKTORI REPO</h2>
            <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-6 glow-box-cyber text-emerald-400 leading-relaxed overflow-x-auto">
                <pre>
vibeforge/
├── public/                 <- Document Root Apache
│   ├── index.php           <- Landing Page
│   ├── login/              <- Shell Login
│   ├── register/           <- Shell Register
│   ├── manajemen/          <- Shell Super Admin
│   ├── admin/              <- Shell Creator Studio
│   ├── client/             <- Shell Client Application
│   └── core/router.php     <- Router Proxy (WAJIB ADA)
├── core/                   <- Router, Auth, Session, Repo Dual-Engine
├── include/                <- Config, Helper, i18n
├── modules/                <- Modul AJAX per Role
└── docs/                   <- PRD & Branding Specification
                </pre>
            </div>
        </div>
    `,
    profile: `
        <div class="max-w-xl space-y-6 font-mono text-xs">
            <h2 class="text-xl font-bold uppercase text-white">// PROFIL CREATOR</h2>
            <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-6 text-center glow-box-cyber space-y-3">
                <div class="w-20 h-20 rounded-2xl bg-orange-500/20 border border-orange-500/40 flex items-center justify-center text-orange-400 text-2xl font-bold mx-auto shadow-lg"><?= $userInitial ?></div>
                <h3 class="text-lg font-bold text-white"><?= $userName ?></h3>
                <p class="text-gray-400"><?= $userEmail ?></p>
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold bg-orange-500/10 text-orange-400 border border-orange-500/20">CREATOR ROLE</span>
            </div>
            <a href="/logout/" class="flex items-center justify-center gap-2 py-3.5 bg-red-500/10 text-red-400 border border-red-500/30 font-bold rounded-xl hover:bg-red-500/20 transition-colors"><i class="ph ph-sign-out text-base"></i> <?= t('auth_logout') ?></a>
        </div>
    `
};

function nav(p) {
    document.getElementById('content').innerHTML = V[p] || V.overview;
    document.getElementById('pageTitle').textContent = '// ' + (window._i18n[p] || window._i18n.overview).toUpperCase();
    ['overview','howto','structure','profile'].forEach(k => {
        const d = document.getElementById('s-'+k);
        const m = document.getElementById('m-'+k);
        if(d) {
            if(k===p) { d.className='w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold bg-orange-500/10 text-[var(--brand-primary)] border border-orange-500/20 transition-all'; }
            else { d.className='w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-400 hover:bg-gray-900 hover:text-white transition-colors'; }
        }
        if(m) {
            if(k===p) { m.className='flex flex-col items-center gap-1 text-orange-400'; }
            else { m.className='flex flex-col items-center gap-1 text-gray-400'; }
        }
    });
}

const html = document.documentElement;
function updateThemeUI(theme) {
    const isDark = theme === 'dark';
    html.classList.toggle('dark', isDark);
    html.setAttribute('data-theme', theme);
    const icon = document.querySelector('#themeToggle i');
    if (icon) {
        icon.className = isDark ? 'ph ph-moon text-base text-amber-400' : 'ph ph-sun text-base text-amber-500';
    }
}
document.getElementById('themeToggle')?.addEventListener('click', () => {
    const current = html.classList.contains('dark') ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', next);
    updateThemeUI(next);
});
updateThemeUI(localStorage.getItem('theme') || 'dark');
nav('overview');
</script>
</body>
</html>
