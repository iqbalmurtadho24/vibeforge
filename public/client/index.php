<?php
/**
 * Vibeforge - Client Dashboard
 * IT Professional / Cyber-Tech Edition
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
$userRole = $user['role'] ?? null;

if (!$isLoggedIn || $userRole !== 'client') {
    header('Location: /login/');
    exit;
}

$themePreference = $user['theme_preference'] ?? 'dark';
$userName = escape($user['name'] ?? 'User');
$userInitial = strtoupper(substr($userName, 0, 2));
$userEmail = escape($user['email'] ?? '');
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $userName ?> - <?= APP_DISPLAY_NAME ?> Client</title>
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
                    colors: { brand: { primary: '#F97316', dark: '#0B0F17', card: '#111726', border: '#1E293B' } },
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
    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex flex-col w-64 bg-gray-950 border-r border-[var(--border-default)] shrink-0 font-mono">
        <div class="h-16 flex items-center px-6 border-b border-[var(--border-default)]">
            <a href="/" class="flex items-center gap-3 group">
                <div class="w-8 h-8 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center group-hover:border-orange-500 transition-colors">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="#F97316"><path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/></svg>
                </div>
                <div class="flex flex-col">
                    <span class="font-heading font-extrabold text-base tracking-tight leading-none"><span class="text-white">Vibe</span><span class="text-gradient">forge</span></span>
                    <span class="text-[9px] text-emerald-400 font-bold tracking-wider uppercase">CLIENT_PORTAL</span>
                </div>
            </a>
        </div>

        <nav class="flex-1 py-4 px-3 space-y-1 text-xs">
            <button onclick="nav('home')" id="s-home" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-bold bg-orange-500/10 text-[var(--brand-primary)] border border-orange-500/20 transition-all"><i class="ph-fill ph-house text-lg"></i> <?= t('client.home') ?></button>
            <button onclick="nav('templates')" id="s-templates" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-400 hover:bg-gray-900 hover:text-white transition-colors"><i class="ph ph-layout text-lg"></i> <?= t('client.explore') ?></button>
            <button onclick="nav('docs')" id="s-docs" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-400 hover:bg-gray-900 hover:text-white transition-colors"><i class="ph ph-book-open text-lg"></i> <?= t('nav_docs') ?></button>
            <button onclick="nav('profile')" id="s-profile" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl font-medium text-gray-400 hover:bg-gray-900 hover:text-white transition-colors"><i class="ph ph-user text-lg"></i> <?= t('client.profile') ?></button>
        </nav>

        <div class="p-4 border-t border-[var(--border-default)] bg-gray-900/50">
            <div class="flex items-center gap-3 px-2 py-1.5">
                <div class="w-9 h-9 rounded-xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 font-bold text-xs"><?= $userInitial ?></div>
                <div class="overflow-hidden">
                    <p class="text-xs font-bold text-white truncate"><?= $userName ?></p>
                    <p class="text-[10px] text-emerald-400 uppercase font-semibold">Client Role</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 flex items-center justify-between px-6 bg-gray-950/80 border-b border-[var(--border-default)] shrink-0 font-mono text-xs">
            <div class="flex items-center gap-3">
                <h1 id="pageTitle" class="font-heading font-extrabold text-sm text-white tracking-wide uppercase">// BERANDA CLIENT</h1>
                <span class="px-2 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px]">CONNECTED</span>
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

                <button id="themeToggle" class="p-2 hover:bg-gray-800 rounded-lg text-amber-400" aria-label="Toggle theme"><i class="ph ph-moon text-base"></i></button>
                <a href="/logout/" class="p-2 hover:bg-red-500/10 rounded-lg text-red-400" title="<?= t('auth_logout') ?>"><i class="ph ph-sign-out text-base"></i></a>
            </div>
        </header>

        <div id="content" class="flex-1 overflow-y-auto p-6 pb-24 md:pb-6 font-sans"></div>
    </main>
</div>

<!-- Mobile Nav -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-gray-950 border-t border-gray-800 z-50 font-mono text-xs">
    <div class="flex justify-around items-center h-16">
        <button onclick="nav('home')" class="flex flex-col items-center gap-1 text-orange-400" id="m-home"><i class="ph-fill ph-house text-xl"></i><span class="text-[9px]">Beranda</span></button>
        <button onclick="nav('templates')" class="flex flex-col items-center gap-1 text-gray-400" id="m-templates"><i class="ph ph-layout text-xl"></i><span class="text-[9px]">Template</span></button>
        <button onclick="nav('docs')" class="flex flex-col items-center gap-1 text-gray-400" id="m-docs"><i class="ph ph-book-open text-xl"></i><span class="text-[9px]">Dokumen</span></button>
        <button onclick="nav('profile')" class="flex flex-col items-center gap-1 text-gray-400" id="m-profile"><i class="ph ph-user text-xl"></i><span class="text-[9px]">Profil</span></button>
    </div>
</nav>

<script>
window._i18n = {
    home: <?= json_encode(t('client.home')) ?>,
    templates: <?= json_encode(t('client.explore')) ?>,
    docs: <?= json_encode(t('nav_docs')) ?>,
    profile: <?= json_encode(t('client.profile')) ?>
};

const V = {
    home: `
        <div class="space-y-6">
            <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-6 glow-box-cyber font-mono space-y-2">
                <h2 class="text-xl font-bold text-white">Selamat Datang, <?= addslashes($userName) ?>!</h2>
                <p class="text-xs text-gray-400 font-sans">Eksplorasi arsitektur SPA Vibeforge untuk proyek aplikasi web Anda.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 font-mono text-xs">
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span>Active Projects</span>
                        <i class="ph ph-folder-simple text-orange-400 text-xl"></i>
                    </div>
                    <p class="text-2xl font-bold text-white">1</p>
                    <span class="text-[10px] text-gray-400">Vibeforge Template</span>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span>Steps Done</span>
                        <i class="ph ph-check-circle text-emerald-400 text-xl"></i>
                    </div>
                    <p class="text-2xl font-bold text-emerald-400">3/12</p>
                    <span class="text-[10px] text-gray-400">Setup Progress</span>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span>HTML Reference</span>
                        <i class="ph ph-code text-blue-400 text-xl"></i>
                    </div>
                    <p class="text-2xl font-bold text-white">6 Files</p>
                    <span class="text-[10px] text-gray-400">Golden Templates</span>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-2">
                    <div class="flex items-center justify-between text-gray-400">
                        <span>Deploy Status</span>
                        <i class="ph ph-rocket-launch text-purple-400 text-xl"></i>
                    </div>
                    <p class="text-2xl font-bold text-purple-400">READY</p>
                    <span class="text-[10px] text-gray-400">VirtualHost Ready</span>
                </div>
            </div>

            <!-- Setup Wizard Banner -->
            <div class="bg-gradient-brand rounded-2xl p-6 text-white shadow-xl glow-box-cyber font-mono">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-white/20 border border-white/30 flex items-center justify-center shrink-0">
                            <i class="ph ph-magic-wand text-2xl"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-extrabold text-lg">Setup Wizard Vibeforge</h3>
                            <p class="text-white/80 text-xs font-sans">Siapkan proyek baru dengan AI assistant dalam hitungan menit.</p>
                        </div>
                    </div>
                    <a href="/install/" class="px-6 py-3 bg-gray-950 text-orange-400 font-bold text-xs rounded-xl hover:bg-gray-900 transition-colors shadow-lg flex items-center gap-2 shrink-0">
                        <i class="ph ph-rocket-launch text-base"></i> LAUNCH WIZARD
                    </a>
                </div>
            </div>
        </div>
    `,
    templates: `
        <div class="space-y-6 font-mono text-xs">
            <h2 class="text-xl font-bold uppercase text-white">// TEMPLATE SHELL REPOSITORI</h2>
            <div class="grid md:grid-cols-3 gap-4 font-sans text-xs">
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-3 font-mono">
                    <div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400">
                        <i class="ph ph-crown text-xl"></i>
                    </div>
                    <h3 class="font-bold text-white text-sm">Super Admin Shell</h3>
                    <p class="text-gray-400 text-xs font-sans">Halaman manajemen overview sistem, RBAC, dan audit trail.</p>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-3 font-mono">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-orange-400">
                        <i class="ph ph-rocket-launch text-xl"></i>
                    </div>
                    <h3 class="font-bold text-white text-sm">Creator Studio Shell</h3>
                    <p class="text-gray-400 text-xs font-sans">Portal produksi karya & analitik untuk kreator terverifikasi.</p>
                </div>
                <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-5 glow-box-cyber space-y-3 font-mono">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400">
                        <i class="ph ph-user text-xl"></i>
                    </div>
                    <h3 class="font-bold text-white text-sm">Client Portal Shell</h3>
                    <p class="text-gray-400 text-xs font-sans">Interface pengguna untuk navigasi cepat berbasis SPA AJAX.</p>
                </div>
            </div>
        </div>
    `,
    docs: `
        <div class="space-y-6 font-mono text-xs">
            <h2 class="text-xl font-bold uppercase text-white">// DOKUMENTASI SISTEM</h2>
            <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-6 space-y-3 glow-box-cyber font-sans">
                <p class="text-gray-300 leading-relaxed text-xs">Semua panduan pengembangan aplikasi disimpan pada folder <code class="text-orange-400 font-mono">docs/</code>:</p>
                <ul class="list-disc list-inside text-gray-400 space-y-1.5 font-mono text-xs">
                    <li><strong class="text-white">docs/prd.md</strong> — Definisi fitur dan kebutuhan aplikasi</li>
                    <li><strong class="text-white">docs/branding.md</strong> — Palette warna, typo, & identitas visual</li>
                    <li><strong class="text-white">docs/install.md</strong> — Panduan instalasi 12 langkah</li>
                </ul>
            </div>
        </div>
    `,
    profile: `
        <div class="max-w-xl space-y-6 font-mono text-xs">
            <h2 class="text-xl font-bold uppercase text-white">// PROFIL CLIENT</h2>
            <div class="bg-gray-950/90 rounded-2xl border border-gray-800 p-6 text-center glow-box-cyber space-y-3">
                <div class="w-20 h-20 rounded-2xl bg-emerald-500/20 border border-emerald-500/40 flex items-center justify-center text-emerald-400 text-2xl font-bold mx-auto shadow-lg"><?= $userInitial ?></div>
                <h3 class="text-lg font-bold text-white"><?= $userName ?></h3>
                <p class="text-gray-400"><?= $userEmail ?></p>
                <span class="inline-block px-3 py-1 rounded-full text-[10px] font-bold bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">CLIENT ROLE</span>
            </div>
            <a href="/logout/" class="flex items-center justify-center gap-2 py-3.5 bg-red-500/10 text-red-400 border border-red-500/30 font-bold rounded-xl hover:bg-red-500/20 transition-colors"><i class="ph ph-sign-out text-base"></i> <?= t('auth_logout') ?></a>
        </div>
    `
};

function nav(p) {
    document.getElementById('content').innerHTML = V[p] || V.home;
    document.getElementById('pageTitle').textContent = '// ' + (window._i18n[p] || window._i18n.home).toUpperCase();
    ['home','templates','docs','profile'].forEach(k => {
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
nav('home');
</script>
</body>
</html>
