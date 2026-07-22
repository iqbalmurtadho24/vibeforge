<?php
/**
 * Login Shell
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

// Language handling
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
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= t('auth.login') ?> - <?= escape(APP_DISPLAY_NAME) ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg width='28' height='24' viewBox='0 0 28 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Crect x='0' y='7' width='3' height='10' rx='1.5' fill='%23FFC107' /%3E%3Crect x='6' y='4' width='3' height='16' rx='1.5' fill='%23FFC107' /%3E%3Crect x='12' y='0' width='3' height='24' rx='1.5' fill='%23FFC107' /%3E%3Crect x='18' y='4' width='3' height='16' rx='1.5' fill='%23FFC107' /%3E%3Crect x='24' y='7' width='3' height='10' rx='1.5' fill='%23FFC107' /%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { gold: '#FFC107', dark: '#0F0F11', card: '#1A1A1D', gray: '#8C8C8C' }
                    },
                    fontFamily: { sans: ['Inter', 'sans-serif'], heading: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { transition: background-color 0.3s ease, color 0.3s ease; }
        .input-icon { position: absolute; top: 50%; transform: translateY(-50%); color: #8C8C8C; transition: color 0.3s ease; }
        input:focus + .input-icon { color: #FFC107; }
        .custom-checkbox:checked { background-color: #FFC107; border-color: #FFC107; }
        /* Theme icon visibility using dark class */
        .dark .icon-sun { display: block; }
        .dark .icon-moon { display: none; }
        :not(.dark) .icon-sun { display: none; }
        :not(.dark) .icon-moon { display: block; }
        /* Language selector dropdown */
        .lang-dropdown { opacity: 0; visibility: hidden; transform: translateY(-8px); transition: all 0.2s ease; }
        .lang-selector:hover .lang-dropdown, .lang-selector:focus-within .lang-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        [dir="rtl"] { text-align: right; }
    </style>
</head>
<body data-theme="<?= $themePreference ?>" class="bg-gray-50 text-gray-900 dark:bg-brand-dark dark:text-white font-sans antialiased min-h-screen flex flex-col relative">

    <div class="absolute top-0 left-0 w-full h-full overflow-hidden -z-10 pointer-events-none">
        <div class="absolute top-[-10%] left-[-10%] w-96 h-96 bg-brand-gold/10 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-[-10%] right-[-10%] w-96 h-96 bg-brand-gold/5 rounded-full blur-[100px]"></div>
    </div>

    <div class="absolute top-6 <?= $isRtl ? 'left-6' : 'right-6' ?> z-20 flex items-center gap-2">
        <div class="relative lang-selector">
            <button type="button" class="h-12 px-3 flex items-center gap-2 rounded-full bg-white dark:bg-brand-card shadow-sm border border-gray-200 dark:border-gray-800 hover:text-brand-gold transition-colors" aria-label="<?= t('common.language') ?>">
                <img src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" alt="<?= $currentLang ?>" class="w-6 h-4 rounded-sm object-cover">
                <i class="ph ph-caret-down text-xs text-gray-500"></i>
            </button>
            <div class="lang-dropdown absolute <?= $isRtl ? 'left-0' : 'right-0' ?> mt-2 bg-white dark:bg-brand-card rounded-xl shadow-xl border border-gray-200 dark:border-gray-700 py-1 min-w-[150px] z-50">
                <?php foreach (getAvailableLanguages() as $code => $lang): ?>
                <a href="?lang=<?= $code ?>" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= $currentLang === $code ? 'text-brand-gold' : 'text-gray-700 dark:text-gray-300' ?>"><img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-5 h-3.5 rounded-sm"> <?= escape($lang['name']) ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <button onclick="toggleTheme()" class="w-12 h-12 flex items-center justify-center rounded-full bg-white dark:bg-brand-card shadow-sm border border-gray-200 dark:border-gray-800 hover:text-brand-gold transition-colors" aria-label="Toggle Dark Mode">
            <svg class="w-5 h-5 text-brand-gold icon-sun" fill="currentColor" viewBox="0 0 24 24"><path d="M12 7c-2.76 0-5 2.24-5 5s2.24 5 5 5 5-2.24 5-5-2.24-5-5-5zM2 13h2c.55 0 1-.45 1-1s-.45-1-1-1H2c-.55 0-1 .45-1 1s.45 1 1 1zm18 0h2c.55 0 1-.45 1-1s-.45-1-1-1h-2c-.55 0-1 .45-1 1s.45 1 1 1zM11 2v2c0 .55.45 1 1 1s1-.45 1-1V2c0-.55-.45-1-1-1s-1 .45-1 1zm0 18v2c0 .55.45 1 1 1s1-.45 1-1v-2c0-.55-.45-1-1-1s-1 .45-1 1zM5.99 4.58c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41L5.99 4.58zm12.37 12.37c-.39-.39-1.03-.39-1.41 0-.39.39-.39 1.03 0 1.41l1.06 1.06c.39.39 1.03.39 1.41 0 .39-.39.39-1.03 0-1.41l-1.06-1.06zm1.06-10.96c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06zM7.05 18.36c.39-.39.39-1.03 0-1.41-.39-.39-1.03-.39-1.41 0l-1.06 1.06c-.39.39-.39 1.03 0 1.41.39.39 1.03.39 1.41 0l1.06-1.06z"/></svg>
            <svg class="w-5 h-5 text-gray-600 icon-moon" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9 9-4.03 9-9c0-.46-.04-.92-.1-1.36-.98 1.37-2.58 2.26-4.4 2.26-2.98 0-5.4-2.42-5.4-5.4 0-1.81.89-3.42 2.26-4.4-.44-.06-.9-.1-1.36-.1z"/></svg>
        </button>
    </div>

    <main class="flex-grow flex flex-col items-center justify-center px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md bg-white dark:bg-brand-card p-8 sm:p-10 rounded-[2.5rem] shadow-2xl border border-gray-100 dark:border-gray-800/60 relative overflow-hidden">

            <div class="text-center mb-10">
                <a href="/" class="inline-flex flex-col items-center gap-3 mb-6 group cursor-pointer">
                    <svg width="42" height="36" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="transform group-hover:scale-105 transition-transform duration-300">
                        <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                        <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                        <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                    </svg>
                    <span class="font-sans font-light text-2xl tracking-[0.25em] text-gray-900 dark:text-white mt-1"><?= escape(APP_DISPLAY_NAME) ?></span>
                </a>
                <h2 class="text-2xl font-heading font-bold text-gray-900 dark:text-white mb-2"><?= t('login.welcome') ?></h2>
                <p class="text-sm text-gray-500 dark:text-gray-400"><?= t('login.subtitle') ?></p>
            </div>

            <div id="messageContainer" class="hidden mb-6 p-4 rounded-xl text-sm"></div>

            <form id="loginForm" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?= escape($csrfToken) ?>">

                <div class="relative">
                    <label for="email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5 ml-1"><?= t('login.email_label') ?></label>
                    <div class="relative">
                        <input type="email" id="email" name="email" required class="w-full bg-gray-50 dark:bg-[#0F0F11] border border-gray-200 dark:border-gray-700 rounded-xl px-11 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold transition-all" placeholder="nama@email.com">
                        <i class="ph ph-envelope text-lg input-icon left-4"></i>
                    </div>
                </div>

                <div class="relative">
                    <label for="password" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1.5 ml-1"><?= t('login.password_label') ?></label>
                    <div class="relative">
                        <input type="password" id="password" name="password" required class="w-full bg-gray-50 dark:bg-[#0F0F11] border border-gray-200 dark:border-gray-700 rounded-xl pl-11 pr-12 py-3.5 text-sm text-gray-900 dark:text-white placeholder-gray-400 focus:outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold transition-all" placeholder="••••••••">
                        <i class="ph ph-lock-key text-lg input-icon left-4"></i>
                        <button type="button" id="togglePassword" class="absolute inset-y-0 right-0 pr-4 flex items-center text-gray-400 hover:text-brand-gold transition-colors focus:outline-none">
                            <i class="ph ph-eye-slash text-lg" id="eyeIcon"></i>
                        </button>
                    </div>
                </div>

                <div class="flex items-center justify-between mt-4">
                    <div class="flex items-center">
                        <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 bg-white dark:bg-[#0F0F11] text-brand-gold focus:ring-brand-gold custom-checkbox cursor-pointer transition-colors">
                        <label for="remember-me" class="ml-2 block text-xs text-gray-600 dark:text-gray-400 cursor-pointer"><?= t('login.remember') ?></label>
                    </div>
                    <a href="#" class="text-xs font-medium text-brand-gold hover:text-yellow-400 transition-colors"><?= t('login.forgot') ?></a>
                </div>

                <div>
                    <button type="submit" id="submitBtn" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-full shadow-lg text-sm font-bold text-brand-dark bg-brand-gold hover:bg-yellow-500 hover:shadow-brand-gold/30 transform hover:-translate-y-0.5 transition-all duration-300 focus:outline-none">
                        <span><?= t('login.submit') ?></span>
                        <i class="ph-bold ph-sign-in ml-2 text-lg"></i>
                    </button>
                </div>
            </form>

            <div class="mt-8 relative">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-gray-200 dark:border-gray-700"></div></div>
                <div class="relative flex justify-center text-xs"><span class="px-3 bg-white dark:bg-brand-card text-gray-500"><?= t('login.or_continue') ?></span></div>
            </div>

            <div class="mt-6 grid grid-cols-2 gap-4">
                <button class="flex items-center justify-center gap-2 w-full py-3 px-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-[#0F0F11] hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-sm font-medium">
                    <img src="https://www.svgrepo.com/show/475656/google-color.svg" alt="Google" class="w-5 h-5">Google
                </button>
                <button class="flex items-center justify-center gap-2 w-full py-3 px-4 border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-[#0F0F11] hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors text-sm font-medium">
                    <i class="ph-fill ph-apple-logo text-xl"></i>Apple
                </button>
            </div>

            <?php if (defined('APP_ENV') && APP_ENV !== 'production'):
                // Demo emails sourced from Repo (data/users.json or SQL), not
                // hardcoded literals (Section 3g) - password123 stays literal
                // per Section 6b (it's the known plaintext for every demo hash).
                $demoRoleLabels = ['manajemen' => 'Manajemen', 'admin' => 'Admin', 'client' => 'Client'];
                $demoRoleClasses = [
                    'manajemen' => 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 hover:bg-purple-200 dark:hover:bg-purple-900/50 border border-purple-200 dark:border-purple-800',
                    'admin' => 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 hover:bg-blue-200 dark:hover:bg-blue-900/50 border border-blue-200 dark:border-blue-800',
                    'client' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 hover:bg-green-200 dark:hover:bg-green-900/50 border border-green-200 dark:border-green-800',
                ];
                $demoUsers = Repo::table('users')->all();
                $demoByRole = [];
                foreach ($demoUsers as $du) {
                    $r = $du['role'] ?? null;
                    if ($r !== null && !isset($demoByRole[$r])) {
                        $demoByRole[$r] = $du;
                    }
                }
            ?>
            <?php if ($demoByRole !== []): ?>
            <div class="mt-6 pt-4 border-t border-gray-200 dark:border-gray-700">
                <p class="text-xs text-center text-gray-500 dark:text-gray-400 mb-3"><?= t('login.demo') ?></p>
                <div class="grid grid-cols-3 gap-2">
                    <?php foreach ($demoRoleLabels as $roleKey => $roleLabel): ?>
                        <?php if (!isset($demoByRole[$roleKey])) { continue; } ?>
                        <button type="button" onclick="quickLoginFill('<?= escape($demoByRole[$roleKey]['email']) ?>', 'password123')" class="py-2 px-3 text-xs font-medium rounded-lg transition-colors <?= $demoRoleClasses[$roleKey] ?? '' ?>"><?= escape($roleLabel) ?></button>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
            <?php endif; ?>

            <div class="mt-6 text-center text-sm text-gray-600 dark:text-gray-400">
                <?= t('login.no_account') ?> <a href="/register/" class="font-bold text-brand-gold hover:text-yellow-400 hover:underline"><?= t('login.register_now') ?></a>
            </div>
        </div>

        <div class="mt-6 text-center">
            <a href="/" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-medium text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800/50 rounded-full hover:text-brand-gold hover:bg-brand-gold/10 transition-all border border-gray-200 dark:border-gray-700">
                <i class="ph ph-arrow-left"></i> <?= t('common.back_home') ?>
            </a>
        </div>
    </main>

    <script>
        // Theme Toggle
        function initTheme() {
            const saved = localStorage.theme || 'dark';
            document.body.setAttribute('data-theme', saved);
            if (saved === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }

        function toggleTheme() {
            const current = document.body.getAttribute('data-theme') || 'dark';
            const next = current === 'dark' ? 'light' : 'dark';
            document.body.setAttribute('data-theme', next);
            localStorage.theme = next;
            if (next === 'dark') {
                document.documentElement.classList.add('dark');
            } else {
                document.documentElement.classList.remove('dark');
            }
        }

        // Password Toggle
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eyeIcon');

        if (togglePassword && passwordInput && eyeIcon) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                eyeIcon.classList.toggle('ph-eye-slash');
                eyeIcon.classList.toggle('ph-eye');
                eyeIcon.classList.toggle('text-brand-gold');
            });
        }

        const loginForm = document.getElementById('loginForm');
        const submitBtn = document.getElementById('submitBtn');
        const messageContainer = document.getElementById('messageContainer');

        function showMessage(message, type, details = null) {
            let msgClass = 'mb-6 p-4 rounded-xl text-sm ';
            if (type === 'success') {
                msgClass += 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300';
            } else {
                msgClass += 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300';
            }
            messageContainer.className = msgClass;
            messageContainer.innerHTML = message;
            if (details && type !== 'success') {
                messageContainer.innerHTML += '<pre class="mt-2 text-xs overflow-auto max-h-32">' + details + '</pre>';
            }
            messageContainer.classList.remove('hidden');
        }

        function quickLoginFill(email, password) {
            // Fill email field
            const emailInput = document.getElementById('email');
            const passwordInput = document.getElementById('password');

            emailInput.value = email;
            passwordInput.value = password;

            // Focus on password field to show filled state
            passwordInput.focus();

            // Highlight the form briefly
            emailInput.classList.add('border-brand-gold', 'ring-1', 'ring-brand-gold');
            passwordInput.classList.add('border-brand-gold', 'ring-1', 'ring-brand-gold');

            setTimeout(() => {
                emailInput.classList.remove('border-brand-gold', 'ring-1', 'ring-brand-gold');
                passwordInput.classList.remove('border-brand-gold', 'ring-1', 'ring-brand-gold');
            }, 1500);

            // Hide any existing message
            messageContainer.classList.add('hidden');

            // Auto-submit after filling
            setTimeout(() => {
                submitLogin();
            }, 300);
        }

        async function submitLogin() {
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;
            const csrfToken = document.querySelector('input[name="csrf_token"]').value;
            const remember = document.getElementById('remember-me').checked ? '1' : '0';

            const originalContent = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="ph ph-spinner-gap animate-spin text-xl"></i> <span class="ml-2"><?= t('login.processing') ?></span>';
            submitBtn.classList.add('opacity-80', 'cursor-not-allowed');

            try {
                const response = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new URLSearchParams({ 'module': 'auth', 'action': 'login', 'email': email, 'password': password, 'csrf_token': csrfToken, 'remember': remember })
                });

                // Get response text first for debugging
                const responseText = await response.text();

                // Try to parse as JSON
                let data;
                try {
                    data = JSON.parse(responseText);
                } catch (parseError) {
                    // If not JSON, show the raw response for debugging
                    console.error('Invalid JSON response:', responseText);
                    showMessage(<?= json_encode(t('login.invalid_response')) ?>, 'error', responseText.substring(0, 500));
                    submitBtn.innerHTML = originalContent;
                    submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                    return;
                }

                if (data.success) {
                    showMessage(<?= json_encode(t('login.success')) ?>, 'success');
                    setTimeout(() => { window.location.href = data.dashboard_url || '/'; }, 500);
                } else {
                    showMessage(data.error || <?= json_encode(t('login.failed')) ?>, 'error');
                    submitBtn.innerHTML = originalContent;
                    submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
                }
            } catch (error) {
                console.error('Login error:', error);
                const errorMsg = <?= json_encode(t('login.error_generic')) ?>;
                showMessage(errorMsg, 'error', error.message || String(error));
                submitBtn.innerHTML = originalContent;
                submitBtn.classList.remove('opacity-80', 'cursor-not-allowed');
            }
        }

        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitLogin();
            });
        }

        initTheme();
    </script>
</body>
</html>
