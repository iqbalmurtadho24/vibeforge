<?php
/**
 * Admin Shell (Creator Studio)
 *
 * Shell for the 'admin' role. Mirrors docs/ref_modul_admin.html.
 * Only the 'admin' role may access this shell; others go to /login/.
 */

defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';

initSession();

// Handle language change
if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
    $_SESSION['language'] = $_GET['lang'];
}
$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$isRtl = isRtlLanguage();

$csrfToken = generateCsrfToken();

// Authentication & role guard: 'admin' ONLY
$isLoggedIn = isLoggedIn();
$user = getCurrentUser();
$userRole = $user['role'] ?? null;
$userId = $user['id'] ?? null;

if (!$isLoggedIn || $userRole !== 'admin') {
    header('Location: /login/');
    exit;
}

// Theme preference (server-side to avoid flicker) - sourced from the same
// Repo-backed $user row as everything else (Section 3g), no direct JSON read.
$themePreference = $user['theme_preference'] ?? 'dark';

$isDev = APP_ENV !== 'production';
$userName = escape($user['name'] ?? 'Creator');
$userInitial = strtoupper(substr($userName, 0, 2));
$userEmail = escape($user['email'] ?? 'admin@example.com');
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $userName ?> - <?= APP_DISPLAY_NAME ?> Studio</title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg width='28' height='24' viewBox='0 0 28 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Crect x='0' y='7' width='3' height='10' rx='1.5' fill='%23FFC107' /%3E%3Crect x='6' y='4' width='3' height='16' rx='1.5' fill='%23FFC107' /%3E%3Crect x='12' y='0' width='3' height='24' rx='1.5' fill='%23FFC107' /%3E%3Crect x='18' y='4' width='3' height='16' rx='1.5' fill='%23FFC107' /%3E%3Crect x='24' y='7' width='3' height='10' rx='1.5' fill='%23FFC107' /%3E%3C/svg%3E">
    
    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons -->
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: {
                            gold: '#FFC107',
                            dark: '#0F0F11',
                            card: '#1A1A1D',
                            gray: '#8C8C8C',
                            surface: '#232328'
                        }
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
            transition: background-color 0.3s ease, color 0.3s ease;
            overflow: hidden; /* Prevent body scroll, handle scroll in containers */
        }
        
        /* Custom Scrollbar for sleek look */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }
        ::-webkit-scrollbar-track {
            background: transparent; 
        }
        ::-webkit-scrollbar-thumb {
            background: rgba(140, 140, 140, 0.3); 
            border-radius: 10px;
        }
        .dark ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 193, 7, 0.5); 
        }

        /* Hide scrollbar for mobile nav if needed */
        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }

        /* Range Slider Styling for Audio Player */
        input[type=range] {
            -webkit-appearance: none;
            width: 100%;
            background: transparent;
        }
        input[type=range]::-webkit-slider-thumb {
            -webkit-appearance: none;
            height: 12px;
            width: 12px;
            border-radius: 50%;
            background: #FFC107;
            cursor: pointer;
            margin-top: -4px;
            box-shadow: 0 0 5px rgba(255, 193, 7, 0.5);
            opacity: 0;
            transition: opacity 0.2s;
        }
        .player-group:hover input[type=range]::-webkit-slider-thumb {
            opacity: 1;
        }
        input[type=range]::-webkit-slider-runnable-track {
            width: 100%;
            height: 4px;
            cursor: pointer;
            background: rgba(140, 140, 140, 0.3);
            border-radius: 2px;
        }
        /* Custom progress bar fill trick using CSS variable via JS */
        .progress-bar {
            background: linear-gradient(to right, #FFC107 var(--progress, 0%), rgba(140, 140, 140, 0.3) var(--progress, 0%));
        }

        /* Fade in animation for SPA views */
        .fade-in {
            animation: fadeIn 0.3s ease-in-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        #desktop-sidebar {
            transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        .sidebar-collapsed {
            width: 88px !important;
        }
        .sidebar-collapsed .sidebar-text,
        .sidebar-collapsed .sidebar-profile-info {
            display: none;
            opacity: 0;
            width: 0;
        }
        .sidebar-collapsed .sidebar-logo-container {
            display: none;
        }
        .sidebar-collapsed .sidebar-logo-icon-only {
            display: flex !important;
        }
        .sidebar-collapsed .nav-btn {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        .sidebar-collapsed .nav-btn i {
            font-size: 1.5rem;
            margin: 0;
        }
        .sidebar-collapsed .sidebar-header {
            justify-content: center;
            padding-left: 0;
            padding-right: 0;
        }
        /* Language selector dropdown */
        .lang-dropdown { opacity: 0; visibility: hidden; transform: translateY(-8px); transition: all 0.2s ease; }
        .lang-selector:hover .lang-dropdown, .lang-selector:focus-within .lang-dropdown { opacity: 1; visibility: visible; transform: translateY(0); }
        /* RTL support */
        [dir="rtl"] { text-align: right; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-[#0A0A0C] dark:text-white font-sans antialiased h-screen flex flex-col">

    <!-- App Container -->
    <div class="flex flex-1 overflow-hidden">
        
        <!-- Desktop Sidebar -->
        <aside id="desktop-sidebar" class="hidden md:flex flex-col w-64 bg-white dark:bg-[#121215] border-r border-gray-200 dark:border-gray-800/60 z-20 overflow-hidden shrink-0">
            <!-- Logo & Toggle -->
            <div class="sidebar-header h-20 flex items-center justify-between px-6 border-b border-gray-200 dark:border-gray-800/60 shrink-0 transition-all">
                <!-- Full Logo -->
                <a href="#" class="sidebar-logo-container flex items-center gap-3">
                    <svg width="24" height="20" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                        <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                        <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                    </svg>
                    <span class="font-sans font-light text-xl tracking-[0.2em] mt-1"><?= APP_DISPLAY_NAME ?></span>
                </a>
                
                <!-- Icon Only Logo (Hidden by default) -->
                <a href="#" class="sidebar-logo-icon-only hidden items-center justify-center w-full" style="display: none;">
                    <svg width="28" height="24" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                        <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                        <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                    </svg>
                </a>

                <button onclick="toggleSidebar()" class="text-gray-400 hover:text-brand-gold transition-colors focus:outline-none" title="Collapse Sidebar">
                    <i id="sidebar-toggle-icon" class="ph ph-caret-left text-xl"></i>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-2">
                <button onclick="navigate('overview')" id="nav-desktop-overview" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all bg-brand-gold/10 text-brand-gold dark:bg-brand-gold/10 dark:text-brand-gold" title="<?= t('studio.overview') ?>">
                    <i class="ph-fill ph-squares-four text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('studio.overview') ?></span>
                </button>
                <button onclick="navigate('upload')" id="nav-desktop-upload" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand-gold dark:hover:text-brand-gold hover:bg-gray-100 dark:hover:bg-brand-card transition-all" title="<?= t('studio.upload') ?>">
                    <i class="ph ph-upload-simple text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('studio.upload') ?></span>
                </button>
                <button onclick="navigate('royalties')" id="nav-desktop-royalties" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand-gold dark:hover:text-brand-gold hover:bg-gray-100 dark:hover:bg-brand-card transition-all" title="<?= t('studio.royalties') ?>">
                    <i class="ph ph-wallet text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('studio.royalties') ?></span>
                </button>
                <button onclick="navigate('explore')" id="nav-desktop-explore" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand-gold dark:hover:text-brand-gold hover:bg-gray-100 dark:hover:bg-brand-card transition-all" title="<?= t('studio.explore') ?>">
                    <i class="ph ph-headphones text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('studio.explore') ?></span>
                </button>
                <button onclick="navigate('profile')" id="nav-desktop-profile" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand-gold dark:hover:text-brand-gold hover:bg-gray-100 dark:hover:bg-brand-card transition-all" title="<?= t('studio.profile') ?>">
                    <i class="ph ph-user-circle text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('studio.profile') ?></span>
                </button>
            </nav>

            <!-- Bottom Sidebar Profile -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800/60 shrink-0">
                <div class="nav-btn flex items-center gap-3 px-2 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors" onclick="navigate('profile')" title="<?= t('studio.profile') ?>">
                    <img src="https://i.pravatar.cc/150?img=11" alt="Creator" class="w-10 h-10 rounded-full border border-gray-200 dark:border-gray-700 shrink-0 object-cover">
                    <div class="sidebar-profile-info flex-1 min-w-0 transition-opacity">
                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate"><?= $userName ?></p>
                        <p class="text-xs text-brand-gold truncate"><?= t('studio.verified_creator') ?></p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content Area -->
        <main class="flex-1 flex flex-col overflow-hidden relative">
            
            <!-- Top Navbar (Desktop & Mobile) -->
            <header class="h-20 flex items-center justify-between px-4 sm:px-8 border-b border-gray-200 dark:border-gray-800/60 bg-white/80 dark:bg-[#0A0A0C]/80 backdrop-blur-md z-10 shrink-0">
                
                <!-- Mobile Logo (Shows only on small screens) -->
                <div class="md:hidden flex items-center gap-2">
                    <svg width="20" height="16" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                        <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                        <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                    </svg>
                    <span class="font-sans font-light text-lg tracking-[0.1em] mt-1">STUDIO</span>
                </div>

                <!-- Page Title (Desktop) -->
                <h1 id="topbar-title" class="hidden md:block text-xl font-heading font-bold text-gray-900 dark:text-white">
                    <?= t('studio.overview') ?>
                </h1>

                <!-- Right Actions -->
                <div class="flex items-center gap-2 sm:gap-4 ml-auto">
                    <!-- Search (Hidden on small mobile) -->
                    <div class="hidden sm:flex relative items-center">
                        <i class="ph ph-magnifying-glass absolute left-3 text-gray-400"></i>
                        <input type="text" placeholder="Cari karya atau analitik..." class="bg-gray-100 dark:bg-brand-card border-none rounded-full pl-10 pr-4 py-2 text-sm focus:ring-1 focus:ring-brand-gold w-64 text-gray-900 dark:text-white placeholder-gray-500">
                    </div>
                    
                    <!-- Language Selector -->
                    <div class="relative lang-selector">
                        <button type="button" class="flex items-center gap-1.5 px-2 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="<?= t('common.language') ?>">
                            <img src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" alt="<?= $currentLang ?>" class="w-6 h-4 rounded-sm shadow-sm object-cover">
                            <i class="ph ph-caret-down text-xs text-gray-500"></i>
                        </button>
                        <div class="lang-dropdown absolute <?= $isRtl ? 'left-0' : 'right-0' ?> mt-1 bg-white dark:bg-brand-card rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 min-w-[140px] z-50">
                            <?php foreach (getAvailableLanguages() as $code => $lang): ?>
                            <a href="?lang=<?= $code ?>" class="flex items-center gap-2 px-4 py-2 text-sm hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= $currentLang === $code ? 'text-brand-gold' : 'text-gray-700 dark:text-gray-300' ?>"><img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-5 h-3.5 rounded-sm"> <?= escape($lang['name']) ?></a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <button id="themeToggleBtn" class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-brand-card hover:text-brand-gold transition-colors">
                        <i class="ph ph-sun text-xl hidden dark:block text-brand-gold"></i>
                        <i class="ph ph-moon text-xl block dark:hidden text-gray-600"></i>
                    </button>

                    <button class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-brand-card hover:text-brand-gold transition-colors relative">
                        <i class="ph ph-bell text-xl"></i>
                        <span class="absolute top-2 right-2 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-brand-card"></span>
                    </button>
                    
                    <img src="https://i.pravatar.cc/150?img=11" alt="Profile" class="w-10 h-10 rounded-full border-2 border-brand-gold md:hidden cursor-pointer">
                </div>
            </header>

            <!-- Dynamic View Container (AJAX Simulation) -->
            <div id="dynamic-content" class="flex-1 overflow-y-auto p-4 sm:p-8 pb-32 sm:pb-8">
                <!-- Content injected via JS -->
            </div>
            
        </main>
    </div>

    <!-- Persistent Music Player -->
    <div class="fixed bottom-[64px] md:bottom-0 left-0 w-full bg-white dark:bg-[#121215] border-t border-gray-200 dark:border-gray-800/80 px-4 py-3 flex items-center justify-between z-40 shadow-[0_-10px_30px_rgba(0,0,0,0.1)] dark:shadow-[0_-10px_30px_rgba(0,0,0,0.5)]">
        
        <!-- Now Playing Info -->
        <div class="flex items-center gap-3 w-1/3 min-w-0">
            <div class="relative w-12 h-12 rounded-md overflow-hidden shrink-0 group cursor-pointer bg-gray-200 dark:bg-gray-800">
                <img id="player-cover" src="https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=150&q=80" alt="Cover" class="w-full h-full object-cover">
                <div class="absolute inset-0 bg-black/40 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                    <i class="ph ph-caret-up text-white text-xl"></i>
                </div>
            </div>
            <div class="min-w-0 flex-1 hidden sm:block">
                <h4 id="player-title" class="text-sm font-bold text-gray-900 dark:text-white truncate hover:underline cursor-pointer">Al-Kahfi (Ayat 1-110)</h4>
                <p id="player-artist" class="text-xs text-gray-500 truncate hover:underline cursor-pointer">Misyari Rasyid</p>
            </div>
            <button class="hidden sm:block text-gray-400 hover:text-brand-gold"><i class="ph ph-heart text-xl"></i></button>
        </div>

        <!-- Controls & Progress -->
        <div class="flex flex-col items-center justify-center w-1/3 max-w-[500px] player-group">
            <div class="flex items-center gap-4 sm:gap-6 mb-1">
                <button class="text-gray-400 hover:text-white hidden sm:block"><i class="ph ph-shuffle text-lg"></i></button>
                <button class="text-gray-400 hover:text-white"><i class="ph-fill ph-skip-back text-xl"></i></button>
                <button id="main-play-btn" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-gray-900 dark:bg-white text-white dark:text-brand-dark flex items-center justify-center hover:scale-105 transition-transform">
                    <i class="ph-fill ph-play text-lg" id="play-icon"></i>
                </button>
                <button class="text-gray-400 hover:text-white"><i class="ph-fill ph-skip-forward text-xl"></i></button>
                <button class="text-gray-400 hover:text-white hidden sm:block"><i class="ph ph-repeat text-lg"></i></button>
            </div>
            <div class="w-full flex items-center gap-2 text-[10px] text-gray-400 font-medium hidden sm:flex">
                <span id="time-current">12:05</span>
                <input type="range" min="0" max="100" value="30" class="progress-bar flex-1 h-1" style="--progress: 30%">
                <span id="time-total">1:12:45</span>
            </div>
        </div>

        <!-- Extra Controls (Volume) -->
        <div class="flex items-center justify-end gap-3 w-1/3 hidden md:flex">
            <i class="ph ph-microphone-stage text-gray-400 hover:text-white cursor-pointer"></i>
            <i class="ph ph-list-dashes text-gray-400 hover:text-white cursor-pointer"></i>
            <i class="ph ph-desktop text-gray-400 hover:text-white cursor-pointer"></i>
            <div class="flex items-center gap-2 w-24 player-group">
                <i class="ph ph-speaker-high text-gray-400"></i>
                <input type="range" min="0" max="100" value="80" class="progress-bar flex-1 h-1" style="--progress: 80%">
            </div>
        </div>
    </div>

    <!-- Mobile Bottom Navigation (Hidden on Desktop) -->
    <nav class="md:hidden fixed bottom-0 w-full bg-white dark:bg-[#0A0A0C] border-t border-gray-200 dark:border-gray-800 pb-safe z-50">
        <div class="flex justify-around items-center h-16 px-2">
            <button onclick="navigate('overview')" id="nav-mobile-overview" class="flex flex-col items-center gap-1 text-brand-gold w-1/5 transition-colors">
                <i class="ph-fill ph-squares-four text-2xl"></i>
                <span class="text-[10px] font-medium"><?= t('studio.overview') ?></span>
            </button>
            <button onclick="navigate('upload')" id="nav-mobile-upload" class="flex flex-col items-center gap-1 text-gray-400 hover:text-brand-gold w-1/5 transition-colors">
                <i class="ph ph-upload-simple text-2xl"></i>
                <span class="text-[10px] font-medium"><?= t('studio.upload') ?></span>
            </button>
            <button onclick="navigate('royalties')" id="nav-mobile-royalties" class="flex flex-col items-center gap-1 text-gray-400 hover:text-brand-gold w-1/5 transition-colors">
                <i class="ph ph-wallet text-2xl"></i>
                <span class="text-[10px] font-medium"><?= t('studio.royalties') ?></span>
            </button>
            <button onclick="navigate('explore')" id="nav-mobile-explore" class="flex flex-col items-center gap-1 text-gray-400 hover:text-brand-gold w-1/5 transition-colors">
                <i class="ph ph-headphones text-2xl"></i>
                <span class="text-[10px] font-medium">Jelajah</span>
            </button>
            <button onclick="navigate('profile')" id="nav-mobile-profile" class="flex flex-col items-center gap-1 text-gray-400 hover:text-brand-gold w-1/5 transition-colors">
                <i class="ph ph-user-circle text-2xl"></i>
                <span class="text-[10px] font-medium"><?= t('studio.profile') ?></span>
            </button>
        </div>
    </nav>

    <script>
        // --- View Templates (HTML Strings for AJAX Simulation) ---

        const views = {
            overview: `
                <div class="fade-in max-w-6xl mx-auto space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-brand-card p-5 rounded-2xl border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between shadow-sm hover:border-brand-gold/50 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3 mb-2 text-gray-500 dark:text-gray-400">
                                <i class="ph-fill ph-play-circle text-xl text-brand-gold"></i>
                                <span class="text-sm font-medium"><?= t('studio.total_plays') ?></span>
                            </div>
                            <div>
                                <h3 class="text-2xl sm:text-3xl font-bold font-heading">2.45M</h3>
                                <p class="text-xs text-green-500 flex items-center mt-1"><i class="ph-bold ph-trend-up mr-1"></i> +12% dari bulan lalu</p>
                            </div>
                        </div>
                        
                        <div class="bg-white dark:bg-brand-card p-5 rounded-2xl border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between shadow-sm hover:border-brand-gold/50 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3 mb-2 text-gray-500 dark:text-gray-400">
                                <i class="ph-fill ph-users text-xl text-brand-gold"></i>
                                <span class="text-sm font-medium"><?= t('studio.followers') ?></span>
                            </div>
                            <div>
                                <h3 class="text-2xl sm:text-3xl font-bold font-heading">15.8K</h3>
                                <p class="text-xs text-green-500 flex items-center mt-1"><i class="ph-bold ph-trend-up mr-1"></i> +342 minggu ini</p>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-brand-card p-5 rounded-2xl border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between shadow-sm hover:border-brand-gold/50 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3 mb-2 text-gray-500 dark:text-gray-400">
                                <i class="ph-fill ph-wallet text-xl text-brand-gold"></i>
                                <span class="text-sm font-medium"><?= t('studio.royalties') ?></span>
                            </div>
                            <div>
                                <h3 class="text-2xl sm:text-3xl font-bold font-heading">Rp 5.2Jt</h3>
                                <p class="text-xs text-gray-400 mt-1">Belum ditarik</p>
                            </div>
                        </div>

                        <div class="bg-white dark:bg-brand-card p-5 rounded-2xl border border-gray-100 dark:border-gray-800/60 flex flex-col justify-between shadow-sm hover:border-brand-gold/50 transition-colors cursor-pointer">
                            <div class="flex items-center gap-3 mb-2 text-gray-500 dark:text-gray-400">
                                <i class="ph-fill ph-music-notes text-xl text-brand-gold"></i>
                                <span class="text-sm font-medium"><?= t('studio.active_works') ?></span>
                            </div>
                            <div>
                                <h3 class="text-2xl sm:text-3xl font-bold font-heading">324</h3>
                                <p class="text-xs text-brand-gold mt-1 hover:underline"><?= t('studio.manage_works') ?></p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Chart & Recent Table Area -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Graphic Placeholder -->
                        <div class="lg:col-span-2 bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 p-6 flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg">Performa Pendengar</h3>
                                <select class="bg-gray-100 dark:bg-[#121215] border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1 text-sm outline-none">
                                    <option>7 Hari Terakhir</option>
                                    <option>30 Hari Terakhir</option>
                                    <option>Tahun Ini</option>
                                </select>
                            </div>
                            <div class="flex-1 flex items-end justify-between gap-2 h-48 relative border-b border-gray-200 dark:border-gray-700 pb-2">
                                <!-- Mock Bars -->
                                <div class="w-full bg-brand-gold/20 rounded-t-sm h-[30%] hover:bg-brand-gold transition-colors relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs bg-black text-white px-1 rounded opacity-0 group-hover:opacity-100">Sen</span></div>
                                <div class="w-full bg-brand-gold/20 rounded-t-sm h-[50%] hover:bg-brand-gold transition-colors relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs bg-black text-white px-1 rounded opacity-0 group-hover:opacity-100">Sel</span></div>
                                <div class="w-full bg-brand-gold rounded-t-sm h-[80%] relative shadow-[0_0_15px_rgba(255,193,7,0.5)]"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs font-bold text-brand-gold">Rab</span></div>
                                <div class="w-full bg-brand-gold/20 rounded-t-sm h-[40%] hover:bg-brand-gold transition-colors relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs bg-black text-white px-1 rounded opacity-0 group-hover:opacity-100">Kam</span></div>
                                <div class="w-full bg-brand-gold/20 rounded-t-sm h-[60%] hover:bg-brand-gold transition-colors relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs bg-black text-white px-1 rounded opacity-0 group-hover:opacity-100">Jum</span></div>
                                <div class="w-full bg-brand-gold/20 rounded-t-sm h-[90%] hover:bg-brand-gold transition-colors relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs bg-black text-white px-1 rounded opacity-0 group-hover:opacity-100">Sab</span></div>
                                <div class="w-full bg-brand-gold/20 rounded-t-sm h-[70%] hover:bg-brand-gold transition-colors relative group"><span class="absolute -top-6 left-1/2 -translate-x-1/2 text-xs bg-black text-white px-1 rounded opacity-0 group-hover:opacity-100">Min</span></div>
                            </div>
                        </div>

                        <!-- Recent Uploads Table/List -->
                        <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 p-6 flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg text-brand-gold"><?= t('studio.recent_works') ?></h3>
                                <button class="text-sm text-gray-500 hover:text-brand-gold"><?= t('studio.see_all') ?></button>
                            </div>
                            <div class="space-y-4 overflow-y-auto pr-2">
                                <!-- Item 1 -->
                                <div class="flex items-center gap-3 group">
                                    <img src="https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=100&q=80" class="w-12 h-12 rounded-lg object-cover">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold truncate group-hover:text-brand-gold cursor-pointer transition-colors">Al-Kahfi (Ayat 1-50)</h4>
                                        <p class="text-xs text-gray-500 truncate"><?= t('studio.cat_murottal') ?> • 23 Okt 2026</p>
                                    </div>
                                    <span class="text-xs bg-green-500/10 text-green-500 px-2 py-1 rounded"><?= t('studio.active') ?></span>
                                </div>
                                <!-- Item 2 -->
                                <div class="flex items-center gap-3 group">
                                    <div class="w-12 h-12 rounded-lg bg-gray-200 dark:bg-gray-800 flex items-center justify-center text-gray-400">
                                        <i class="ph ph-music-notes text-xl"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold truncate group-hover:text-brand-gold cursor-pointer transition-colors">Nasyid Perjuangan</h4>
                                        <p class="text-xs text-gray-500 truncate"><?= t('studio.cat_nasyid') ?> • 20 Okt 2026</p>
                                    </div>
                                    <span class="text-xs bg-yellow-500/10 text-yellow-500 px-2 py-1 rounded"><?= t('studio.review') ?></span>
                                </div>
                                 <!-- Item 3 -->
                                <div class="flex items-center gap-3 group">
                                    <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?auto=format&fit=crop&w=100&q=80" class="w-12 h-12 rounded-lg object-cover">
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-semibold truncate group-hover:text-brand-gold cursor-pointer transition-colors">Kajian Hati</h4>
                                        <p class="text-xs text-gray-500 truncate"><?= t('studio.cat_podcast') ?> • 15 Okt 2026</p>
                                    </div>
                                    <span class="text-xs bg-green-500/10 text-green-500 px-2 py-1 rounded"><?= t('studio.active') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            upload: `
                <div class="fade-in max-w-4xl mx-auto">
                    <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 p-6 sm:p-10">
                        <div class="text-center mb-8">
                            <h2 class="text-2xl font-bold font-heading mb-2"><?= t('studio.upload_title') ?></h2>
                            <p class="text-gray-500 dark:text-gray-400 text-sm"><?= t('studio.upload_subtitle') ?></p>
                            <p class="text-gray-500 dark:text-gray-400 text-sm">Bagikan suaramu ke jutaan pendengar. Format didukung: MP3, WAV (Max 50MB).</p>
                        </div>
                        
                        <!-- Drag & Drop Zone -->
                        <div class="border-2 border-dashed border-gray-300 dark:border-gray-700 rounded-2xl p-10 text-center mb-8 hover:border-brand-gold hover:bg-brand-gold/5 transition-all cursor-pointer group">
                            <div class="w-16 h-16 bg-brand-gold/10 text-brand-gold rounded-full flex items-center justify-center mx-auto mb-4 group-hover:scale-110 transition-transform">
                                <i class="ph ph-upload-simple text-3xl"></i>
                            </div>
                            <h4 class="font-bold text-lg mb-1 group-hover:text-brand-gold"><?= t('studio.upload_drop') ?></h4>
                            <p class="text-sm text-gray-500"><?= t('studio.upload_quality') ?></p>
                            <p class="text-sm text-gray-500"><?= t('studio.audio_quality_rec') ?></p>
                        </div>

                        <!-- Form Details -->
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            <!-- Cover Image -->
                            <div class="col-span-1">
                                <label class="block text-sm font-medium mb-2">Cover Art</label>
                                <div class="aspect-square bg-gray-100 dark:bg-[#121215] rounded-xl border border-gray-200 dark:border-gray-700 flex flex-col items-center justify-center cursor-pointer hover:border-brand-gold transition-colors">
                                    <i class="ph ph-image text-4xl text-gray-400 mb-2"></i>
                                    <span class="text-xs text-gray-500"><?= t('studio.upload_gambar') ?></span>
                                </div>
                            </div>
                            
                            <!-- Inputs -->
                            <div class="col-span-1 md:col-span-2 space-y-5">
                                <div>
                                    <label class="block text-sm font-medium mb-1"><?= t('studio.judul_karya') ?> <span class="text-red-500">*</span></label>
                                    <input type="text" class="w-full bg-gray-50 dark:bg-[#121215] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold" placeholder="Contoh: Senandung Rindu">
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-sm font-medium mb-1"><?= t('studio.kategori') ?> <span class="text-red-500">*</span></label>
                                        <select class="w-full bg-gray-50 dark:bg-[#121215] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-brand-gold text-gray-400">
                                            <option><?= t('studio.pilih_kategori') ?></option>
                                            <option><?= t('studio.cat_nasyid') ?></option>
                                            <option><?= t('studio.cat_murottal') ?></option>
                                            <option><?= t('studio.cat_podcast') ?></option>
                                            <option><?= t('studio.cat_kajian') ?></option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium mb-1"><?= t('studio.language') ?></label>
                                        <select class="w-full bg-gray-50 dark:bg-[#121215] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-brand-gold text-gray-400">
                                            <option>Indonesia</option>
                                            <option>Arab</option>
                                            <option>Inggris</option>
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium mb-1"><?= t('studio.description') ?></label>
                                    <textarea rows="3" class="w-full bg-gray-50 dark:bg-[#121215] border border-gray-200 dark:border-gray-700 rounded-lg px-4 py-3 text-sm focus:outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold" placeholder="Ceritakan tentang karya ini..."></textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-8 flex justify-end gap-3 border-t border-gray-200 dark:border-gray-800 pt-6">
                            <button class="px-6 py-2.5 rounded-full border border-gray-300 dark:border-gray-600 font-medium hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"><?= t('studio.batal') ?></button>
                            <button class="px-8 py-2.5 rounded-full bg-brand-gold text-brand-dark font-bold hover:bg-yellow-500 hover:shadow-[0_0_15px_rgba(255,193,7,0.4)] transition-all"><?= t('studio.upload_btn') ?></button>
                        </div>
                    </div>
                </div>
            `,
            royalties: `
                <div class="fade-in max-w-5xl mx-auto space-y-6">
                    <div class="bg-gradient-to-br from-brand-card to-[#121215] border border-gray-800 rounded-2xl p-8 relative overflow-hidden text-white shadow-xl">
                        <!-- BG Element -->
                        <i class="ph-fill ph-wallet text-[15rem] absolute -right-10 -bottom-10 opacity-5 text-brand-gold transform rotate-12"></i>
                        
                        <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6">
                            <div>
                                <p class="text-gray-400 font-medium mb-1 flex items-center gap-2">
                                    <i class="ph-fill ph-check-circle text-brand-gold"></i> Saldo Tersedia
                                </p>
                                <h2 class="text-4xl md:text-5xl font-bold font-heading text-brand-gold">Rp 5.200.450</h2>
                                <p class="text-sm mt-2 text-gray-500">Estimasi pencairan berikutnya: 1 Nov 2026</p>
                            </div>
                            <button class="bg-brand-gold text-brand-dark font-bold px-8 py-3 rounded-full hover:bg-yellow-500 transition-transform transform hover:scale-105 flex items-center gap-2 shadow-lg shadow-brand-gold/20 w-full md:w-auto justify-center">
                                <?= t('studio.withdraw_funds') ?> <i class="ph-bold ph-arrow-right"></i>
                            </button>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 p-6">
                        <h3 class="font-bold text-lg mb-6">Riwayat Penarikan</h3>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="pb-3 font-medium">ID Transaksi</th>
                                        <th class="pb-3 font-medium">Tanggal</th>
                                        <th class="pb-3 font-medium">Metode</th>
                                        <th class="pb-3 font-medium">Jumlah</th>
                                        <th class="pb-3 font-medium">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                    <tr>
                                        <td class="py-4 text-gray-900 dark:text-gray-300 font-mono">WD-89273</td>
                                        <td class="py-4 text-gray-500">1 Okt 2026</td>
                                        <td class="py-4">Bank BCA (****4567)</td>
                                        <td class="py-4 font-bold">Rp 3.100.000</td>
                                        <td class="py-4"><span class="px-2 py-1 bg-green-500/10 text-green-500 text-xs rounded border border-green-500/20">Berhasil</span></td>
                                    </tr>
                                    <tr>
                                        <td class="py-4 text-gray-900 dark:text-gray-300 font-mono">WD-78122</td>
                                        <td class="py-4 text-gray-500">1 Sep 2026</td>
                                        <td class="py-4">Gopay (****0998)</td>
                                        <td class="py-4 font-bold">Rp 2.450.000</td>
                                        <td class="py-4"><span class="px-2 py-1 bg-green-500/10 text-green-500 text-xs rounded border border-green-500/20">Berhasil</span></td>
                                    </tr>
                                    <tr>
                                        <td class="py-4 text-gray-900 dark:text-gray-300 font-mono">WD-65490</td>
                                        <td class="py-4 text-gray-500">1 Ags 2026</td>
                                        <td class="py-4">Bank BCA (****4567)</td>
                                        <td class="py-4 font-bold">Rp 1.800.000</td>
                                        <td class="py-4"><span class="px-2 py-1 bg-green-500/10 text-green-500 text-xs rounded border border-green-500/20">Berhasil</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `,
            explore: `
                <div class="fade-in max-w-6xl mx-auto pb-10">
                    <div class="flex items-end justify-between mb-8">
                        <div>
                            <h2 class="text-3xl font-bold font-heading mb-1 text-gray-900 dark:text-white"><?= t('studio.explore') ?></h2>
                            <p class="text-gray-500 dark:text-gray-400 text-sm"><?= t('studio.explore_subtitle') ?></p>
                        </div>
                    </div>

                    <!-- Categories Row -->
                    <div class="flex gap-4 overflow-x-auto hide-scrollbar mb-8 pb-2">
                        <button class="px-5 py-2 bg-brand-gold text-brand-dark rounded-full font-medium text-sm shrink-0 shadow-[0_0_15px_rgba(255,193,7,0.3)]"><?= t('studio.all') ?></button>
                        <button class="px-5 py-2 bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-700 rounded-full font-medium text-sm hover:border-brand-gold transition-colors shrink-0"><?= t('studio.cat_nasyid') ?></button>
                        <button class="px-5 py-2 bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-700 rounded-full font-medium text-sm hover:border-brand-gold transition-colors shrink-0"><?= t('studio.cat_murottal') ?></button>
                        <button class="px-5 py-2 bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-700 rounded-full font-medium text-sm hover:border-brand-gold transition-colors shrink-0"><?= t('studio.cat_podcast') ?></button>
                    </div>

                    <!-- Grid of Tracks (Spotify Style) -->
                    <h3 class="font-bold text-lg mb-4 text-brand-gold"><?= t('studio.trending_now') ?></h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-4 md:gap-6">
                        
                        <!-- Track Card 1 -->
                        <div class="bg-gray-50 dark:bg-brand-card/50 hover:bg-white dark:hover:bg-brand-card p-4 rounded-xl transition-colors group cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-800" onclick="playSong('Cinta Karena Allah', 'SNADA', 'https://images.unsplash.com/photo-1519682577862-22b62b24e493?auto=format&fit=crop&w=300&q=80')">
                            <div class="relative aspect-square rounded-lg overflow-hidden mb-3 shadow-md">
                                <img src="https://images.unsplash.com/photo-1519682577862-22b62b24e493?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
                                <div class="absolute bottom-2 right-2 w-10 h-10 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all">
                                    <i class="ph-fill ph-play text-lg pl-1"></i>
                                </div>
                            </div>
                            <h4 class="font-bold text-sm truncate text-gray-900 dark:text-white">Cinta Karena Allah</h4>
                            <p class="text-xs text-gray-500 truncate mt-1">SNADA</p>
                        </div>

                        <!-- Track Card 2 -->
                        <div class="bg-gray-50 dark:bg-brand-card/50 hover:bg-white dark:hover:bg-brand-card p-4 rounded-xl transition-colors group cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-800" onclick="playSong('Menjaga Hati', 'Ust. Adnin Roslan', 'https://images.unsplash.com/photo-1555529771-835f59bfc50c?auto=format&fit=crop&w=300&q=80')">
                            <div class="relative aspect-square rounded-lg overflow-hidden mb-3 shadow-md">
                                <img src="https://images.unsplash.com/photo-1555529771-835f59bfc50c?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
                                <div class="absolute bottom-2 right-2 w-10 h-10 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all">
                                    <i class="ph-fill ph-play text-lg pl-1"></i>
                                </div>
                            </div>
                            <h4 class="font-bold text-sm truncate text-gray-900 dark:text-white">Menjaga Hati</h4>
                            <p class="text-xs text-gray-500 truncate mt-1">Ust. Adnin Roslan</p>
                        </div>

                         <!-- Track Card 3 -->
                         <div class="bg-gray-50 dark:bg-brand-card/50 hover:bg-white dark:hover:bg-brand-card p-4 rounded-xl transition-colors group cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-800" onclick="playSong('Muhasabah Cinta', 'Edcoustic', 'https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=300&q=80')">
                            <div class="relative aspect-square rounded-lg overflow-hidden mb-3 shadow-md">
                                <img src="https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
                                <div class="absolute bottom-2 right-2 w-10 h-10 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all">
                                    <i class="ph-fill ph-play text-lg pl-1"></i>
                                </div>
                            </div>
                            <h4 class="font-bold text-sm truncate text-gray-900 dark:text-white">Muhasabah Cinta</h4>
                            <p class="text-xs text-gray-500 truncate mt-1">Edcoustic</p>
                        </div>

                        <!-- Track Card 4 -->
                        <div class="bg-gray-50 dark:bg-brand-card/50 hover:bg-white dark:hover:bg-brand-card p-4 rounded-xl transition-colors group cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-800" onclick="playSong('Anak Bertanya', 'Gradasi', 'https://images.unsplash.com/photo-1493225457124-a1a2a5f52479?auto=format&fit=crop&w=300&q=80')">
                            <div class="relative aspect-square rounded-lg overflow-hidden mb-3 shadow-md">
                                <img src="https://images.unsplash.com/photo-1493225457124-a1a2a5f52479?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
                                <div class="absolute bottom-2 right-2 w-10 h-10 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all">
                                    <i class="ph-fill ph-play text-lg pl-1"></i>
                                </div>
                            </div>
                            <h4 class="font-bold text-sm truncate text-gray-900 dark:text-white">Anak Bertanya</h4>
                            <p class="text-xs text-gray-500 truncate mt-1">Gradasi</p>
                        </div>

                        <!-- Track Card 5 -->
                        <div class="bg-gray-50 dark:bg-brand-card/50 hover:bg-white dark:hover:bg-brand-card p-4 rounded-xl transition-colors group cursor-pointer border border-transparent hover:border-gray-200 dark:hover:border-gray-800" onclick="playSong('Al-Kahfi', 'Misyari Rasyid', 'https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=300&q=80')">
                            <div class="relative aspect-square rounded-lg overflow-hidden mb-3 shadow-md">
                                <img src="https://images.unsplash.com/photo-1542816417-0983c9c9ad53?auto=format&fit=crop&w=300&q=80" class="w-full h-full object-cover">
                                <div class="absolute bottom-2 right-2 w-10 h-10 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all">
                                    <i class="ph-fill ph-play text-lg pl-1"></i>
                                </div>
                            </div>
                            <h4 class="font-bold text-sm truncate text-gray-900 dark:text-white">Al-Kahfi</h4>
                            <p class="text-xs text-gray-500 truncate mt-1">Misyari Rasyid</p>
                        </div>
                    </div>
                </div>
            `,
            profile: `
                <div class="fade-in max-w-5xl mx-auto space-y-6 pb-10">
                    <!-- Profile Header (Improved) -->
                    <div class="bg-white dark:bg-brand-card rounded-3xl border border-gray-100 dark:border-gray-800/60 overflow-hidden shadow-sm relative">
                        <!-- Banner -->
                        <div class="h-40 md:h-56 bg-gradient-to-r from-gray-900 to-[#121215] relative group">
                            <img src="https://images.unsplash.com/photo-1614850523459-c2f4c699c52e?auto=format&fit=crop&w=1200&q=80" class="w-full h-full object-cover mix-blend-overlay opacity-40 group-hover:opacity-50 transition-opacity">
                            <button class="absolute top-4 right-4 bg-black/50 hover:bg-black/80 text-white backdrop-blur-md px-4 py-2 rounded-full text-xs font-medium flex items-center gap-2 transition-colors">
                                <i class="ph ph-camera"></i> Ubah Sampul
                            </button>
                        </div>
                        
                        <!-- Profile Details -->
                        <div class="px-6 md:px-10 pb-8 relative">
                            <!-- Avatar positioned over banner -->
                            <div class="flex flex-col md:flex-row md:items-end justify-between gap-6 -mt-16 md:-mt-20 mb-6">
                                <div class="flex flex-col md:flex-row items-center md:items-end gap-5">
                                    <div class="relative group cursor-pointer">
                                        <img src="https://i.pravatar.cc/150?img=11" alt="Ahmad Fulan" class="w-32 h-32 md:w-40 md:h-40 rounded-full border-4 border-white dark:border-brand-card object-cover shadow-xl bg-white dark:bg-gray-800">
                                        <div class="absolute inset-0 bg-black/50 rounded-full flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
                                            <i class="ph ph-camera text-white text-2xl"></i>
                                        </div>
                                    </div>
                                    <div class="text-center md:text-left mb-2">
                                        <h2 class="text-3xl font-bold font-heading flex items-center justify-center md:justify-start gap-2 text-gray-900 dark:text-white">
                                            <?= $userName ?> <i class="ph-fill ph-seal-check text-brand-gold text-2xl" title="<?= t('studio.verification') ?>"></i>
                                        </h2>
                                        <p class="text-gray-500 dark:text-gray-400 font-medium">@ahmadfulan_official</p>
                                    </div>
                                </div>
                                
                                <div class="flex gap-3 justify-center">
                                    <button class="px-6 py-2.5 bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white font-medium rounded-full hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors flex items-center gap-2">
                                        <i class="ph ph-share-network"></i> Bagikan
                                    </button>
                                    <button class="px-6 py-2.5 bg-brand-gold text-brand-dark font-bold rounded-full hover:bg-yellow-500 transition-colors shadow-lg shadow-brand-gold/20 flex items-center gap-2">
                                        <i class="ph-bold ph-pencil-simple"></i> <?= t('studio.edit_profile_btn') ?>
                                    </button>
                                </div>
                            </div>

                            <!-- Quick Stats Row inside header card -->
                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 pt-6 border-t border-gray-100 dark:border-gray-800/60">
                                <div class="text-center md:text-left">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?= t('studio.followers') ?></p>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">15.8K</h4>
                                </div>
                                <div class="text-center md:text-left">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?= t('studio.total_plays') ?></p>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">2.45M</h4>
                                </div>
                                <div class="text-center md:text-left">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1"><?= t('studio.active_works') ?></p>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">324</h4>
                                </div>
                                <div class="text-center md:text-left">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">Bergabung Sejak</p>
                                    <h4 class="text-xl font-bold text-gray-900 dark:text-white">Mar 2024</h4>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Main Content Grid -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left Column: About & Personal Info -->
                        <div class="space-y-6">
                            <div class="bg-white dark:bg-brand-card rounded-3xl border border-gray-100 dark:border-gray-800/60 p-6 md:p-8 shadow-sm">
                                <h3 class="font-bold text-lg mb-6 flex items-center gap-2 text-gray-900 dark:text-white">
                                    <i class="ph-fill ph-user text-brand-gold"></i> <?= t('studio.profile') !== 'Profil' ? 'Personal Details' : 'Detail Personal' ?>
                                </h3>
                                <div class="space-y-5">
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider font-semibold"><?= t('studio.email_registered') ?></p>
                                        <p class="font-medium text-gray-900 dark:text-white"><?= $userEmail ?></p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider font-semibold"><?= t('studio.phone_number') ?></p>
                                        <p class="font-medium text-gray-900 dark:text-white">+62 812 3456 7890</p>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-1 uppercase tracking-wider font-semibold"><?= t('studio.bio') ?></p>
                                        <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed"><?= t('studio.bio_text') ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Right Column: Settings & Preferences -->
                        <div class="lg:col-span-2 space-y-6">
                            <div class="bg-white dark:bg-brand-card rounded-3xl border border-gray-100 dark:border-gray-800/60 overflow-hidden shadow-sm">
                                <div class="px-6 md:px-8 py-6 border-b border-gray-100 dark:border-gray-800/60 flex items-center gap-3">
                                    <i class="ph-fill ph-gear text-xl text-brand-gold"></i>
                                    <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?= t('studio.profile') !== 'Profil' ? 'Settings & Preferences' : 'Pengaturan & Preferensi' ?></h3>
                                </div>
                                <div class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                    
                                    <!-- Settings Items List -->
                                    <div class="p-2">
                                        <!-- Item -->
                                        <button class="w-full p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-[#121215] rounded-2xl transition-colors group">
                                            <div class="flex items-center gap-4 text-left">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 group-hover:text-brand-gold group-hover:bg-brand-gold/10 transition-colors shadow-sm">
                                                    <i class="ph-fill ph-bell-ringing text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white">Notifikasi</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Atur pemberitahuan email, perangkat, & analitik</p>
                                                </div>
                                            </div>
                                            <i class="ph ph-caret-right text-gray-400 group-hover:text-brand-gold transition-colors"></i>
                                        </button>
                                        
                                        <!-- Item -->
                                        <button class="w-full p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-[#121215] rounded-2xl transition-colors group">
                                            <div class="flex items-center gap-4 text-left">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 group-hover:text-brand-gold group-hover:bg-brand-gold/10 transition-colors shadow-sm">
                                                    <i class="ph-fill ph-bank text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white">Rekening & Pembayaran</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5"><?= t('studio.payment_method_desc') ?></p>
                                                </div>
                                            </div>
                                            <i class="ph ph-caret-right text-gray-400 group-hover:text-brand-gold transition-colors"></i>
                                        </button>

                                        <!-- Theme Toggle Item -->
                                        <div class="w-full p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-[#121215] rounded-2xl transition-colors">
                                            <div class="flex items-center gap-4 text-left">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 shadow-sm">
                                                    <i class="ph-fill ph-moon-stars text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white">Mode Gelap</p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Sesuaikan tema tampilan studio</p>
                                                </div>
                                            </div>
                                            <label class="relative inline-flex items-center cursor-pointer">
                                                <input type="checkbox" id="profileThemeToggle" class="sr-only peer" onchange="toggleTheme()">
                                                <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all dark:border-gray-600 peer-checked:bg-brand-gold shadow-inner"></div>
                                            </label>
                                        </div>
                                        
                                        <!-- Item -->
                                        <button class="w-full p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-[#121215] rounded-2xl transition-colors group">
                                            <div class="flex items-center gap-4 text-left">
                                                <div class="w-12 h-12 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 group-hover:text-brand-gold group-hover:bg-brand-gold/10 transition-colors shadow-sm">
                                                    <i class="ph-fill ph-headset text-xl"></i>
                                                </div>
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white"><?= t('studio.creator_help') ?></p>
                                                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Hubungi tim dukungan kami</p>
                                                </div>
                                            </div>
                                            <i class="ph ph-caret-right text-gray-400 group-hover:text-brand-gold transition-colors"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <!-- Danger Zone (Logout) -->
                            <button onclick="window.location.href='/logout/'" class="w-full bg-white dark:bg-brand-card hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500 border border-red-500/20 font-bold py-5 rounded-3xl transition-all flex items-center justify-center gap-3 shadow-sm hover:shadow-md">
                                <i class="ph-bold ph-sign-out text-2xl"></i> <?= t('studio.logout_studio') ?>
                            </button>
                        </div>
                    </div>
                </div>
            `
        };

        // State variables
        let currentRoute = 'overview';
        let isPlaying = false;

        const contentContainer = document.getElementById('dynamic-content');
        const topbarTitle = document.getElementById('topbar-title');
        
        const playBtn = document.getElementById('main-play-btn');
        const playIcon = document.getElementById('play-icon');
        const playerTitle = document.getElementById('player-title');
        const playerArtist = document.getElementById('player-artist');
        const playerCover = document.getElementById('player-cover');

        // Navigation Titles mapping
        const titles = {
            'overview': <?= json_encode(t('studio.overview')) ?>,
            'upload': <?= json_encode(t('studio.upload')) ?>,
            'royalties': <?= json_encode(t('studio.royalties')) ?>,
            'explore': <?= json_encode(t('studio.explore')) ?>,
            'profile': <?= json_encode(t('studio.profile')) ?>
        };

        let isSidebarCollapsed = false;

        function toggleSidebar() {
            const sidebar = document.getElementById('desktop-sidebar');
            const icon = document.getElementById('sidebar-toggle-icon');
            
            isSidebarCollapsed = !isSidebarCollapsed;
            
            if(isSidebarCollapsed) {
                sidebar.classList.add('sidebar-collapsed');
                icon.classList.remove('ph-caret-left');
                icon.classList.add('ph-caret-right');
                icon.parentElement.title = 'Expand Sidebar';
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                icon.classList.remove('ph-caret-right');
                icon.classList.add('ph-caret-left');
                icon.parentElement.title = 'Collapse Sidebar';
            }
        }

        // Initialize SPA
        function initApp() {
            navigate('overview'); // Load default route
            initTheme(); // Load theme
        }

        // The "AJAX" Navigation function
        function navigate(route) {
            currentRoute = route;
            
            // 1. Inject HTML Content
            contentContainer.innerHTML = views[route] || '<h1>Halaman tidak ditemukan</h1>';
            
            // 2. Update Topbar Title
            if(topbarTitle) topbarTitle.textContent = titles[route];

            // 3. Update Active States on Navbars
            updateNavActiveState('desktop', route);
            updateNavActiveState('mobile', route);
            
            // 4. Sync profile toggle if it exists
            syncProfileThemeToggle();
        }

        // Update styling for active menu items
        function updateNavActiveState(type, activeRoute) {
            const routes = ['overview', 'upload', 'royalties', 'explore', 'profile'];
            
            routes.forEach(route => {
                const btn = document.getElementById(`nav-${type}-${route}`);
                if (!btn) return;

                if (route === activeRoute) {
                    // Active Styling
                    if (type === 'desktop') {
                        btn.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all bg-brand-gold/10 text-brand-gold dark:bg-brand-gold/10 dark:text-brand-gold";
                    } else { // Mobile
                        btn.className = "flex flex-col items-center gap-1 text-brand-gold w-1/5 transition-colors";
                    }
                } else {
                    // Inactive Styling
                    if (type === 'desktop') {
                        btn.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-brand-gold dark:hover:text-brand-gold hover:bg-gray-100 dark:hover:bg-brand-card transition-all";
                    } else { // Mobile
                        btn.className = "flex flex-col items-center gap-1 text-gray-400 hover:text-brand-gold w-1/5 transition-colors";
                    }
                }
            });
        }

        // Global Audio Player Logic
        function togglePlay() {
            isPlaying = !isPlaying;
            if (isPlaying) {
                playIcon.classList.remove('ph-play');
                playIcon.classList.add('ph-pause');
                // Simulate playing progress via CSS animation class if needed
            } else {
                playIcon.classList.remove('ph-pause');
                playIcon.classList.add('ph-play');
            }
        }

        // Callable from Explore view track cards
        function playSong(title, artist, coverUrl) {
            playerTitle.textContent = title;
            playerArtist.textContent = artist;
            playerCover.src = coverUrl;
            
            if(!isPlaying) togglePlay(); // Force play if paused
            
            // Show subtle notification
            showNotification(`Memutar: ${title}`);
        }

        playBtn.addEventListener('click', togglePlay);

        // Progress Bar input handling (CSS variable trick)
        document.querySelectorAll('input[type=range]').forEach(input => {
            input.addEventListener('input', function() {
                this.style.setProperty('--progress', this.value + '%');
            });
        });

        // Theme Toggle Logic
        const htmlElement = document.documentElement;
        const themeToggleBtn = document.getElementById('themeToggleBtn');

        function initTheme() {
            if (localStorage.theme === 'light') {
                htmlElement.classList.remove('dark');
            } else {
                htmlElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }

        function toggleTheme() {
            if (htmlElement.classList.contains('dark')) {
                htmlElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                htmlElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
            syncProfileThemeToggle();
        }

        function syncProfileThemeToggle() {
            const profileToggle = document.getElementById('profileThemeToggle');
            if (profileToggle) {
                profileToggle.checked = htmlElement.classList.contains('dark');
            }
        }

        if(themeToggleBtn) {
            themeToggleBtn.addEventListener('click', toggleTheme);
        }

        // Simple notification toaster
        function showNotification(message) {
            const notif = document.createElement('div');
            notif.className = 'fixed top-24 left-1/2 transform -translate-x-1/2 bg-brand-gold text-brand-dark px-6 py-3 rounded-full shadow-[0_5px_20px_rgba(255,193,7,0.4)] z-50 transition-opacity duration-300 text-sm font-bold flex items-center gap-2';
            notif.innerHTML = `<i class="ph-bold ph-music-notes"></i> ${message}`;
            
            document.body.appendChild(notif);
            
            setTimeout(() => {
                notif.classList.add('opacity-0');
                setTimeout(() => notif.remove(), 300);
            }, 3000);
        }

        // Run Init
        window.addEventListener('DOMContentLoaded', initApp);

    </script>
</body>
</html>