<?php
/**
 * Vibeforge - Installation Wizard Shell
 * IT Professional Edition
 *
 * Flow: Welcome Overview (1) -> PRD (2) -> Branding (3) -> Logo (4) ->
 *        HTML Templates (5-10) -> Server Config (11) -> Install Path (12)
 */
defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/header.php';

initSession();
if (!empty($_GET['lang']) && in_array($_GET['lang'], getAvailableLocaleCodes(), true)) {
    $_SESSION['language'] = $_GET['lang'];
}
$currentLang = $_SESSION['language'] ?? detectLanguage();
$_SESSION['language'] = $currentLang;
$csrfToken = generateCsrfToken();
$isLoggedIn = isLoggedIn();
$dashboardUrl = getDashboardUrl();

$projectRoot = dirname(__DIR__, 2);

// Pre-load existing file contents for pre-population
$filesData = [
    'prd'            => file_exists($projectRoot . '/docs/prd.md')                   ? file_get_contents($projectRoot . '/docs/prd.md')                   : '',
    'branding'       => file_exists($projectRoot . '/docs/branding.md')              ? file_get_contents($projectRoot . '/docs/branding.md')              : '',
    'landingPage'    => file_exists($projectRoot . '/references/landingpage.html')   ? file_get_contents($projectRoot . '/references/landingpage.html')   : '',
    'loginPage'      => file_exists($projectRoot . '/references/login.html')         ? file_get_contents($projectRoot . '/references/login.html')         : '',
    'registerPage'   => file_exists($projectRoot . '/references/register.html')       ? file_get_contents($projectRoot . '/references/register.html')       : '',
    'manajemenPage'  => file_exists($projectRoot . '/references/modul_manajemen.html') ? file_get_contents($projectRoot . '/references/modul_manajemen.html') : '',
    'adminPage'      => file_exists($projectRoot . '/references/modul_admin.html')    ? file_get_contents($projectRoot . '/references/modul_admin.html')    : '',
    'clientPage'     => file_exists($projectRoot . '/references/modul_client.html')   ? file_get_contents($projectRoot . '/references/modul_client.html')   : '',
    'logoBase64'     => file_exists($projectRoot . '/docs/logo.png')                 ? base64_encode(file_get_contents($projectRoot . '/docs/logo.png'))   : '',
];

$installConfig = [];
$configPath = $projectRoot . '/data/install_config.json';
if (file_exists($configPath)) {
    $installConfig = json_decode(file_get_contents($configPath), true) ?? [];
}
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard - <?= APP_DISPLAY_NAME ?> Engine</title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F97316'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="../assets/css/branding.css">

    <!-- Monaco Editor Loader -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.39.0/min/vs/loader.min.js"></script>
    <script>
        require.config({ paths: { vs: 'https://cdnjs.cloudflare.com/ajax/libs/monaco-editor/0.39.0/min/vs' } });
        window.monacoLoaded = false;
        window.monacoLoadFailed = false;

        var monacoTimeout = setTimeout(function() {
            if (!window.monacoLoaded) {
                window.monacoLoadFailed = true;
                window.monacoLoaded = true;
                if (typeof renderStep === 'function') renderStep();
            }
        }, 10000);

        require(['vs/editor/editor.main'], function() {
            clearTimeout(monacoTimeout);
            window.monacoLoaded = true;
            if (typeof window.onMonacoReady === 'function') window.onMonacoReady();
        }, function(err) {
            clearTimeout(monacoTimeout);
            window.monacoLoadFailed = true;
            window.monacoLoaded = true;
        });
    </script>

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
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg-primary); color: var(--text-primary); }
        .font-mono { font-family: 'JetBrains Mono', monospace; }
        .tech-grid {
            background-image: linear-gradient(to right, rgba(255, 255, 255, 0.03) 1px, transparent 1px),
                              linear-gradient(to bottom, rgba(255, 255, 255, 0.03) 1px, transparent 1px);
            background-size: 32px 32px;
        }
        .glow-mesh {
            background: radial-gradient(circle at 50% 10%, rgba(249, 115, 22, 0.12) 0%, rgba(11, 15, 23, 0) 70%);
        }
        .text-gradient { background: linear-gradient(135deg, #F97316 0%, #FBBF24 50%, #F59E0B 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .bg-gradient-brand { background: linear-gradient(135deg, #F97316 0%, #EA580C 100%); }
        .glow-orange { box-shadow: 0 0 35px rgba(249, 115, 22, 0.25); }
        .glow-orange-sm { box-shadow: 0 0 20px rgba(249, 115, 22, 0.15); }
        .glow-box-cyber { box-shadow: 0 0 0 1px rgba(249, 115, 22, 0.2), 0 10px 30px -10px rgba(0, 0, 0, 0.8); }
        .step-dot { transition: all 0.3s ease; }
        .step-dot.active { background: var(--brand-primary); border-color: var(--brand-primary); transform: scale(1.15); color: #fff; box-shadow: 0 0 15px rgba(249, 115, 22, 0.4); }
        .step-dot.completed { background: #10B981; border-color: #10B981; color: #fff; }
        .step-dot.inactive { background: var(--bg-card); border-color: var(--border-default); color: var(--text-muted); }
        .step-connector { width: 14px; height: 2px; background: var(--border-default); transition: background 0.3s ease; }
        .step-connector.completed { background: #10B981; }
        .form-input { width: 100%; padding: 0.75rem 1rem; background: var(--bg-card); border: 1px solid var(--border-default); border-radius: 0.75rem; color: var(--text-primary); font-size: 0.875rem; transition: all 0.2s; }
        .form-input:focus { outline: none; border-color: var(--brand-primary); box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.15); }
        .form-label { display: block; font-size: 0.875rem; font-weight: 600; margin-bottom: 0.5rem; color: var(--text-secondary); }
        .upload-zone { border: 2px dashed var(--border-default); border-radius: 1rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s; }
        .upload-zone:hover, .upload-zone.dragover { border-color: var(--brand-primary); background: rgba(249, 115, 22, 0.05); }
        .editor-container { height: 420px; border: 1px solid var(--border-default); border-radius: 0.75rem; overflow: hidden; background: #0B0F17; }
        .step-content { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
        @keyframes checkmark { 0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .success-check { animation: checkmark 0.4s ease-out; }
        button:disabled { opacity: 0.5; cursor: not-allowed; }
        .fallback-notice { background: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.3); color: #FBBF24; padding: 0.6rem 1rem; border-radius: 0.75rem; font-size: 0.75rem; font-family: 'JetBrains Mono', monospace; display: flex; align-items: center; gap: 0.5rem; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>
</head>
<body class="antialiased min-h-screen pt-24 tech-grid bg-[var(--bg-primary)] text-[var(--text-primary)]">
    <div class="min-h-screen flex flex-col glow-mesh">

        <!-- Top Status Bar -->
        <div class="fixed top-0 w-full bg-[var(--bg-secondary)] border-b border-[var(--border-default)] py-1 px-4 text-[11px] font-mono text-[var(--text-secondary)] hidden sm:flex items-center justify-between z-50">
            <div class="flex items-center gap-4">
                <span class="flex items-center gap-1.5 text-emerald-400 font-bold">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    INSTALLER: ACTIVE
                </span>
                <span class="text-[var(--border-default)]">|</span>
                <span>PHP 8.3+ Runtime</span>
                <span class="text-[var(--border-default)]">|</span>
                <span>Config Engine: Dual-Mode</span>
            </div>
            <div class="flex items-center gap-4">
                <span>Wizard Version: 3.2.0</span>
                <span class="text-[var(--border-default)]">|</span>
                <span class="text-[var(--brand-primary)]">VIBEFORGE_ENGINE</span>
            </div>
        </div>

        <!-- Navbar -->
        <nav class="fixed top-7 w-full z-40 bg-[var(--bg-primary)]/85 backdrop-blur-xl border-b border-[var(--border-default)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="/" class="flex items-center gap-3 group">
                        <div class="w-9 h-9 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center group-hover:border-orange-500 transition-colors shadow-[0_0_15px_rgba(249,115,22,0.2)]">
                            <svg width="22" height="22" viewBox="0 0 24 24" fill="#F97316"><path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/></svg>
                        </div>
                        <div class="flex flex-col">
                            <span class="font-heading font-extrabold text-lg tracking-tight leading-none"><span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span></span>
                            <span class="font-mono text-[9px] text-[var(--text-muted)] tracking-wider">SETUP_WIZARD</span>
                        </div>
                    </a>

                    <div class="hidden md:flex items-center gap-8 font-medium text-sm">
                        <a href="/#fitur" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5"><i class="ph ph-cpu text-base text-[var(--brand-primary)]"></i> Arsitektur</a>
                        <a href="/#cara-pasang" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5"><i class="ph ph-terminal-window text-base text-[var(--brand-primary)]"></i> Installer</a>
                        <a href="/#demo" class="text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1.5"><i class="ph ph-shield-check text-base text-[var(--brand-primary)]"></i> Demo Roles</a>
                    </div>

                    <div class="flex items-center gap-3">
                        <a href="/" class="px-3 py-1.5 text-[var(--text-secondary)] hover:text-[var(--text-primary)] text-xs font-mono font-semibold rounded-lg hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5"><i class="ph ph-arrow-left"></i> BERANDA</a>
                        <button id="themeToggle" class="w-9 h-9 rounded-lg bg-[var(--bg-card)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] flex items-center justify-center transition-colors text-[var(--text-muted)] hover:text-amber-400" aria-label="Toggle theme"><i class="ph ph-moon text-lg dark:text-amber-400"></i></button>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Progress Stepper Header -->
        <div class="bg-[var(--bg-card)]/90 backdrop-blur-md border-b border-[var(--border-default)] px-4 py-4 mt-2">
            <div class="max-w-7xl mx-auto space-y-3">
                <div class="flex items-center justify-between text-[10px] font-mono text-[var(--text-muted)] uppercase tracking-wider" id="phaseLabels"></div>

                <div class="flex flex-col sm:flex-row items-center justify-between gap-2 bg-[var(--bg-primary)] px-4 py-2.5 rounded-xl border border-[var(--border-default)]">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-0.5 rounded bg-orange-500/10 border border-orange-500/30 text-[var(--brand-primary)] font-mono text-xs font-bold" id="stepLabel">STEP 01/12</span>
                        <span class="text-xs font-heading font-bold text-[var(--text-primary)] flex items-center gap-2" id="stepName"><i class="ph ph-map-trifold text-[var(--brand-primary)]"></i> Overview</span>
                    </div>
                    <div class="flex items-center gap-2 font-mono text-xs">
                        <span id="saveStatus" class="text-emerald-400 hidden flex items-center gap-1.5 font-bold">
                            <i class="ph-bold ph-check-circle text-sm"></i> <span id="saveStatusText">Auto-Saved</span>
                        </span>
                    </div>
                </div>

                <div class="flex items-center justify-center pt-1 overflow-x-auto hide-scrollbar pb-1" id="stepsDots"></div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div id="eduBanner" class="mb-6 p-4 bg-gray-950 border border-[var(--border-default)] rounded-2xl flex items-start gap-4 shadow-xl glow-box-cyber">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)] shrink-0"><i class="ph ph-cpu text-2xl"></i></div>
                    <div>
                        <h4 class="font-heading font-bold text-sm text-[var(--text-primary)] mb-1" id="eduTitle">Developer Reference Context</h4>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed" id="eduDesc">File yang Anda edit di sini akan langsung disimpan ke project lokal.</p>
                    </div>
                </div>

                <div id="wizardContent" class="step-content"></div>

                <div class="mt-8 flex items-center justify-between gap-3 pt-4 border-t border-[var(--border-default)] font-mono">
                    <button id="prevBtn" onclick="prevStep()" class="hidden px-6 py-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl text-xs font-bold hover:border-[var(--brand-primary)] transition-all flex items-center gap-2">
                        <i class="ph ph-arrow-left text-sm"></i> PREV STEP
                    </button>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-3">
                        <button id="nextBtn" onclick="nextStep()" class="hidden px-6 py-3 bg-gradient-brand text-white text-xs font-bold rounded-xl hover:opacity-90 transition-all shadow-md glow-orange-sm flex items-center gap-2">
                            NEXT STEP <i class="ph ph-arrow-right text-sm"></i>
                        </button>
                        <button id="finishBtn" onclick="finishWizard()" class="hidden px-6 py-3 bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-bold rounded-xl transition-all shadow-md flex items-center gap-2">
                            <i class="ph ph-check text-base"></i> CONFIRM & GENERATE
                        </button>
                        <button id="executeBtn" onclick="executeTerminal()" class="hidden px-6 py-3 bg-gradient-brand text-white text-xs font-bold rounded-xl hover:opacity-90 transition-all shadow-xl glow-orange flex items-center gap-2">
                            <i class="ph ph-terminal-window text-base"></i> LAUNCH TERMINAL
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-50 p-4" onclick="closeSuccessModal()" role="dialog" aria-modal="true">
        <div class="bg-gray-950 rounded-2xl p-8 max-w-md w-full border border-gray-800 shadow-2xl relative glow-box-cyber text-center" onclick="event.stopPropagation()">
            <button onclick="closeSuccessModal()" class="absolute top-4 right-4 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-white transition-colors" aria-label="Close"><i class="ph ph-x text-lg"></i></button>
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center mx-auto mb-5 success-check"><i class="ph ph-check-circle text-4xl text-emerald-400"></i></div>
            <h2 class="text-2xl font-heading font-extrabold mb-2">Konfigurasi Disimpan!</h2>
            <p class="text-xs text-[var(--text-secondary)] mb-6">Seluruh file spesifikasi aplikasi Anda telah diperbarui.</p>

            <div class="bg-black/60 rounded-xl p-4 text-left mb-6 border border-gray-800 font-mono text-xs">
                <p class="text-[11px] text-gray-400 mb-2 font-bold uppercase tracking-wider">// Saved Specifications:</p>
                <ul id="savedFilesList" class="space-y-1.5 mb-4 max-h-32 overflow-y-auto hide-scrollbar"></ul>
                <p class="text-[11px] text-gray-400 mb-2 font-bold uppercase tracking-wider">// AI Prompt Command:</p>
                <div class="flex items-center gap-2 bg-gray-900 p-2.5 rounded-lg border border-gray-800">
                    <code class="text-orange-400 font-mono text-xs flex-1 truncate">baca dan jalankan @docs/install.md</code>
                    <button onclick="copyModalInstallCommand()" class="px-2.5 py-1 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded text-[11px] transition-colors shrink-0 flex items-center gap-1"><i class="ph ph-copy"></i> Copy</button>
                </div>
            </div>
            <a href="/" class="w-full py-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl font-mono text-xs font-bold text-[var(--text-primary)] hover:border-[var(--brand-primary)] transition-all inline-flex items-center justify-center gap-2">
                <i class="ph ph-house text-base"></i> KEMBALI KE BERANDA
            </a>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-950 border border-gray-800 rounded-xl px-5 py-3.5 shadow-2xl flex items-center gap-3 z-50 transition-all duration-300 opacity-0 translate-y-4 pointer-events-none font-mono">
        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center shrink-0"><i class="ph ph-check-circle text-lg text-emerald-400" id="toastIcon"></i></div>
        <div><p class="font-bold text-xs text-white" id="toastTitle">Success!</p><p class="text-[11px] text-gray-400" id="toastMessage"></p></div>
    </div>

    <!-- Saving Overlay -->
    <div id="savingOverlay" class="hidden fixed inset-0 bg-black/65 backdrop-blur-xs flex items-center justify-center z-50 font-mono">
        <div class="bg-gray-950 border border-gray-800 rounded-2xl px-6 py-4 flex items-center gap-3 shadow-2xl glow-box-cyber">
            <i class="ph ph-circle-notch text-2xl text-[var(--brand-primary)] animate-spin"></i>
            <span class="text-xs font-bold text-gray-200">Writing file specifications...</span>
        </div>
    </div>

    <script>
    var csrfToken = '<?= $csrfToken ?>';
    var currentStep = 1;
    var totalSteps = 12;
    var editor = null;
    var autoSaveTimeout = null;
    var isNavigating = false;
    var savedFiles = new Set();

    var urlParams = new URLSearchParams(window.location.search);
    var paramMode = urlParams.get('mode');
    var paramDrive = urlParams.get('drive');
    var paramServer = urlParams.get('serverType');

    var appMode = paramMode === 'redesign' ? 'redesign' : 'new';
    var refFiles = [];

    var formData = <?= json_encode($filesData) ?>;
    formData.logo = null;
    formData.serverType = paramServer || <?= $jsServer ?>;
    formData.drive = paramDrive || <?= $jsDrive ?>;
    formData.installPath = <?= $jsInstallPath ?>;
    formData.referencesPath = <?= $jsReferencesPath ?>;
    formData.availableDrives = <?= $jsAvailableDrives ?>;

    var stepsNew = [
        { id: 1,  name: 'Overview',    icon: 'ph-map-trifold',   file: null,           type: 'welcome' },
        { id: 2,  name: 'PRD',         icon: 'ph-file-text',     file: 'docs/prd.md',  type: 'markdown' },
        { id: 3,  name: 'Branding',    icon: 'ph-palette',       file: 'docs/branding.md', type: 'markdown' },
        { id: 4,  name: 'Logo',        icon: 'ph-image',         file: 'docs/logo.png', type: 'image' },
        { id: 5,  name: 'Landing',     icon: 'ph-browser',       file: 'references/landingpage.html',   type: 'html' },
        { id: 6,  name: 'Login',       icon: 'ph-sign-in',       file: 'references/login.html',         type: 'html' },
        { id: 7,  name: 'Register',    icon: 'ph-user-plus',     file: 'references/register.html',      type: 'html' },
        { id: 8,  name: 'Manajemen',   icon: 'ph-users-three',   file: 'references/modul_manajemen.html', type: 'html' },
        { id: 9,  name: 'Admin',       icon: 'ph-rocket-launch', file: 'references/modul_admin.html',   type: 'html' },
        { id: 10, name: 'Client',      icon: 'ph-user',          file: 'references/modul_client.html',  type: 'html' },
        { id: 11, name: 'Server',      icon: 'ph-hard-drives',   file: null,           type: 'config' },
        { id: 12, name: 'Path',        icon: 'ph-folder',         file: null,           type: 'config' }
    ];

    var stepsRedesign = [
        { id: 1,  name: 'Overview',    icon: 'ph-map-trifold',   file: null,           type: 'welcome' },
        { id: 2,  name: 'References',  icon: 'ph-folder-simple', file: null,           type: 'ref_manager' },
        { id: 3,  name: 'Logo',        icon: 'ph-image',         file: 'docs/logo.png', type: 'image' },
        { id: 4,  name: 'Server',      icon: 'ph-hard-drives',   file: null,           type: 'config' },
        { id: 5,  name: 'Path',        icon: 'ph-folder',         file: null,           type: 'config' }
    ];

    var steps = stepsNew;

    function initSteps() {
        var dotsContainer = document.getElementById('stepsDots');
        var labelsContainer = document.getElementById('phaseLabels');
        if (labelsContainer) {
            labelsContainer.innerHTML = steps.map(function(s) {
                return '<span>' + s.name + '</span>';
            }).join('');
        }
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';
        steps.forEach(function(s, i) {
            var dot = document.createElement('div');
            dot.className = 'step-dot border shrink-0 w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs cursor-pointer';
            dot.setAttribute('data-step', s.id);
            dot.setAttribute('onclick', 'jumpToStep(' + s.id + ')');
            dot.setAttribute('title', s.name);
            dot.innerHTML = s.type === 'welcome' ? '<i class="ph ph-map-trifold text-sm"></i>' : (s.id < 10 ? '0' + s.id : s.id);
            dotsContainer.appendChild(dot);
            if (i < steps.length - 1) {
                var connector = document.createElement('div');
                connector.className = 'step-connector';
                dotsContainer.appendChild(connector);
            }
        });
    }

    function setAppMode(mode) {
        appMode = mode;
        if (mode === 'redesign') {
            steps = stepsRedesign;
            totalSteps = stepsRedesign.length;
        } else {
            steps = stepsNew;
            totalSteps = stepsNew.length;
        }
        initSteps();
        renderStep();
    }

    function updateStepUI() {
        document.getElementById('stepLabel').textContent = 'STEP ' + (currentStep < 10 ? '0' + currentStep : currentStep) + '/' + (totalSteps < 10 ? '0' + totalSteps : totalSteps);
        var step = steps[currentStep - 1];
        document.getElementById('stepName').innerHTML = '<i class="ph ' + step.icon + ' text-[var(--brand-primary)]"></i> ' + step.name;

        var dots = document.querySelectorAll('.step-dot');
        var connectors = document.querySelectorAll('.step-connector');
        dots.forEach(function(dot, i) {
            dot.className = 'step-dot border shrink-0 w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs cursor-pointer';
            if (i + 1 < currentStep) dot.classList.add('completed');
            else if (i + 1 === currentStep) dot.classList.add('active');
            else dot.classList.add('inactive');
        });
        connectors.forEach(function(c, i) {
            c.className = 'step-connector';
            if (i + 1 < currentStep) c.classList.add('completed');
        });

        var isWelcome = currentStep === 1;
        var isServer = step.type === 'config' && step.name === 'Server';
        var isPath = step.type === 'config' && step.name === 'Path';

        document.getElementById('prevBtn').classList.toggle('hidden', isWelcome);
        document.getElementById('nextBtn').classList.toggle('hidden', isServer || isPath || isWelcome);
        document.getElementById('finishBtn').classList.toggle('hidden', !isServer);
        document.getElementById('executeBtn').classList.toggle('hidden', !isPath);

        var banner = document.getElementById('eduBanner');
        if (step.type === 'welcome') {
            banner.style.display = 'none';
        } else {
            banner.style.display = 'flex';
            var bannerTitle = document.getElementById('eduTitle');
            var bannerDesc = document.getElementById('eduDesc');
            if (step.type === 'markdown') {
                bannerTitle.textContent = 'Dokumen Konsep Aplikasi (PRD / Branding)';
                bannerDesc.textContent = 'AI coding assistant membutuhkan PRD & Branding untuk memandu pembuatan fitur bisnis secara presisi.';
            } else if (step.type === 'html') {
                bannerTitle.textContent = 'Referensi Layout Halaman HTML';
                bannerDesc.textContent = 'File HTML ini adalah template visual. AI akan membacanya sebagai pedoman struktur & styling.';
            } else if (step.type === 'image') {
                bannerTitle.textContent = 'Unggah Logo Aplikasi (PNG)';
                bannerDesc.textContent = 'Upload file logo PNG resmi aplikasi Anda.';
            } else {
                bannerTitle.textContent = 'Konfigurasi Lokasi & Server';
                bannerDesc.textContent = 'Tentukan jenis server lokal dan folder kerja Anda.';
            }
        }
    }

    function renderStep() {
        var content = document.getElementById('wizardContent');
        if (editor) { editor.dispose(); editor = null; }

        var currentStepObj = steps[currentStep - 1];

        if (currentStepObj.type === 'welcome') {
            content.innerHTML = renderWelcomeStep();
        } else if (currentStepObj.type === 'markdown') {
            var title = currentStepObj.name === 'PRD' ? 'PRD (Product Requirements Document)' : 'Branding Identity';
            var dataKey = currentStepObj.name === 'PRD' ? 'prd' : 'branding';
            content.innerHTML = renderCodeEditorStep(title, currentStepObj.file, dataKey, 'markdown');
            initMonacoEditor('markdown', dataKey);
        } else if (currentStepObj.type === 'image') {
            content.innerHTML = renderLogoUploadStep();
        } else if (currentStepObj.type === 'html') {
            var stepMapHtml = {
                'references/landingpage.html': { title: 'HTML Landing Page', key: 'landingPage' },
                'references/login.html': { title: 'HTML Login', key: 'loginPage' },
                'references/register.html': { title: 'HTML Register', key: 'registerPage' },
                'references/modul_manajemen.html': { title: 'HTML Manajemen', key: 'manajemenPage' },
                'references/modul_admin.html': { title: 'HTML Admin', key: 'adminPage' },
                'references/modul_client.html': { title: 'HTML Client', key: 'clientPage' }
            };
            var hInfo = stepMapHtml[currentStepObj.file];
            if (hInfo) {
                content.innerHTML = renderCodeEditorStep(hInfo.title, currentStepObj.file, hInfo.key, 'html');
                initMonacoEditor('html', hInfo.key);
            }
        } else if (currentStepObj.type === 'ref_manager') {
            content.innerHTML = renderReferencesManagerStep();
            loadReferencesList();
        } else if (currentStepObj.type === 'config') {
            if (currentStepObj.name === 'Server') {
                content.innerHTML = renderServerStep();
            } else {
                content.innerHTML = renderPathStep();
            }
        }

        updateStepUI();
        initUploadZones();

        if (currentStepObj.type === 'image' && formData.logoBase64) {
            var preview = document.getElementById('logoPreview');
            var container = document.getElementById('logoPreviewContainer');
            var placeholder = document.getElementById('logoPlaceholder');
            if (preview && container && placeholder) {
                preview.src = 'data:image/png;base64,' + formData.logoBase64;
                container.classList.remove('hidden');
                placeholder.classList.add('hidden');
            }
        }
    }

    function renderWelcomeStep() {
        return '<div class="space-y-8">' +
            '<div class="text-center max-w-xl mx-auto space-y-3">' +
                '<div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-orange-500/10 border border-orange-500/30 mb-2 glow-orange">' +
                    '<i class="ph ph-magic-wand text-4xl text-[var(--brand-primary)]"></i>' +
                '</div>' +
                '<h2 class="text-3xl sm:text-4xl font-heading font-extrabold tracking-tight">Vibeforge Setup Wizard</h2>' +
                '<p class="text-[var(--text-secondary)] text-sm leading-relaxed">Pilih alur persiapan proyek aplikasi Anda untuk memandu AI assistant:</p>' +
            '</div>' +

            '<!-- Mode Selection Cards -->' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">' +
                '<div onclick="setAppMode(\'new\')" class="cursor-pointer p-6 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-[var(--brand-primary)] glow-box-cyber ' + (appMode === 'new' ? 'border-[var(--brand-primary)] bg-orange-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-4">' +
                        '<div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)]"><i class="ph ph-sparkle text-2xl"></i></div>' +
                        '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (appMode === 'new' ? 'bg-[var(--brand-primary)] text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">GREENFIELD FLOW</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-lg mb-2">Aplikasi Baru (12 Steps)</h3>' +
                    '<p class="text-xs text-[var(--text-secondary)] leading-relaxed mb-4">Alur lengkap: Susun PRD, Branding, Logo, hingga kustomisasi 6 template HTML referensi.</p>' +
                    '<ul class="text-xs font-mono text-[var(--text-muted)] space-y-2 border-t border-[var(--border-default)] pt-4">' +
                        '<li class="flex items-center gap-2"><i class="ph ph-check-circle text-emerald-400 text-base"></i> Editor dokumen PRD & Branding</li>' +
                        '<li class="flex items-center gap-2"><i class="ph ph-check-circle text-emerald-400 text-base"></i> Editor 6 template HTML visual</li>' +
                    '</ul>' +
                '</div>' +

                '<div onclick="setAppMode(\'redesign\')" class="cursor-pointer p-6 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-purple-500 glow-box-cyber ' + (appMode === 'redesign' ? 'border-purple-500 bg-purple-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-4">' +
                        '<div class="w-12 h-12 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400"><i class="ph ph-paint-brush text-2xl"></i></div>' +
                        '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (appMode === 'redesign' ? 'bg-purple-600 text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">REFIT FLOW</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-lg mb-2">Redesain Aplikasi (5 Steps)</h3>' +
                    '<p class="text-xs text-[var(--text-secondary)] leading-relaxed mb-4">Alur cepat: Upload Logo, kelola folder References, konfigurasi Server & Path.</p>' +
                    '<ul class="text-xs font-mono text-[var(--text-muted)] space-y-2 border-t border-[var(--border-default)] pt-4">' +
                        '<li class="flex items-center gap-2"><i class="ph ph-check-circle text-purple-400 text-base"></i> Upload Logo + Kelola References</li>' +
                        '<li class="flex items-center gap-2"><i class="ph ph-check-circle text-purple-400 text-base"></i> Auto PRD/Branding dari References</li>' +
                    '</ul>' +
                '</div>' +
            '</div>' +

            '<div class="text-center pt-2" id="welcomeCTA">' +
                '<button onclick="startEditor()" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-brand text-white font-mono font-bold text-xs rounded-xl hover:opacity-90 transition-opacity glow-orange shadow-lg cursor-pointer">' +
                    '<i class="ph ph-rocket-launch text-base"></i> START CONFIGURATION <i class="ph ph-arrow-right text-base"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
    }

    function renderCodeEditorStep(title, targetFile, dataKey, lang) {
        var fallbackNotice = window.monacoLoadFailed ? '<div class="fallback-notice mb-3"><i class="ph ph-warning"></i> Monaco Editor tidak tersedia. Menggunakan editor textarea standar.</div>' : '';
        return '<div class="space-y-4">' +
            '<div class="flex items-center justify-between flex-wrap gap-2">' +
                '<div><h2 class="text-2xl font-heading font-bold mb-1">' + title + '</h2><p class="text-xs font-mono text-[var(--text-secondary)]">Target File: <code class="px-2 py-0.5 bg-[var(--bg-card)] rounded text-[var(--brand-primary)] border border-[var(--border-default)]">' + targetFile + '</code></p></div>' +
                '<button id="copyBtn" class="px-4 py-2 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl text-xs font-mono font-bold hover:border-[var(--brand-primary)] transition-all flex items-center gap-2 shadow-sm"><i class="ph ph-copy"></i> SALIN REFERENSI</button>' +
            '</div>' +
            fallbackNotice +
            '<div id="monaco-editor-container" class="editor-container shadow-2xl border border-[var(--border-default)]"></div>' +
            '<div class="flex items-center gap-4 text-xs font-mono text-[var(--text-muted)] border-t border-[var(--border-default)] pt-4">' +
                '<span>Timpa dengan file lokal:</span>' +
                '<input type="file" id="fileInput" class="hidden" accept=".html,.md,.txt" onchange="handleFileUpload(this, \'' + dataKey + '\')">' +
                '<button onclick="document.getElementById(\'fileInput\').click()" class="px-3.5 py-1.5 bg-[var(--bg-card)] hover:bg-[var(--bg-hover)] border border-[var(--border-default)] rounded-lg flex items-center gap-1.5 transition-colors text-xs font-bold text-gray-300"><i class="ph ph-upload-simple"></i> Upload File</button>' +
            '</div>' +
        '</div>';
    }

    function renderLogoUploadStep() {
        var existingLogo = formData.logoBase64 ? '<img src="data:image/png;base64,' + formData.logoBase64 + '" class="max-h-32 mx-auto mb-3 rounded-lg shadow-md border border-[var(--border-default)]">' : '';
        var hasExisting = formData.logoBase64 || formData.logo;

        return '<div class="space-y-6">' +
            '<div><h2 class="text-2xl font-heading font-bold mb-2">Upload Logo Aplikasi</h2><p class="text-xs font-mono text-[var(--text-secondary)]">Disimpan otomatis ke <code class="px-2 py-0.5 bg-[var(--bg-card)] border border-[var(--border-default)] rounded text-[var(--brand-primary)]">docs/logo.png</code></p></div>' +
            '<div class="upload-zone bg-[var(--bg-card)] border-2 border-dashed border-[var(--border-default)] rounded-2xl p-8 hover:border-[var(--brand-primary)] transition-all glow-box-cyber" id="uploadZone" onclick="document.getElementById(\'logoInput\').click()">' +
                '<input type="file" id="logoInput" class="hidden" accept=".png,image/png" onchange="handleLogoUpload(this)">' +
                '<div id="logoPreviewContainer" class="' + (hasExisting ? '' : 'hidden') + '"><img id="logoPreview" class="max-h-32 mx-auto mb-3 rounded-lg shadow-md border border-[var(--border-default)]"></div>' +
                '<div id="logoPlaceholder" class="' + (hasExisting ? 'hidden' : '') + '"><i class="ph ph-image text-4xl text-[var(--brand-primary)] mb-3"></i><p class="text-sm font-semibold text-[var(--text-primary)]">Klik / Drag & Drop logo PNG</p><p class="text-xs font-mono text-[var(--text-muted)] mt-1">Format: PNG (Max 2MB)</p></div>' +
            '</div>' +
            (hasExisting ? '<div class="flex items-center gap-2 text-emerald-400 font-mono text-xs font-bold"><i class="ph ph-check-circle text-sm"></i><span>Logo file ready: docs/logo.png</span></div>' : '') +
        '</div>';
    }

    function renderReferencesManagerStep() {
        return '<div class="space-y-6">' +
            '<div><h2 class="text-2xl font-heading font-bold mb-2">Folder References Manager</h2><p class="text-xs font-mono text-[var(--text-secondary)]">Kelola file referensi aplikasi lama di <code class="px-2 py-0.5 bg-[var(--bg-card)] border border-[var(--border-default)] rounded text-purple-400">references/</code></p></div>' +

            '<div class="p-4 bg-purple-500/10 border border-purple-500/30 rounded-2xl flex items-start gap-3.5 font-mono text-xs">' +
                '<i class="ph ph-info text-xl text-purple-400 shrink-0 mt-0.5"></i>' +
                '<div class="text-[11px] text-[var(--text-secondary)] space-y-1.5 leading-relaxed">' +
                    '<p class="font-bold text-purple-300">// Refit Mode Protocol:</p>' +
                    '<p>1. Folder <code class="px-1 bg-black/50 text-purple-300 rounded">references/</code> telah siap untuk menampung referensi codebase lama.</p>' +
                    '<p>2. Unggah file / folder codebase lama via tombol di bawah atau buka folder File Explorer.</p>' +
                    '<p>3. PRD & Branding akan otomatis dirangkum oleh AI CLI saat menjalankan <code class="px-1 bg-black/50 text-purple-300 rounded">@docs/install.md</code>.</p>' +
                '</div>' +
            '</div>' +

            '<div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-4 glow-box-cyber">' +
                '<div class="flex items-center justify-between flex-wrap gap-3">' +
                    '<h3 class="font-heading font-bold text-lg flex items-center gap-2"><i class="ph ph-folder-open text-purple-400"></i> Contents: references/</h3>' +
                    '<div class="flex items-center gap-2 font-mono text-xs">' +
                        '<button onclick="openReferencesFolder()" class="px-3.5 py-1.5 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-xl font-bold hover:bg-blue-500/20 transition-colors flex items-center gap-1.5"><i class="ph ph-folder-simple-open"></i> File Explorer</button>' +
                        '<button onclick="clearReferencesFolder()" class="px-3.5 py-1.5 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl font-bold hover:bg-red-500/20 transition-colors flex items-center gap-1.5"><i class="ph ph-trash"></i> Clear</button>' +
                        '<button onclick="loadReferencesList()" class="px-3.5 py-1.5 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl font-bold hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5"><i class="ph ph-arrows-clockwise"></i> Refresh</button>' +
                    '</div>' +
                '</div>' +

                '<div id="refFilesContainer" class="min-h-[140px] bg-black/60 rounded-xl p-4 border border-[var(--border-default)] flex flex-col justify-center items-center text-center font-mono">' +
                    '<i class="ph ph-circle-notch text-2xl text-[var(--brand-primary)] animate-spin"></i>' +
                    '<p class="text-xs text-[var(--text-muted)] mt-2">Reading directory...</p>' +
                '</div>' +

                '<div class="pt-2 border-t border-[var(--border-default)] font-mono text-xs">' +
                    '<p class="text-[11px] text-[var(--text-muted)] mb-3">// Upload files or folder:</p>' +
                    '<input type="file" id="refFileInput" class="hidden" multiple onchange="handleRefUpload(this)">' +
                    '<input type="file" id="refFolderInput" class="hidden" webkitdirectory directory multiple onchange="handleRefUpload(this)">' +
                    '<div class="flex items-center gap-2.5 flex-wrap">' +
                        '<button onclick="openReferencesFolder()" class="px-4 py-2 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-xl font-bold hover:bg-blue-500/20 transition-colors flex items-center gap-1.5 shadow-sm"><i class="ph ph-folder-simple-open"></i> Open Folder</button>' +
                        '<button onclick="document.getElementById(\'refFileInput\').click()" class="px-4 py-2 bg-[var(--bg-surface)] border border-[var(--border-default)] text-[var(--text-primary)] rounded-xl font-bold hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5 shadow-sm"><i class="ph ph-file-plus"></i> Upload Files</button>' +
                        '<button onclick="document.getElementById(\'refFolderInput\').click()" class="px-4 py-2 bg-purple-600 text-white rounded-xl font-bold hover:bg-purple-500 transition-colors flex items-center gap-1.5 shadow-sm"><i class="ph ph-folder-plus"></i> Upload Folder</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    async function openReferencesFolder() {
        showToast('System Action', 'Membuka folder references/ di File Explorer...');
        try {
            var res = await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    module: 'install',
                    action: 'open_folder',
                    folder: 'references',
                    csrf_token: csrfToken
                })
            });
            var data = await res.json();
            if (data.success) {
                showToast('Terbuka', 'Folder references/ dibuka di File Explorer');
            } else {
                showToast('Error', data.error || 'Gagal membuka folder', true);
            }
        } catch(e) {
            showToast('Error', 'Gagal koneksi ke server', true);
        }
    }

    async function loadReferencesList() {
        var container = document.getElementById('refFilesContainer');
        if (!container) return;

        try {
            var res = await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module: 'install', action: 'list_references', csrf_token: csrfToken })
            });
            var data = await res.json();
            refFiles = data.files || [];

            if (refFiles.length === 0) {
                container.innerHTML = '<div class="py-6"><i class="ph ph-folder-open text-4xl text-[var(--text-muted)] mb-2 block"></i><p class="text-xs font-bold text-[var(--text-secondary)] font-mono">Folder references/ Kosong</p><p class="text-[11px] text-[var(--text-muted)] mt-1 font-mono">Silakan upload file/folder referensi aplikasi lama.</p></div>';
            } else {
                var html = '<div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3 text-left font-mono text-xs">';
                refFiles.forEach(function(file) {
                    var isDir = file.is_dir;
                    var icon = isDir ? 'ph-folder-simple text-amber-400' : 'ph-file-code text-purple-400';
                    var bg = isDir ? 'bg-amber-500/10' : 'bg-purple-500/10';
                    var kb = isDir ? 'DIR' : (file.size / 1024).toFixed(1) + ' KB';

                    html += '<div class="p-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl flex items-center gap-3">' +
                        '<div class="w-9 h-9 rounded-lg ' + bg + ' flex items-center justify-center shrink-0"><i class="ph ' + icon + ' text-lg"></i></div>' +
                        '<div class="flex-1 min-w-0"><p class="text-xs font-semibold text-[var(--text-primary)] truncate">' + file.name + '</p><p class="text-[10px] text-[var(--text-muted)]">' + kb + ' · ' + file.updated_at + '</p></div>' +
                    '</div>';
                });
                html += '</div>';
                container.innerHTML = html;
            }
        } catch(e) {
            container.innerHTML = '<p class="text-xs font-mono text-red-400">Gagal mendeteksi isi folder references</p>';
        }
    }

    async function clearReferencesFolder() {
        if (!confirm('Apakah Anda yakin ingin menghapus seluruh file di folder references/?')) return;
        showSavingOverlay();
        try {
            var res = await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module: 'install', action: 'clear_references', csrf_token: csrfToken })
            });
            var data = await res.json();
            if (data.success) {
                showToast('Dibersihkan!', data.deleted_count + ' file referensi berhasil dihapus');
                loadReferencesList();
            }
        } catch(e) {
            showToast('Gagal!', 'Gagal membersihkan folder', true);
        } finally {
            hideSavingOverlay();
        }
    }

    async function handleRefUpload(input) {
        if (!input.files || input.files.length === 0) return;
        showSavingOverlay();

        for (var i = 0; i < input.files.length; i++) {
            var file = input.files[i];
            var relativePath = file.webkitRelativePath || file.name;
            var reader = new FileReader();
            await new Promise(function(resolve) {
                reader.onload = async function(e) {
                    await fetch('/core/router.php', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({
                            module: 'install',
                            action: 'save',
                            file: 'references/' + relativePath,
                            content: e.target.result,
                            csrf_token: csrfToken
                        })
                    });
                    resolve();
                };
                reader.readAsText(file);
            });
        }

        hideSavingOverlay();
        showToast('Berhasil!', input.files.length + ' item berhasil diunggah');
        loadReferencesList();
    }

    function renderServerStep() {
        var drives = formData.availableDrives || ['C', 'D', 'E', 'F', 'G', 'H'];
        var currentDrive = formData.drive || 'C';
        var server = formData.serverType || 'laragon';
        var subPath = server === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs';
        var fullPathPreview = currentDrive + ':\\' + subPath;

        var driveButtons = drives.map(function(d) {
            var activeClass = d === currentDrive
                ? 'bg-[var(--brand-primary)] text-white font-bold border-[var(--brand-primary)] shadow-md'
                : 'bg-[var(--bg-card)] text-[var(--text-secondary)] border-[var(--border-default)] hover:bg-[var(--bg-hover)]';

            return '<button type="button" onclick="updateDrive(\'' + d + '\')" class="shrink-0 px-4 py-2.5 rounded-xl border text-xs font-mono font-bold transition-all flex items-center gap-1.5 ' + activeClass + '">' +
                '<i class="ph ph-hard-drive text-base"></i> (' + d + ':)' +
            '</button>';
        }).join('');

        return '<div class="space-y-6">' +
            '<div><h2 class="text-2xl font-heading font-bold mb-2">Web Server Runtime</h2><p class="text-xs font-mono text-[var(--text-secondary)]">Pilih lingkungan web server lokal target:</p></div>' +
            '<div class="grid grid-cols-2 gap-4">' +
                '<label class="cursor-pointer">' +
                    '<input type="radio" name="serverType" value="laragon" class="hidden peer" ' + (server === 'laragon' ? 'checked' : '') + ' onchange="updateServerType(\'laragon\')">' +
                    '<div class="p-6 bg-[var(--bg-card)] border-2 border-[var(--border-default)] rounded-2xl transition-all peer-checked:border-[var(--brand-primary)] peer-checked:bg-orange-500/5 glow-box-cyber">' +
                        '<div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center mb-4"><i class="ph ph-bug text-2xl text-emerald-400"></i></div>' +
                        '<h3 class="font-heading font-bold text-lg mb-1">Laragon Engine</h3><p class="text-xs text-[var(--text-muted)]">Apache/Nginx isolated environment</p><p class="text-xs text-[var(--brand-primary)] mt-3 font-mono" id="laragonPathPreview">' + currentDrive + ':\\laragon\\www</p>' +
                    '</div>' +
                '</label>' +
                '<label class="cursor-pointer">' +
                    '<input type="radio" name="serverType" value="xampp" class="hidden peer" ' + (server === 'xampp' ? 'checked' : '') + ' onchange="updateServerType(\'xampp\')">' +
                    '<div class="p-6 bg-[var(--bg-card)] border-2 border-[var(--border-default)] rounded-2xl transition-all peer-checked:border-[var(--brand-primary)] peer-checked:bg-orange-500/5 glow-box-cyber">' +
                        '<div class="w-12 h-12 rounded-xl bg-blue-500/10 border border-blue-500/30 flex items-center justify-center mb-4"><i class="ph ph-file-css text-2xl text-blue-400"></i></div>' +
                        '<h3 class="font-heading font-bold text-lg mb-1">XAMPP Engine</h3><p class="text-xs text-[var(--text-muted)]">Apache + PHP + MySQL stack</p><p class="text-xs text-[var(--brand-primary)] mt-3 font-mono" id="xamppPathPreview">' + currentDrive + ':\\xampp\\htdocs</p>' +
                    '</div>' +
                '</label>' +
            '</div>' +
            '<div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">' +
                '<label class="form-label font-mono font-bold text-xs uppercase text-[var(--text-secondary)] block">// Select Target Local Disk Drive:</label>' +
                '<div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin hide-scrollbar" id="driveContainer">' +
                    driveButtons +
                '</div>' +
                '<p class="text-xs font-mono text-[var(--text-muted)] pt-2 border-t border-[var(--border-default)]">Target Server Path: <code class="px-2 py-0.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded text-[var(--brand-primary)] font-mono font-bold" id="serverFullPathPreview">' + fullPathPreview + '</code></p>' +
            '</div>' +
        '</div>';
    }

    function updateDrive(driveVal) {
        formData.drive = driveVal;
        var subPath = formData.serverType === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs';
        formData.installPath = driveVal + ':\\' + subPath;

        renderStep();
        triggerAutoSave('serverConfig', formData.serverType);
    }

    function updateServerType(val) {
        formData.serverType = val;
        var drive = formData.drive || 'C';
        var subPath = val === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs';
        formData.installPath = drive + ':\\' + subPath;

        renderStep();
        triggerAutoSave('serverConfig', val);
    }

    function renderPathStep() {
        var drive = formData.drive || 'C';
        var server = formData.serverType || 'laragon';
        var folderName = 'vibeforge';
        var basePath = drive + ':\\' + (server === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs');
        var projectPath = basePath + '\\' + folderName;

        return '<div class="space-y-6">' +
            '<div><h2 class="text-2xl font-heading font-bold mb-2">Lokasi Project & Execution</h2><p class="text-xs font-mono text-[var(--text-secondary)]">Selesai! Konfirmasi lokasi workspace sebelum membuka CLI.</p></div>' +

            '<div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 glow-box-cyber">' +
                '<div class="flex items-center justify-between mb-1">' +
                    '<h3 class="font-mono text-xs font-bold uppercase text-gray-300 flex items-center gap-2"><i class="ph ph-folder-simple text-[var(--brand-primary)] text-base"></i> Target Directory Path</h3>' +
                    '<button onclick="copyProjectPath()" class="px-3 py-1 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-lg text-xs font-mono font-bold hover:border-[var(--brand-primary)] transition-colors flex items-center gap-1.5"><i class="ph ph-copy"></i> Salin Path</button>' +
                '</div>' +
                '<div class="bg-black/80 rounded-xl p-4 font-mono text-xs border border-gray-800 text-emerald-400 select-all"><span class="text-gray-500 select-none">PS&gt; cd </span><span id="pathPreview">' + projectPath + '</span></div>' +
                '<p class="text-[11px] font-mono text-[var(--text-muted)]">Default directory name: <code class="px-1.5 py-0.5 bg-[var(--bg-primary)] rounded text-[var(--brand-primary)] font-bold">vibeforge</code></p>' +
            '</div>' +

            '<div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-4 glow-box-cyber">' +
                '<div>' +
                    '<h3 class="font-heading font-bold text-lg mb-1 flex items-center gap-2"><i class="ph ph-terminal-window text-[var(--brand-primary)] text-xl"></i> Eksekusi AI Assistant Terminal</h3>' +
                    '<p class="text-xs text-[var(--text-muted)]">Seluruh file spesifikasi aplikasi Anda sudah disimpan. Klik tombol di bawah untuk membuka PowerShell secara otomatis.</p>' +
                '</div>' +
                '<button onclick="executeTerminal()" class="w-full py-4 bg-gradient-brand text-white font-mono font-bold text-xs rounded-xl hover:opacity-95 transition-all glow-orange flex items-center justify-center gap-2 shadow-2xl cursor-pointer">' +
                    '<i class="ph ph-play-circle text-xl"></i> OPEN POWERSHELL & RUN CLAUDE CODE' +
                '</button>' +
            '</div>' +

            '<div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-3 font-mono text-xs glow-box-cyber">' +
                '<h3 class="font-bold text-gray-300 flex items-center gap-2"><i class="ph ph-clipboard text-[var(--brand-primary)] text-base"></i> Manual Prompt Command</h3>' +
                '<div class="relative"><code class="block bg-black/80 border border-gray-800 rounded-xl p-4 font-mono text-xs text-orange-400 select-all">baca dan jalankan @docs/install.md</code>' +
                '<button onclick="copyInstallCommand()" class="mt-3 w-full py-2.5 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl font-bold hover:border-[var(--brand-primary)] transition-colors flex items-center justify-center gap-2 text-xs"><i class="ph ph-copy"></i> Copy Prompt Command</button></div>' +
            '</div>' +
        '</div>';
    }

    function copyProjectPath() {
        var drive = formData.drive || 'C';
        var server = formData.serverType || 'laragon';
        var basePath = drive + ':\\' + (server === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs');
        var projectPath = basePath + '\\vibeforge';

        var successCallback = function() {
            showToast('Tersalin!', 'Project Path disalin ke clipboard');
        };
        var fallbackCopy = function() {
            var textarea = document.createElement('textarea');
            textarea.value = projectPath;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try { document.execCommand('copy'); successCallback(); }
            catch (err) { showToast('Gagal!', 'Gagal menyalin path', true); }
            document.body.removeChild(textarea);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(projectPath).then(successCallback).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    }

    function updateInstallPath(val) {
        formData.installPath = val;
        var projectPath = val + '\\vibeforge';
        var preview = document.getElementById('pathPreview');
        if (preview) preview.textContent = projectPath;
        triggerAutoSave('pathConfig', val);
    }

    var monacoThemesDefined = false;

    function initMonacoEditor(language, dataKey) {
        var container = document.getElementById('monaco-editor-container');
        if (!container) return;

        if (!window.monacoLoaded || window.monacoLoadFailed) {
            initTextareaFallback(container, dataKey, language);
            return;
        }

        requestAnimationFrame(function() {
            var containerCheck = document.getElementById('monaco-editor-container');
            if (!containerCheck) return;
            if (editor) { editor.dispose(); editor = null; }

            if (!monacoThemesDefined) {
                monaco.editor.defineTheme('vibeforgeDark', {
                    base: 'vs-dark', inherit: true, rules: [],
                    colors: { 'editor.background': '#0B0F17', 'editor.lineHighlightBackground': '#111726' }
                });
                monacoThemesDefined = true;
            }

            editor = monaco.editor.create(containerCheck, {
                value: formData[dataKey] || '',
                language: language === 'markdown' ? 'markdown' : 'html',
                theme: 'vibeforgeDark',
                fontSize: 13,
                fontFamily: 'JetBrains Mono, Fira Code, Consolas, monospace',
                minimap: { enabled: false },
                lineNumbers: 'on',
                automaticLayout: true,
                wordWrap: 'on'
            });

            editor.onDidChangeModelContent(function() {
                var currentVal = editor.getValue();
                formData[dataKey] = currentVal;
                triggerAutoSave(dataKey, currentVal);
            });

            var copyBtn = document.getElementById('copyBtn');
            if (copyBtn) copyBtn.onclick = function() { copyToClipboard(dataKey); };
        });
    }

    function initTextareaFallback(container, dataKey, language) {
        container.innerHTML = '<textarea id="fallbackEditor" class="w-full h-full p-4 font-mono text-xs resize-none border-0 focus:outline-none" style="min-height: 400px; background-color: #0B0F17; color: #F0F6FC; line-height: 1.6;" oninput="handleTextareaInput(\'' + dataKey + '\', this.value)">' + (formData[dataKey] || '') + '</textarea>';
        var copyBtn = document.getElementById('copyBtn');
        if (copyBtn) copyBtn.onclick = function() { copyToClipboard(dataKey); };
    }

    function handleTextareaInput(dataKey, value) {
        formData[dataKey] = value;
        triggerAutoSave(dataKey, value);
    }

    function triggerAutoSave(dataKey, value) {
        var step = steps[currentStep - 1];
        var status = document.getElementById('saveStatus');
        var statusText = document.getElementById('saveStatusText');

        if (!step.file && dataKey !== 'serverConfig' && dataKey !== 'pathConfig') {
            if (status) status.classList.add('hidden');
            return;
        }

        if (status) {
            statusText.textContent = 'Saving...';
            status.className = 'text-xs font-mono text-amber-400 flex items-center gap-1.5';
        }

        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(async function() {
            try {
                var bodyData;
                if (dataKey === 'serverConfig' || dataKey === 'pathConfig') {
                    bodyData = {
                        module: 'install', action: 'save', actionType: 'config',
                        serverType: formData.serverType, drive: formData.drive || 'C', installPath: formData.installPath, csrf_token: csrfToken
                    };
                } else {
                    bodyData = {
                        module: 'install', action: 'save', file: step.file,
                        content: value, csrf_token: csrfToken
                    };
                    savedFiles.add(step.file);
                }

                var response = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(bodyData)
                });

                if (response.ok) {
                    if (status) {
                        statusText.textContent = 'Auto-Saved';
                        status.className = 'text-xs font-mono text-emerald-400 flex items-center gap-1.5';
                    }
                    if (step.file) triggerGraphifyUpdate(step.file);
                } else {
                    if (status) {
                        statusText.textContent = 'Save Failed';
                        status.className = 'text-xs font-mono text-rose-400 flex items-center gap-1.5';
                    }
                }
            } catch(e) {
                if (status) {
                    statusText.textContent = 'Network Error';
                    status.className = 'text-xs font-mono text-rose-400 flex items-center gap-1.5';
                }
            }
        }, 800);
    }

    async function triggerGraphifyUpdate(changedFile) {
        try {
            await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module: 'install', action: 'graphify', file: changedFile, csrf_token: csrfToken })
            });
        } catch(e) {}
    }

    function copyToClipboard(dataKey) {
        var text = formData[dataKey] || '';
        try {
            if (editor && typeof editor.getValue === 'function') text = editor.getValue();
            else {
                var textarea = document.getElementById('fallbackEditor');
                if (textarea && textarea.value) text = textarea.value;
            }
        } catch(e) {}

        if (!text) { showToast('Gagal!', 'Tidak ada konten untuk disalin', true); return; }
        var fileName = steps[currentStep - 1].file;

        var successCallback = function() {
            showToast('Tersalin!', 'Isi ' + fileName + ' disalin ke clipboard');
        };
        var fallbackCopy = function() {
            var textarea = document.createElement('textarea');
            textarea.value = text;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                successCallback();
            } catch (err) {
                showToast('Gagal!', 'Tidak dapat menyalin ke clipboard', true);
            }
            document.body.removeChild(textarea);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(successCallback).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    }

    function initUploadZones() {
        var zones = document.querySelectorAll('.upload-zone');
        zones.forEach(function(zone) {
            zone.ondragover = function(e) { e.preventDefault(); zone.classList.add('dragover'); };
            zone.ondragleave = function() { zone.classList.remove('dragover'); };
            zone.ondrop = function(e) {
                e.preventDefault(); zone.classList.remove('dragover');
                var file = e.dataTransfer.files[0];
                if (file) {
                    var input = zone.querySelector('input[type="file"]');
                    if (input) {
                        var dt = new DataTransfer(); dt.items.add(file);
                        input.files = dt.files;
                        input.dispatchEvent(new Event('change'));
                    }
                }
            };
        });
    }

    function handleFileUpload(input, dataKey) {
        var file = input.files[0];
        if (file) {
            var reader = new FileReader();
            reader.onload = function(e) {
                formData[dataKey] = e.target.result;
                if (editor) editor.setValue(e.target.result);
                triggerAutoSave(dataKey, e.target.result);
            };
            reader.readAsText(file);
        }
    }

    function handleLogoUpload(input) {
        var file = input.files[0];
        if (file && file.type === 'image/png') {
            formData.logo = file;
            var reader = new FileReader();
            reader.onload = async function(e) {
                document.getElementById('logoPreview').src = e.target.result;
                document.getElementById('logoPreviewContainer').classList.remove('hidden');
                document.getElementById('logoPlaceholder').classList.add('hidden');
                await saveCurrentStep();
            };
            reader.readAsDataURL(file);
        }
    }

    async function saveCurrentStep() {
        var step = steps[currentStep - 1];

        if (step.name === 'Logo' && formData.logo) {
            var formDataObj = new FormData();
            formDataObj.append('module', 'install');
            formDataObj.append('action', 'save');
            formDataObj.append('logo', formData.logo);
            formDataObj.append('csrf_token', csrfToken);
            var response = await fetch('/core/router.php', { method: 'POST', body: formDataObj });
            if (response.ok) savedFiles.add('docs/logo.png');
            return response.ok;
        }

        if (step.name === 'Server') {
            var res = await fetch('/core/router.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    module: 'install', action: 'save', actionType: 'config',
                    serverType: formData.serverType, drive: formData.drive || 'C', installPath: formData.installPath, csrf_token: csrfToken
                })
            });
            return res.ok;
        }

        if (!step.file) return true;

        var dataKey = step.file === 'docs/prd.md' ? 'prd' : (step.file === 'docs/branding.md' ? 'branding' : null);
        if (!dataKey) {
            var stepMapHtml = {
                'references/landingpage.html': 'landingPage',
                'references/login.html': 'loginPage',
                'references/register.html': 'registerPage',
                'references/modul_manajemen.html': 'manajemenPage',
                'references/modul_admin.html': 'adminPage',
                'references/modul_client.html': 'clientPage'
            };
            dataKey = stepMapHtml[step.file];
        }
        var content = null;

        if (editor && typeof editor.getValue === 'function') {
            content = editor.getValue();
            formData[dataKey] = content;
        } else {
            var textarea = document.getElementById('fallbackEditor');
            if (textarea) { content = textarea.value; formData[dataKey] = content; }
            else content = formData[dataKey];
        }

        if (content === null || content === undefined) return true;

        var res = await fetch('/core/router.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ module: 'install', action: 'save', file: step.file, content: content, csrf_token: csrfToken })
        });
        if (res.ok) savedFiles.add(step.file);
        return res.ok;
    }

    function startEditor() { currentStep = 2; renderStep(); }

    function showSavingOverlay() {
        var overlay = document.getElementById('savingOverlay');
        if (overlay) overlay.classList.remove('hidden');
    }

    function hideSavingOverlay() {
        var overlay = document.getElementById('savingOverlay');
        if (overlay) overlay.classList.add('hidden');
    }

    async function nextStep() {
        if (isNavigating) return;
        isNavigating = true;
        setButtonsDisabled(true);
        showSavingOverlay();

        try {
            await saveCurrentStep();
            if (currentStep < totalSteps) { currentStep++; renderStep(); }
        } finally {
            isNavigating = false;
            setButtonsDisabled(false);
            hideSavingOverlay();
        }
    }

    function prevStep() {
        if (isNavigating) return;
        if (currentStep > 1) {
            isNavigating = true;
            setButtonsDisabled(true);
            showSavingOverlay();
            saveCurrentStep().then(function() {
                currentStep--;
                renderStep();
            }).finally(function() {
                isNavigating = false;
                setButtonsDisabled(false);
                hideSavingOverlay();
            });
        }
    }

    function jumpToStep(stepId) {
        if (isNavigating) return;
        if (stepId < 1 || stepId > totalSteps) {
            showToast('Warning', 'Langkah tidak valid', true);
            return;
        }
        isNavigating = true;
        setButtonsDisabled(true);
        showSavingOverlay();

        saveCurrentStep().then(function() {
            currentStep = stepId;
            renderStep();
        }).finally(function() {
            isNavigating = false;
            setButtonsDisabled(false);
            hideSavingOverlay();
        });
    }

    function finishWizard() {
        if (isNavigating) return;
        isNavigating = true;
        setButtonsDisabled(true);
        showSavingOverlay();

        saveCurrentStep().then(async function() {
            try {
                var res = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        module: 'install',
                        action: 'generate_install_md',
                        serverType: formData.serverType,
                        drive: formData.drive,
                        installPath: formData.installPath,
                        appMode: appMode,
                        projectName: 'vibeforge',
                        csrf_token: csrfToken
                    })
                });
                var data = await res.json();
                if (data.success) {
                    savedFiles.add('docs/install.md');
                    showToast('install.md Generated', 'File dokumentasi berhasil dibuat');
                }
            } catch(e) {
                console.log('Install md generation failed:', e);
            }

            currentStep = totalSteps;
            renderStep();
        }).finally(function() {
            isNavigating = false;
            setButtonsDisabled(false);
            hideSavingOverlay();
        });
    }

    function setButtonsDisabled(disabled) {
        ['nextBtn', 'prevBtn', 'finishBtn', 'executeBtn'].forEach(function(id) {
            var btn = document.getElementById(id);
            if (btn) btn.disabled = disabled;
        });
    }

    async function executeTerminal() {
        var btn = document.getElementById('executeBtn');
        if (btn) btn.disabled = true;

        var drive = formData.drive || 'C';
        var server = formData.serverType || 'laragon';
        var basePath = drive + ':\\' + (server === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs');
        var projectPath = basePath + '\\vibeforge';

        try {
            await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    module: 'install',
                    action: 'save',
                    actionType: 'config',
                    serverType: formData.serverType,
                    drive: drive,
                    installPath: basePath,
                    csrf_token: csrfToken
                })
            });

            await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    module: 'install',
                    action: 'generate_install_md',
                    serverType: formData.serverType,
                    drive: drive,
                    installPath: basePath,
                    appMode: appMode,
                    projectName: 'vibeforge',
                    csrf_token: csrfToken
                })
            });
            savedFiles.add('docs/install.md');

            showSuccessModal();

            setTimeout(function() {
                fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        module: 'install',
                        action: 'execute',
                        drive: drive,
                        serverType: formData.serverType,
                        projectPath: projectPath,
                        csrf_token: csrfToken
                    })
                }).catch(function(err) { console.error('Delayed launch error:', err); });
            }, 3000);

        } catch(e) {
            console.error('Execute terminal error:', e);
            showSuccessModal();
        } finally {
            if (btn) btn.disabled = false;
        }
    }

    function showSuccessModal() {
        var list = document.getElementById('savedFilesList');
        if (list) {
            list.innerHTML = '';
            if (savedFiles.size === 0) {
                list.innerHTML = '<li class="flex items-center gap-2 text-gray-400 font-mono"><i class="ph ph-info"></i> Belum ada file yang diubah</li>';
            } else {
                savedFiles.forEach(function(f) {
                    var li = document.createElement('li');
                    li.className = 'flex items-center gap-2 text-emerald-400 font-mono';
                    li.innerHTML = '<i class="ph ph-check-circle"></i> ' + f;
                    list.appendChild(li);
                });
            }
        }
        document.getElementById('successModal').classList.remove('hidden');
    }

    function closeSuccessModal() {
        document.getElementById('successModal').classList.add('hidden');
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeSuccessModal();
    });

    function showToast(title, message, isError) {
        var toast = document.getElementById('toast');
        var toastTitle = document.getElementById('toastTitle');
        var toastMessage = document.getElementById('toastMessage');
        var toastIcon = document.getElementById('toastIcon');

        if (toastTitle) toastTitle.textContent = title;
        if (toastMessage) toastMessage.textContent = message;

        if (toastIcon) {
            toastIcon.className = isError ? 'ph ph-warning-circle text-lg text-rose-400' : 'ph ph-check-circle text-lg text-emerald-400';
        }

        toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
        toast.classList.add('opacity-100', 'translate-y-0');

        setTimeout(function() {
            toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
            toast.classList.remove('opacity-100', 'translate-y-0');
        }, 3000);
    }

    function copyInstallCommand() {
        var commandText = 'baca dan jalankan @docs/install.md';
        var successCallback = function() {
            showToast('Tersalin!', 'Command disalin ke clipboard');
        };
        var fallbackCopy = function() {
            var textarea = document.createElement('textarea');
            textarea.value = commandText;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                successCallback();
            } catch (err) {
                showToast('Gagal!', 'Tidak dapat menyalin command', true);
            }
            document.body.removeChild(textarea);
        };

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(commandText).then(successCallback).catch(fallbackCopy);
        } else {
            fallbackCopy();
        }
    }

    function copyModalInstallCommand() {
        copyInstallCommand();
    }

    var htmlTheme = document.documentElement;
    document.getElementById('themeToggle')?.addEventListener('click', function() {
        var isDark = htmlTheme.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        if (editor) editor.updateOptions({ theme: isDark ? 'vibeforgeDark' : 'vs' });
    });

    window.onMonacoReady = function() {
        if (_pendingMonacoInit) initMonacoEditor(_pendingMonacoInit.language, _pendingMonacoInit.dataKey);
    };

    initSteps();
    renderStep();
    </script>
</body>
</html>
