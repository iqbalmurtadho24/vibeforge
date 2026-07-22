<?php
/**
 * Landing Page (Public Index)
 *
 * Entry point for unauthenticated users.
 */

defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../include/config.php';
require_once __DIR__ . '/../include/helper.php';

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

// Check login status
$isLoggedIn = isLoggedIn();
$user = getCurrentUser();
$dashboardUrl = getDashboardUrl();

// Get theme preference
$themePreference = 'dark';
if ($isLoggedIn && isset($user['id'])) {
    $users = loadJsonFile('users.json');
    foreach ($users as $u) {
        if ($u['id'] === $user['id']) {
            $themePreference = $u['theme_preference'] ?? 'dark';
            break;
        }
    }
}

$isDev = APP_ENV !== 'production';
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" class="<?= $themePreference === 'light' ? '' : 'dark' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_DISPLAY_NAME ?> - <?= escape(APP_TAGLINE) ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg width='28' height='24' viewBox='0 0 28 24' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Crect x='0' y='7' width='3' height='10' rx='1.5' fill='%23FFC107' /%3E%3Crect x='6' y='4' width='3' height='16' rx='1.5' fill='%23FFC107' /%3E%3Crect x='12' y='0' width='3' height='24' rx='1.5' fill='%23FFC107' /%3E%3Crect x='18' y='4' width='3' height='16' rx='1.5' fill='%23FFC107' /%3E%3Crect x='24' y='7' width='3' height='10' rx='1.5' fill='%23FFC107' /%3E%3C/svg%3E">
    
    <!-- Fonts: Inter for general text, possibly Poppins for headings to match elegance -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@500;600;700&display=swap" rel="stylesheet">
    
    <!-- Icons: Phosphor Icons for a clean, modern look -->
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
                            gold: '#FFC107', // Approximate gold from image
                            dark: '#0F0F11', // Main dark background
                            card: '#1A1A1D', // Slightly lighter dark for cards
                            gray: '#8C8C8C',
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
        /* Custom styles for elements that are tricky with utility classes alone */
        body {
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        
        .glass-header {
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        .hide-scrollbar::-webkit-scrollbar {
            display: none;
        }
        .hide-scrollbar {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }

        /* SVG Logo styling to match colors dynamically if needed, though we'll use inline SVG */
        .app-logo path.bar {
            fill: #FFC107;
        }

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
<body class="bg-gray-50 text-gray-900 dark:bg-brand-dark dark:text-white font-sans antialiased min-h-screen flex flex-col">

    <!-- Navigation -->
    <header class="fixed top-0 w-full z-50 glass-header bg-white/80 dark:bg-brand-dark/80 border-b border-gray-200 dark:border-gray-800 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo & Tagline -->
                <div class="flex flex-col justify-center items-start">
                    <a href="#" class="flex items-center gap-3">
                        <!-- Custom SVG Logo representing the audio waves -->
                        <svg width="28" height="24" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                            <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                            <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                            <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                            <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                        </svg>
                        <span class="font-sans font-light text-2xl tracking-[0.25em] text-gray-900 dark:text-white mt-1">MYAPP</span>
                    </a>
                    <span class="text-[10px] sm:text-xs font-medium text-brand-gold mt-1 ml-[44px]"><?= APP_TAGLINE ?></span>
                </div>

                <!-- Desktop Menu & Actions -->
                <div class="hidden md:flex items-center gap-6">
                    <nav class="flex gap-6">
                        <a href="#kategori" class="text-sm font-medium hover:text-brand-gold transition-colors"><?= t('nav.categories', 'Kategori') ?></a>
                        <a href="#terbaru" class="text-sm font-medium hover:text-brand-gold transition-colors"><?= t('nav.popular', 'Populer') ?></a>
                        <a href="#premium" class="text-sm font-medium hover:text-brand-gold transition-colors"><?= t('nav.premium', 'Premium') ?></a>
                    </nav>

                    <div class="flex items-center gap-4">
                        <!-- Language Selector -->
                        <div class="relative lang-selector">
                            <button class="flex items-center gap-1.5 px-2 py-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors" aria-label="Change Language">
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
                        <button id="themeToggleBtn" class="p-2 rounded-full hover:bg-gray-200 dark:hover:bg-gray-800 transition-colors" aria-label="Toggle Dark Mode">
                            <i class="ph ph-sun text-xl hidden dark:block text-brand-gold"></i>
                            <i class="ph ph-moon text-xl block dark:hidden text-gray-600"></i>
                        </button>

                        <!-- Auth Buttons - PHP Based -->
                        <?php if ($isLoggedIn): ?>
                        <a href="<?= $dashboardUrl ?>" class="flex items-center gap-2 px-4 py-2 text-sm font-medium bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white rounded-full border border-gray-200 dark:border-gray-700 hover:border-brand-gold transition-colors">
                            <i class="ph-fill ph-squares-four text-brand-gold"></i> <?= t('auth.dashboard', 'Dashboard') ?>
                        </a>
                        <a href="/logout/" class="flex items-center gap-2 px-4 py-2 text-sm font-medium text-red-500 hover:text-red-600 transition-colors">
                            <i class="ph-bold ph-sign-out"></i> <?= t('auth.logout', 'Keluar') ?>
                        </a>
                        <?php else: ?>
                        <a href="/login/" class="px-4 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 hover:text-brand-gold transition-colors"><?= t('auth.login', 'Masuk') ?></a>
                        <a href="/register/" class="px-5 py-2 text-sm font-medium bg-brand-gold text-brand-dark rounded-full hover:bg-yellow-500 transition-colors shadow-sm"><?= t('auth.register', 'Daftar') ?></a>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Mobile Header (No Menu Button) -->
                <div class="flex items-center gap-3 md:hidden">
                    <button id="mobileThemeToggle" class="p-2 rounded-full text-brand-gold">
                        <i class="ph ph-sun text-xl hidden dark:block"></i>
                        <i class="ph ph-moon text-xl block dark:hidden text-gray-600"></i>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <main class="flex-grow pt-24 pb-20">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Hero Section (Public Landing Page) -->
            <section class="py-12 lg:py-20 flex flex-col-reverse lg:flex-row justify-between items-center gap-12 lg:gap-8">
                <div class="lg:w-1/2 flex flex-col items-start text-left">
                    <div class="inline-block px-3 py-1 mb-6 rounded-full bg-brand-gold/10 border border-brand-gold/20 text-brand-gold text-xs font-semibold tracking-wide uppercase">
                        <?= t('hero.badge', 'The Home of Nasheed') ?>
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-heading font-bold mb-6 leading-tight text-gray-900 dark:text-white">
                        <?= t('hero.title', 'Dengarkan Suara') ?> <br class="hidden sm:block">
                        <span class="text-brand-gold"><?= t('hero.title_highlight', 'Ketenangan Hati.') ?></span>
                    </h1>
                    <p class="text-lg text-gray-600 dark:text-gray-400 mb-8 max-w-lg leading-relaxed">
                        <?= t('hero.subtitle', APP_DISPLAY_NAME . ' menemani setiap momenmu dengan ribuan nasyid, murottal, dan kajian inspiratif pilihan. Mulai perjalanan spiritualmu hari ini.') ?>
                    </p>

                    <!-- Hero Auth Actions - PHP Based -->
                    <?php if ($isLoggedIn): ?>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <a href="<?= $dashboardUrl ?>" class="px-8 py-4 bg-brand-gold text-brand-dark font-semibold rounded-full hover:bg-yellow-500 transition-all shadow-lg hover:shadow-brand-gold/20 flex items-center justify-center gap-2 text-center w-full sm:w-auto">
                            <i class="ph-bold ph-play-circle text-xl"></i> <?= t('hero.cta.listen', 'Mulai Mendengarkan') ?>
                        </a>
                        <a href="<?= $dashboardUrl ?>" class="px-8 py-4 bg-white dark:bg-brand-card text-gray-900 dark:text-white font-medium rounded-full border border-gray-200 dark:border-gray-700 hover:border-brand-gold transition-all flex items-center justify-center gap-2 text-center w-full sm:w-auto">
                            <i class="ph-bold ph-arrow-right text-xl"></i> <?= t('auth.dashboard', 'Ke Dashboard') ?>
                        </a>
                    </div>
                    <?php else: ?>
                    <div class="flex flex-col sm:flex-row gap-4 w-full sm:w-auto">
                        <a href="/login/" class="px-8 py-4 bg-brand-gold text-brand-dark font-semibold rounded-full hover:bg-yellow-500 transition-all shadow-lg hover:shadow-brand-gold/20 flex items-center justify-center gap-2 text-center w-full sm:w-auto">
                            <?= t('hero.cta.listen', 'Mulai Mendengarkan') ?> <i class="ph-bold ph-play-circle text-xl"></i>
                        </a>
                        <button onclick="document.getElementById('kategori').scrollIntoView({behavior: 'smooth'})" class="px-8 py-4 bg-white dark:bg-brand-card text-gray-900 dark:text-white font-medium rounded-full border border-gray-200 dark:border-gray-700 hover:border-brand-gold transition-all flex items-center justify-center gap-2 text-center w-full sm:w-auto">
                            <?= t('hero.cta.explore', 'Jelajahi Kategori') ?>
                        </button>
                    </div>
                    <?php endif; ?>

                    <!-- Social Proof -->
                    <div class="mt-10 flex items-center gap-4 text-sm text-gray-500 dark:text-gray-400">
                        <div class="flex -space-x-3">
                            <img class="w-8 h-8 rounded-full border-2 border-white dark:border-brand-dark" src="https://i.pravatar.cc/100?img=1" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white dark:border-brand-dark" src="https://i.pravatar.cc/100?img=2" alt="User">
                            <img class="w-8 h-8 rounded-full border-2 border-white dark:border-brand-dark" src="https://i.pravatar.cc/100?img=3" alt="User">
                            <div class="w-8 h-8 rounded-full border-2 border-white dark:border-brand-dark bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-[10px] font-bold text-gray-600 dark:text-gray-300">
                                10k+
                            </div>
                        </div>
                        <p><?= t('social_proof.active_listeners', 'Pendengar aktif setiap hari.') ?></p>
                    </div>
                </div>

                <!-- Hero Image/Graphic -->
                <div class="lg:w-1/2 relative w-full flex justify-center lg:justify-end">
                    <!-- Decorative background blob -->
                    <div class="absolute inset-0 bg-gradient-to-tr from-brand-gold/20 to-transparent rounded-full blur-3xl -z-10 w-3/4 h-3/4 mx-auto lg:mx-0 lg:ml-auto"></div>
                    
                    <!-- Mockup Container -->
                    <div class="relative w-[300px] h-[600px] bg-black rounded-[2.5rem] border-8 border-gray-900 dark:border-gray-800 shadow-2xl overflow-hidden transform rotate-2 hover:rotate-0 transition-transform duration-500">
                        <!-- Notch -->
                        <div class="absolute top-0 inset-x-0 h-6 bg-black z-20 rounded-b-3xl mx-16"></div>
                        
                        <!-- App UI Mockup inside phone -->
                        <div class="absolute inset-0 bg-brand-dark text-white p-4 pt-10 flex flex-col">
                             <div class="flex items-center justify-between mb-6">
                                 <i class="ph ph-list text-xl"></i>
                                 <div class="flex items-center gap-2">
                                     <svg width="20" height="16" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                         <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                                         <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                                         <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                                         <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                                         <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                                     </svg>
                                     <span class="font-sans font-light text-base tracking-[0.25em]">MYAPP</span>
                                 </div>
                                 <i class="ph ph-magnifying-glass text-xl"></i>
                             </div>
                             
                             <div class="bg-gray-900 p-4 rounded-xl mb-6">
                                 <h4 class="font-heading font-bold text-lg text-brand-gold mb-1">Murottal Pilihan</h4>
                                 <p class="text-xs text-gray-400 mb-3">Surah Al-Kahfi</p>
                                 <div class="flex items-center justify-between">
                                     <button class="w-8 h-8 bg-brand-gold rounded-full flex items-center justify-center text-black">
                                         <i class="ph-fill ph-play text-sm"></i>
                                     </button>
                                     <div class="w-2/3 h-1 bg-gray-700 rounded-full overflow-hidden">
                                         <div class="w-1/2 h-full bg-brand-gold"></div>
                                     </div>
                                 </div>
                             </div>

                             <h4 class="font-semibold text-sm mb-3">Populer Hari Ini</h4>
                             <div class="grid grid-cols-2 gap-3 mb-6">
                                 <div class="bg-gray-900 rounded-lg aspect-square overflow-hidden relative">
                                     <img src="https://images.unsplash.com/photo-1519682577862-22b62b24e493?auto=format&fit=crop&w=200&q=80" alt="Cover" class="w-full h-full object-cover opacity-60">
                                     <div class="absolute inset-0 flex items-end p-2">
                                         <span class="text-[10px] font-bold">SNADA</span>
                                     </div>
                                 </div>
                                 <div class="bg-gray-900 rounded-lg aspect-square overflow-hidden relative">
                                     <img src="https://images.unsplash.com/photo-1511379938547-c1f69419868d?auto=format&fit=crop&w=200&q=80" alt="Cover" class="w-full h-full object-cover opacity-60">
                                     <div class="absolute inset-0 flex items-end p-2">
                                         <span class="text-[10px] font-bold">EDCOUSTIC</span>
                                     </div>
                                 </div>
                             </div>
                             
                             <div class="mt-auto bg-gray-900 mx--4 px-4 py-3 flex justify-around border-t border-gray-800">
                                  <i class="ph-fill ph-house text-brand-gold text-xl"></i>
                                  <i class="ph ph-compass text-gray-500 text-xl"></i>
                                  <i class="ph ph-user text-gray-500 text-xl"></i>
                             </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Categories / Quick Links -->
            <section id="kategori" class="py-12 border-t border-gray-200 dark:border-gray-800/50">
                <div class="text-center mb-10">
                    <h2 class="text-2xl md:text-3xl font-heading font-bold mb-3"><?= t('categories.title', 'Jelajahi Kategori') ?></h2>
                    <p class="text-gray-600 dark:text-gray-400 text-sm md:text-base"><?= t('categories.subtitle', 'Temukan konten audio yang sesuai dengan suasana hatimu.') ?></p>
                </div>
                <div class="flex flex-wrap justify-center gap-4 sm:gap-6 lg:gap-10">
                    <!-- Nasyid -->
                    <button class="flex flex-col items-center gap-3 w-[80px] sm:w-[100px] group">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 flex items-center justify-center text-brand-gold group-hover:bg-brand-gold group-hover:text-brand-dark transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:-translate-y-1">
                            <i class="ph ph-music-notes text-3xl sm:text-4xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-gold transition-colors"><?= t('categories.nasyid', 'Nasyid') ?></span>
                    </button>
                    <!-- Quran -->
                    <button class="flex flex-col items-center gap-3 w-[80px] sm:w-[100px] group">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 flex items-center justify-center text-brand-gold group-hover:bg-brand-gold group-hover:text-brand-dark transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:-translate-y-1">
                            <i class="ph ph-book-open text-3xl sm:text-4xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-gold transition-colors"><?= t('categories.quran', 'Quran') ?></span>
                    </button>
                    <!-- Kajian -->
                    <button class="flex flex-col items-center gap-3 w-[80px] sm:w-[100px] group">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 flex items-center justify-center text-brand-gold group-hover:bg-brand-gold group-hover:text-brand-dark transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:-translate-y-1">
                            <i class="ph ph-microphone-stage text-3xl sm:text-4xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-gold transition-colors"><?= t('categories.kajian', 'Kajian') ?></span>
                    </button>
                    <!-- Podcast -->
                    <button class="flex flex-col items-center gap-3 w-[80px] sm:w-[100px] group">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 flex items-center justify-center text-brand-gold group-hover:bg-brand-gold group-hover:text-brand-dark transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:-translate-y-1">
                            <i class="ph ph-headphones text-3xl sm:text-4xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-gold transition-colors"><?= t('categories.podcast', 'Podcast') ?></span>
                    </button>
                    <!-- Dzikir -->
                    <button class="flex flex-col items-center gap-3 w-[80px] sm:w-[100px] group">
                        <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-2xl bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 flex items-center justify-center text-brand-gold group-hover:bg-brand-gold group-hover:text-brand-dark transition-all duration-300 shadow-sm group-hover:shadow-lg group-hover:-translate-y-1">
                            <i class="ph ph-heart text-3xl sm:text-4xl"></i>
                        </div>
                        <span class="text-sm font-medium text-gray-700 dark:text-gray-300 group-hover:text-brand-gold transition-colors"><?= t('categories.dzikir', 'Dzikir') ?></span>
                    </button>
                </div>
            </section>

            <!-- Populer Saat Ini -->
            <section id="terbaru" class="py-12">
                <div class="flex justify-between items-end mb-8">
                    <div>
                        <h2 class="text-2xl font-heading font-bold mb-1"><?= t('popular.title', 'Paling Banyak Didengarkan') ?></h2>
                        <p class="text-sm text-gray-500"><?= t('popular.subtitle', 'Audio favorit pilihan pendengar ' . APP_DISPLAY_NAME) ?>.</p>
                    </div>
                    <a href="#" class="text-sm text-brand-gold hover:underline font-medium hidden sm:block"><?= t('common.see_all', 'Lihat semua') ?></a>
                </div>
                
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4 sm:gap-6">
                    <!-- Item 1 -->
                    <div class="group cursor-pointer">
                        <div class="relative rounded-2xl overflow-hidden aspect-square mb-4 shadow-sm group-hover:shadow-xl transition-all duration-300 bg-gray-200 dark:bg-gray-800">
                            <img src="https://images.unsplash.com/photo-1542816417-0983c9c9ad53?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Al-Kahfi" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="w-12 h-12 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                    <i class="ph-fill ph-play text-xl"></i>
                                </button>
                            </div>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base truncate group-hover:text-brand-gold transition-colors">Al-Kahfi (Ayat 1-110)</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Misyari Rasyid</p>
                    </div>

                    <!-- Item 3 -->
                    <div class="group cursor-pointer">
                        <div class="relative rounded-2xl overflow-hidden aspect-square mb-4 shadow-sm group-hover:shadow-xl transition-all duration-300 bg-gray-200 dark:bg-gray-800">
                            <img src="https://images.unsplash.com/photo-1555529771-835f59bfc50c?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Ust. Adnin Roslan" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="w-12 h-12 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                    <i class="ph-fill ph-play text-xl"></i>
                                </button>
                            </div>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base truncate group-hover:text-brand-gold transition-colors">Menjaga Hati</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Ust. Adnin Roslan</p>
                    </div>

                    <!-- Item 4 (Added for wider screens) -->
                     <div class="group cursor-pointer hidden sm:block">
                        <div class="relative rounded-2xl overflow-hidden aspect-square mb-4 shadow-sm group-hover:shadow-xl transition-all duration-300 bg-gray-200 dark:bg-gray-800">
                            <img src="https://images.unsplash.com/photo-1511379938547-c1f69419868d?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Edcoustic" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="w-12 h-12 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                    <i class="ph-fill ph-play text-xl"></i>
                                </button>
                            </div>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base truncate group-hover:text-brand-gold transition-colors">Muhasabah Cinta</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Edcoustic</p>
                    </div>

                     <!-- Item 5 (Added for wider screens) -->
                     <div class="group cursor-pointer hidden xl:block">
                        <div class="relative rounded-2xl overflow-hidden aspect-square mb-4 shadow-sm group-hover:shadow-xl transition-all duration-300 bg-gray-200 dark:bg-gray-800">
                            <img src="https://images.unsplash.com/photo-1493225457124-a1a2a5f52479?ixlib=rb-4.0.3&auto=format&fit=crop&w=400&q=80" alt="Gradasi" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                                <button class="w-12 h-12 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg transform translate-y-4 group-hover:translate-y-0 transition-all duration-300">
                                    <i class="ph-fill ph-play text-xl"></i>
                                </button>
                            </div>
                        </div>
                        <h3 class="font-bold text-sm sm:text-base truncate group-hover:text-brand-gold transition-colors">Anak Bertanya</h3>
                        <p class="text-xs sm:text-sm text-gray-500 dark:text-gray-400 mt-1">Gradasi</p>
                    </div>
                </div>
                 <div class="mt-6 text-center sm:hidden">
                    <button class="px-6 py-2 border border-brand-gold text-brand-gold rounded-full text-sm font-medium hover:bg-brand-gold hover:text-brand-dark transition-colors">Lihat Semua</button>
                </div>
            </section>

            <!-- Premium Banner -->
            <section id="premium" class="py-12">
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 dark:from-brand-card dark:to-[#151518] rounded-[2rem] p-8 md:p-12 flex flex-col md:flex-row items-center justify-between border border-gray-800 relative overflow-hidden shadow-2xl">
                    <!-- Background waves -->
                    <div class="absolute right-0 top-0 h-full w-1/2 opacity-20 pointer-events-none">
                        <svg viewBox="0 0 100 100" preserveAspectRatio="none" class="w-full h-full text-brand-gold fill-current">
                            <path d="M0,50 C20,20 40,80 60,50 C80,20 100,50 100,50 L100,100 L0,100 Z" />
                        </svg>
                    </div>
                    <div class="flex items-center gap-6 mb-8 md:mb-0 relative z-10 flex-col md:flex-row text-center md:text-left">
                        <div>
                            <i class="ph-fill ph-crown text-4xl text-brand-gold mb-2 mx-auto md:mx-0 block"></i>
                            <h3 class="text-white font-sans font-light tracking-[0.25em] text-lg">MYAPP<br><span class="text-brand-gold font-medium tracking-normal text-sm">PREMIUM</span></h3>
                        </div>
                        <div class="w-px h-16 bg-gray-700 hidden md:block"></div>
                        <div class="text-white max-w-md">
                            <h4 class="font-bold text-2xl mb-2"><?= t('premium.banner.title', 'Tanpa Batas, Tanpa Iklan') ?></h4>
                            <p class="font-normal text-gray-400 text-sm md:text-base"><?= t('premium.banner.subtitle', 'Dengarkan ribuan konten Islami dengan kualitas tinggi, unduh untuk didengarkan offline, di mana saja dan kapan saja.') ?></p>
                        </div>
                    </div>

                    <a href="/register/" class="relative z-10 w-full md:w-auto bg-brand-gold hover:bg-yellow-500 text-brand-dark font-bold text-lg py-4 px-10 rounded-full transition-all shadow-lg hover:shadow-brand-gold/30 flex items-center justify-center gap-2 transform hover:scale-105">
                        <?= t('premium.banner.cta', 'Coba Gratis 30 Hari') ?> <i class="ph-bold ph-arrow-right"></i>
                    </a>
                </div>
            </section>

             <!-- Kajian Populer -->
             <section class="py-8">
                <div class="flex justify-between items-end mb-6">
                    <h2 class="text-xl font-heading font-semibold"><?= t('kajian.title', 'Kajian Populer') ?></h2>
                    <a href="#" class="text-sm text-brand-gold hover:underline"><?= t('common.see_all', 'Lihat semua') ?></a>
                </div>
                
                <div class="flex flex-col gap-4">
                    <!-- List Item 1 -->
                    <div class="flex items-center gap-4 bg-white dark:bg-brand-card p-3 rounded-xl border border-gray-100 dark:border-gray-800 hover:border-brand-gold/50 transition-colors cursor-pointer group">
                        <img src="https://images.unsplash.com/photo-1506794778202-cad84cf45f1d?ixlib=rb-4.0.3&auto=format&fit=crop&w=150&q=80" alt="Hanan Attaki" class="w-16 h-16 rounded-lg object-cover">
                        <div class="flex-grow">
                            <h3 class="font-semibold text-sm group-hover:text-brand-gold transition-colors">Kiat Menjadi Hamba yang Bersyukur</h3>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Ustadz Hanan Attaki, Lc.</p>
                        </div>
                        <div class="flex items-center gap-3 text-gray-400">
                            <button class="hover:text-brand-gold"><i class="ph ph-bookmark text-xl"></i></button>
                            <button class="w-10 h-10 rounded-full border border-gray-300 dark:border-gray-600 flex items-center justify-center hover:text-brand-gold hover:border-brand-gold transition-colors">
                                <i class="ph-fill ph-play"></i>
                            </button>
                            <button class="hover:text-gray-600 dark:hover:text-gray-200"><i class="ph ph-dots-three-vertical text-xl"></i></button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Fitur Unggulan / Kenapa <?= APP_DISPLAY_NAME ?> -->
            <section id="fitur" class="py-16 mt-8 border-t border-gray-200 dark:border-gray-800/50">
                <div class="text-center mb-12">
                    <h2 class="text-2xl md:text-3xl font-heading font-bold mb-4"><?= t('features.title', 'Mengapa Mendengarkan di ' . APP_DISPLAY_NAME . '?') ?></h2>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto"><?= t('features.subtitle', 'Kami merancang pengalaman mendengarkan audio Islami yang tidak hanya lengkap, tapi juga nyaman dan menenangkan hati.') ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                    <!-- Feature 1 -->
                    <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-shadow">
                        <div class="w-14 h-14 bg-brand-gold/10 text-brand-gold rounded-xl flex items-center justify-center text-3xl mb-6">
                            <i class="ph ph-waveform"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white"><?= t('features.hd_quality', 'Kualitas Audio HD') ?></h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed"><?= t('features.hd_quality.desc', 'Nikmati lantunan ayat suci dan nasyid dengan kejernihan maksimal seolah mendengarkan langsung.') ?></p>
                    </div>
                    <!-- Feature 2 -->
                    <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-shadow">
                        <div class="w-14 h-14 bg-brand-gold/10 text-brand-gold rounded-xl flex items-center justify-center text-3xl mb-6">
                            <i class="ph ph-download-simple"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white"><?= t('features.offline', 'Dengarkan Offline') ?></h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed"><?= t('features.offline.desc', 'Unduh audio favoritmu dan dengarkan kapan saja di mana saja, tanpa khawatir kehabisan kuota internet.') ?></p>
                    </div>
                    <!-- Feature 3 -->
                    <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-shadow">
                        <div class="w-14 h-14 bg-brand-gold/10 text-brand-gold rounded-xl flex items-center justify-center text-3xl mb-6">
                            <i class="ph ph-playlist"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white"><?= t('features.personal', 'Kurasi Personal') ?></h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed"><?= t('features.personal.desc', 'Dapatkan rekomendasi playlist harian yang disesuaikan dengan suasana hati dan kebutuhan spiritualmu.') ?></p>
                    </div>
                    <!-- Feature 4 -->
                    <div class="bg-white dark:bg-brand-card p-6 rounded-2xl border border-gray-100 dark:border-gray-800 hover:shadow-lg transition-shadow">
                        <div class="w-14 h-14 bg-brand-gold/10 text-brand-gold rounded-xl flex items-center justify-center text-3xl mb-6">
                            <i class="ph ph-shield-check"></i>
                        </div>
                        <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white"><?= t('features.no_ads', '100% Bebas Iklan') ?></h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 leading-relaxed"><?= t('features.no_ads.desc', 'Fokus beribadah, merenung, dan mencari inspirasi tanpa gangguan iklan audio yang tiba-tiba muncul.') ?></p>
                    </div>
                </div>
            </section>

            <!-- Testimonial Section -->
            <section id="testimoni" class="py-16 border-t border-gray-200 dark:border-gray-800/50">
                <div class="text-center mb-10">
                    <h2 class="text-2xl md:text-3xl font-heading font-bold mb-3"><?= t('testimonials.title', 'Apa Kata Sahabat ' . APP_DISPLAY_NAME . '?') ?></h2>
                    <p class="text-gray-600 dark:text-gray-400 max-w-2xl mx-auto"><?= t('testimonials.subtitle', 'Ribuan orang telah menjadikan ' . APP_DISPLAY_NAME . ' sebagai teman setia di setiap momen keseharian mereka.') ?></p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Review 1 -->
                    <div class="bg-gray-100 dark:bg-brand-card/50 p-8 rounded-2xl">
                        <div class="flex text-brand-gold mb-4 text-sm">
                            <i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mb-6 italic leading-relaxed">"Alhamdulillah, aplikasi ini menemani perjalanan harian saya ke kantor. Pilihan murottalnya sangat lengkap dan antarmukanya bersih, sangat elegan."</p>
                        <div class="flex items-center gap-3">
                            <img src="https://i.pravatar.cc/150?img=11" alt="Budi" class="w-10 h-10 rounded-full">
                            <div>
                                <h4 class="font-semibold text-sm">Budi Santoso</h4>
                                <p class="text-xs text-gray-500">Karyawan Swasta</p>
                            </div>
                        </div>
                    </div>
                    <!-- Review 2 -->
                    <div class="bg-gray-100 dark:bg-brand-card/50 p-8 rounded-2xl">
                        <div class="flex text-brand-gold mb-4 text-sm">
                            <i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mb-6 italic leading-relaxed">"Aplikasi audio Islami terbaik yang pernah saya coba. Nasyid kenangan era 2000-an semuanya ada di sini. Sangat mengobati rindu."</p>
                        <div class="flex items-center gap-3">
                            <img src="https://i.pravatar.cc/150?img=5" alt="Aisyah" class="w-10 h-10 rounded-full">
                            <div>
                                <h4 class="font-semibold text-sm">Aisyah Fitriani</h4>
                                <p class="text-xs text-gray-500">Ibu Rumah Tangga</p>
                            </div>
                        </div>
                    </div>
                    <!-- Review 3 -->
                    <div class="bg-gray-100 dark:bg-brand-card/50 p-8 rounded-2xl">
                        <div class="flex text-brand-gold mb-4 text-sm">
                            <i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i><i class="ph-fill ph-star"></i>
                        </div>
                        <p class="text-gray-700 dark:text-gray-300 mb-6 italic leading-relaxed">"Kajiannya sangat terstruktur. Saya suka fitur kurasi playlist yang bikin hati tenang saat sedang banyak pikiran. Sangat direkomendasikan!"</p>
                        <div class="flex items-center gap-3">
                            <img src="https://i.pravatar.cc/150?img=33" alt="Hendi" class="w-10 h-10 rounded-full">
                            <div>
                                <h4 class="font-semibold text-sm">dr. Hendi</h4>
                                <p class="text-xs text-gray-500">Tenaga Medis</p>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

            <!-- FAQ Section -->
            <section id="faq" class="py-16 border-t border-gray-200 dark:border-gray-800/50">
                <div class="max-w-3xl mx-auto">
                    <div class="text-center mb-12">
                        <h2 class="text-2xl md:text-3xl font-heading font-bold mb-4"><?= t('faq.title', 'Pertanyaan Seputar ' . APP_DISPLAY_NAME) ?></h2>
                        <p class="text-gray-600 dark:text-gray-400"><?= t('faq.subtitle', 'Temukan jawaban yang sering ditanyakan terkait layanan kami.') ?></p>
                    </div>

                    <div class="space-y-4">
                        <!-- FAQ Item 1 -->
                        <div class="bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                            <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white"><?= t('faq.paid.title', 'Apakah aplikasi ' . APP_DISPLAY_NAME . ' berbayar?') ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= t('faq.paid.content', 'Anda dapat menikmati sebagian besar konten ' . APP_DISPLAY_NAME . ' secara gratis dengan dukungan iklan. Namun, untuk pengalaman tanpa batas, bebas iklan, dan fitur offline, Anda dapat berlangganan ' . APP_DISPLAY_NAME . ' Premium.') ?></p>
                        </div>
                        <!-- FAQ Item 2 -->
                        <div class="bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                            <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white"><?= t('faq.offline.title', 'Bagaimana cara mengaktifkan Mode Offline?') ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= t('faq.offline.content', 'Mode offline eksklusif untuk pengguna Premium. Cukup klik ikon unduh (download) pada album, playlist, atau episode podcast yang Anda inginkan, dan dengarkan tanpa koneksi internet di menu Koleksi.') ?></p>
                        </div>
                        <!-- FAQ Item 3 -->
                        <div class="bg-white dark:bg-brand-card border border-gray-200 dark:border-gray-800 rounded-xl p-6">
                            <h3 class="font-bold text-lg mb-2 text-gray-900 dark:text-white"><?= t('faq.creator.title', 'Apakah saya bisa menjadi podcaster/kreator di ' . APP_DISPLAY_NAME . '?') ?></h3>
                            <p class="text-sm text-gray-600 dark:text-gray-400"><?= t('faq.creator.content', 'Tentu! Kami selalu mencari suara-suara inspiratif baru. Anda dapat mengajukan podcast Islami atau konten kajian Anda melalui menu "Mitra Kreator" di pengaturan akun Anda.') ?></p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Final Call to Action -->
            <section class="py-16">
                <div class="bg-brand-gold rounded-[2rem] p-10 md:p-16 text-center relative overflow-hidden shadow-2xl shadow-brand-gold/20">
                    <!-- Decor -->
                    <div class="absolute inset-0 opacity-10 pointer-events-none flex justify-center items-center">
                        <i class="ph-fill ph-headphones text-[20rem] text-brand-dark transform rotate-12 translate-x-32"></i>
                    </div>

                    <div class="relative z-10 max-w-2xl mx-auto">
                        <h2 class="text-3xl md:text-5xl font-heading font-bold text-brand-dark mb-6"><?= t('cta.title', 'Siap Menemukan Ketenangan Hati?') ?></h2>
                        <p class="text-brand-dark/80 font-medium text-lg mb-10"><?= t('cta.subtitle', 'Bergabunglah dengan ribuan pendengar lainnya. Buat akun gratis sekarang dan mulai dengarkan nasyid, murottal, serta kajian inspiratif.') ?></p>
                        <a href="/register/" class="px-10 py-5 bg-brand-dark text-brand-gold font-bold text-lg rounded-full hover:bg-gray-900 transition-transform transform hover:scale-105 shadow-xl flex items-center justify-center gap-2 mx-auto w-full sm:w-auto">
                            <?= t('cta.button', 'Daftar Sekarang Secara Gratis') ?>
                        </a>
                    </div>
                </div>
            </section>

        </div>
    </main>

    <!-- Mobile Bottom Navigation (Visible on small screens) -->
    <div class="md:hidden fixed bottom-0 w-full bg-white dark:bg-[#1A1A1D] border-t border-gray-200 dark:border-gray-800 pb-safe z-40">
        <div class="flex justify-around items-center h-16 px-2">

            <a href="#kategori" onclick="document.getElementById('kategori').scrollIntoView({behavior: 'smooth'})" class="mobile-nav-item flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-music-notes text-2xl"></i>
                <span class="text-[10px] font-medium"><?= t('nav.categories', 'Kategori') ?></span>
            </a>

            <a href="#terbaru" onclick="document.getElementById('terbaru').scrollIntoView({behavior: 'smooth'})" class="mobile-nav-item flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-fire text-2xl"></i>
                <span class="text-[10px] font-medium"><?= t('nav.popular', 'Populer') ?></span>
            </a>

            <!-- Auth buttons - CENTER position -->
            <?php if ($isLoggedIn): ?>
            <a href="<?= $dashboardUrl ?>" class="flex flex-col items-center gap-1 text-brand-gold w-1/5 -mt-4">
                <div class="w-12 h-12 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg border-4 border-white dark:border-[#1A1A1D]">
                    <i class="ph-fill ph-squares-four text-lg"></i>
                </div>
                <span class="text-[10px] font-medium"><?= t('auth.dashboard', 'Dashboard') ?></span>
            </a>
            <?php else: ?>
            <a href="/login/" class="flex flex-col items-center gap-1 text-brand-gold w-1/5 -mt-4">
                <div class="w-12 h-12 bg-brand-gold rounded-full flex items-center justify-center text-brand-dark shadow-lg border-4 border-white dark:border-[#1A1A1D]">
                    <i class="ph ph-user text-lg"></i>
                </div>
                <span class="text-[10px] font-medium"><?= t('auth.login', 'Masuk') ?></span>
            </a>
            <?php endif; ?>

            <a href="#faq" onclick="document.getElementById('faq').scrollIntoView({behavior: 'smooth'})" class="mobile-nav-item flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-question text-2xl"></i>
                <span class="text-[10px] font-medium">FAQ</span>
            </a>

            <a href="#testimoni" onclick="document.getElementById('testimoni').scrollIntoView({behavior: 'smooth'})" class="mobile-nav-item flex flex-col items-center gap-1 text-gray-400 w-1/5 transition-colors">
                <i class="ph ph-chat-circle-text text-2xl"></i>
                <span class="text-[10px] font-medium">Testimoni</span>
            </a>
        </div>
    </div>

    <!-- Desktop Footer -->
    <footer class="hidden md:block bg-white dark:bg-brand-card border-t border-gray-200 dark:border-gray-800 mt-12 pt-16 pb-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Brand Info -->
                <div class="col-span-1 md:col-span-1">
                    <div class="flex items-center gap-4 mb-6">
                        <svg width="28" height="24" viewBox="0 0 28 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <rect x="0" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                            <rect x="6" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                            <rect x="12" y="0" width="3" height="24" rx="1.5" fill="#FFC107" />
                            <rect x="18" y="4" width="3" height="16" rx="1.5" fill="#FFC107" />
                            <rect x="24" y="7" width="3" height="10" rx="1.5" fill="#FFC107" />
                        </svg>
                        <span class="font-sans font-light text-xl tracking-[0.25em] text-gray-900 dark:text-white mt-1">MYAPP</span>
                    </div>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6 leading-relaxed">
                        <?= t('footer.about_text', 'Platform streaming audio Islami terdepan. Misi kami adalah menghadirkan ketenangan hati melalui lantunan ayat suci, nasyid, dan kajian inspiratif ke dalam genggaman Anda.') ?>
                    </p>
                    <!-- Social Links -->
                    <div class="flex gap-4">
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-brand-gold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <i class="ph-fill ph-instagram-logo text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-brand-gold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <i class="ph-fill ph-twitter-logo text-xl"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-full bg-gray-100 dark:bg-gray-800 flex items-center justify-center text-gray-600 dark:text-gray-400 hover:text-brand-gold hover:bg-gray-200 dark:hover:bg-gray-700 transition-colors">
                            <i class="ph-fill ph-youtube-logo text-xl"></i>
                        </a>
                    </div>
                </div>

                <!-- Links Group 1 -->
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-6"><?= t('footer.company', 'Perusahaan') ?></h4>
                    <ul class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.about', 'Tentang Kami') ?></a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.careers', 'Karir') ?></a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.press', 'Berita & Pers') ?></a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.creators', 'Mitra Kreator') ?></a></li>
                    </ul>
                </div>

                <!-- Links Group 2 -->
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-6"><?= t('footer.community', 'Komunitas') ?></h4>
                    <ul class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= APP_DISPLAY_NAME ?> for Artists</a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.developers', 'Pengembang (API)') ?></a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.ads', 'Iklan') ?></a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.investors', 'Investor') ?></a></li>
                    </ul>
                </div>

                <!-- Links Group 3 -->
                <div>
                    <h4 class="font-bold text-gray-900 dark:text-white mb-6"><?= t('footer.useful_links', 'Tautan Berguna') ?></h4>
                    <ul class="space-y-4 text-sm text-gray-600 dark:text-gray-400">
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.help', 'Pusat Bantuan') ?></a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.android_app', 'Aplikasi Mobile Android') ?></a></li>
                        <li><a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.ios_app', 'Aplikasi Mobile iOS') ?></a></li>
                    </ul>
                </div>
            </div>

            <!-- Copyright -->
            <div class="border-t border-gray-200 dark:border-gray-800 pt-8 flex flex-col md:flex-row justify-between items-center gap-4 text-xs text-gray-500">
                <div class="flex gap-6">
                    <a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.legal', 'Legal') ?></a>
                    <a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.privacy', 'Pusat Privasi') ?></a>
                    <a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.privacy_policy', 'Kebijakan Privasi') ?></a>
                    <a href="#" class="hover:text-brand-gold transition-colors"><?= t('footer.cookies', 'Cookie') ?></a>
                </div>
                <div>
                    <?= t('footer.copyright', '&copy; 2026 ' . APP_DISPLAY_NAME . '. All rights reserved.') ?>
                </div>
            </div>
        </div>
    </footer>

    <script>
        // DOM Elements
        const htmlElement = document.documentElement;
        const themeToggleBtns = [document.getElementById('themeToggleBtn'), document.getElementById('mobileThemeToggle')];

        // Theme Handling
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
        }

        themeToggleBtns.forEach(btn => {
            if(btn) btn.addEventListener('click', toggleTheme);
        });

        // Mobile Nav Scroll Spy
        const sectionIds = ['kategori', 'terbaru', 'premium', 'faq', 'testimoni'];

        function updateMobileNavHighlight(activeId) {
            const navItems = document.querySelectorAll('.mobile-nav-item');
            navItems.forEach(item => {
                item.classList.remove('text-brand-gold');
                item.classList.add('text-gray-400');
            });
            const activeNav = document.querySelector(`.mobile-nav-item[href="#${activeId}"]`);
            if (activeNav) {
                activeNav.classList.add('text-brand-gold');
                activeNav.classList.remove('text-gray-400');
            }
        }

        const observerOptions = {
            rootMargin: '-40% 0px -40% 0px',
            threshold: 0
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    updateMobileNavHighlight(entry.target.id);
                }
            });
        }, observerOptions);

        sectionIds.forEach(id => {
            const section = document.getElementById(id);
            if (section) observer.observe(section);
        });

        initTheme();
    </script>
</body>
</html>
