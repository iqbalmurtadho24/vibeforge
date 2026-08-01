<?php
/**
 * Vibeforge Landing Page
 * Full informational page with installation guide & 13 Pillars Architecture Alignment
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
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= escape(APP_DISPLAY_NAME) ?> - Developer-Grade Vibe Coding Architecture</title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F97316'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
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

    <!-- Top Status Bar -->
    <div class="bg-[var(--bg-secondary)] border-b border-[var(--border-default)] py-1 px-4 text-[11px] font-mono text-[var(--text-secondary)] hidden sm:flex items-center justify-between z-50">
        <div class="flex items-center gap-4">
            <span class="flex items-center gap-1.5 text-emerald-400 font-bold">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <?= t('index.system_online') ?>
            </span>
            <span class="text-[var(--border-default)]">|</span>
            <span><?= t('index.php_native') ?></span>
            <span class="text-[var(--border-default)]">|</span>
            <span><?= t('index.arch_spa') ?></span>
        </div>
        <div class="flex items-center gap-4">
            <span><?= t('index.repo_mode') ?></span>
            <span class="text-[var(--border-default)]">|</span>
            <span class="text-[var(--brand-primary)]">v3.3.0-STABLE</span>
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
                        <i class="ph ph-cpu text-base text-[var(--brand-primary)]"></i> <?= t('index.arch_link') ?>
                    </a>
                    <a href="#pilar" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5">
                        <i class="ph ph-tree-structure text-base text-[var(--brand-primary)]"></i> 13 Pilar
                    </a>
                    <a href="#cara-pasang" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5">
                        <i class="ph ph-terminal-window text-base text-[var(--brand-primary)]"></i> <?= t('index.installer_link') ?>
                    </a>
                    <a href="#demo" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5">
                        <i class="ph ph-shield-check text-base text-[var(--brand-primary)]"></i> <?= t('index.demo_roles_link') ?>
                    </a>
                    <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1">
                        <i class="ph ph-github-logo text-base"></i> <?= t('index.github_link') ?>
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
                            <a href="<?= escape(buildLangUrl($code)) ?>" class="flex items-center gap-2.5 px-3.5 py-2 hover:bg-[var(--bg-hover)] transition-colors <?= $currentLang === $code ? 'text-[var(--brand-primary)] font-bold bg-orange-500/10' : 'text-[var(--text-secondary)]' ?>">
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
                        <i class="ph ph-squares-four text-base"></i> <?= t('auth_dashboard') ?>
                    </a>
                    <?php else: ?>
                    <a href="/login/" class="px-3.5 py-2 text-[var(--text-secondary)] hover:text-[var(--text-primary)] text-xs font-semibold rounded-lg hover:bg-[var(--bg-hover)] transition-colors"><?= t('auth_login') ?></a>
                    <a href="/register/" class="px-4 py-2 bg-gradient-brand text-white text-xs font-bold rounded-lg hover:opacity-95 transition-all shadow-md glow-orange-sm flex items-center gap-1">
                        <i class="ph ph-user-plus text-sm"></i> <?= t('auth_register') ?>
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
                        <span><?= t('index.badge') ?></span>
                    </div>

                    <h1 class="text-4xl sm:text-6xl lg:text-7xl font-heading font-extrabold tracking-tight leading-[1.1]">
                        <?= t('index.hero_title') ?><br>
                        <span class="text-gradient"><?= t('index.hero_title_highlight') ?></span>
                    </h1>

                    <p class="text-base sm:text-xl text-[var(--text-secondary)] max-w-3xl mx-auto leading-relaxed font-normal">
                        <strong class="text-[var(--text-primary)]"><?= escape(APP_DISPLAY_NAME) ?></strong> <?= t('index.hero_desc') ?>
                    </p>

                    <!-- Quick Terminal Command Bar in Hero -->
                    <div class="pt-2 max-w-3xl mx-auto">
                        <div class="bg-gray-950/90 backdrop-blur-md border border-[var(--border-default)] rounded-xl p-3.5 sm:p-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 font-mono text-xs text-emerald-400 shadow-2xl group hover:border-orange-500/50 transition-all glow-box-cyber">
                            <div class="flex items-center gap-2.5 overflow-x-auto hide-scrollbar py-0.5 min-w-0">
                                <span class="text-xs font-bold text-orange-400 bg-orange-500/10 border border-orange-500/20 px-2 py-0.5 rounded select-none shrink-0">PS</span>
                                <span class="text-gray-500 select-none font-bold">&gt;</span>
                                <span class="truncate sm:whitespace-normal font-mono text-emerald-400 font-medium tracking-tight">irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex</span>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                <button onclick="launchSetupAdmin()"
                                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-sans font-bold transition-all flex items-center gap-1.5 shadow-md cursor-pointer">
                                    <i class="ph ph-download-simple text-sm"></i>
                                    <?= t('index.btn_download') ?>
                                </button>
                                <button onclick="copySnippet(this, 'irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex')"
                                        class="px-4 py-2 bg-gradient-brand hover:opacity-90 text-white rounded-lg text-xs font-sans font-bold transition-all shrink-0 flex items-center gap-1.5 shadow-md glow-orange-sm cursor-pointer">
                                    <i class="ph ph-copy text-sm"></i> <?= t('index.copy_cmd') ?>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Call To Action -->
                    <div class="flex justify-center pt-4">
                        <a href="install/index.php" class="inline-flex items-center justify-center gap-2.5 px-10 py-5 bg-gradient-brand text-white font-extrabold rounded-2xl hover:opacity-95 transition-all shadow-2xl glow-orange text-base font-mono tracking-wide group">
                            <i class="ph ph-sparkle text-2xl group-hover:rotate-12 transition-transform"></i> <?= t('index.btn_setup') ?>
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
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]"><?= t('index.spec_php') ?></div>
                            <div class="text-[11px] text-[var(--text-muted)]"><?= t('index.spec_php_sub') ?></div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-[var(--bg-card)] border border-[var(--border-default)] flex items-center gap-4 hover:border-orange-500/40 transition-colors">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-amber-400 shrink-0">
                            <i class="ph ph-database text-2xl"></i>
                        </div>
                        <div>
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]"><?= t('index.spec_repo') ?></div>
                            <div class="text-[11px] text-[var(--text-muted)]"><?= t('index.spec_repo_sub') ?></div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-[var(--bg-card)] border border-[var(--border-default)] flex items-center gap-4 hover:border-orange-500/40 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-emerald-400 shrink-0">
                            <i class="ph ph-shield-check text-2xl"></i>
                        </div>
                        <div>
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]"><?= t('index.spec_owasp') ?></div>
                            <div class="text-[11px] text-[var(--text-muted)]"><?= t('index.spec_owasp_sub') ?></div>
                        </div>
                    </div>

                    <div class="p-4 rounded-xl bg-[var(--bg-card)] border border-[var(--border-default)] flex items-center gap-4 hover:border-orange-500/40 transition-colors">
                        <div class="w-12 h-12 rounded-lg bg-indigo-500/10 border border-indigo-500/20 flex items-center justify-center text-indigo-400 shrink-0">
                            <i class="ph ph-browser text-2xl"></i>
                        </div>
                        <div>
                            <div class="font-mono text-xs font-bold text-[var(--text-primary)]"><?= t('index.spec_spa') ?></div>
                            <div class="text-[11px] text-[var(--text-muted)]"><?= t('index.spec_spa_sub') ?></div>
                        </div>
                    </div>

                </div>
            </div>
        </section>

        <!-- Feature Grid -->
        <section id="fitur" class="py-20 sm:py-28">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <span class="font-mono text-xs text-[var(--brand-primary)] tracking-widest uppercase font-bold">// KEY ARCHITECTURE //</span>
                    <h2 class="text-3xl sm:text-5xl font-heading font-extrabold tracking-tight"><?= t('index.why_title') ?></h2>
                    <p class="text-[var(--text-secondary)] text-base"><?= t('index.why_subtitle') ?></p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)] group-hover:scale-110 transition-transform">
                            <i class="ph ph-file-doc text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-[var(--text-primary)]"><?= t('index.feat_doc_title') ?></h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed"><?= t('index.feat_doc_desc') ?></p>
                    </div>

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-amber-500/10 border border-amber-500/30 flex items-center justify-center text-amber-400 group-hover:scale-110 transition-transform">
                            <i class="ph ph-layout text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-[var(--text-primary)]"><?= t('index.feat_shell_title') ?></h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed"><?= t('index.feat_shell_desc') ?></p>
                    </div>

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400 group-hover:scale-110 transition-transform">
                            <i class="ph ph-shield-check text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-bold text-lg text-[var(--text-primary)]"><?= t('index.feat_security_title') ?></h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed"><?= t('index.feat_security_desc') ?></p>
                    </div>

                    <div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] hover:border-orange-500/50 transition-all duration-300 group space-y-4 glow-box-cyber">
                        <div class="w-12 h-12 rounded-xl bg-indigo-500/10 border border-indigo-500/30 flex items-center justify-center text-indigo-400 group-hover:scale-110 transition-transform">
                            <i class="ph ph-translate text-2xl"></i>
                        </div>
                        <h3 class="font-heading font-semibold text-lg text-[var(--text-primary)]"><?= t('index.feat_i18n_title') ?></h3>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed"><?= t('index.feat_i18n_desc') ?></p>
                    </div>

                </div>
            </div>
        </section>

        <!-- 13 Pillars Software Architecture Matrix Section (New README Alignment) -->
        <section id="pilar" class="py-20 sm:py-28 bg-[var(--bg-secondary)]/30 border-y border-[var(--border-default)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-3">
                    <span class="font-mono text-xs text-[var(--brand-primary)] tracking-widest uppercase font-bold">// 13 PILARS SOFTWARE ARCHITECTURE //</span>
                    <h2 class="text-3xl sm:text-5xl font-heading font-extrabold tracking-tight"><?= t('index.pillars_title') ?></h2>
                    <p class="text-[var(--text-secondary)] text-base"><?= t('index.pillars_subtitle') ?></p>
                </div>

                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 font-mono text-xs">
                    <!-- Layer 1 -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <span class="font-bold text-orange-400 uppercase"><?= t('index.layer_1') ?></span>
                            <i class="ph ph-cpu text-lg text-orange-400"></i>
                        </div>
                        <ul class="space-y-2 text-gray-300 font-sans text-xs">
                            <li><strong class="font-mono text-white">1. Frontend:</strong> SPA Shell (`public/*/`), Golden References (`references/*.html`), Dynamic Branding</li>
                            <li><strong class="font-mono text-white">2. API & Logic:</strong> Router Proxy Pattern, Module Controllers, Helper Functions</li>
                            <li><strong class="font-mono text-white">3. Database:</strong> Centralized `Repo::table()` Auto-Switch SQL/JSON with File Locking & Atomic Write</li>
                        </ul>
                    </div>

                    <!-- Layer 2 -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <span class="font-bold text-emerald-400 uppercase"><?= t('index.layer_2') ?></span>
                            <i class="ph ph-shield-check text-lg text-emerald-400"></i>
                        </div>
                        <ul class="space-y-2 text-gray-300 font-sans text-xs">
                            <li><strong class="font-mono text-white">4. Auth & Session:</strong> Argon2ID Hashing, Session Regeneration, Remember-Me Tokens</li>
                            <li><strong class="font-mono text-white">5. RBAC & Guards:</strong> Role-to-Shell Mapping (`manajemen`, `admin`, `client`), Dual-Pattern Entry Guard</li>
                        </ul>
                    </div>

                    <!-- Layer 3 -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <span class="font-bold text-indigo-400 uppercase"><?= t('index.layer_3') ?></span>
                            <i class="ph ph-cloud-slash text-lg text-indigo-400"></i>
                        </div>
                        <ul class="space-y-2 text-gray-300 font-sans text-xs">
                            <li><strong class="font-mono text-white">6. Hosting:</strong> Document Root `public/`, Laragon/XAMPP Vhost, Security Headers (.htaccess)</li>
                            <li><strong class="font-mono text-white">7. Cloud Compute:</strong> Native PHP 8.3+ Runtime, Strict Environment Isolation (`APP_ENV`)</li>
                        </ul>
                    </div>

                    <!-- Layer 4 -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <span class="font-bold text-amber-400 uppercase"><?= t('index.layer_4') ?></span>
                            <i class="ph ph-git-branch text-lg text-amber-400"></i>
                        </div>
                        <ul class="space-y-2 text-gray-300 font-sans text-xs">
                            <li><strong class="font-mono text-white">8. CI/CD & Versioning:</strong> SemVer Changelog, Syntax Validation (`php -l`), 3-Stage Build Protocol</li>
                        </ul>
                    </div>

                    <!-- Layer 5 -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <span class="font-bold text-purple-400 uppercase"><?= t('index.layer_5') ?></span>
                            <i class="ph ph-gauge text-lg text-purple-400"></i>
                        </div>
                        <ul class="space-y-2 text-gray-300 font-sans text-xs">
                            <li><strong class="font-mono text-white">9. Rate Limiting:</strong> Fixed-Window IP + Username Brute Force Protection</li>
                            <li><strong class="font-mono text-white">10. Cache & CDN:</strong> Tailwind/Phosphor CDN Whitelist, In-Memory Locale Cache</li>
                            <li><strong class="font-mono text-white">11. Scaling:</strong> Seamless MySQL Migration without breaking controller logic</li>
                        </ul>
                    </div>

                    <!-- Layer 6 -->
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">
                        <div class="flex items-center justify-between border-b border-gray-800 pb-3">
                            <span class="font-bold text-red-400 uppercase"><?= t('index.layer_6') ?></span>
                            <i class="ph ph-warning-circle text-lg text-red-400"></i>
                        </div>
                        <ul class="space-y-2 text-gray-300 font-sans text-xs">
                            <li><strong class="font-mono text-white">12. Logging:</strong> `cache/debug.log` (Dev Only) vs `data/audit_trail.json` (Append-Only)</li>
                            <li><strong class="font-mono text-white">13. Availability:</strong> Atomic Temp Write + Rename, Production JSON-DB Mode Halt Guard</li>
                        </ul>
                    </div>
                </div>
            </div>
        </section>

        <!-- Interactive Setup Console Component -->
        <section id="cara-pasang" class="py-20 sm:py-28 bg-[var(--bg-secondary)]/50 border-y border-[var(--border-default)]">
            <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">

                <div class="text-center max-w-3xl mx-auto mb-12 space-y-3">
                    <span class="font-mono text-xs text-[var(--brand-primary)] tracking-widest uppercase font-bold">// AUTOMATED DEPLOYMENT CONSOLE //</span>
                    <h2 class="text-3xl sm:text-5xl font-heading font-extrabold tracking-tight"><?= t('index.setup_title') ?></h2>
                    <p class="text-[var(--text-secondary)] text-sm sm:text-base"><?= t('index.setup_subtitle') ?></p>
                </div>

                <!-- Console Window Component -->
                <div id="appDownloaderComponent" x-data="appDownloader()" class="bg-gray-950 rounded-2xl border border-[var(--border-default)] shadow-2xl overflow-hidden mb-16 glow-box-cyber">
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

                    <div class="p-6 sm:p-8 font-mono text-xs space-y-4 bg-gray-950 text-emerald-400">
                        <div class="text-gray-500"># Run PowerShell command in your terminal (Laragon / XAMPP):</div>
                        <div class="flex items-start gap-2 bg-black/60 p-4 rounded-xl border border-gray-800">
                            <span class="text-orange-400 select-none font-bold">PS &gt;</span>
                            <code class="text-emerald-400 leading-relaxed break-all">irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex</code>
                        </div>
                        <div class="flex items-center gap-3 pt-1">
                            <button onclick="launchSetupAdmin()"
                                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-500 text-white rounded-lg text-xs font-sans font-bold transition-all flex items-center gap-1.5 shadow-md cursor-pointer">
                                <i class="ph ph-download-simple text-sm"></i>
                                <?= t('index.btn_download') ?>
                            </button>
                            <button onclick="copySnippet(this, 'irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex')"
                                    class="px-4 py-2 bg-gradient-brand hover:opacity-90 text-white rounded-lg text-xs font-sans font-bold transition-all flex items-center gap-1.5 shadow-md glow-orange-sm cursor-pointer">
                                <i class="ph ph-copy text-sm"></i>
                                <?= t('index.copy_cmd') ?>
                            </button>
                        </div>
                        <div class="text-gray-400 pt-2 space-y-1.5 leading-relaxed">
                            <p class="text-white font-bold"><?= t('index.skrip_auto') ?></p>
                            <p>✓ <?= t('index.skrip_step1') ?></p>
                            <p>✓ <?= t('index.skrip_step2') ?></p>
                            <p>✓ <?= t('index.skrip_step3') ?></p>
                            <p>✓ <?= t('index.skrip_step4') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Workflow Step Tabs -->
                <div id="alur-aplikasi" class="space-y-8">
                    <div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 sm:p-8 space-y-6">
                        <div class="flex items-center justify-between border-b border-[var(--border-default)] pb-4">
                            <h3 class="font-heading font-extrabold text-xl"><?= t('index.unified_title') ?></h3>
                            <span class="px-3 py-1 bg-orange-500/10 border border-orange-500/30 text-[var(--brand-primary)] font-mono text-xs rounded-full">MODE: UNIFIED FLOW</span>
                        </div>

                        <p class="text-xs text-[var(--text-secondary)] -mt-2"><?= t('index.unified_desc') ?></p>

                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">

                            <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">TAHAP 1</span>
                                    <i class="ph ph-hard-drives text-lg text-[var(--text-muted)]"></i>
                                </div>
                                <h4 class="font-bold text-sm text-[var(--text-primary)]"><?= t('index.step1_title') ?></h4>
                                <p class="text-xs text-[var(--text-secondary)]"><?= t('index.step1_desc') ?></p>
                            </div>

                            <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">TAHAP 2</span>
                                    <i class="ph ph-folder-open text-lg text-[var(--text-muted)]"></i>
                                </div>
                                <h4 class="font-bold text-sm text-[var(--text-primary)]"><?= t('index.step2_title') ?></h4>
                                <p class="text-xs text-[var(--text-secondary)]"><?= t('index.step2_desc') ?></p>
                            </div>

                            <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">TAHAP 3</span>
                                    <i class="ph ph-palette text-lg text-[var(--text-muted)]"></i>
                                </div>
                                <h4 class="font-bold text-sm text-[var(--text-primary)]"><?= t('index.step3_title') ?></h4>
                                <p class="text-xs text-[var(--text-secondary)]"><?= t('index.step3_desc') ?></p>
                            </div>

                            <div class="p-4 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] space-y-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-mono text-xs text-[var(--brand-primary)] font-bold">TAHAP 4</span>
                                    <i class="ph ph-file-text text-lg text-[var(--text-muted)]"></i>
                                </div>
                                <h4 class="font-bold text-sm text-[var(--text-primary)]"><?= t('index.step4_title') ?></h4>
                                <p class="text-xs text-[var(--text-secondary)]"><?= t('index.step4_desc') ?></p>
                            </div>

                        </div>

                        <div class="text-center pt-2">
                            <a href="install/index.php" class="inline-flex items-center justify-center gap-2.5 px-10 py-5 bg-gradient-brand text-white font-extrabold rounded-2xl hover:opacity-95 transition-all shadow-2xl glow-orange text-base font-mono tracking-wide group">
                                <i class="ph ph-sparkle text-2xl group-hover:rotate-12 transition-transform"></i> <?= t('index.btn_setup') ?>
                            </a>
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
                    <h2 class="text-3xl sm:text-5xl font-heading font-extrabold tracking-tight"><?= t('index.demo_title') ?></h2>
                    <p class="text-[var(--text-secondary)] text-base"><?= t('index.demo_subtitle') ?></p>
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
                                <h3 class="font-heading font-bold text-xl text-[var(--text-primary)]"><?= t('index.role_super_admin') ?></h3>
                                <p class="text-xs text-[var(--text-secondary)] mt-1"><?= t('index.role_super_admin_desc') ?></p>
                            </div>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="manajemen@example.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/manajemen/')" class="w-full py-3 bg-purple-600 hover:bg-purple-500 text-white font-mono font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                                <i class="ph ph-sign-in text-base"></i> <?= t('index.login_super_admin') ?>
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
                                <h3 class="font-heading font-bold text-xl text-[var(--text-primary)]"><?= t('index.role_creator') ?></h3>
                                <p class="text-xs text-[var(--text-secondary)] mt-1"><?= t('index.role_creator_desc') ?></p>
                            </div>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="admin@example.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/admin/')" class="w-full py-3 bg-gradient-brand text-white font-mono font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2 glow-orange-sm cursor-pointer">
                                <i class="ph ph-sign-in text-base"></i> <?= t('index.login_creator') ?>
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
                                <h3 class="font-heading font-bold text-xl text-[var(--text-primary)]"><?= t('index.role_client') ?></h3>
                                <p class="text-xs text-[var(--text-secondary)] mt-1"><?= t('index.role_client_desc') ?></p>
                            </div>
                        </div>
                        <form action="/core/router.php" method="POST" class="w-full">
                            <input type="hidden" name="module" value="auth">
                            <input type="hidden" name="action" value="login">
                            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
                            <input type="hidden" name="email" value="client@example.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" onclick="directLogin(event, this.form, '/client/')" class="w-full py-3 bg-emerald-600 hover:bg-emerald-500 text-white font-mono font-bold text-xs rounded-xl transition-all shadow-md flex items-center justify-center gap-2 cursor-pointer">
                                <i class="ph ph-sign-in text-base"></i> <?= t('index.login_client') ?>
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
                <h2 class="text-3xl sm:text-4xl font-heading font-extrabold"><?= t('index.cta_ready') ?></h2>
                <p class="text-white/80 max-w-xl mx-auto text-sm"><?= t('index.cta_desc') ?></p>
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
                <span class="text-[9px] font-semibold"><?= t('nav_features') ?></span>
            </a>
            <a href="#cara-pasang" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-terminal-window text-xl"></i>
                <span class="text-[9px] font-semibold"><?= t('nav_how_it_works') ?></span>
            </a>
            <a href="#demo" class="flex flex-col items-center gap-1 text-[var(--text-muted)] hover:text-[var(--brand-primary)] transition-colors">
                <i class="ph ph-shield-check text-xl"></i>
                <span class="text-[9px] font-semibold">Demo</span>
            </a>
            <a href="install/index.php" class="flex flex-col items-center gap-1 text-[var(--brand-primary)]">
                <i class="ph ph-magic-wand text-xl"></i>
                <span class="text-[9px] font-bold">Wizard</span>
            </a>
        </div>
    </nav>

    <!-- Footer -->
    <footer class="bg-[var(--bg-secondary)] border-t border-[var(--border-default)] py-8 pb-24 md:pb-8 font-mono text-xs">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-4 text-[var(--text-muted)]">
            <p><?= t('footer_copyright') ?></p>
            <div class="flex gap-6">
                <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="hover:text-[var(--brand-primary)] transition-colors">GitHub</a>
                <a href="#" class="hover:text-[var(--brand-primary)] transition-colors"><?= t('footer_docs') ?></a>
                <a href="#" class="hover:text-[var(--brand-primary)] transition-colors"><?= t('footer_license') ?></a>
            </div>
        </div>
    </footer>

    <script>
    function copySnippet(button, text) {
        var successCallback = function() {
            var originalHTML = button.innerHTML;
            button.innerHTML = '<i class="ph ph-check text-sm"></i> Copied!';
            button.disabled = true;
            setTimeout(function() {
                button.innerHTML = originalHTML;
                button.disabled = false;
            }, 2000);
        };

        var fallbackCopy = function() {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            textarea.style.left = '-9999px';
            document.body.appendChild(textarea);
            textarea.focus();
            textarea.select();
            try {
                document.execCommand('copy');
                successCallback();
            } catch(e) {
                console.error('Copy failed:', e);
                alert('Copy tidak didukung browser ini. Silakan copy manual: ' + text);
            }
            document.body.removeChild(textarea);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(successCallback).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    }

    function launchSetupAdmin() {
        var btn = event && event.currentTarget;
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph ph-spinner text-sm animate-spin"></i> Opening...'; }
        fetch('/setup-launch.php', { method: 'POST', credentials: 'same-origin' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.success) {
                    if (btn) { btn.innerHTML = '<i class="ph ph-check text-sm"></i> Launched!'; }
                } else {
                    alert('Gagal membuka PowerShell: ' + (data.error || 'Unknown error'));
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ph ph-download-simple text-sm"></i> Download'; }
                }
            })
            .catch(function() {
                alert('Gagal menghubungi server. Pastikan berjalan di Laragon.');
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ph ph-download-simple text-sm"></i> Download'; }
            })
            .finally(function() {
                setTimeout(function() {
                    if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ph ph-download-simple text-sm"></i> Download'; }
                }, 3000);
            });
    }

    function directLogin(event, form, redirectUrl) {
        event.preventDefault();
        var formData = new FormData(form);
        formData.append('module', 'auth');
        formData.append('action', 'login');
        fetch('/core/router.php', {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then(function(response) {
            if (response.redirected) {
                window.location.href = response.url;
            } else {
                return response.json().catch(function() { return null; });
            }
        })
        .then(function(data) {
            if (data && data.success) {
                window.location.href = redirectUrl;
            } else {
                form.submit();
            }
        })
        .catch(function() {
            form.submit();
        });
    }

    function appDownloader() {
        return {
            downloading: false,
            copied: false,
            command: 'irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex',
            downloadScript: function() {
                this.downloading = true;
                window.open('https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1', '_blank');
                var self = this;
                setTimeout(function() { self.downloading = false; }, 3000);
            },
            copyCommand: function(button) {
                copySnippet(button, this.command);
                this.copied = true;
                var self = this;
                setTimeout(function() { self.copied = false; }, 2000);
            }
        };
    }

    (function() {
        var toggleBtn = document.getElementById('themeToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                var html = document.documentElement;
                var currentTheme = html.getAttribute('data-theme') || 'dark';
                var newTheme = currentTheme === 'dark' ? 'light' : 'dark';
                html.setAttribute('data-theme', newTheme);
                var icon = toggleBtn.querySelector('i');
                if (icon) {
                    icon.className = newTheme === 'dark' ? 'ph ph-moon text-lg dark:text-amber-400' : 'ph ph-sun text-lg';
                }
                var csrfInput = document.querySelector('input[name="csrf_token"]');
                if (csrfInput) {
                    var params = new URLSearchParams({
                        module: 'user',
                        action: 'updateTheme',
                        theme: newTheme,
                        csrf_token: csrfInput.value
                    });
                    fetch('/core/router.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                        body: params.toString()
                    }).catch(function() {});
                }
            });
        }
    })();
    </script>
</body>
</html>
