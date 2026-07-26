<?php
/**
 * Vibeforge Login Page
 */

defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';

initSession();

if (isLoggedIn()) {
    redirect(getDashboardUrl());
}

if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
    $_SESSION['language'] = $_GET['lang'];
}
$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$isRtl = isRtlLanguage();

$csrfToken = generateCsrfToken();
$themePreference = 'dark';
$isDev = APP_ENV !== 'production';

// Get demo users for quick login
$demoUsers = [];
if ($isDev) {
    $allUsers = Repo::table('users')->all();
    foreach ($allUsers as $u) {
        $demoUsers[$u['role']] = $u;
    }
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('auth_login') ?> - <?= APP_DISPLAY_NAME ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FF6B35'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
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
                        brand: { primary: '#F97316', dark: '#0D1117', card: '#161B22' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        heading: ['Poppins', 'sans-serif'],
                    }
                }
            }
        }
    </script>

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-primary);
            color: var(--text-primary);
        }
        h1, h2, h3 { font-family: 'Poppins', sans-serif; }
        .text-gradient {
            background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .bg-gradient-brand {
            background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%);
        }
        .glow-orange { box-shadow: 0 0 30px rgba(255, 107, 53, 0.3); }
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <!-- Top Bar -->
    <div class="absolute top-0 left-0 right-0 z-20 p-4 flex justify-between items-center">
        <a href="/" class="flex items-center gap-2">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="#F97316">
                <path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/>
            </svg>
            <span class="font-heading font-bold text-lg">
                <span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span>
            </span>
        </a>

        <div class="flex items-center gap-2">
            <!-- Language Selector -->
            <div class="relative group" x-data="{ open: false }">
                <button @click="open = !open" @click.away="open = false" class="flex items-center gap-1.5 px-3 py-2 rounded-lg bg-[var(--bg-card)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors text-xs font-medium">
                    <img src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" alt="<?= $currentLang ?>" class="w-5 h-3.5 rounded-sm shadow-sm">
                    <span class="uppercase font-bold text-[var(--text-secondary)]"><?= escape($currentLang) ?></span>
                    <i class="ph ph-caret-down text-xs text-[var(--text-muted)]"></i>
                </button>
                <div x-show="open" x-transition class="absolute right-0 mt-1 bg-[var(--bg-card)] rounded-xl shadow-2xl border border-[var(--border-default)] py-1 min-w-[150px] z-50">
                    <?php foreach (getAvailableLanguages() as $code => $lang): ?>
                    <a href="<?= escape(buildLangUrl($code)) ?>" class="flex items-center gap-2.5 px-3 py-2 text-xs hover:bg-[var(--bg-hover)] transition-colors <?= $currentLang === $code ? 'text-[var(--brand-primary)] font-bold bg-[var(--brand-primary-light)]/10' : 'text-[var(--text-secondary)]' ?>">
                        <img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-5 h-3.5 rounded-sm">
                        <span><?= escape($lang['name']) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Theme Toggle -->
            <button id="themeToggle" class="w-10 h-10 rounded-lg bg-[var(--bg-card)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors flex items-center justify-center" aria-label="Toggle theme">
                <i class="ph ph-moon text-[var(--text-muted)] dark:text-yellow-400 text-lg"></i>
            </button>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow flex items-center justify-center p-4 pt-20">
        <div class="w-full max-w-md">
            <!-- Login Card -->
            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-8 glow-orange">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-heading font-bold mb-2"><?= t('login.welcome') ?></h1>
                    <p class="text-[var(--text-secondary)] text-sm"><?= t('login.subtitle') ?></p>
                </div>

                <!-- Messages -->
                <div id="message" class="hidden mb-6 p-4 rounded-xl text-sm"></div>

                <!-- Form -->
                <form id="loginForm" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">

                    <div>
                        <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]"><?= t('login.email_label') ?></label>
                        <div class="relative">
                            <input type="email" name="email" required class="w-full bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl px-4 py-3 pl-11 text-[var(--text-primary)] focus:outline-none focus:border-[var(--brand-primary)] focus:ring-1 focus:ring-[var(--brand-primary)] transition-colors" placeholder="nama@email.com">
                            <i class="ph ph-envelope absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]"><?= t('login.password_label') ?></label>
                        <div class="relative">
                            <input type="password" name="password" required class="w-full bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl px-4 py-3 pl-11 pr-11 text-[var(--text-primary)] focus:outline-none focus:border-[var(--brand-primary)] focus:ring-1 focus:ring-[var(--brand-primary)] transition-colors" placeholder="••••••••">
                            <i class="ph ph-lock absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"></i>
                            <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                                <i class="ph ph-eye text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-[var(--border-default)] bg-[var(--bg-surface)] text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                            <span class="text-sm text-[var(--text-secondary)]"><?= t('auth.remember_me') ?></span>
                        </label>
                        <a href="#" class="text-sm text-[var(--brand-primary)] hover:underline"><?= t('auth.forgot_password') ?></a>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full py-3.5 bg-gradient-brand text-white font-semibold rounded-xl hover:opacity-90 transition-opacity shadow-lg">
                        <?= t('login.submit') ?>
                    </button>
                </form>

                <!-- Demo Quick Login -->
                <?php if ($isDev && !empty($demoUsers)): ?>
                <div class="mt-6 pt-6 border-t border-[var(--border-default)]">
                    <p class="text-xs text-center text-[var(--text-muted)] mb-3"><?= t('login.demo') ?></p>
                    <div class="grid grid-cols-3 gap-2">
                        <?php if (isset($demoUsers['manajemen'])): ?>
                        <button type="button" onclick="quickLogin('<?= escape($demoUsers['manajemen']['email']) ?>')" class="py-2 px-3 text-xs font-medium rounded-lg bg-purple-500/10 text-purple-400 border border-purple-500/20 hover:bg-purple-500/20 transition-colors">
                            Manajemen
                        </button>
                        <?php endif; ?>
                        <?php if (isset($demoUsers['admin'])): ?>
                        <button type="button" onclick="quickLogin('<?= escape($demoUsers['admin']['email']) ?>')" class="py-2 px-3 text-xs font-medium rounded-lg bg-blue-500/10 text-blue-400 border border-blue-500/20 hover:bg-blue-500/20 transition-colors">
                            Admin
                        </button>
                        <?php endif; ?>
                        <?php if (isset($demoUsers['client'])): ?>
                        <button type="button" onclick="quickLogin('<?= escape($demoUsers['client']['email']) ?>')" class="py-2 px-3 text-xs font-medium rounded-lg bg-green-500/10 text-green-400 border border-green-500/20 hover:bg-green-500/20 transition-colors">
                            Client
                        </button>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="mt-6 text-center text-sm text-[var(--text-secondary)]">
                    <?= t('auth.no_account') ?> <a href="/register/" class="text-[var(--brand-primary)] font-medium hover:underline"><?= t('auth.register_now') ?></a>
                </div>
            </div>

            <div class="text-center mt-6">
                <a href="/" class="inline-flex items-center gap-2 text-sm text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                    <i class="ph ph-arrow-left"></i> <?= t('common.back_home') ?>
                </a>
            </div>
        </div>
    </main>

    <script>
        // Theme
        const html = document.documentElement;
        const themeBtn = document.getElementById('themeToggle');

        function updateThemeUI(theme) {
            const isDark = theme === 'dark';
            html.classList.toggle('dark', isDark);
            html.setAttribute('data-theme', theme);
            const icon = themeBtn?.querySelector('i');
            if (icon) {
                icon.className = isDark ? 'ph ph-moon text-lg text-amber-400' : 'ph ph-sun text-lg text-amber-500';
            }
        }

        function initTheme() {
            const saved = localStorage.getItem('theme') || 'dark';
            updateThemeUI(saved);
        }

        function toggleTheme() {
            const current = html.classList.contains('dark') ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            updateThemeUI(next);
        }

        themeBtn?.addEventListener('click', toggleTheme);
        initTheme();

        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.querySelector('input[name="password"]');

        togglePassword?.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePassword.innerHTML = `<i class="ph ph-${isPassword ? 'eye-slash' : 'eye'} text-lg"></i>`;
        });

        // Quick Login
        function quickLogin(email) {
            document.querySelector('input[name="email"]').value = email;
            document.querySelector('input[name="password"]').value = 'password123';
            submitLogin();
        }

        // Form Submit
        const form = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const message = document.getElementById('message');

        function showMessage(text, type) {
            message.className = `mb-6 p-4 rounded-xl text-sm ${type === 'success' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'}`;
            message.textContent = text;
            message.classList.remove('hidden');
        }

        async function submitLogin() {
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const csrf = document.querySelector('input[name="csrf_token"]').value;
            const remember = document.querySelector('input[name="remember"]').checked ? '1' : '0';

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Memproses...';
            submitBtn.classList.add('opacity-70');

            try {
                const res = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams({ module: 'auth', action: 'login', email, password, csrf_token: csrf, remember })
                });

                const data = await res.json();

                if (data.success) {
                    showMessage('Login berhasil! Mengalihkan...', 'success');
                    setTimeout(() => window.location.href = data.dashboard_url || '/', 500);
                } else {
                    showMessage(data.error || 'Login gagal.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Masuk';
                    submitBtn.classList.remove('opacity-70');
                }
            } catch (e) {
                showMessage('Terjadi kesalahan. Coba lagi.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Masuk';
                submitBtn.classList.remove('opacity-70');
            }
        }

        form?.addEventListener('submit', (e) => {
            e.preventDefault();
            submitLogin();
        });
    </script>
</body>
</html>
