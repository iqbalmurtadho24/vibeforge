<?php
/**
 * Vibeforge Register Page
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
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('auth_register') ?> - <?= APP_DISPLAY_NAME ?></title>
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
    </style>
</head>
<body class="min-h-screen flex flex-col">

    <div class="absolute top-0 left-0 right-0 z-20 p-4 flex justify-between items-center">
        <a href="/" class="flex items-center gap-2">
            <svg width="28" height="28" viewBox="0 0 24 24" fill="#F97316"><path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/></svg>
            <span class="font-heading font-bold text-lg"><span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span></span>
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

    <main class="flex-grow flex items-center justify-center p-4 pt-20">
        <div class="w-full max-w-md">
            <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-8">
                <div class="text-center mb-8">
                    <h1 class="text-2xl font-heading font-bold mb-2"><?= t('register.heading') ?></h1>
                    <p class="text-[var(--text-secondary)] text-sm"><?= t('register.subtitle') ?></p>
                </div>

                <div id="message" class="hidden mb-6 p-4 rounded-xl text-sm"></div>

                <form id="registerForm" class="space-y-5">
                    <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">

                    <div>
                        <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]"><?= t('register.name_label') ?></label>
                        <div class="relative">
                            <input type="text" name="name" required class="w-full bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl px-4 py-3 pl-11 text-[var(--text-primary)] focus:outline-none focus:border-[var(--brand-primary)] transition-colors" placeholder="<?= t('register.name_label') ?>">
                            <i class="ph ph-user absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]"><?= t('register.email_label') ?></label>
                        <div class="relative">
                            <input type="email" name="email" required class="w-full bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl px-4 py-3 pl-11 text-[var(--text-primary)] focus:outline-none focus:border-[var(--brand-primary)] transition-colors" placeholder="nama@email.com">
                            <i class="ph ph-envelope absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"></i>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]"><?= t('register.password_label') ?></label>
                        <div class="relative">
                            <input type="password" name="password" required minlength="8" class="w-full bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl px-4 py-3 pl-11 pr-11 text-[var(--text-primary)] focus:outline-none focus:border-[var(--brand-primary)] transition-colors" placeholder="<?= t('register.password_ph') ?>">
                            <i class="ph ph-lock absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"></i>
                            <button type="button" id="togglePassword" class="absolute right-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                                <i class="ph ph-eye text-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-2 text-[var(--text-secondary)]"><?= t('register.confirm_label') ?></label>
                        <div class="relative">
                            <input type="password" name="password_confirm" required minlength="8" class="w-full bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl px-4 py-3 pl-11 text-[var(--text-primary)] focus:outline-none focus:border-[var(--brand-primary)] transition-colors" placeholder="<?= t('register.confirm_ph') ?>">
                            <i class="ph ph-lock-simple absolute left-4 top-1/2 -translate-y-1/2 text-[var(--text-muted)]"></i>
                        </div>
                    </div>

                    <div class="flex items-start gap-2">
                        <input type="checkbox" required class="w-4 h-4 mt-1 rounded border-[var(--border-default)] bg-[var(--bg-surface)] text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">
                        <span class="text-sm text-[var(--text-secondary)]"><?= t('register.terms_agree') ?> <a href="#" class="text-[var(--brand-primary)] hover:underline"><?= t('register.terms') ?></a> <?= t('register.and') ?> <a href="#" class="text-[var(--brand-primary)] hover:underline"><?= t('register.privacy') ?></a></span>
                    </div>

                    <button type="submit" id="submitBtn" class="w-full py-3.5 bg-gradient-brand text-white font-semibold rounded-xl hover:opacity-90 transition-opacity shadow-lg">
                        <?= t('register.submit') ?>
                    </button>
                </form>

                <div class="mt-6 text-center text-sm text-[var(--text-secondary)]">
                    <?= t('auth.have_account') ?> <a href="/login/" class="text-[var(--brand-primary)] font-medium hover:underline"><?= t('auth.login_here') ?></a>
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
        const html = document.documentElement;
        function updateThemeUI(theme) {
            const isDark = theme === 'dark';
            html.classList.toggle('dark', isDark);
            html.setAttribute('data-theme', theme);
            const icon = document.querySelector('#themeToggle i');
            if (icon) {
                icon.className = isDark ? 'ph ph-moon text-lg text-amber-400' : 'ph ph-sun text-lg text-amber-500';
            }
        }
        function initTheme() {
            const saved = localStorage.getItem('theme') || 'dark';
            updateThemeUI(saved);
        }
        document.getElementById('themeToggle')?.addEventListener('click', () => {
            const current = html.classList.contains('dark') ? 'dark' : 'light';
            const next = current === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', next);
            updateThemeUI(next);
        });
        initTheme();

        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.querySelector('input[name="password"]');
        togglePassword?.addEventListener('click', () => {
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            togglePassword.innerHTML = `<i class="ph ph-${isPassword ? 'eye-slash' : 'eye'} text-lg"></i>`;
        });

        const form = document.getElementById('registerForm');
        const submitBtn = document.getElementById('submitBtn');
        const message = document.getElementById('message');

        function showMessage(text, type) {
            message.className = `mb-6 p-4 rounded-xl text-sm ${type === 'success' ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-red-500/20 text-red-400 border border-red-500/30'}`;
            message.textContent = text;
            message.classList.remove('hidden');
        }

        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const name = document.querySelector('input[name="name"]').value;
            const email = document.querySelector('input[name="email"]').value;
            const password = document.querySelector('input[name="password"]').value;
            const passwordConfirm = document.querySelector('input[name="password_confirm"]').value;
            const csrf = document.querySelector('input[name="csrf_token"]').value;

            if (password !== passwordConfirm) {
                showMessage('Kata sandi tidak cocok.', 'error');
                return;
            }

            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="ph ph-spinner animate-spin"></i> Membuat akun...';
            submitBtn.classList.add('opacity-70');

            try {
                const res = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams({ module: 'auth', action: 'register', name, email, password, csrf_token: csrf })
                });
                const data = await res.json();
                if (data.success) {
                    showMessage('Akun berhasil dibuat! Mengalihkan...', 'success');
                    setTimeout(() => window.location.href = '/login/', 1000);
                } else {
                    showMessage(data.error || 'Pendaftaran gagal.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = 'Buat Akun';
                    submitBtn.classList.remove('opacity-70');
                }
            } catch (e) {
                showMessage('Terjadi kesalahan. Coba lagi.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = 'Buat Akun';
                submitBtn.classList.remove('opacity-70');
            }
        });
    </script>
</body>
</html>
