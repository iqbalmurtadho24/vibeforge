<?php
/**
 * Manajemen Shell (Super Admin)
 *
 * Shell for the 'manajemen' (Super Admin) role. Mirrors
 * docs/ref_modul_manajemen.html. Only the 'manajemen' role may access.
 */

defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';

// Initialize session
initSession();

// Handle language change
if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
    $_SESSION['language'] = $_GET['lang'];
}

// Detect/set language
$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$isRtl = isRtlLanguage();

// Generate CSRF token
$csrfToken = generateCsrfToken();

// Check authentication and role - manajemen (Super Admin) ONLY
$isLoggedIn = isLoggedIn();
$user = getCurrentUser();
$userRole = $user['role'] ?? null;
$userId = $user['id'] ?? null;

// Validasi role: hanya 'manajemen' yang boleh akses shell ini
if (!$isLoggedIn || $userRole !== 'manajemen') {
    header('Location: /login/');
    exit;
}

// Get theme preference - sourced from the same Repo-backed $user row as
// everything else (Section 3g), no direct JSON read.
$themePreference = $user['theme_preference'] ?? 'dark';

$isDev = APP_ENV !== 'production';
$userName = escape($user['name'] ?? 'Super Admin');
$userInitial = strtoupper(substr($userName, 0, 2));
$userEmail = escape($user['email'] ?? 'manajemen@example.com');
?>

<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= $userName ?> - <?= APP_DISPLAY_NAME ?> Management</title>
    
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
                            dark: '#0A0A0C', 
                            card: '#121215', 
                            surface: '#1A1A1D', 
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
            overflow: hidden;
        }
        
        /* Custom Scrollbar */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: rgba(140, 140, 140, 0.2); border-radius: 10px; }
        .dark ::-webkit-scrollbar-thumb:hover { background: rgba(255, 193, 7, 0.5); }
        .hide-scrollbar::-webkit-scrollbar { display: none; }

        /* SPA Animations */
        .fade-in { animation: fadeIn 0.4s cubic-bezier(0.4, 0, 0.2, 1); }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Sidebar Collapse Logic */
        #desktop-sidebar { transition: width 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
        .sidebar-collapsed { width: 88px !important; }
        .sidebar-collapsed .sidebar-text,
        .sidebar-collapsed .sidebar-profile-info { display: none; opacity: 0; width: 0; }
        .sidebar-collapsed .sidebar-logo-container { display: none; }
        .sidebar-collapsed .sidebar-logo-icon-only { display: flex !important; }
        .sidebar-collapsed .nav-btn { justify-content: center; padding-left: 0; padding-right: 0; }
        .sidebar-collapsed .nav-btn i { font-size: 1.5rem; margin: 0; }
        .sidebar-collapsed .sidebar-header { justify-content: center; padding-left: 0; padding-right: 0; }

        /* Status Badges */
        .badge-success { background: rgba(34, 197, 94, 0.1); color: #22c55e; border: 1px solid rgba(34, 197, 94, 0.2); }
        .badge-warning { background: rgba(245, 158, 11, 0.1); color: #f59e0b; border: 1px solid rgba(245, 158, 11, 0.2); }
        .badge-danger { background: rgba(239, 68, 68, 0.1); color: #ef4444; border: 1px solid rgba(239, 68, 68, 0.2); }
        .badge-premium { background: rgba(255, 193, 7, 0.1); color: #FFC107; border: 1px solid rgba(255, 193, 7, 0.2); }

        /* Toast Notifications */
        #toast-container { z-index: 9999; }
        .toast-enter { animation: toastSlideIn 0.3s forwards; }
        .toast-leave { animation: toastSlideOut 0.3s forwards; }
        @keyframes toastSlideIn { from { transform: translateX(100%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
        @keyframes toastSlideOut { from { transform: translateX(0); opacity: 1; } to { transform: translateX(100%); opacity: 0; } }

        /* Language Selector Dropdown */
        .lang-dropdown {
            opacity: 0;
            visibility: hidden;
            transform: translateY(-10px);
            transition: all 0.2s ease;
        }
        .lang-selector:hover .lang-dropdown {
            opacity: 1;
            visibility: visible;
            transform: translateY(0);
        }

        /* RTL Support */
        [dir="rtl"] { direction: rtl; text-align: right; }
        [dir="rtl"] .ms-auto { margin-left: auto; margin-right: 0; }
        [dir="rtl"] .me-auto { margin-right: auto; margin-left: 0; }
    </style>
</head>
<body class="bg-gray-50 text-gray-900 dark:bg-brand-dark dark:text-white font-sans antialiased h-screen flex flex-col relative">

    <!-- Toast Container -->
    <div id="toast-container" class="fixed top-20 right-4 flex flex-col gap-2 pointer-events-none"></div>

    <div class="flex flex-1 overflow-hidden h-screen">
        
        <!-- Desktop Sidebar -->
        <aside id="desktop-sidebar" class="hidden md:flex flex-col w-72 bg-white dark:bg-brand-card border-r border-gray-200 dark:border-gray-800/60 z-20 shrink-0 overflow-hidden">
            <!-- Header & Toggle -->
            <div class="sidebar-header h-20 flex items-center justify-between px-6 shrink-0 border-b border-transparent transition-all">
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

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-6 px-4 space-y-1">
                <p class="sidebar-text text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-3"><?= t('mgmt.section_management', 'Management') ?></p>
                <button onclick="navigate('overview')" id="nav-desktop-overview" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all group">
                    <i class="ph-fill ph-squares-four text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('admin.dashboard', 'Dashboard') ?></span>
                </button>
                <button onclick="navigate('users')" id="nav-desktop-users" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all group">
                    <i class="ph ph-users text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('admin.users', 'Pengguna & Premium') ?></span>
                </button>
                <button onclick="navigate('creators')" id="nav-desktop-creators" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all group">
                    <i class="ph ph-identification-badge text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('admin.creators', 'Verifikasi Kreator') ?></span>
                </button>
                <button onclick="navigate('moderation')" id="nav-desktop-moderation" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all group">
                    <i class="ph ph-shield-warning text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('admin.moderation', 'Moderasi Konten') ?></span>
                </button>
                <button onclick="navigate('financials')" id="nav-desktop-financials" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all group">
                    <i class="ph ph-money text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('admin.financials', 'Keuangan & Royalti') ?></span>
                </button>

                <div class="sidebar-text my-4 border-t border-gray-200 dark:border-gray-800/60 mx-3"></div>
                <p class="sidebar-text text-xs font-semibold text-gray-400 uppercase tracking-wider px-3 mb-3"><?= t('mgmt.section_system', 'Sistem') ?></p>
                
                <button onclick="navigate('profile')" id="nav-desktop-profile" class="nav-btn w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all group">
                    <i class="ph ph-gear text-xl shrink-0"></i> <span class="sidebar-text whitespace-nowrap"><?= t('admin.settings', 'Pengaturan Admin') ?></span>
                </button>
            </nav>

            <!-- Admin Profile Bottom -->
            <div class="p-4 border-t border-gray-200 dark:border-gray-800/60 shrink-0">
                <div class="nav-btn flex items-center gap-3 px-2 py-2 cursor-pointer hover:bg-gray-100 dark:hover:bg-brand-surface rounded-xl transition-colors" onclick="navigate('profile')">
                    <div class="w-10 h-10 rounded-full bg-brand-gold text-brand-dark font-bold flex items-center justify-center shrink-0">
                        <?= $userInitial ?>
                    </div>
                    <div class="sidebar-profile-info flex-1 min-w-0 transition-opacity">
                        <p class="text-sm font-bold text-gray-900 dark:text-white truncate"><?= $userName ?></p>
                        <p class="text-xs text-brand-gold truncate"><?= ucfirst($userRole ?? 'Admin') ?></p>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Area -->
        <main class="flex-1 flex flex-col relative bg-gray-50 dark:bg-brand-dark overflow-hidden">
            
            <!-- Top Header -->
            <header class="h-20 flex items-center justify-between px-4 sm:px-8 bg-white/80 dark:bg-brand-dark/80 backdrop-blur-md border-b border-gray-200 dark:border-gray-800/60 z-10 shrink-0" id="main-header">
                
                <!-- Mobile Logo -->
                <div class="md:hidden flex items-center gap-2">
                    <svg width="20" height="16" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                        <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                        <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                        <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                    </svg>
                    <span class="font-sans font-light text-lg tracking-[0.1em] mt-1">ADMIN</span>
                </div>

                <!-- Page Title (Desktop) -->
                <h1 id="topbar-title" class="hidden md:block text-xl font-heading font-bold text-gray-900 dark:text-white">
                    <?= t('admin.dashboard', 'Dashboard Overview') ?>
                </h1>

                <!-- Right Actions -->
                <div class="flex items-center gap-3 sm:gap-4 ms-auto">
                    <!-- Global Search -->
                    <div class="hidden sm:flex relative items-center group">
                        <i class="ph ph-magnifying-glass absolute left-4 text-gray-400 group-focus-within:text-brand-gold transition-colors"></i>
                        <input type="text" placeholder="<?= t('common.search', 'Cari...') ?>" class="bg-gray-100 dark:bg-brand-surface border border-transparent rounded-full pl-11 pr-4 py-2.5 text-sm focus:outline-none focus:border-brand-gold focus:ring-1 focus:ring-brand-gold w-72 text-gray-900 dark:text-white placeholder-gray-500 transition-all">
                    </div>
                    
                    <!-- Language Selector -->
                    <div class="relative lang-selector">
                        <button class="flex items-center gap-1.5 px-2 py-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="Change Language">
                            <img src="<?= escape(getAvailableLanguages()[$currentLang]['flag'] ?? '/assets/flags/_default.svg') ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" alt="<?= $currentLang ?>" class="w-6 h-4 rounded-sm shadow-sm object-cover">
                            <i class="ph ph-caret-down text-xs text-gray-500"></i>
                        </button>
                        <div class="lang-dropdown absolute right-0 mt-1 bg-white dark:bg-gray-900 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 py-1 min-w-[140px] z-50">
                            <?php foreach (getAvailableLanguages() as $code => $lang): ?>
                            <a href="?lang=<?= $code ?>" class="flex items-center gap-2 px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors <?= $currentLang === $code ? 'text-brand-gold' : 'text-gray-700 dark:text-gray-300' ?>">
                                <img src="<?= escape($lang['flag']) ?>" onerror="this.onerror=null;this.src='/assets/flags/_default.svg';" class="w-5 h-3.5 rounded-sm"> <?= escape($lang['name']) ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Theme Toggle -->
                    <button id="themeToggleBtn" onclick="toggleTheme()" class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-brand-surface hover:text-brand-gold transition-colors">
                        <i class="ph-fill ph-sun text-xl hidden dark:block text-brand-gold"></i>
                        <i class="ph-fill ph-moon text-xl block dark:hidden text-gray-600"></i>
                    </button>
                    
                    <!-- Notifications -->
                    <button class="w-10 h-10 rounded-full flex items-center justify-center bg-gray-100 dark:bg-brand-surface hover:text-brand-gold transition-colors relative">
                        <i class="ph-fill ph-bell text-xl"></i>
                        <span class="absolute top-2 right-2 w-2.5 h-2.5 bg-red-500 rounded-full border-2 border-white dark:border-brand-surface"></span>
                    </button>
                </div>
            </header>
            <!-- Dynamic Content Area -->
            <div id="dynamic-content" class="flex-1 overflow-y-auto p-4 sm:p-8 pb-24 md:pb-8 custom-scrollbar">
                <!-- Content injected via JS -->
            </div>
        </main>
    </div>

    <!-- Mobile Bottom Navigation -->
    <nav class="md:hidden fixed bottom-0 w-full bg-white dark:bg-[#0A0A0C]/95 backdrop-blur-lg border-t border-gray-200 dark:border-gray-800 pb-safe z-50">
        <div class="flex justify-around items-center h-16 px-1">
            <button onclick="navigate('overview')" id="nav-mobile-overview" class="flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph-fill ph-squares-four text-2xl nav-icon"></i>
                <span class="text-[9px] font-medium">Dasbor</span>
            </button>
            <button onclick="navigate('users')" id="nav-mobile-users" class="flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-users text-2xl nav-icon"></i>
                <span class="text-[9px] font-medium">Pengguna</span>
            </button>
            <button onclick="navigate('creators')" id="nav-mobile-creators" class="flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-identification-badge text-2xl nav-icon"></i>
                <span class="text-[9px] font-medium"><?= t('admin.creators') ?></span>
            </button>
            <button onclick="navigate('moderation')" id="nav-mobile-moderation" class="flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-shield-warning text-2xl nav-icon"></i>
                <span class="text-[9px] font-medium">Moderasi</span>
            </button>
            <button onclick="navigate('profile')" id="nav-mobile-profile" class="flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-gear text-2xl nav-icon"></i>
                <span class="text-[9px] font-medium">Sistem</span>
            </button>
        </div>
    </nav>
    <script>
        // --- View Templates (SPA Content) ---
        const views = {
            overview: `
                <div class="fade-in max-w-7xl mx-auto space-y-6">
                    <!-- KPI Cards (Ref: image_24069d.jpg) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
                        <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm flex items-center gap-4 hover:border-brand-gold/30 transition-colors">
                            <div class="w-14 h-14 rounded-xl bg-brand-gold/10 text-brand-gold flex items-center justify-center shrink-0">
                                <i class="ph-fill ph-users text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-0.5"><?= t('mgmt.kpi_clients') ?></p>
                                <h3 class="text-2xl font-bold font-heading">2,450,123</h3>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm flex items-center gap-4 hover:border-brand-gold/30 transition-colors">
                            <div class="w-14 h-14 rounded-xl bg-brand-gold/10 text-brand-gold flex items-center justify-center shrink-0">
                                <i class="ph-fill ph-microphone-stage text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-0.5"><?= t('mgmt.kpi_creators') ?></p>
                                <h3 class="text-2xl font-bold font-heading">15,800</h3>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm flex items-center gap-4 hover:border-brand-gold/30 transition-colors">
                            <div class="w-14 h-14 rounded-xl bg-brand-gold/10 text-brand-gold flex items-center justify-center shrink-0">
                                <i class="ph-fill ph-wallet text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-0.5"><?= t('mgmt.kpi_payout') ?></p>
                                <h3 class="text-2xl font-bold font-heading">Rp 5.2M</h3>
                            </div>
                        </div>
                        <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm flex items-center gap-4 hover:border-brand-gold/30 transition-colors">
                            <div class="w-14 h-14 rounded-xl bg-brand-gold/10 text-brand-gold flex items-center justify-center shrink-0">
                                <i class="ph-fill ph-crown text-3xl"></i>
                            </div>
                            <div>
                                <p class="text-sm text-gray-500 font-medium mb-0.5"><?= t('mgmt.kpi_premium') ?></p>
                                <h3 class="text-2xl font-bold font-heading">420,500</h3>
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Chart Area -->
                        <div class="lg:col-span-2 bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 p-6 shadow-sm">
                            <div class="flex justify-between items-center mb-6">
                                <div>
                                    <h3 class="font-bold text-lg"><?= t('mgmt.revenue_growth') ?></h3>
                                    <p class="text-xs text-gray-500"><?= t('mgmt.revenue_desc') ?></p>
                                </div>
                                <select class="bg-gray-50 dark:bg-brand-surface border border-gray-200 dark:border-gray-700 rounded-lg px-3 py-1.5 text-sm outline-none">
                                    <option><?= t('mgmt.this_year') ?></option>
                                    <option><?= t('mgmt.this_month') ?></option>
                                </select>
                            </div>
                            <!-- Mock Stacked Bar Chart -->
                            <div class="h-64 flex items-end justify-between gap-2 sm:gap-4 relative border-b border-gray-200 dark:border-gray-700 pb-2">
                                <div class="absolute inset-0 flex flex-col justify-between pointer-events-none opacity-20">
                                    <div class="w-full h-px bg-gray-400"></div><div class="w-full h-px bg-gray-400"></div><div class="w-full h-px bg-gray-400"></div><div class="w-full h-px bg-gray-400"></div>
                                </div>
                                <!-- Bars (Bottom: Ads/Gray, Top: Premium/Gold) -->
                                <div class="w-full flex flex-col justify-end gap-1 h-[40%] group"><div class="w-full bg-brand-gold rounded-t-sm h-[60%]"></div><div class="w-full bg-gray-400 dark:bg-gray-600 rounded-b-sm h-[40%]"></div><span class="text-[10px] text-center mt-2 text-gray-500">Jan</span></div>
                                <div class="w-full flex flex-col justify-end gap-1 h-[45%] group"><div class="w-full bg-brand-gold rounded-t-sm h-[65%]"></div><div class="w-full bg-gray-400 dark:bg-gray-600 rounded-b-sm h-[35%]"></div><span class="text-[10px] text-center mt-2 text-gray-500">Feb</span></div>
                                <div class="w-full flex flex-col justify-end gap-1 h-[55%] group"><div class="w-full bg-brand-gold rounded-t-sm h-[70%]"></div><div class="w-full bg-gray-400 dark:bg-gray-600 rounded-b-sm h-[30%]"></div><span class="text-[10px] text-center mt-2 text-gray-500">Mar</span></div>
                                <div class="w-full flex flex-col justify-end gap-1 h-[50%] group"><div class="w-full bg-brand-gold rounded-t-sm h-[60%]"></div><div class="w-full bg-gray-400 dark:bg-gray-600 rounded-b-sm h-[40%]"></div><span class="text-[10px] text-center mt-2 text-gray-500">Apr</span></div>
                                <div class="w-full flex flex-col justify-end gap-1 h-[70%] group"><div class="w-full bg-brand-gold rounded-t-sm h-[75%]"></div><div class="w-full bg-gray-400 dark:bg-gray-600 rounded-b-sm h-[25%]"></div><span class="text-[10px] text-center mt-2 text-gray-500">Mei</span></div>
                                <div class="w-full flex flex-col justify-end gap-1 h-[85%] group relative"><div class="w-full bg-brand-gold rounded-t-sm h-[80%] shadow-[0_0_15px_rgba(255,193,7,0.3)]"></div><div class="w-full bg-gray-400 dark:bg-gray-600 rounded-b-sm h-[20%]"></div><span class="text-[10px] text-center mt-2 text-brand-gold font-bold">Jun</span></div>
                            </div>
                            <div class="flex justify-center gap-4 mt-4 text-xs text-gray-500">
                                <span class="flex items-center gap-1"><div class="w-3 h-3 bg-brand-gold rounded-sm"></div> <?= t('mgmt.legend_premium') ?></span>
                                <span class="flex items-center gap-1"><div class="w-3 h-3 bg-gray-400 dark:bg-gray-600 rounded-sm"></div> <?= t('mgmt.legend_ads') ?></span>
                            </div>
                        </div>

                        <!-- System Alerts -->
                        <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 p-6 shadow-sm flex flex-col">
                            <div class="flex justify-between items-center mb-6">
                                <h3 class="font-bold text-lg text-red-500 flex items-center gap-2"><i class="ph-fill ph-warning-circle"></i> <?= t('mgmt.system_alerts') ?></h3>
                            </div>
                            <div class="space-y-4 flex-1">
                                <div class="p-3 bg-red-50 dark:bg-red-500/10 border border-red-100 dark:border-red-500/20 rounded-xl">
                                    <h4 class="text-sm font-bold text-red-600 dark:text-red-400"><?= t('mgmt.alert_copyright') ?></h4>
                                    <p class="text-xs text-red-500/80 mt-1"><?= t('mgmt.alert_copyright_desc') ?></p>
                                </div>
                                <div class="p-3 bg-yellow-50 dark:bg-yellow-500/10 border border-yellow-100 dark:border-yellow-500/20 rounded-xl">
                                    <h4 class="text-sm font-bold text-yellow-700 dark:text-yellow-500"><?= t('mgmt.alert_withdraw') ?></h4>
                                    <p class="text-xs text-yellow-600/80 mt-1"><?= t('mgmt.alert_withdraw_desc') ?></p>
                                </div>
                            </div>
                            <button onclick="navigate('moderation')" class="w-full py-2.5 mt-4 text-sm font-medium text-gray-700 dark:text-gray-300 bg-gray-100 dark:bg-brand-surface rounded-xl hover:text-brand-gold transition-colors"><?= t('mgmt.to_moderation') ?></button>
                        </div>
                    </div>
                </div>
            `,
            users: `
                <div class="fade-in max-w-7xl mx-auto space-y-6">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-2">
                        <div>
                            <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white"><?= t('mgmt.users_heading') ?></h2>
                            <p class="text-sm text-gray-500"><?= t('mgmt.users_desc') ?></p>
                        </div>
                        <button class="px-5 py-2.5 bg-brand-gold text-brand-dark font-bold rounded-xl shadow-lg hover:bg-yellow-500 transition-colors flex items-center justify-center gap-2">
                            <i class="ph-bold ph-export"></i> <?= t('mgmt.export_csv') ?>
                        </button>
                    </div>

                    <!-- Filters -->
                    <div class="bg-white dark:bg-brand-card p-4 rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm flex flex-col md:flex-row gap-4 items-center justify-between">
                        <div class="flex gap-2 w-full md:w-auto overflow-x-auto hide-scrollbar">
                            <button class="px-4 py-1.5 bg-gray-900 dark:bg-white text-white dark:text-brand-dark rounded-full text-sm font-medium whitespace-nowrap"><?= t('mgmt.filter_all') ?></button>
                            <button class="px-4 py-1.5 bg-gray-100 dark:bg-brand-surface text-gray-600 dark:text-gray-300 hover:text-brand-gold rounded-full text-sm font-medium whitespace-nowrap transition-colors"><?= t('mgmt.filter_premium') ?></button>
                            <button class="px-4 py-1.5 bg-gray-100 dark:bg-brand-surface text-gray-600 dark:text-gray-300 hover:text-brand-gold rounded-full text-sm font-medium whitespace-nowrap transition-colors"><?= t('mgmt.filter_free') ?></button>
                            <button class="px-4 py-1.5 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400 rounded-full text-sm font-medium whitespace-nowrap transition-colors"><?= t('mgmt.filter_banned') ?></button>
                        </div>
                        <div class="relative w-full md:w-64">
                            <i class="ph ph-magnifying-glass absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
                            <input type="text" placeholder="<?= t('mgmt.search_user') ?>" class="w-full bg-gray-50 dark:bg-brand-surface border border-gray-200 dark:border-gray-700 rounded-lg pl-10 pr-4 py-2 text-sm focus:outline-none focus:border-brand-gold">
                        </div>
                    </div>

                    <!-- Table -->
                    <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="bg-gray-50 dark:bg-brand-surface/50 text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-6 py-4 font-medium"><?= t('col.user') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.reg_date') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.status') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.plan') ?></th>
                                        <th class="px-6 py-4 font-medium text-right"><?= t('col.action') ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                    <!-- User 1 -->
                                    <tr class="hover:bg-gray-50 dark:hover:bg-brand-surface/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <img src="https://i.pravatar.cc/150?img=68" class="w-10 h-10 rounded-full">
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white">Akhi Budi</p>
                                                    <p class="text-xs text-gray-500">akhi.budi@email.com</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">12 Jan 2026</td>
                                        <td class="px-6 py-4"><span class="px-2.5 py-1 badge-success text-xs rounded-md font-medium"><?= t('status.active') ?></span></td>
                                        <td class="px-6 py-4"><span class="px-2.5 py-1 badge-premium text-xs rounded-md font-bold flex items-center gap-1 w-max"><i class="ph-fill ph-crown"></i> <?= t('plan.premium') ?></span></td>
                                        <td class="px-6 py-4 text-right">
                                            <button onclick="adminAction('suspend_user')" class="text-gray-400 hover:text-red-500 transition-colors mr-2" data-i18n-label="mgmt.action.suspend_user"><i class="ph-bold ph-prohibit text-lg"></i></button>
                                            <button onclick="adminAction('view_user')" class="text-gray-400 hover:text-brand-gold transition-colors" data-i18n-label="mgmt.action.view_user"><i class="ph-bold ph-caret-right text-lg"></i></button>
                                        </td>
                                    </tr>
                                    <!-- User 2 -->
                                    <tr class="hover:bg-gray-50 dark:hover:bg-brand-surface/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <img src="https://i.pravatar.cc/150?img=5" class="w-10 h-10 rounded-full">
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white">Siti Aisyah</p>
                                                    <p class="text-xs text-gray-500">siti.a@email.com</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">05 Feb 2026</td>
                                        <td class="px-6 py-4"><span class="px-2.5 py-1 badge-success text-xs rounded-md font-medium"><?= t('status.active') ?></span></td>
                                        <td class="px-6 py-4"><span class="px-2.5 py-1 bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 text-xs rounded-md font-medium border border-gray-200 dark:border-gray-700"><?= t('plan.free') ?></span></td>
                                        <td class="px-6 py-4 text-right">
                                            <button onclick="adminAction('suspend_user')" class="text-gray-400 hover:text-red-500 transition-colors mr-2" data-i18n-label="mgmt.action.suspend_user"><i class="ph-bold ph-prohibit text-lg"></i></button>
                                            <button onclick="adminAction('view_user')" class="text-gray-400 hover:text-brand-gold transition-colors" data-i18n-label="mgmt.action.view_user"><i class="ph-bold ph-caret-right text-lg"></i></button>
                                        </td>
                                    </tr>
                                     <!-- User 3 -->
                                     <tr class="hover:bg-gray-50 dark:hover:bg-brand-surface/30 transition-colors">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 rounded-full bg-gray-200 dark:bg-gray-700 flex items-center justify-center text-gray-500"><i class="ph-fill ph-user"></i></div>
                                                <div>
                                                    <p class="font-bold text-gray-900 dark:text-white">Spammer123</p>
                                                    <p class="text-xs text-gray-500">bot@spam.com</p>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">10 Jul 2026</td>
                                        <td class="px-6 py-4"><span class="px-2.5 py-1 badge-danger text-xs rounded-md font-medium"><?= t('status.banned') ?></span></td>
                                        <td class="px-6 py-4"><span class="px-2.5 py-1 bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-300 text-xs rounded-md font-medium border border-gray-200 dark:border-gray-700"><?= t('plan.free') ?></span></td>
                                        <td class="px-6 py-4 text-right">
                                            <button onclick="adminAction('restore_user')" class="text-gray-400 hover:text-green-500 transition-colors mr-2" data-i18n-label="mgmt.action.restore_user"><i class="ph-bold ph-arrow-counter-clockwise text-lg"></i></button>
                                            <button onclick="adminAction('view_user')" class="text-gray-400 hover:text-brand-gold transition-colors" data-i18n-label="mgmt.action.view_user"><i class="ph-bold ph-caret-right text-lg"></i></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="p-4 border-t border-gray-100 dark:border-gray-800/60 flex items-center justify-between text-sm text-gray-500">
                            <p><?= t('mgmt.showing_users') ?></p>
                            <div class="flex gap-2">
                                <button class="px-3 py-1 bg-gray-50 dark:bg-brand-surface border border-gray-200 dark:border-gray-700 rounded hover:text-brand-gold disabled:opacity-50" disabled><?= t('common.prev') ?></button>
                                <button class="px-3 py-1 bg-gray-50 dark:bg-brand-surface border border-gray-200 dark:border-gray-700 rounded hover:text-brand-gold"><?= t('common.next') ?></button>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            creators: `
                <div class="fade-in max-w-7xl mx-auto space-y-6">
                    <div class="mb-2">
                        <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white"><?= t('admin.creators') ?></h2>
                        <p class="text-sm text-gray-500"><?= t('mgmt.creators_desc') ?></p>
                    </div>

                    <!-- Pending Approvals (Ref: image_24069d.jpg) -->
                    <div class="bg-gradient-to-br from-brand-card to-[#151518] rounded-2xl border border-brand-gold/30 shadow-[0_10px_30px_rgba(255,193,7,0.05)] overflow-hidden">
                        <div class="p-5 border-b border-gray-800 bg-[#1A1A1D]/50 backdrop-blur-sm">
                            <h3 class="font-bold text-brand-gold text-lg"><?= t('mgmt.pending_verif') ?></h3>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="text-gray-400 border-b border-gray-800">
                                    <tr>
                                        <th class="px-6 py-4 font-medium"><?= t('col.creator_name') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.email') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.submit_date') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.status') ?></th>
                                        <th class="px-6 py-4 font-medium text-center"><?= t('col.action') ?></th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-800 text-gray-200" id="creator-verification-table">
                                    <tr id="row-hafiz">
                                        <td class="px-6 py-4 font-bold flex items-center gap-2">
                                            <div class="w-8 h-8 rounded bg-gray-800 flex items-center justify-center text-gray-400"><i class="ph-fill ph-user"></i></div>
                                            Hafiz Ahmed
                                        </td>
                                        <td class="px-6 py-4 text-gray-400">hafiz.a@email.com</td>
                                        <td class="px-6 py-4 text-gray-400">2026-07-14</td>
                                        <td class="px-6 py-4"><span class="text-yellow-500 font-medium"><?= t('status.pending') ?></span></td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <button onclick="handleCreatorAction('row-hafiz', 'approve', 'Hafiz Ahmed')" class="px-4 py-1.5 bg-brand-gold text-black font-bold rounded hover:bg-yellow-500 transition-colors"><?= t('action.approve') ?></button>
                                                <button onclick="handleCreatorAction('row-hafiz', 'reject', 'Hafiz Ahmed')" class="px-4 py-1.5 border border-gray-600 text-gray-300 rounded hover:bg-gray-800 hover:text-white transition-colors"><?= t('action.reject') ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="row-siti">
                                        <td class="px-6 py-4 font-bold flex items-center gap-2">
                                            <div class="w-8 h-8 rounded bg-gray-800 flex items-center justify-center text-gray-400"><i class="ph-fill ph-user"></i></div>
                                            Siti Nurhaliza
                                        </td>
                                        <td class="px-6 py-4 text-gray-400">siti.nur@email.com</td>
                                        <td class="px-6 py-4 text-gray-400">2026-07-13</td>
                                        <td class="px-6 py-4"><span class="text-yellow-500 font-medium"><?= t('status.pending') ?></span></td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <button onclick="handleCreatorAction('row-siti', 'approve', 'Siti Nurhaliza')" class="px-4 py-1.5 bg-brand-gold text-black font-bold rounded hover:bg-yellow-500 transition-colors"><?= t('action.approve') ?></button>
                                                <button onclick="handleCreatorAction('row-siti', 'reject', 'Siti Nurhaliza')" class="px-4 py-1.5 border border-gray-600 text-gray-300 rounded hover:bg-gray-800 hover:text-white transition-colors"><?= t('action.reject') ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr id="row-omar">
                                        <td class="px-6 py-4 font-bold flex items-center gap-2">
                                            <div class="w-8 h-8 rounded bg-gray-800 flex items-center justify-center text-gray-400"><i class="ph-fill ph-user"></i></div>
                                            Omar Yusof
                                        </td>
                                        <td class="px-6 py-4 text-gray-400">omar.y@email.com</td>
                                        <td class="px-6 py-4 text-gray-400">2026-07-10</td>
                                        <td class="px-6 py-4"><span class="text-yellow-500 font-medium"><?= t('status.pending') ?></span></td>
                                        <td class="px-6 py-4">
                                            <div class="flex justify-center gap-2">
                                                <button onclick="handleCreatorAction('row-omar', 'approve', 'Omar Yusof')" class="px-4 py-1.5 bg-brand-gold text-black font-bold rounded hover:bg-yellow-500 transition-colors"><?= t('action.approve') ?></button>
                                                <button onclick="handleCreatorAction('row-omar', 'reject', 'Omar Yusof')" class="px-4 py-1.5 border border-gray-600 text-gray-300 rounded hover:bg-gray-800 hover:text-white transition-colors"><?= t('action.reject') ?></button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `,
            moderation: `
                <div class="fade-in max-w-7xl mx-auto space-y-6">
                    <div class="mb-2">
                        <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white"><?= t('admin.moderation') ?></h2>
                        <p class="text-sm text-gray-500"><?= t('mgmt.moderation_desc') ?></p>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                        <!-- List of Reported Tracks -->
                        <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm overflow-hidden flex flex-col h-[600px]">
                            <div class="p-5 border-b border-gray-100 dark:border-gray-800/60 bg-gray-50 dark:bg-brand-surface/50">
                                <h3 class="font-bold text-lg flex items-center gap-2"><i class="ph-fill ph-flag text-red-500"></i> <?= t('mgmt.report_queue') ?></h3>
                            </div>
                            <div class="flex-1 overflow-y-auto p-2 space-y-2 custom-scrollbar">
                                
                                <!-- Report Item 1 -->
                                <div class="p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-brand-surface border border-transparent dark:hover:border-gray-800 transition-colors cursor-pointer group" onclick="loadAuditTrack('Podcast Kontroversial', 'Kreator X', 'SARA / Ujaran Kebencian (12 Laporan)')">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="px-2 py-0.5 badge-danger text-[10px] uppercase font-bold rounded"><?= t('mgmt.high_priority') ?></span>
                                        <span class="text-xs text-gray-400">1 jam yang lalu</span>
                                    </div>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">Podcast Kontroversial Ep 12</h4>
                                    <p class="text-xs text-gray-500 mb-2">Oleh Kreator X</p>
                                    <p class="text-xs text-red-500/80 line-clamp-1"><i class="ph-fill ph-warning"></i> Alasan: SARA / Ujaran Kebencian (12 Laporan)</p>
                                </div>
                                
                                <div class="border-t border-gray-100 dark:border-gray-800 mx-3"></div>

                                <!-- Report Item 2 -->
                                <div class="p-3 rounded-xl hover:bg-gray-50 dark:hover:bg-brand-surface border border-transparent dark:hover:border-gray-800 transition-colors cursor-pointer group" onclick="loadAuditTrack('Lagu Cover', 'Penyanyi Y', 'Pelanggaran Hak Cipta (Sistem)')">
                                    <div class="flex justify-between items-start mb-2">
                                        <span class="px-2 py-0.5 badge-warning text-[10px] uppercase font-bold rounded"><?= t('mgmt.copyright') ?></span>
                                        <span class="text-xs text-gray-400">5 jam yang lalu</span>
                                    </div>
                                    <h4 class="font-bold text-sm text-gray-900 dark:text-white">Nasyid Cover (Unofficial)</h4>
                                    <p class="text-xs text-gray-500 mb-2">Oleh Penyanyi Y</p>
                                    <p class="text-xs text-yellow-600/80 line-clamp-1"><i class="ph-fill ph-copyright"></i> Alasan: Deteksi Sistem (Audio Match 98%)</p>
                                </div>

                            </div>
                        </div>

                        <!-- Audit Action Panel -->
                        <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm flex flex-col h-[600px] relative overflow-hidden">
                            <div class="p-6 flex-1 flex flex-col justify-center items-center text-center" id="audit-placeholder">
                                <div class="w-20 h-20 bg-gray-100 dark:bg-brand-surface rounded-full flex items-center justify-center text-gray-400 mb-4">
                                    <i class="ph ph-headphones text-4xl"></i>
                                </div>
                                <h3 class="font-bold text-gray-500"><?= t('mgmt.select_track') ?></h3>
                                <p class="text-sm text-gray-400 mt-2"><?= t('mgmt.select_track_desc') ?></p>
                            </div>
                            
                            <!-- Hidden until track selected -->
                            <div class="absolute inset-0 bg-white dark:bg-brand-card flex flex-col hidden" id="audit-active-panel">
                                <div class="p-5 border-b border-gray-100 dark:border-gray-800/60">
                                    <h3 class="font-bold text-lg text-brand-gold"><?= t('mgmt.review_audio') ?></h3>
                                </div>
                                <div class="p-6 flex-1">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="w-16 h-16 bg-gray-200 dark:bg-gray-800 rounded-lg flex items-center justify-center text-gray-400"><i class="ph-fill ph-music-notes text-2xl"></i></div>
                                        <div>
                                            <h4 class="font-bold text-lg text-gray-900 dark:text-white" id="audit-title">Title</h4>
                                            <p class="text-sm text-gray-500" id="audit-artist">Artist</p>
                                        </div>
                                    </div>
                                    <div class="p-4 bg-gray-50 dark:bg-brand-surface rounded-xl border border-gray-200 dark:border-gray-700 mb-8">
                                        <p class="text-xs text-gray-500 uppercase font-bold mb-1"><?= t('mgmt.report_info') ?></p>
                                        <p class="text-sm text-red-500 font-medium" id="audit-reason">Reason</p>
                                    </div>
                                    
                                    <!-- Mini Player for Admin -->
                                    <div class="bg-gray-100 dark:bg-[#0A0A0C] rounded-xl p-4 flex items-center gap-4 border border-gray-200 dark:border-gray-800">
                                        <button class="w-10 h-10 bg-brand-gold rounded-full flex items-center justify-center text-black shadow-md hover:scale-105 transition-transform shrink-0"><i class="ph-fill ph-play ml-0.5"></i></button>
                                        <div class="w-full">
                                            <div class="flex justify-between text-xs text-gray-500 font-mono mb-1"><span>0:00</span><span>3:45</span></div>
                                            <div class="w-full h-1.5 bg-gray-300 dark:bg-gray-700 rounded-full overflow-hidden"><div class="w-0 h-full bg-brand-gold"></div></div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Actions -->
                                <div class="p-5 border-t border-gray-100 dark:border-gray-800/60 bg-gray-50 dark:bg-brand-surface/50 grid grid-cols-2 gap-3">
                                    <button onclick="adminAction('Abaikan Laporan')" class="py-3 rounded-xl font-bold border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors"><?= t('mgmt.ignore_safe') ?></button>
                                    <button onclick="adminAction('Takedown Audio')" class="py-3 rounded-xl font-bold bg-red-500 text-white hover:bg-red-600 shadow-[0_0_15px_rgba(239,68,68,0.3)] transition-all"><?= t('mgmt.takedown') ?></button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            financials: `
                <div class="fade-in max-w-7xl mx-auto space-y-6">
                    <div class="mb-2">
                        <h2 class="text-2xl font-bold font-heading text-gray-900 dark:text-white"><?= t('admin.financials') ?></h2>
                        <p class="text-sm text-gray-500"><?= t('mgmt.financials_desc') ?></p>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div class="bg-gradient-to-br from-[#121215] to-[#1A1A1D] border border-brand-gold/20 p-8 rounded-2xl relative overflow-hidden text-white shadow-lg">
                            <i class="ph-fill ph-trend-up text-[10rem] absolute -right-10 -bottom-10 opacity-5 text-brand-gold"></i>
                            <p class="text-gray-400 font-medium mb-1"><?= t('mgmt.total_revenue_month') ?></p>
                            <h2 class="text-4xl font-bold font-heading text-brand-gold">Rp 1.2M</h2>
                            <p class="text-xs text-green-500 mt-2">+15.4% vs bulan lalu</p>
                        </div>
                        <div class="bg-gradient-to-br from-[#121215] to-[#1A1A1D] border border-red-500/20 p-8 rounded-2xl relative overflow-hidden text-white shadow-lg">
                            <i class="ph-fill ph-hand-coins text-[10rem] absolute -right-10 -bottom-10 opacity-5 text-red-500"></i>
                            <p class="text-gray-400 font-medium mb-1"><?= t('mgmt.royalty_liability') ?></p>
                            <h2 class="text-4xl font-bold font-heading text-red-400">Rp 450 Jt</h2>
                            <p class="text-xs text-gray-500 mt-2">Terdiri dari 342 pengajuan penarikan.</p>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-brand-card rounded-2xl border border-gray-100 dark:border-gray-800/60 shadow-sm overflow-hidden">
                        <div class="p-5 border-b border-gray-100 dark:border-gray-800/60 flex justify-between items-center bg-gray-50 dark:bg-brand-surface/50">
                            <h3 class="font-bold text-lg"><?= t('mgmt.withdraw_queue') ?></h3>
                            <button onclick="adminAction('Proses Batch Penarikan')" class="px-4 py-2 bg-brand-gold text-black text-sm font-bold rounded-lg hover:bg-yellow-500 transition-colors shadow-sm"><?= t('mgmt.process_batch') ?></button>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-left text-sm whitespace-nowrap">
                                <thead class="text-gray-500 dark:text-gray-400 border-b border-gray-200 dark:border-gray-800">
                                    <tr>
                                        <th class="px-6 py-4 font-medium"><?= t('col.tx_id') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.creator') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.transfer_method') ?></th>
                                        <th class="px-6 py-4 font-medium"><?= t('col.amount') ?></th>
                                        <th class="px-6 py-4 font-medium text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100 dark:divide-gray-800/60">
                                    <tr id="wd-1">
                                        <td class="px-6 py-4 font-mono text-gray-500">WD-260715-A1</td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">Ahmad Fulan</td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">BCA (**** 4567)</td>
                                        <td class="px-6 py-4 font-bold text-brand-gold">Rp 5.200.000</td>
                                        <td class="px-6 py-4 text-right">
                                            <button onclick="handlePayout('wd-1')" class="px-3 py-1 bg-gray-900 dark:bg-white text-white dark:text-black rounded text-xs font-bold hover:scale-105 transition-transform"><?= t('action.transfer') ?></button>
                                        </td>
                                    </tr>
                                    <tr id="wd-2">
                                        <td class="px-6 py-4 font-mono text-gray-500">WD-260715-A2</td>
                                        <td class="px-6 py-4 font-bold text-gray-900 dark:text-white">Ust. Hanan Attaki</td>
                                        <td class="px-6 py-4 text-gray-600 dark:text-gray-400">BSI (**** 9982)</td>
                                        <td class="px-6 py-4 font-bold text-brand-gold">Rp 12.450.000</td>
                                        <td class="px-6 py-4 text-right">
                                            <button onclick="handlePayout('wd-2')" class="px-3 py-1 bg-gray-900 dark:bg-white text-white dark:text-black rounded text-xs font-bold hover:scale-105 transition-transform"><?= t('action.transfer') ?></button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `,
            profile: `
                <div class="fade-in max-w-4xl mx-auto space-y-6 pb-10">
                    <!-- Profile Header -->
                    <div class="bg-white dark:bg-brand-card rounded-3xl border border-gray-100 dark:border-gray-800/60 p-8 shadow-sm text-center md:text-left flex flex-col md:flex-row items-center gap-8">
                        <div class="w-32 h-32 rounded-full bg-gradient-to-br from-brand-gold to-yellow-600 flex items-center justify-center text-4xl font-bold text-brand-dark shadow-[0_0_30px_rgba(255,193,7,0.4)] shrink-0">
                            <?= $userInitial ?>
                        </div>
                        <div class="flex-1">
                            <div class="inline-block px-3 py-1 bg-brand-gold/10 text-brand-gold text-xs font-bold uppercase tracking-wider rounded-full mb-3"><?= t('mgmt.system_root') ?></div>
                            <h2 class="text-3xl font-black font-heading text-gray-900 dark:text-white mb-2"><?= $userName ?></h2>
                            <p class="text-gray-500"><?= escape($user['email'] ?? 'manajemen@example.com') ?> - Akses: Full <?= ucfirst($userRole ?? 'Management') ?></p>
                        </div>
                    </div>

                    <!-- Settings List -->
                    <div class="bg-white dark:bg-brand-card rounded-3xl border border-gray-100 dark:border-gray-800/60 overflow-hidden shadow-sm">
                        <div class="px-6 py-5 border-b border-gray-100 dark:border-gray-800/60">
                            <h3 class="font-bold text-lg text-gray-900 dark:text-white"><?= t('mgmt.settings_heading') ?></h3>
                        </div>
                        <div class="divide-y divide-gray-100 dark:divide-gray-800/60">
                            <!-- Theme Toggle -->
                            <div class="p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-[#121215] transition-colors">
                                <div class="flex items-center gap-4 text-left">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400">
                                        <i class="ph-fill ph-moon-stars text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white"><?= t('mgmt.dark_mode_admin') ?></p>
                                    </div>
                                </div>
                                <label class="relative inline-flex items-center cursor-pointer">
                                    <input type="checkbox" id="profileThemeToggle" class="sr-only peer" onchange="toggleTheme()">
                                    <div class="w-11 h-6 bg-gray-300 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-brand-gold shadow-inner"></div>
                                </label>
                            </div>
                            
                            <!-- Backup Data -->
                            <button onclick="adminAction('backup_db')" class="w-full p-4 flex items-center justify-between hover:bg-gray-50 dark:hover:bg-[#121215] transition-colors group" data-i18n-label="mgmt.action.backup_db">
                                <div class="flex items-center gap-4 text-left">
                                    <div class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 group-hover:text-brand-gold transition-colors">
                                        <i class="ph-fill ph-database text-xl"></i>
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white"><?= t('mgmt.backup_db') ?></p>
                                        <p class="text-xs text-gray-500 mt-0.5"><?= t('mgmt.last_backup') ?></p>
                                    </div>
                                </div>
                                <i class="ph ph-download-simple text-gray-400 group-hover:text-brand-gold"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <button onclick="window.location.href='/logout/'" class="w-full bg-white dark:bg-brand-card hover:bg-red-50 dark:hover:bg-red-500/10 text-red-500 border border-red-500/20 font-bold py-4 rounded-2xl transition-all flex items-center justify-center gap-3 shadow-sm">
                        <i class="ph-bold ph-sign-out text-xl"></i> <?= t('mgmt.logout_admin') ?>
                    </button>
                </div>
            `
        };

        // --- Core Application Logic ---

        const contentContainer = document.getElementById('dynamic-content');
        const topbarTitle = document.getElementById('topbar-title');
        let currentRoute = 'overview';
        let isSidebarCollapsed = false;

        const titles = {
            'overview': <?= json_encode(t('admin.dashboard', 'Dashboard Overview')) ?>,
            'users': <?= json_encode(t('mgmt.users_heading', 'Manajemen Pengguna')) ?>,
            'creators': <?= json_encode(t('admin.creators', 'Verifikasi Kreator')) ?>,
            'moderation': <?= json_encode(t('admin.moderation', 'Moderasi Konten')) ?>,
            'financials': <?= json_encode(t('admin.financials', 'Keuangan & Royalti')) ?>,
            'profile': <?= json_encode(t('mgmt.settings_heading', 'Pengaturan Sistem')) ?>
        };

        function initApp() {
            initTheme();
            navigate('overview');
        }

        // Navigation (SPA logic)
        function navigate(route) {
            currentRoute = route;
            contentContainer.innerHTML = views[route] || '<h1>Halaman tidak ditemukan</h1>';
            
            if(topbarTitle) topbarTitle.textContent = titles[route];

            updateNavActiveState('desktop', route);
            updateNavActiveState('mobile', route);
            
            syncProfileThemeToggle();
        }

        function updateNavActiveState(type, activeRoute) {
            const routes = ['overview', 'users', 'creators', 'moderation', 'financials', 'profile'];
            routes.forEach(route => {
                const btn = document.getElementById(`nav-${type}-${route}`);
                if (!btn) return;

                if (route === activeRoute) {
                    if (type === 'desktop') {
                        btn.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all bg-brand-gold/10 text-brand-gold dark:bg-brand-gold/10 dark:text-brand-gold group";
                    } else { // Mobile
                        btn.className = "flex flex-col items-center gap-1 text-brand-gold w-1/5 transition-colors";
                    }
                } else {
                    if (type === 'desktop') {
                        btn.className = "w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium transition-all text-gray-600 dark:text-gray-400 hover:text-brand-gold dark:hover:text-brand-gold hover:bg-gray-100 dark:hover:bg-brand-surface group";
                    } else { // Mobile
                        btn.className = "flex flex-col items-center gap-1 text-gray-400 hover:text-brand-gold w-1/5 transition-colors";
                    }
                }
            });
        }

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

        const htmlElement = document.documentElement;
        function initTheme() {
            if (localStorage.theme === 'light') htmlElement.classList.remove('dark');
            else { htmlElement.classList.add('dark'); localStorage.theme = 'dark'; }
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
            if (profileToggle) profileToggle.checked = htmlElement.classList.contains('dark');
        }

        // Custom Toast Notification instead of alert()
        function showToast(message, type = 'success') {
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            
            let colorClasses = type === 'success' ? 'bg-gray-900 text-brand-gold border-brand-gold/30' : 'bg-red-600 text-white border-red-500';
            let icon = type === 'success' ? 'ph-check-circle' : 'ph-warning-circle';

            toast.className = `flex items-center gap-3 px-4 py-3 rounded-lg shadow-xl border toast-enter ${colorClasses}`;
            toast.innerHTML = `<i class="ph-fill ${icon} text-xl"></i> <span class="font-medium text-sm">${message}</span>`;
            
            container.appendChild(toast);
            
            setTimeout(() => {
                toast.classList.replace('toast-enter', 'toast-leave');
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // General Mock Action Handler
        function adminAction(i18nKey) {
            const msgTpl = <?= json_encode(t('mgmt.toast_action')) ?>;
            showToast(msgTpl.replace('{action}', actionLabels[i18nKey] || i18nKey), 'success');
        }

        // Action label map injected from PHP
        const actionLabels = {
            'suspend_user': <?= json_encode(t('mgmt.action.suspend_user')) ?>,
            'view_user': <?= json_encode(t('mgmt.action.view_user')) ?>,
            'restore_user': <?= json_encode(t('mgmt.action.restore_user')) ?>,
            'backup_db': <?= json_encode(t('mgmt.action.backup_db')) ?>,
            'abaikan_laporan': <?= json_encode(t('mgmt.ignore_safe')) ?>,
            'takedown_audio': <?= json_encode(t('mgmt.takedown')) ?>,
            'proses_batch': <?= json_encode(t('mgmt.process_batch')) ?>,
        };

        // Specific Mock Action for Creator Verification
        window.handleCreatorAction = function(rowId, action, name) {
            const row = document.getElementById(rowId);
            if(row) {
                row.style.opacity = '0.5';
                setTimeout(() => {
                    row.remove();
                    let msg = action === 'approve'
                        ? <?= json_encode(t('mgmt.toast_creator_approved')) ?>.replace('{name}', name)
                        : <?= json_encode(t('mgmt.toast_creator_rejected')) ?>.replace('{name}', name);
                    showToast(msg, action === 'approve' ? 'success' : 'error');
                }, 300);
            }
        }

        // Specific Mock Action for Payouts
        window.handlePayout = function(rowId) {
            const row = document.getElementById(rowId);
            if(row) {
                const btn = row.querySelector('button');
                btn.innerHTML = '<i class="ph ph-spinner animate-spin"></i>';
                btn.classList.add('opacity-50', 'cursor-not-allowed');
                setTimeout(() => {
                    row.remove();
                    showToast(<?= json_encode(t('mgmt.toast_payout')) ?>, 'success');
                }, 800);
            }
        }

        // Specific Logic for Moderation View
        window.loadAuditTrack = function(title, artist, reason) {
            document.getElementById('audit-placeholder').classList.add('hidden');
            document.getElementById('audit-active-panel').classList.remove('hidden');
            
            document.getElementById('audit-title').textContent = title;
            document.getElementById('audit-artist').textContent = artist;
            document.getElementById('audit-reason').textContent = reason;
        }

        // Init
        window.addEventListener('DOMContentLoaded', initApp);

    </script>
</body>
</html>

