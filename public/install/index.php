<?php
/**
 * Vibeforge - Installation Wizard Shell
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
    <title>Setup Wizard - <?= APP_DISPLAY_NAME ?></title>
    <link rel="icon" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23FF6B35'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Poppins:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/branding.css">

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
                    colors: { brand: { primary: '#F97316', dark: '#0D1117', card: '#161B22' } },
                    fontFamily: { sans: ['Inter', 'sans-serif'], heading: ['Poppins', 'sans-serif'] }
                }
            }
        }
    </script>
    <style>
        body { font-family: 'Inter', sans-serif; background: var(--bg-primary); color: var(--text-primary); }
        h1, h2, h3 { font-family: 'Poppins', sans-serif; }
        .text-gradient { background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .bg-gradient-brand { background: linear-gradient(135deg, #F97316 0%, #F59E0B 100%); }
        .glow-orange { box-shadow: 0 0 40px rgba(255, 107, 53, 0.3); }
        .glow-orange-sm { box-shadow: 0 0 20px rgba(255, 107, 53, 0.2); }
        .step-dot { transition: all 0.3s ease; }
        .step-dot.active { background: var(--brand-primary); transform: scale(1.2); color: #fff; }
        .step-dot.completed { background: #22C55E; color: #fff; }
        .step-dot.inactive { background: var(--bg-surface); color: var(--text-muted); }
        .step-connector { width: 12px; height: 2px; background: var(--border-default); transition: background 0.3s ease; }
        .step-connector.completed { background: #22C55E; }
        .form-input { width: 100%; padding: 0.75rem 1rem; background: var(--bg-card); border: 1px solid var(--border-default); border-radius: 0.75rem; color: var(--text-primary); font-size: 0.875rem; transition: all 0.2s; }
        .form-input:focus { outline: none; border-color: var(--brand-primary); box-shadow: 0 0 0 3px rgba(255, 107, 53, 0.1); }
        .form-label { display: block; font-size: 0.875rem; font-weight: 500; margin-bottom: 0.5rem; color: var(--text-secondary); }
        .upload-zone { border: 2px dashed var(--border-default); border-radius: 1rem; padding: 2rem; text-align: center; cursor: pointer; transition: all 0.2s; }
        .upload-zone:hover, .upload-zone.dragover { border-color: var(--brand-primary); background: rgba(255, 107, 53, 0.05); }
        .editor-container { height: 400px; border: 1px solid var(--border-default); border-radius: 0.75rem; overflow: hidden; background: #161B22; }
        .step-content { animation: slideIn 0.3s ease-out; }
        @keyframes slideIn { from { opacity: 0; transform: translateX(20px); } to { opacity: 1; transform: translateX(0); } }
        @keyframes checkmark { 0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .success-check { animation: checkmark 0.4s ease-out; }
        button:disabled { opacity: 0.6; cursor: not-allowed; }
        .fallback-notice { background: var(--status-warning); color: #000; padding: 0.5rem 1rem; border-radius: 0.5rem; font-size: 0.75rem; display: flex; align-items: center; gap: 0.5rem; }
        #successModal { cursor: pointer; }
        #successModal > div { cursor: default; }
    </style>
</head>
<body class="antialiased min-h-screen pt-16">
    <div class="min-h-screen flex flex-col">

        <!-- Navbar -->
        <nav class="fixed top-0 w-full z-50 bg-[var(--bg-secondary)]/90 backdrop-blur-md border-b border-[var(--border-default)]">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between h-16">
                    <a href="/" class="flex items-center gap-2">
                        <svg width="28" height="28" viewBox="0 0 24 24" fill="#F97316"><path d="M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z"/></svg>
                        <span class="font-heading font-bold text-xl"><span class="text-[var(--text-primary)]">Vibe</span><span class="text-gradient">forge</span></span>
                    </a>
                    <div class="hidden md:flex items-center gap-8">
                        <a href="/#fitur" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">Fitur</a>
                        <a href="/#cara-pasang" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">Cara Pasang</a>
                        <a href="/#demo" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors">Demo</a>
                        <a href="https://github.com/iqbalmurtadho24/vibeforge" target="_blank" class="text-sm font-medium text-[var(--text-secondary)] hover:text-[var(--brand-primary)] transition-colors flex items-center gap-1"><i class="ph ph-github-logo"></i> GitHub</a>
                    </div>
                    <div class="flex items-center gap-3">
                        <a href="/" class="px-3 py-1.5 text-[var(--text-secondary)] text-sm font-medium rounded-lg hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5"><i class="ph ph-arrow-left"></i> Kembali</a>
                        <button id="themeToggle" class="w-10 h-10 rounded-lg flex items-center justify-center hover:bg-[var(--bg-hover)] transition-colors" aria-label="Toggle theme"><i class="ph ph-moon text-lg text-[var(--text-muted)]"></i></button>
<?php if ($isLoggedIn): ?>
                        <a href="<?= $dashboardUrl ?>" class="px-4 py-2 bg-[var(--brand-primary)] text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity">Dashboard</a>
<?php else: ?>
                        <a href="/login/" class="px-4 py-2 text-[var(--text-secondary)] text-sm font-medium rounded-lg hover:bg-[var(--bg-hover)] transition-colors">Masuk</a>
                        <a href="/register/" class="px-4 py-2 bg-gradient-brand text-white text-sm font-medium rounded-lg hover:opacity-90 transition-opacity shadow-lg glow-orange-sm">Daftar</a>
<?php endif; ?>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Progress Bar -->
        <div class="bg-[var(--bg-secondary)] border-b border-[var(--border-default)] px-4 py-4">
            <div class="max-w-7xl mx-auto">
                <div class="flex items-center justify-between mb-3 text-xs text-[var(--text-muted)]" id="phaseLabels">
                    <span>Overview</span><span>PRD</span><span>Branding</span><span>Logo</span>
                    <span>Landing</span><span>Login</span><span>Register</span><span>Manajemen</span>
                    <span>Admin</span><span>Client</span><span>Server</span><span>Path</span>
                </div>
                <div class="flex flex-col items-center mb-3 gap-1">
                    <span class="text-sm font-medium" id="stepLabel">Langkah 1 dari 12</span>
                    <div class="flex items-center gap-3">
                        <span id="saveStatus" class="text-xs text-green-400 hidden flex items-center gap-1">
                            <i class="ph-bold ph-check-circle"></i> <span id="saveStatusText">Tersimpan otomatis</span>
                        </span>
                        <span class="text-sm text-[var(--text-muted)]" id="stepName"><i class="ph ph-map-trifold"></i> Overview</span>
                    </div>
                </div>
                <div class="flex items-center justify-center pb-1" id="stepsDots"></div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div id="eduBanner" class="mb-6 p-4 bg-gradient-to-r from-[var(--brand-primary-light)] to-[var(--bg-card)] border border-[var(--brand-primary)]/20 rounded-2xl flex items-start gap-4">
                    <div class="w-10 h-10 rounded-lg bg-[var(--brand-primary)]/10 flex items-center justify-center text-[var(--brand-primary)] shrink-0"><i class="ph ph-info text-2xl"></i></div>
                    <div>
                        <h4 class="font-bold text-sm text-[var(--text-primary)] mb-1" id="eduTitle">Gunakan Konsep ini Sebagai Referensi AI</h4>
                        <p class="text-xs text-[var(--text-secondary)] leading-relaxed" id="eduDesc">File yang Anda edit di sini akan langsung disimpan ke project lokal.</p>
                    </div>
                </div>
                <div id="wizardContent" class="step-content"></div>

                <div class="mt-8 flex items-center justify-between gap-3">
                    <button id="prevBtn" onclick="prevStep()" class="hidden px-6 py-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl font-medium hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-2">
                        <i class="ph ph-arrow-left"></i> Kembali
                    </button>
                    <div class="flex-1"></div>
                    <div class="flex items-center gap-2">
                        <button id="nextBtn" onclick="nextStep()" class="hidden px-6 py-3 bg-[var(--brand-primary)] text-white font-medium rounded-xl hover:opacity-90 transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            Lanjut <i class="ph ph-arrow-right"></i>
                        </button>
                        <button id="finishBtn" onclick="finishWizard()" class="hidden px-6 py-3 bg-green-500 text-white font-medium rounded-xl hover:opacity-90 transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="ph ph-check"></i> Konfirmasi & Lanjut
                        </button>
                        <button id="executeBtn" onclick="executeTerminal()" class="hidden px-6 py-3 bg-green-500 text-white font-medium rounded-xl hover:opacity-90 transition-colors flex items-center gap-2 disabled:opacity-60 disabled:cursor-not-allowed">
                            <i class="ph ph-terminal"></i> Buka Terminal
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-black/60 backdrop-blur-sm flex items-center justify-center z-50" onclick="closeSuccessModal()" role="dialog" aria-modal="true">
        <div class="bg-[var(--bg-card)] rounded-2xl p-8 max-w-md mx-4 text-center border border-[var(--border-default)] relative" onclick="event.stopPropagation()">
            <button onclick="closeSuccessModal()" class="absolute top-4 right-4 w-8 h-8 rounded-lg flex items-center justify-center hover:bg-[var(--bg-hover)] transition-colors" aria-label="Close"><i class="ph ph-x text-lg"></i></button>
            <div class="w-20 h-20 rounded-full bg-green-500/20 flex items-center justify-center mx-auto mb-6 success-check"><i class="ph ph-check-circle text-5xl text-green-500"></i></div>
            <h2 class="text-2xl font-bold mb-4">Semua File Sudah Disimpan!</h2>
            <div class="bg-[var(--bg-primary)] rounded-xl p-4 text-left mb-6">
                <p class="text-sm text-[var(--text-secondary)] mb-3">File yang sudah diedit:</p>
                <ul id="savedFilesList" class="text-xs text-[var(--text-secondary)] space-y-1.5"></ul>
                <p class="text-sm text-[var(--text-secondary)] mt-4 mb-2">Jalankan AI coding assistant:</p>
                <div class="flex items-center gap-2 bg-[var(--bg-card)] p-2 rounded border border-[var(--border-default)] mb-2">
                    <code class="text-[var(--brand-primary)] font-mono text-sm flex-1">baca dan jalankan @docs/install.md </code>
                    <button onclick="copyModalInstallCommand()" class="px-2.5 py-1 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-lg text-xs font-medium hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1 shrink-0" title="Salin command"><i class="ph ph-copy"></i> Salin</button>
                </div>
                <p class="text-xs text-[var(--text-muted)] mt-2">AI akan menempa kode fungsional dari konsep Anda.</p>
            </div>
            <a href="/" class="inline-flex items-center gap-2 px-6 py-3 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl font-medium hover:bg-[var(--bg-hover)] transition-colors">
                <i class="ph ph-house"></i> Kembali ke Beranda
            </a>
        </div>
    </div>

    <!-- Toast Notification -->
    <div id="toast" class="fixed bottom-6 left-1/2 -translate-x-1/2 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl px-6 py-4 shadow-2xl flex items-center gap-3 z-50 transition-all duration-300 opacity-0 translate-y-4 pointer-events-none">
        <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center"><i class="ph ph-check-circle text-xl text-green-500" id="toastIcon"></i></div>
        <div><p class="font-medium text-sm" id="toastTitle">Berhasil!</p><p class="text-xs text-[var(--text-muted)]" id="toastMessage"></p></div>
    </div>

    <!-- Saving Overlay -->
    <div id="savingOverlay" class="hidden fixed inset-0 bg-black/50 backdrop-blur-xs flex items-center justify-center z-50">
        <div class="bg-[var(--bg-card)] border border-[var(--border-default)] rounded-2xl px-6 py-4 flex items-center gap-3 shadow-2xl">
            <i class="ph ph-circle-notch text-2xl text-[var(--brand-primary)] animate-spin"></i>
            <span class="text-sm font-medium text-[var(--text-primary)]">Menyimpan perubahan file...</span>
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
    // Use variables from header.php or URL params for auto-detected server/drive
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

    // Redesign mode: Overview (1) -> References (2) -> Logo (3) -> Server (4) -> Path (5)
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
            dot.className = 'step-dot shrink-0 w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs cursor-pointer';
            dot.setAttribute('data-step', s.id);
            dot.setAttribute('onclick', 'jumpToStep(' + s.id + ')');
            dot.setAttribute('title', s.name);
            dot.innerHTML = s.type === 'welcome' ? '<i class="ph ph-map-trifold"></i>' : s.id;
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
        document.getElementById('stepLabel').textContent = 'Langkah ' + currentStep + ' dari ' + totalSteps;
        var step = steps[currentStep - 1];
        document.getElementById('stepName').innerHTML = '<i class="ph ' + step.icon + '"></i> ' + step.name;

        var dots = document.querySelectorAll('.step-dot');
        var connectors = document.querySelectorAll('.step-connector');
        dots.forEach(function(dot, i) {
            dot.className = 'step-dot shrink-0 w-8 h-8 rounded-lg flex items-center justify-center font-bold text-xs cursor-pointer';
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
            '<div class="text-center">' +
                '<div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-brand mb-6 glow-orange">' +
                    '<i class="ph ph-magic-wand text-4xl text-white"></i>' +
                '</div>' +
                '<h2 class="text-3xl sm:text-4xl font-heading font-bold mb-3">Setup Wizard</h2>' +
                '<p class="text-[var(--text-secondary)] max-w-xl mx-auto">Pilih jenis alur persiapan proyek aplikasi Anda di bawah ini:</p>' +
            '</div>' +

            '<!-- Mode Selection Cards -->' +
            '<div class="grid grid-cols-1 md:grid-cols-2 gap-6">' +
                '<div onclick="setAppMode(\'new\')" class="cursor-pointer p-6 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-[var(--brand-primary)] ' + (appMode === 'new' ? 'border-[var(--brand-primary)] bg-[var(--brand-primary-light)]/10 shadow-lg glow-orange-sm' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-4">' +
                        '<div class="w-12 h-12 rounded-xl bg-[var(--brand-primary)]/10 flex items-center justify-center text-[var(--brand-primary)]"><i class="ph ph-sparkle text-2xl"></i></div>' +
                        '<span class="text-xs font-bold px-2.5 py-1 rounded-full ' + (appMode === 'new' ? 'bg-[var(--brand-primary)] text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">Default Flow</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-lg mb-2">Membuat Aplikasi Baru</h3>' +
                    '<p class="text-xs text-[var(--text-secondary)] leading-relaxed mb-4">Alur lengkap 12 langkah: Susun PRD, Branding, Logo, hingga kustomisasi 6 template HTML referensi.</p>' +
                    '<ul class="text-xs text-[var(--text-muted)] space-y-1.5 border-t border-[var(--border-default)] pt-3">' +
                        '<li class="flex items-center gap-1.5"><i class="ph ph-check text-green-400"></i> Editor dokumen PRD & Branding</li>' +
                        '<li class="flex items-center gap-1.5"><i class="ph ph-check text-green-400"></i> Editor 6 template HTML visual</li>' +
                    '</ul>' +
                '</div>' +

                '<div onclick="setAppMode(\'redesign\')" class="cursor-pointer p-6 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-[var(--brand-primary)] ' + (appMode === 'redesign' ? 'border-[var(--brand-primary)] bg-[var(--brand-primary-light)]/10 shadow-lg glow-orange-sm' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-4">' +
                        '<div class="w-12 h-12 rounded-xl bg-purple-500/10 flex items-center justify-center text-purple-400"><i class="ph ph-paint-brush text-2xl"></i></div>' +
                        '<span class="text-xs font-bold px-2.5 py-1 rounded-full ' + (appMode === 'redesign' ? 'bg-purple-500 text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">Quick Flow</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-lg mb-2">Redesain Aplikasi</h3>' +
                    '<p class="text-xs text-[var(--text-secondary)] leading-relaxed mb-4">Langkah praktis 5 langkah: Upload Logo, kelola folder References, konfigurasi Server & Path. PRD & Branding dibuat saat menjalankan @docs/install.md</p>' +
                    '<ul class="text-xs text-[var(--text-muted)] space-y-1.5 border-t border-[var(--border-default)] pt-3">' +
                        '<li class="flex items-center gap-1.5"><i class="ph ph-check text-purple-400"></i> Upload Logo + Kelola References</li>' +
                        '<li class="flex items-center gap-1.5"><i class="ph ph-check text-purple-400"></i> PRD & Branding otomatis dari References</li>' +
                    '</ul>' +
                '</div>' +
            '</div>' +

            '<div class="text-center pt-2" id="welcomeCTA">' +
                '<button onclick="startEditor()" class="inline-flex items-center gap-2 px-8 py-4 bg-gradient-brand text-white font-bold rounded-xl hover:opacity-90 transition-opacity glow-orange text-lg shadow-lg cursor-pointer">' +
                    '<i class="ph ph-rocket-launch"></i> Mulai Persiapan <i class="ph ph-arrow-right"></i>' +
                '</button>' +
            '</div>' +
        '</div>';
    }

    function renderCodeEditorStep(title, targetFile, dataKey, lang) {
        var fallbackNotice = window.monacoLoadFailed ? '<div class="fallback-notice mb-3"><i class="ph ph-warning"></i> Monaco Editor tidak tersedia. Menggunakan text editor dasar.</div>' : '';
        return '<div class="space-y-4">' +
            '<div class="flex items-center justify-between flex-wrap gap-2">' +
                '<div><h2 class="text-2xl font-bold mb-1">' + title + '</h2><p class="text-xs text-[var(--text-secondary)]">Mengedit: <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono">' + targetFile + '</code></p></div>' +
                '<button id="copyBtn" class="px-4 py-2 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl text-xs font-semibold hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5 shadow-sm"><i class="ph ph-copy"></i> Salin Referensi</button>' +
            '</div>' +
            fallbackNotice +
            '<div id="monaco-editor-container" class="editor-container shadow-inner"></div>' +
            '<div class="flex items-center gap-4 text-xs text-[var(--text-muted)] border-t border-[var(--border-default)] pt-4">' +
                '<span>Atau timpa dengan upload file:</span>' +
                '<input type="file" id="fileInput" class="hidden" accept=".html,.md,.txt" onchange="handleFileUpload(this, \'' + dataKey + '\')">' +
                '<button onclick="document.getElementById(\'fileInput\').click()" class="px-3 py-1.5 bg-[var(--bg-surface)] hover:bg-[var(--bg-hover)] border border-[var(--border-default)] rounded-lg flex items-center gap-1.5 transition-colors"><i class="ph ph-upload-simple"></i> Upload File</button>' +
            '</div>' +
        '</div>';
    }

    function renderLogoUploadStep() {
        var existingLogo = formData.logoBase64 ? '<img src="data:image/png;base64,' + formData.logoBase64 + '" class="max-h-32 mx-auto mb-3 rounded-lg">' : '';
        var hasExisting = formData.logoBase64 || formData.logo;

        return '<div class="space-y-6">' +
            '<div><h2 class="text-2xl font-bold mb-2">Upload Logo</h2><p class="text-[var(--text-secondary)]">Upload file gambar PNG untuk logo aplikasi. Disimpan ke <code class="px-2 py-1 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono text-sm">docs/logo.png</code></p></div>' +
            '<div class="upload-zone" id="uploadZone" onclick="document.getElementById(\'logoInput\').click()">' +
                '<input type="file" id="logoInput" class="hidden" accept=".png,image/png" onchange="handleLogoUpload(this)">' +
                '<div id="logoPreviewContainer" class="' + (hasExisting ? '' : 'hidden') + '"><img id="logoPreview" class="max-h-32 mx-auto mb-3 rounded-lg"></div>' +
                '<div id="logoPlaceholder" class="' + (hasExisting ? 'hidden' : '') + '"><i class="ph ph-image text-4xl text-[var(--brand-primary)] mb-3"></i><p class="text-[var(--text-secondary)]">Klik untuk upload logo PNG</p><p class="text-xs text-[var(--text-muted)] mt-1">Format: PNG, maks 2MB</p></div>' +
            '</div>' +
            (hasExisting ? '<div class="flex items-center gap-2 text-green-400 text-sm"><i class="ph ph-check-circle"></i><span>' + (formData.logo ? 'Logo baru dipilih' : 'Logo sudah ada') + ': docs/logo.png</span></div>' : '') +
        '</div>';
    }

    function renderReferencesManagerStep() {
        return '<div class="space-y-6">' +
            '<div><h2 class="text-2xl font-bold mb-2">Manajemen Folder References</h2><p class="text-[var(--text-secondary)]">Kelola file referensi aplikasi Anda di folder <code class="px-2 py-1 bg-[var(--bg-surface)] rounded text-purple-400 font-mono text-sm">references/</code></p></div>' +

            '<div class="p-4 bg-purple-500/10 border border-purple-500/30 rounded-2xl flex items-start gap-3">' +
                '<i class="ph ph-info text-xl text-purple-400 shrink-0 mt-0.5"></i>' +
                '<div class="text-xs text-[var(--text-secondary)] space-y-1 leading-relaxed">' +
                    '<p class="font-semibold text-purple-300">Panduan Mode Redesain:</p>' +
                    '<p>1. Folder <code class="px-1 bg-[var(--bg-surface)] text-purple-300 font-mono">references/</code> telah otomatis dikosongkan untuk menampung referensi baru.</p>' +
                    '<p>2. Unggah file/folder referensi aplikasi lama, atau buka folder langsung di File Explorer.</p>' +
                    '<p>3. Dokumen <code class="px-1 bg-[var(--bg-surface)] text-purple-300 font-mono">docs/prd.md</code> dan <code class="px-1 bg-[var(--bg-surface)] text-purple-300 font-mono">docs/branding.md</code> akan otomatis disusun oleh AI saat menjalankan <code class="px-1 bg-[var(--bg-surface)] text-purple-300 font-mono">@docs/install.md</code>.</p>' +
                '</div>' +
            '</div>' +

            '<div class="bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] p-6 space-y-4">' +
                '<div class="flex items-center justify-between flex-wrap gap-3">' +
                    '<h3 class="font-heading font-semibold text-lg flex items-center gap-2"><i class="ph ph-folder-open text-purple-400"></i> Isi Folder references/</h3>' +
                    '<div class="flex items-center gap-2">' +
                        '<button onclick="openReferencesFolder()" class="px-3.5 py-1.5 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-xl text-xs font-semibold hover:bg-blue-500/20 transition-colors flex items-center gap-1.5" title="Buka folder di File Explorer"><i class="ph ph-folder-simple-open"></i> Buka Folder</button>' +
                        '<button onclick="clearReferencesFolder()" class="px-3.5 py-1.5 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl text-xs font-semibold hover:bg-red-500/20 transition-colors flex items-center gap-1.5"><i class="ph ph-trash"></i> Bersihkan</button>' +
                        '<button onclick="loadReferencesList()" class="px-3.5 py-1.5 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl text-xs font-semibold hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5"><i class="ph ph-arrows-clockwise"></i> Refresh</button>' +
                    '</div>' +
                '</div>' +

                '<div id="refFilesContainer" class="min-h-[120px] bg-[var(--bg-primary)] rounded-xl p-4 border border-[var(--border-default)] flex flex-col justify-center items-center text-center">' +
                    '<i class="ph ph-circle-notch text-2xl text-[var(--brand-primary)] animate-spin"></i>' +
                    '<p class="text-xs text-[var(--text-muted)] mt-2">Mendeteksi isi folder...</p>' +
                '</div>' +

                '<div class="pt-2 border-t border-[var(--border-default)]">' +
                    '<p class="text-xs text-[var(--text-muted)] mb-3">Kelola atau unggah file/folder referensi baru:</p>' +
                    '<input type="file" id="refFileInput" class="hidden" multiple onchange="handleRefUpload(this)">' +
                    '<input type="file" id="refFolderInput" class="hidden" webkitdirectory directory multiple onchange="handleRefUpload(this)">' +
                    '<div class="flex items-center gap-2 flex-wrap">' +
                        '<button onclick="openReferencesFolder()" class="px-3.5 py-2 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-xl font-medium hover:bg-blue-500/20 transition-colors flex items-center gap-1.5 shadow-sm" title="Buka folder di File Explorer"><i class="ph ph-folder-simple-open"></i> Buka Folder</button>' +
                        '<button onclick="document.getElementById(\'refFileInput\').click()" class="px-3.5 py-2 bg-[var(--bg-surface)] border border-[var(--border-default)] text-[var(--text-primary)] rounded-xl font-medium hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5 shadow-sm"><i class="ph ph-file-plus"></i> Upload File</button>' +
                        '<button onclick="document.getElementById(\'refFolderInput\').click()" class="px-3.5 py-2 bg-purple-600 text-white rounded-xl font-medium hover:bg-purple-500 transition-colors flex items-center gap-1.5 shadow-sm"><i class="ph ph-folder-plus"></i> Upload Folder</button>' +
                    '</div>' +
                '</div>' +
            '</div>' +
        '</div>';
    }

    // Buka folder references/ di File Explorer via AJAX call to backend
    async function openReferencesFolder() {
        showToast('Membuka Folder', 'Membuka folder references/ di File Explorer...');
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
                showToast('Folder Terbuka', 'File Explorer telah dibuka di folder references/');
            } else {
                showToast('Gagal', data.error || 'Gagal membuka folder', true);
            }
        } catch(e) {
            showToast('Gagal', 'Gagal menghubungi server', true);
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
                container.innerHTML = '<div class="py-6"><i class="ph ph-folder-open text-4xl text-[var(--text-muted)] mb-2 block"></i><p class="text-sm text-[var(--text-secondary)] font-medium">Folder references/ Kosong</p><p class="text-xs text-[var(--text-muted)] mt-1">Silakan upload file/folder referensi Anda atau masukkan file ke folder tersebut.</p></div>';
            } else {
                var html = '<div class="w-full grid grid-cols-1 sm:grid-cols-2 gap-3 text-left">';
                refFiles.forEach(function(file) {
                    var isDir = file.is_dir;
                    var icon = isDir ? 'ph-folder-simple text-amber-400' : 'ph-file-code text-purple-400';
                    var bg = isDir ? 'bg-amber-500/10' : 'bg-purple-500/10';
                    var kb = isDir ? 'Folder' : (file.size / 1024).toFixed(1) + ' KB';

                    html += '<div class="p-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl flex items-center gap-3">' +
                        '<div class="w-9 h-9 rounded-lg ' + bg + ' flex items-center justify-center shrink-0"><i class="ph ' + icon + ' text-lg"></i></div>' +
                        '<div class="flex-1 min-w-0"><p class="text-xs font-semibold text-[var(--text-primary)] truncate">' + file.name + '</p><p class="text-[10px] text-[var(--text-muted)]">' + kb + ' · ' + file.updated_at + '</p></div>' +
                    '</div>';
                });
                html += '</div>';
                container.innerHTML = html;
            }
        } catch(e) {
            container.innerHTML = '<p class="text-xs text-red-400">Gagal mendeteksi isi folder references</p>';
        }
    }

    async function clearReferencesFolder() {
        if (!confirm('Apakah Anda yakin ingin menghapus seluruh file HTML bawaan di folder references/?')) return;
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
                : 'bg-[var(--bg-surface)] text-[var(--text-secondary)] border-[var(--border-default)] hover:bg-[var(--bg-hover)]';

            return '<button type="button" onclick="updateDrive(\'' + d + '\')" class="shrink-0 px-4 py-2.5 rounded-xl border text-sm transition-all flex items-center gap-1.5 ' + activeClass + '">' +
                '<i class="ph ph-hard-drive text-base"></i> Disk (' + d + ':)' +
            '</button>';
        }).join('');

        return '<div class="space-y-6">' +
            '<div><h2 class="text-2xl font-bold mb-2">Pilih Local Server</h2><p class="text-[var(--text-secondary)]">Aplikasi web server mana yang Anda gunakan?</p></div>' +
            '<div class="grid grid-cols-2 gap-4">' +
                '<label class="cursor-pointer">' +
                    '<input type="radio" name="serverType" value="laragon" class="hidden peer" ' + (server === 'laragon' ? 'checked' : '') + ' onchange="updateServerType(\'laragon\')">' +
                    '<div class="p-6 bg-[var(--bg-card)] border-2 rounded-xl transition-all peer-checked:border-[var(--brand-primary)] peer-checked:bg-[var(--brand-primary-light)]/10">' +
                        '<div class="w-14 h-14 rounded-xl bg-green-500/10 flex items-center justify-center mb-4"><i class="ph ph-bug text-3xl text-green-500"></i></div>' +
                        '<h3 class="font-semibold text-lg mb-1">Laragon</h3><p class="text-sm text-[var(--text-muted)]">Full stack dev environment</p><p class="text-xs text-[var(--text-muted)] mt-2 font-mono" id="laragonPathPreview">' + currentDrive + ':\\laragon\\www</p>' +
                    '</div>' +
                '</label>' +
                '<label class="cursor-pointer">' +
                    '<input type="radio" name="serverType" value="xampp" class="hidden peer" ' + (server === 'xampp' ? 'checked' : '') + ' onchange="updateServerType(\'xampp\')">' +
                    '<div class="p-6 bg-[var(--bg-card)] border-2 rounded-xl transition-all peer-checked:border-[var(--brand-primary)] peer-checked:bg-[var(--brand-primary-light)]/10">' +
                        '<div class="w-14 h-14 rounded-xl bg-blue-500/10 flex items-center justify-center mb-4"><i class="ph ph-file-css text-3xl text-blue-500"></i></div>' +
                        '<h3 class="font-semibold text-lg mb-1">XAMPP</h3><p class="text-sm text-[var(--text-muted)]">Apache + PHP + MySQL</p><p class="text-xs text-[var(--text-muted)] mt-2 font-mono" id="xamppPathPreview">' + currentDrive + ':\\xampp\\htdocs</p>' +
                    '</div>' +
                '</label>' +
            '</div>' +
            '<div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6">' +
                '<label class="form-label font-semibold mb-3 block">Pilih Local Disk Lokasi Server</label>' +
                '<div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-thin hide-scrollbar" id="driveContainer">' +
                    driveButtons +
                '</div>' +
                '<p class="text-xs text-[var(--text-muted)] mt-3">Server path yang dikonfigurasi: <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono" id="serverFullPathPreview">' + fullPathPreview + '</code></p>' +
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
            '<div><h2 class="text-2xl font-bold mb-2">Lokasi Instalasi</h2><p class="text-[var(--text-secondary)]">Di mana Anda ingin menyimpan project ini?</p></div>' +

            '<div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6">' +
                '<div class="flex items-center justify-between mb-3">' +
                    '<h3 class="font-medium flex items-center gap-2"><i class="ph ph-folder-simple text-[var(--brand-primary)]"></i> Project Path</h3>' +
                    '<button onclick="copyProjectPath()" class="px-3 py-1 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-lg text-xs font-medium hover:bg-[var(--bg-hover)] transition-colors flex items-center gap-1.5"><i class="ph ph-copy"></i> Salin Path</button>' +
                '</div>' +
                '<div class="bg-[var(--bg-primary)] rounded-lg p-4 font-mono text-sm mb-3"><span class="text-[var(--text-muted)]">$ cd </span><span class="text-[var(--brand-primary)]" id="pathPreview">' + projectPath + '</span></div>' +
                '<p class="text-xs text-[var(--text-muted)]">Nama folder project default: <code class="px-1.5 py-0.5 bg-[var(--bg-surface)] rounded text-[var(--brand-primary)] font-mono font-semibold">' + folderName + '</code> (Dapat diubah nama foldernya setelah instalasi selesai).</p>' +
            '</div>' +

            '<div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6">' +
                '<h3 class="font-medium mb-2 flex items-center gap-2"><i class="ph ph-terminal text-[var(--brand-primary)]"></i> Jalankan Terminal</h3>' +
                '<p class="text-xs text-[var(--text-muted)] mb-4">Semua file konsep sudah siap! Klik tombol di bawah untuk langsung membuka PowerShell & menjalankan Claude Code di folder project ini.</p>' +
                '<button onclick="executeTerminal()" class="w-full py-3 bg-gradient-brand text-white font-bold rounded-xl hover:opacity-90 transition-opacity glow-orange flex items-center justify-center gap-2 shadow-lg cursor-pointer">' +
                    '<i class="ph ph-play text-xl"></i> Buka Terminal & Jalankan Claude Code' +
                '</button>' +
            '</div>' +

            '<div class="bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] p-6">' +
                '<h3 class="font-medium mb-3 flex items-center gap-2"><i class="ph ph-clipboard text-[var(--brand-primary)]"></i> Copy Command</h3>' +
                '<div class="relative"><code class="block bg-[var(--bg-primary)] rounded-lg p-4 font-mono text-sm text-[var(--brand-primary)] select-all">baca dan jalankan @docs/install.md </code>' +
                '<button onclick="copyInstallCommand()" class="mt-3 w-full py-3 bg-[var(--bg-surface)] border border-[var(--border-default)] rounded-xl font-medium hover:bg-[var(--bg-hover)] transition-colors flex items-center justify-center gap-2"><i class="ph ph-copy"></i> Copy Command</button></div>' +
            '</div>' +
        '</div>';
    }

    function copyProjectPath() {
        var drive = formData.drive || 'C';
        var server = formData.serverType || 'laragon';
        var basePath = drive + ':\\' + (server === 'laragon' ? 'laragon\\www' : 'xampp\\htdocs');
        var projectPath = basePath + '\\vibeforge';

        var successCallback = function() {
            showToast('Tersalin!', 'Project Path berhasil disalin ke clipboard');
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
    var _pendingMonacoInit = null;

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
                    colors: { 'editor.background': '#161B22', 'editor.lineHighlightBackground': '#1f242c' }
                });
                monacoThemesDefined = true;
            }

            editor = monaco.editor.create(containerCheck, {
                value: formData[dataKey] || '',
                language: language === 'markdown' ? 'markdown' : 'html',
                theme: 'vibeforgeDark',
                fontSize: 14,
                fontFamily: 'Consolas, Monaco, monospace',
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
        container.innerHTML = '<textarea id="fallbackEditor" class="w-full h-full p-4 font-mono text-sm resize-none border-0 focus:outline-none" style="min-height: 400px; background-color: #161B22; color: #E6EDF3; line-height: 1.6;" oninput="handleTextareaInput(\'' + dataKey + '\', this.value)">' + (formData[dataKey] || '') + '</textarea>';
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
            statusText.textContent = 'Menyimpan otomatis...';
            status.className = 'text-xs text-yellow-400 flex items-center gap-1';
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
                        statusText.textContent = 'Tersimpan otomatis';
                        status.className = 'text-xs text-green-400 flex items-center gap-1';
                    }
                    if (step.file) triggerGraphifyUpdate(step.file);
                } else {
                    if (status) {
                        statusText.textContent = 'Gagal menyimpan';
                        status.className = 'text-xs text-red-400 flex items-center gap-1';
                    }
                }
            } catch(e) {
                if (status) {
                    statusText.textContent = 'Gagal koneksi';
                    status.className = 'text-xs text-red-400 flex items-center gap-1';
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
            showToast('Tersalin!', 'Isi ' + fileName + ' berhasil disalin ke clipboard');
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

    // startEditor - jump to step 2 (References or PRD depending on mode)
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
            showToast('Peringatan', 'Langkah tidak valid', true);
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
            // Generate install.md based on current config
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
            // 1. Save config first
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

            // 2. Generate install.md with current config
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

            // 3. Show success modal immediately so user can copy prompt
            showSuccessModal();

            // 4. Open terminal ONCE after 3 seconds delay
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
                list.innerHTML = '<li class="flex items-center gap-2 text-gray-400"><i class="ph ph-info"></i> Belum ada file yang diubah</li>';
            } else {
                savedFiles.forEach(function(f) {
                    var li = document.createElement('li');
                    li.className = 'flex items-center gap-2 text-green-400';
                    li.innerHTML = '<i class="ph ph-check"></i> ' + f;
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
            toastIcon.className = isError ? 'ph ph-warning-circle text-xl text-red-500' : 'ph ph-check-circle text-xl text-green-500';
        }

        toast.classList.remove('opacity-0', 'translate-y-4', 'pointer-events-none');
        toast.classList.add('opacity-100', 'translate-y-0');

        setTimeout(function() {
            toast.classList.add('opacity-0', 'translate-y-4', 'pointer-events-none');
            toast.classList.remove('opacity-100', 'translate-y-0');
        }, 3000);
    }

    function copyInstallCommand() {
        var commandText = 'baca dan jalankan @docs/install.md ';
        var successCallback = function() {
            showToast('Tersalin!', 'Command berhasil disalin ke clipboard');
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