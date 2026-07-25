<?php
/**
 * Vibeforge - Client Dashboard
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
<html lang="<?= $currentLang ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $userName ?> - <?= APP_DISPLAY_NAME ?> Client</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FF6B35'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/branding.css">
    <script>
        tailwind.config = { darkMode: 'class', theme: { extend: { colors: { brand: { primary: '#F97316', dark: '#0D1117', card: '#161B22' } } } } }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: var(--bg-primary); color: var(--text-primary); }
        h1, h2, h3 { font-family: 'Poppins', sans-serif; }
        .text-gradient {
            background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%);
            -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text;
        }
        .bg-gradient-brand { background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%); }
    </style>
</head>
<body class="antialiased h-screen flex flex-col overflow-hidden">

<div class="flex flex-1 overflow-hidden">
    <!-- Desktop Sidebar -->
    <aside class="hidden md:flex flex-col w-64 bg-[var(--bg-card)] border-r border-[var(--border-default)] shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-[var(--border-default)]">
            <a href="/" class="flex items-center gap-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#F97316">
                    <path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/>
                </svg>
                <span class="font-heading font-bold text-lg">
                    <span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span>
                </span>
                <span class="text-xs text-[var(--text-muted)] ml-1">Client</span>
            </a>
        </div>

        <nav class="flex-1 py-4 px-3 space-y-1">
            <button onclick="nav('home')" id="s-home" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium bg-[var(--brand-primary-light)] text-[var(--brand-primary)]">
                <i class="ph-fill ph-house text-xl"></i> Beranda
            </button>
            <button onclick="nav('templates')" id="s-templates" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors">
                <i class="ph ph-layout text-xl"></i> Template
            </button>
            <button onclick="nav('docs')" id="s-docs" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors">
                <i class="ph ph-book-open text-xl"></i> Dokumentasi
            </button>
            <button onclick="nav('profile')" id="s-profile" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors">
                <i class="ph ph-user text-xl"></i> Profil
            </button>
        </nav>

        <div class="p-4 border-t border-[var(--border-default)]">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#F97316] to-orange-400 flex items-center justify-center text-white font-bold">
                    <?= $userInitial ?>
                </div>
                <div>
                    <p class="text-sm font-medium"><?= $userName ?></p>
                    <p class="text-xs text-[var(--brand-primary)]">Client</p>
                </div>
            </div>
        </div>
    </aside>

    <!-- Main Content Area -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 flex items-center justify-between px-6 bg-[var(--bg-secondary)] border-b border-[var(--border-default)] shrink-0">
            <h1 id="pageTitle" class="font-heading font-semibold">Beranda</h1>
            <div class="flex items-center gap-2">
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

                <button id="themeToggle" class="p-2 hover:bg-[var(--bg-hover)] rounded-lg" aria-label="Toggle theme">
                    <i class="ph ph-moon text-lg text-[var(--brand-primary)]"></i>
                </button>
                <a href="/logout/" class="p-2 hover:bg-red-500/10 rounded-lg text-red-400" title="Logout">
                    <i class="ph ph-sign-out text-lg"></i>
                </a>
            </div>
        </header>

        <div id="content" class="flex-1 overflow-y-auto p-6 pb-24 md:pb-6"></div>
    </main>
</div>

<!-- Mobile Bottom Navigation -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-[var(--bg-card)] border-t border-[var(--border-default)] z-50">
    <div class="flex justify-around items-center h-16">
        <button onclick="nav('home')" class="flex flex-col items-center gap-1 text-[var(--brand-primary)]" id="m-home">
            <i class="ph-fill ph-house text-2xl"></i>
            <span class="text-[10px] font-medium">Beranda</span>
        </button>
        <button onclick="nav('templates')" class="flex flex-col items-center gap-1 text-[var(--text-muted)]" id="m-templates">
            <i class="ph ph-layout text-2xl"></i>
            <span class="text-[10px] font-medium">Template</span>
        </button>
        <button onclick="nav('docs')" class="flex flex-col items-center gap-1 text-[var(--text-muted)]" id="m-docs">
            <i class="ph ph-book-open text-2xl"></i>
            <span class="text-[10px] font-medium">Dokumen</span>
        </button>
        <button onclick="nav('profile')" class="flex flex-col items-center gap-1 text-[var(--text-muted)]" id="m-profile">
            <i class="ph ph-user text-2xl"></i>
            <span class="text-[10px] font-medium">Profil</span>
        </button>
    </div>
</nav>

<script>
const V = {
    home: `
        <div class="space-y-6">
            <div>
                <h2 class="text-2xl sm:text-3xl font-bold mb-2">Selamat datang, <?= addslashes($userName) ?>!</h2>
                <p class="text-[var(--text-secondary)]">Lanjutkan pekerjaan Anda dengan template Vibeforge.</p>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-[var(--brand-primary-light)] flex items-center justify-center mb-3">
                        <i class="ph ph-folder-simple text-xl text-[var(--brand-primary)]"></i>
                    </div>
                    <p class="text-2xl font-bold">1</p>
                    <p class="text-sm text-[var(--text-muted)]">Proyek Aktif</p>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center mb-3">
                        <i class="ph ph-check-circle text-xl text-green-500"></i>
                    </div>
                    <p class="text-2xl font-bold">3</p>
                    <p class="text-sm text-[var(--text-muted)]">Langkah Selesai</p>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center mb-3">
                        <i class="ph ph-book-open text-xl text-blue-500"></i>
                    </div>
                    <p class="text-2xl font-bold">6</p>
                    <p class="text-sm text-[var(--text-muted)]">Referensi HTML</p>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center mb-3">
                        <i class="ph ph-rocket-launch text-xl text-purple-500"></i>
                    </div>
                    <p class="text-2xl font-bold">Ready</p>
                    <p class="text-sm text-[var(--text-muted)]">Status Deployment</p>
                </div>
            </div>

            <!-- Setup Wizard CTA -->
            <div class="bg-gradient-to-r from-[var(--brand-primary)] to-orange-400 rounded-xl p-6 text-white shadow-lg glow-orange-sm">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                            <i class="ph ph-magic-wand text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-xl mb-1">Setup Wizard Vibeforge</h3>
                            <p class="text-white/80 text-sm">Siapkan project baru dengan AI assistant dalam hitungan menit</p>
                        </div>
                    </div>
                    <a href="/install/" class="px-6 py-3 bg-white text-[var(--brand-primary)] font-bold rounded-xl hover:bg-white/90 transition-colors shadow-md flex items-center gap-2 whitespace-nowrap">
                        <i class="ph ph-rocket-launch"></i> Mulai Menyiapkan Instalasi
                    </a>
                </div>
            </div>

            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6">
                <h3 class="font-semibold mb-4 flex items-center gap-2">
                    <i class="ph ph-rocket-launch text-[var(--brand-primary)]"></i> Langkah Instalasi Awal
                </h3>
                <div class="space-y-4 text-sm text-[var(--text-secondary)]">
                    <p>Unduh template via terminal (ganti <code class="px-2 py-1 bg-[var(--bg-primary)] text-[var(--brand-primary)] rounded font-mono">vibeforge</code> dengan nama project Anda):</p>
                    <div class="bg-[var(--bg-primary)] rounded-xl p-4 font-mono text-sm border border-[var(--border-default)]">
                        <p class="text-[var(--text-muted)]"># Laragon</p>
                        <p class="text-[var(--brand-primary)]">cd C:\\laragon\\www</p>
                        <p class="text-[var(--text-primary)]">npx -y degit iqbalmurtadho24/vibeforge vibeforge</p>
                        <p class="text-[var(--text-primary)]">cd vibeforge</p>
                        <p class="text-[var(--text-muted)] mt-3"># XAMPP</p>
                        <p class="text-[var(--brand-primary)]">cd C:\\xampp\\htdocs</p>
                        <p class="text-[var(--text-primary)]">npx -y degit iqbalmurtadho24/vibeforge vibeforge</p>
                        <p class="text-[var(--text-primary)]">cd vibeforge</p>
                    </div>
                    <p class="text-xs text-[var(--text-muted)]">
                        Flag <code class="px-1 bg-[var(--bg-primary)] rounded font-mono">-y</code> menyetujui download package degit otomatis tanpa prompt konfirmasi.
                    </p>
                </div>
            </div>
        </div>
    `,
    templates: `
        <div class="space-y-6">
            <h2 class="text-2xl font-bold">Template Vibeforge</h2>
            <p class="text-[var(--text-secondary)]">Pilih template acuan visual dan struktur UI.</p>
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] overflow-hidden hover:border-[var(--brand-primary)] transition-colors group">
                    <div class="h-24 bg-gradient-to-br from-[var(--brand-primary)] to-orange-400 flex items-center justify-center">
                        <i class="ph ph-storefront text-4xl text-white/40"></i>
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold group-hover:text-[var(--brand-primary)]">E-Commerce</h4>
                        <p class="text-sm text-[var(--text-muted)]">Template toko online lengkap</p>
                    </div>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] overflow-hidden hover:border-[var(--brand-primary)] transition-colors group">
                    <div class="h-24 bg-gradient-to-br from-blue-500 to-cyan-400 flex items-center justify-center">
                        <i class="ph ph-article text-4xl text-white/40"></i>
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold group-hover:text-[var(--brand-primary)]">Blog / Portal</h4>
                        <p class="text-sm text-[var(--text-muted)]">Template konten & artikel</p>
                    </div>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] overflow-hidden hover:border-[var(--brand-primary)] transition-colors group">
                    <div class="h-24 bg-gradient-to-br from-purple-500 to-pink-400 flex items-center justify-center">
                        <i class="ph ph-gauge text-4xl text-white/40"></i>
                    </div>
                    <div class="p-4">
                        <h4 class="font-semibold group-hover:text-[var(--brand-primary)]">Dashboard Panel</h4>
                        <p class="text-sm text-[var(--text-muted)]">Template admin & manajemen</p>
                    </div>
                </div>
            </div>
        </div>
    `,
    docs: `
        <div class="space-y-4">
            <h2 class="text-2xl font-bold">Dokumentasi</h2>
            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                <div class="flex items-center gap-4 mb-3">
                    <div class="w-12 h-12 rounded-xl bg-[var(--brand-primary-light)] flex items-center justify-center shrink-0">
                        <i class="ph ph-rocket-launch text-2xl text-[var(--brand-primary)]"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold">Memulai Vibeforge</h4>
                        <p class="text-sm text-[var(--text-muted)]">Panduan setup 3 langkah mudah.</p>
                    </div>
                </div>
                <div class="bg-[var(--bg-primary)] rounded-lg p-4 font-mono text-sm border border-[var(--border-default)]">
                    <p class="text-[var(--text-muted)]"># Unduh template</p>
                    <p class="text-[var(--brand-primary)]">npx -y degit iqbalmurtadho24/vibeforge vibeforge</p>
                    <p class="text-[var(--text-primary)]">cd vibeforge</p>
                    <p class="text-[var(--text-muted)] mt-2"># Jalankan AI</p>
                    <p class="text-[var(--text-primary)]">claude</p>
                </div>
            </div>

            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                <div class="flex items-center gap-4 mb-2">
                    <div class="w-12 h-12 rounded-xl bg-blue-500/10 flex items-center justify-center shrink-0">
                        <i class="ph ph-file-text text-2xl text-blue-500"></i>
                    </div>
                    <div>
                        <h4 class="font-semibold">Dokumen Konsep</h4>
                        <p class="text-sm text-[var(--text-muted)]">Pengaturan konsep dan identitas aplikasi.</p>
                    </div>
                </div>
                <ul class="space-y-1 text-sm text-[var(--text-secondary)] ml-16">
                    <li>• <code class="text-[var(--brand-primary)] font-mono">docs/prd.md</code> — Fitur, nama, peran pengguna</li>
                    <li>• <code class="text-[var(--brand-primary)] font-mono">docs/branding.md</code> — Nama brand, warna, font</li>
                    <li>• <code class="text-[var(--brand-primary)] font-mono">references/*.html</code> — Referensi struktur UI</li>
                </ul>
            </div>
        </div>
    `,
    profile: `
        <div class="max-w-xl space-y-6">
            <h2 class="text-2xl font-bold">Profil</h2>
            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6 text-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#F97316] to-orange-400 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4 shadow-lg" style="box-shadow: 0 0 30px rgba(255, 107, 53, 0.3);">
                    <?= $userInitial ?>
                </div>
                <h3 class="text-xl font-semibold"><?= $userName ?></h3>
                <p class="text-[var(--text-muted)]"><?= $userEmail ?></p>
                <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-[var(--brand-primary-light)] text-[var(--brand-primary)]">Client User</span>
            </div>
            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] divide-y divide-[var(--border-default)]">
                <div class="p-4 flex justify-between text-sm"><span class="text-[var(--text-secondary)]">Role</span><span class="font-medium text-[var(--brand-primary)]">Client</span></div>
                <div class="p-4 flex justify-between text-sm"><span class="text-[var(--text-secondary)]">Bergabung</span><span><?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?></span></div>
            </div>
            <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="flex items-center justify-center gap-2 py-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl hover:border-[var(--brand-primary)] transition-colors">
                <i class="ph ph-github-logo text-xl"></i> Lihat di GitHub
            </a>
            <a href="/logout/" class="flex items-center justify-center gap-2 py-3 bg-red-500/10 text-red-400 font-medium rounded-xl hover:bg-red-500/20 transition-colors">
                <i class="ph ph-sign-out text-lg"></i> Logout
            </a>
        </div>
    `
};

function nav(p) {
    document.getElementById('content').innerHTML = V[p] || V.home;
    document.getElementById('pageTitle').textContent = {home:'Beranda',templates:'Template',docs:'Dokumentasi',profile:'Profil'}[p]||'Beranda';

    ['home','templates','docs','profile'].forEach(k => {
        const d = document.getElementById('s-' + k);
        const m = document.getElementById('m-' + k);
        if (d) {
            if (k === p) {
                d.className = 'w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium bg-[var(--brand-primary-light)] text-[var(--brand-primary)]';
            } else {
                d.className = 'w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors';
            }
        }
        if (m) {
            if (k === p) {
                m.className = 'flex flex-col items-center gap-1 text-[var(--brand-primary)]';
            } else {
                m.className = 'flex flex-col items-center gap-1 text-[var(--text-muted)]';
            }
        }
    });
}

const html = document.documentElement;
document.getElementById('themeToggle')?.addEventListener('click', () => {
    const isDark = html.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
});
(() => {
    const saved = localStorage.getItem('theme') || 'dark';
    html.classList.toggle('dark', saved === 'dark');
})();
nav('home');
</script>
</body>
</html>
