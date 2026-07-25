<?php
/**
 * Vibeforge - Administrator Dashboard (Super Admin)
 */
defined('APP_ENTRY') or define('APP_ENTRY', true);
require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';
initSession();
if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) { $_SESSION['language'] = $_GET['lang']; }
$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$isLoggedIn = isLoggedIn();
$user = getCurrentUser();
if (!$isLoggedIn || ($user['role'] ?? null) !== 'manajemen') { header('Location: /login/'); exit; }
$themePreference = $user['theme_preference'] ?? 'dark';
$userName = escape($user['name'] ?? 'Admin');
$userInitial = strtoupper(substr($userName, 0, 2));
$userEmail = escape($user['email'] ?? '');
$allUsers = Repo::table('users')->all();
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $userName ?> - <?= APP_DISPLAY_NAME ?> Admin</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FF6B35'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700&display=swap">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/branding.css">
    <script>tailwind.config={darkMode:'class',theme:{extend:{colors:{brand:{primary:'#F97316',dark:'#0D1117',card:'#161B22'}}}}}</script>
    <style>
        body{font-family:'Inter',sans-serif;background:var(--bg-primary);color:var(--text-primary)}
        h1,h2,h3{font-family:'Poppins',sans-serif}
        .text-gradient{background:linear-gradient(135deg,#F97316,#F59E0B);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
    </style>
</head>
<body class="antialiased h-screen flex flex-col overflow-hidden">

<div class="flex flex-1 overflow-hidden">
    <!-- Sidebar -->
    <aside class="hidden md:flex flex-col w-64 bg-[var(--bg-card)] border-r border-[var(--border-default)] shrink-0">
        <div class="h-16 flex items-center px-6 border-b border-[var(--border-default)]">
            <a href="/" class="flex items-center gap-2">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="#F97316"><path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/></svg>
                <span class="font-heading font-bold text-lg"><span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span></span>
                <span class="text-xs text-[var(--text-muted)] ml-1">Admin</span>
            </a>
        </div>
        <nav class="flex-1 py-4 px-3 space-y-1">
            <button onclick="nav('dashboard')" id="s-dashboard" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium bg-[var(--brand-primary-light)] text-[var(--brand-primary)]"><i class="ph-fill ph-squares-four text-xl"></i> Dashboard</button>
            <button onclick="nav('users')" id="s-users" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors"><i class="ph ph-users text-xl"></i> Users</button>
            <button onclick="nav('system')" id="s-system" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors"><i class="ph ph-gear text-xl"></i> System</button>
            <button onclick="nav('profile')" id="s-profile" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors"><i class="ph ph-user text-xl"></i> Profil</button>
        </nav>
        <div class="p-4 border-t border-[var(--border-default)]">
            <div class="flex items-center gap-3 px-2 py-2">
                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-[#F97316] to-orange-400 flex items-center justify-center text-white font-bold"><?= $userInitial ?></div>
                <div><p class="text-sm font-medium"><?= $userName ?></p><p class="text-xs text-[var(--brand-primary)]">Super Admin</p></div>
            </div>
        </div>
    </aside>

    <!-- Main -->
    <main class="flex-1 flex flex-col overflow-hidden">
        <header class="h-16 flex items-center justify-between px-6 bg-[var(--bg-secondary)] border-b border-[var(--border-default)] shrink-0">
            <h1 id="pageTitle" class="font-heading font-semibold">Dashboard</h1>
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

                <button id="themeToggle" class="p-2 hover:bg-[var(--bg-hover)] rounded-lg" aria-label="Toggle theme"><i class="ph ph-moon text-lg text-[var(--brand-primary)]"></i></button>
                <a href="/logout/" class="p-2 hover:bg-red-500/10 rounded-lg text-red-400" title="Keluar"><i class="ph ph-sign-out text-lg"></i></a>
            </div>
        </header>
        <div id="content" class="flex-1 overflow-y-auto p-6 pb-24 md:pb-6"></div>
    </main>
</div>

<!-- Mobile Nav -->
<nav class="md:hidden fixed bottom-0 left-0 right-0 bg-[var(--bg-card)] border-t border-[var(--border-default)] z-50">
    <div class="flex justify-around items-center h-16">
        <button onclick="nav('dashboard')" class="flex flex-col items-center gap-1 text-[var(--brand-primary)]"><i class="ph-fill ph-squares-four text-2xl"></i><span class="text-[10px]">Dashboard</span></button>
        <button onclick="nav('users')" class="flex flex-col items-center gap-1 text-[var(--text-muted)]"><i class="ph ph-users text-2xl"></i><span class="text-[10px]">Users</span></button>
        <button onclick="nav('system')" class="flex flex-col items-center gap-1 text-[var(--text-muted)]"><i class="ph ph-gear text-2xl"></i><span class="text-[10px]">System</span></button>
        <button onclick="nav('profile')" class="flex flex-col items-center gap-1 text-[var(--text-muted)]"><i class="ph ph-user text-2xl"></i><span class="text-[10px]">Profil</span></button>
    </div>
</nav>

<script>
const V = {
    dashboard: `
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 flex-1">
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-[var(--brand-primary-light)] flex items-center justify-center mb-3"><i class="ph ph-users text-xl text-[var(--brand-primary)]"></i></div>
                    <p class="text-2xl font-bold"><?= count($allUsers) ?></p><p class="text-sm text-[var(--text-muted)]">Total User</p>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-green-500/10 flex items-center justify-center mb-3"><i class="ph ph-database text-xl text-green-500"></i></div>
                    <p class="text-2xl font-bold">JSON</p><p class="text-sm text-[var(--text-muted)]">Database Mode</p>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-blue-500/10 flex items-center justify-center mb-3"><i class="ph ph-shield-check text-xl text-blue-500"></i></div>
                    <p class="text-2xl font-bold">Argon2ID</p><p class="text-sm text-[var(--text-muted)]">Password Hash</p>
                </div>
                <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-5">
                    <div class="w-10 h-10 rounded-lg bg-purple-500/10 flex items-center justify-center mb-3"><i class="ph ph-code text-xl text-purple-500"></i></div>
                    <p class="text-2xl font-bold"><?= APP_ENV ?></p><p class="text-sm text-[var(--text-muted)]">Environment</p>
                </div>
            </div>
            </div>

            <!-- Setup Wizard CTA -->
            <div class="bg-gradient-to-r from-[var(--brand-primary)] to-orange-400 rounded-xl p-6 text-white">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 rounded-xl bg-white/20 flex items-center justify-center">
                            <i class="ph ph-magic-wand text-3xl"></i>
                        </div>
                        <div>
                            <h3 class="font-heading font-bold text-xl mb-1">Setup Wizard Vibeforge</h3>
                            <p class="text-white/80 text-sm">Siapkan project baru dengan AI assistant dalam hitungan menit</p>
                        </div>
                    </div>
                    <a href="/install/" class="px-6 py-3 bg-white text-[var(--brand-primary)] font-bold rounded-xl hover:bg-white/90 transition-colors shadow-lg flex items-center gap-2">
                        <i class="ph ph-rocket-launch"></i> Mulai Menyiapkan Instalasi
                    </a>
                </div>
            </div>

            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6">
                <h3 class="font-semibold mb-4 flex items-center gap-2"><i class="ph ph-shield-check text-[var(--brand-primary)]"></i> Fitur Keamanan Vibeforge</h3>
                <div class="grid md:grid-cols-2 gap-4 text-sm">
                    <div class="p-4 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)]">
                        <p class="font-bold text-[var(--brand-primary)] mb-1">Argon2ID Password Hashing</p>
                        <p class="text-[var(--text-muted)]">Password di-hash dengan PASSWORD_ARGON2ID untuk keamanan maksimal.</p>
                    </div>
                    <div class="p-4 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)]">
                        <p class="font-bold text-[var(--brand-primary)] mb-1">CSRF Protection</p>
                        <p class="text-[var(--text-muted)]">Token CSRF terpusat di core/router.php yang diverifikasi via hash_equals().</p>
                    </div>
                    <div class="p-4 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)]">
                        <p class="font-bold text-[var(--brand-primary)] mb-1">Rate Limiting</p>
                        <p class="text-[var(--text-muted)]">Mencegah brute force login berbasis IP + username.</p>
                    </div>
                    <div class="p-4 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)]">
                        <p class="font-bold text-[var(--brand-primary)] mb-1">Dual-Mode Repo</p>
                        <p class="text-[var(--text-muted)]">Auto-switch MySQL / JSON per entitas secara transparan.</p>
                    </div>
                </div>
            </div>
        </div>
    `,
    users: `
        <div class="space-y-6">
            <h2 class="text-2xl font-bold">Daftar Pengguna</h2>
            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] overflow-x-auto">
                <table class="w-full text-left text-sm">
                    <thead class="bg-[var(--bg-surface)] text-[var(--text-muted)] border-b border-[var(--border-default)]">
                        <tr>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Email</th>
                            <th class="px-6 py-3 font-medium">Role</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[var(--border-default)]">
                        <?php foreach($allUsers as $u): ?>
                        <tr class="hover:bg-[var(--bg-hover)] transition-colors">
                            <td class="px-6 py-4 flex items-center gap-3 font-medium">
                                <div class="w-8 h-8 rounded-full bg-gradient-to-br from-[#F97316] to-orange-400 flex items-center justify-center text-white font-bold text-xs"><?= strtoupper(substr($u['name'], 0, 1)) ?></div>
                                <?= escape($u['name']) ?>
                            </td>
                            <td class="px-6 py-4 text-[var(--text-muted)]"><?= escape($u['email']) ?></td>
                            <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs font-medium <?= $u['role']==='manajemen'?'bg-purple-500/20 text-purple-400':($u['role']==='admin'?'bg-blue-500/20 text-blue-400':'bg-green-500/20 text-green-400') ?>"><?= ucfirst($u['role']) ?></span></td>
                            <td class="px-6 py-4"><span class="px-2 py-1 rounded text-xs bg-green-500/20 text-green-400">Active</span></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    `,
    system: `
        <div class="space-y-6 max-w-2xl">
            <h2 class="text-2xl font-bold">Sistem & Konfigurasi</h2>
            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] divide-y divide-[var(--border-default)] text-sm">
                <div class="p-4 flex justify-between"><span class="text-[var(--text-secondary)]">APP_DISPLAY_NAME</span><span class="font-mono"><?= APP_DISPLAY_NAME ?></span></div>
                <div class="p-4 flex justify-between"><span class="text-[var(--text-secondary)]">APP_ENV</span><span class="font-mono text-yellow-400"><?= APP_ENV ?></span></div>
                <div class="p-4 flex justify-between"><span class="text-[var(--text-secondary)]">DB_MODE</span><span class="font-mono text-[var(--brand-primary)]"><?= DB_MODE ?></span></div>
                <div class="p-4 flex justify-between"><span class="text-[var(--text-secondary)]">Data Path</span><span class="font-mono">data/*.json</span></div>
                <div class="p-4 flex justify-between"><span class="text-[var(--text-secondary)]">PHP Version</span><span class="font-mono"><?= PHP_VERSION ?></span></div>
            </div>
            <div class="text-center">
                <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="inline-flex items-center gap-2 px-6 py-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl hover:border-[var(--brand-primary)] transition-colors"><i class="ph ph-github-logo text-xl"></i> Repository GitHub</a>
            </div>
        </div>
    `,
    profile: `
        <div class="max-w-xl space-y-6">
            <h2 class="text-2xl font-bold">Profil Admin</h2>
            <div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6 text-center">
                <div class="w-24 h-24 rounded-full bg-gradient-to-br from-[#F97316] to-orange-400 flex items-center justify-center text-white text-3xl font-bold mx-auto mb-4 shadow-lg"><?= $userInitial ?></div>
                <h3 class="text-xl font-semibold"><?= $userName ?></h3>
                <p class="text-[var(--text-muted)]"><?= $userEmail ?></p>
                <span class="inline-block mt-2 px-3 py-1 rounded-full text-sm bg-purple-500/20 text-purple-400">Super Admin</span>
            </div>
            <a href="/logout/" class="flex items-center justify-center gap-2 py-3 bg-red-500/10 text-red-400 font-medium rounded-xl hover:bg-red-500/20 transition-colors"><i class="ph ph-sign-out"></i> Logout</a>
        </div>
    `
};

function nav(p) {
    document.getElementById('content').innerHTML = V[p] || V.dashboard;
    document.getElementById('pageTitle').textContent = {dashboard:'Dashboard',users:'Users',system:'System',profile:'Profil'}[p]||'Dashboard';
    ['dashboard','users','system','profile'].forEach(k => {
        const el = document.getElementById('s-'+k);
        if(!el) return;
        if(k===p) { el.className='w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium bg-[var(--brand-primary-light)] text-[var(--brand-primary)]'; }
        else { el.className='w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-[var(--text-secondary)] hover:bg-[var(--bg-hover)] transition-colors'; }
    });
}
const html=document.documentElement;
document.getElementById('themeToggle')?.addEventListener('click',()=>{const d=html.classList.toggle('dark');localStorage.setItem('theme',d?'dark':'light')});
(()=>{const s=localStorage.getItem('theme')||'dark';html.classList.toggle('dark',s==='dark')})();
nav('dashboard');
</script>
</body>
</html>
