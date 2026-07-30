<?php
/**
 * Vibeforge - Installation Wizard Shell
 * 4-Step Unified Flow
 *
 * Tahap 1: Install (Auto-Detect)
 * Tahap 2: Referensi (Opsional)
 * Tahap 3: Branding & Logo
 * Tahap 4: PRD (7 Bagian)
 */
defined('APP_ENTRY') or define('APP_ENTRY', true);

require_once __DIR__ . '/../../include/config.php';
require_once __DIR__ . '/../../include/helper.php';
require_once __DIR__ . '/../../core/session.php';
require_once __DIR__ . '/../../core/csrf.php';
require_once __DIR__ . '/header.php';
?>
<!DOCTYPE html>
<html lang="<?= $currentLang ?>" dir="<?= isRtlLanguage() ? 'rtl' : 'ltr' ?>" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Wizard — <?= APP_DISPLAY_NAME ?></title>
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 24 24' fill='%23F97316'%3E%3Cpath d='M12 23c-4.97 0-9-3.134-9-7 0-2.5 1.5-5.5 3-8.5 1.5-3 1.5-5 1.5-5s3 2.5 3 5.5c0 1.5-1 3-2 4 1-1.5 2-3.5 3-6 1.5 2.5 3 5.5 3 5.5s-1 2-2.5 4c1-1 1.5-2 1.5-2s2 1.5 2 3.5c0 .5-.5 1-1 1 1.5 0 2.5 1.5 2.5 3.5 0 3.866-4.03 7-9 7z'/%3E%3C/svg%3E">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fira+Code:wght@400;500;600&family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <script src="https://unpkg.com/@phosphor-icons/web"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        var origWarn = console.warn;
        console.warn = function() { if (arguments[0] && typeof arguments[0] === 'string' && arguments[0].includes('cdn.tailwindcss.com should not be used in production')) return; origWarn.apply(console, arguments); };
    </script>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/assets/css/branding.css">
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        brand: { primary: '#F97316', hover: '#EA580C', dark: '#0B0F17', card: '#111726', border: '#1E293B' }
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
        .tech-grid { background-image: linear-gradient(to right, rgba(255,255,255,0.03) 1px, transparent 1px), linear-gradient(to bottom, rgba(255,255,255,0.03) 1px, transparent 1px); background-size: 32px 32px; }
        .text-gradient { background: linear-gradient(135deg, #F97316 0%, #FBBF24 50%, #F59E0B 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .bg-gradient-brand { background: linear-gradient(135deg, #F97316 0%, #EA580C 100%); }
        .glow-orange { box-shadow: 0 0 35px rgba(249,115,22,0.25); }
        .glow-orange-sm { box-shadow: 0 0 20px rgba(249,115,22,0.15); }
        .glow-box-cyber { box-shadow: 0 0 0 1px rgba(249,115,22,0.2), 0 10px 30px -10px rgba(0,0,0,0.8); }
        .step-dot { transition: all 0.3s ease; }
        .step-dot.active { background: var(--brand-primary); border-color: var(--brand-primary); transform: scale(1.15); color: #fff; box-shadow: 0 0 15px rgba(249,115,22,0.4); }
        .step-dot.completed { background: #10B981; border-color: #10B981; color: #fff; }
        .step-dot.inactive { background: var(--bg-card); border-color: var(--border-default); color: var(--text-muted); }
        .step-connector { width: 28px; height: 2px; background: var(--border-default); transition: background 0.3s ease; }
        .step-connector.completed { background: #10B981; }
        .editor-container { min-height: 420px; border-radius: 12px; overflow: hidden; }
        .prd-form-field { width: 100%; padding: 0.625rem 0.75rem; background: var(--bg-primary); border: 1px solid var(--border-default); border-radius: 0.5rem; font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--text-primary); transition: border-color 0.2s; resize: vertical; }
        .prd-form-field:focus { border-color: var(--brand-primary); outline: none; }
        .prd-form-field-sm { padding: 0.5rem 0.625rem; background: var(--bg-primary); border: 1px solid var(--border-default); border-radius: 0.5rem; font-family: 'JetBrains Mono', monospace; font-size: 0.7rem; color: var(--text-primary); transition: border-color 0.2s; }
        .prd-form-field-sm:focus { border-color: var(--brand-primary); outline: none; }
        .hide-scrollbar::-webkit-scrollbar { display: none; }
        .hide-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .success-check { animation: successPop 0.5s ease; }
        @keyframes successPop { 0% { transform: scale(0); } 50% { transform: scale(1.2); } 100% { transform: scale(1); } }
        .check-item { transition: all 0.3s ease; }
        .check-pass { border-color: #10B981; }
        .check-fail { border-color: #EF4444; }
    </style>
</head>
<body class="min-h-screen flex flex-col antialiased tech-grid bg-[var(--bg-primary)]">
    <div class="flex-1 flex flex-col">

        <!-- Navbar -->
        <nav class="sticky top-0 w-full z-50 bg-[var(--bg-primary)]/85 backdrop-blur-xl border-b border-[var(--border-default)]">
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
                <div class="flex items-center justify-center gap-2 overflow-x-auto hide-scrollbar pb-1" id="stepsDots"></div>
                <div class="flex items-center justify-between gap-2 bg-[var(--bg-primary)] px-4 py-2.5 rounded-xl border border-[var(--border-default)]">
                    <div class="flex items-center gap-3">
                        <span class="px-2.5 py-0.5 rounded bg-orange-500/10 border border-orange-500/30 text-[var(--brand-primary)] font-mono text-xs font-bold" id="stepLabel">TAHAP 1/4</span>
                        <span class="text-xs font-heading font-bold text-[var(--text-primary)] flex items-center gap-2" id="stepName"><i class="ph ph-hard-drives text-[var(--brand-primary)]"></i> Install</span>
                    </div>
                    <div class="flex items-center gap-2 font-mono text-xs">
                        <span id="saveStatus" class="text-emerald-400 hidden flex items-center gap-1.5 font-bold">
                            <i class="ph-bold ph-check-circle text-sm"></i> <span id="saveStatusText">Auto-Saved</span>
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <main class="flex-1 px-4 py-8">
            <div class="max-w-4xl mx-auto">
                <div id="wizardContent" class="step-content"></div>

                <!-- Navigation Buttons -->
                <div id="navButtons" class="mt-8 flex items-center justify-center gap-3 pt-6 border-t border-[var(--border-default)] font-mono">
                    <button id="prevBtn" onclick="prevStep()" class="hidden px-6 py-3 bg-[var(--bg-card)] border border-[var(--border-default)] rounded-xl text-xs font-bold hover:border-[var(--brand-primary)] transition-all flex items-center gap-2">
                        <i class="ph ph-arrow-left text-sm"></i> SEBELUMNYA
                    </button>
                    <button id="nextBtn" onclick="nextStep()" class="hidden px-10 py-4 bg-gradient-brand text-white text-sm font-extrabold rounded-2xl hover:opacity-95 transition-all shadow-xl glow-orange flex items-center gap-2.5 tracking-wide">
                        <i class="ph ph-play text-lg"></i> MULAI
                    </button>
                    <button id="executeBtn" onclick="executeTerminal()" class="hidden px-8 py-3.5 bg-gradient-brand text-white text-xs font-bold rounded-xl hover:opacity-90 transition-all shadow-xl glow-orange flex items-center gap-2">
                        <i class="ph ph-terminal-window text-base"></i> JALANKAN AI
                    </button>
                </div>
            </div>
        </main>
    </div>

    <!-- Success Modal -->
    <div id="successModal" class="hidden fixed inset-0 bg-black/80 backdrop-blur-md flex items-center justify-center z-50 p-4" onclick="closeSuccessModal()" role="dialog" aria-modal="true">
        <div class="bg-gray-950 rounded-2xl p-8 max-w-md w-full border border-gray-800 shadow-2xl relative glow-box-cyber text-center" onclick="event.stopPropagation()">
            <button onclick="closeSuccessModal()" class="absolute top-4 right-4 w-8 h-8 rounded-lg flex items-center justify-center text-gray-400 hover:text-white transition-colors" aria-label="Close"><i class="ph ph-x text-lg"></i></button>
            <div class="w-16 h-16 rounded-2xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center mx-auto mb-5 success-check"><i class="ph ph-check-circle text-4xl text-emerald-400"></i></div>
            <h2 class="text-2xl font-heading font-extrabold mb-2">Siap Dieksekusi!</h2>
            <p class="text-xs text-[var(--text-secondary)] mb-4">File spesifikasi sudah diperbarui. <code class="text-[var(--brand-primary)]">docs/install.md</code> berisi instruksi lengkap termasuk file yang akan di-generate otomatis.</p>
            <div class="bg-black/60 rounded-xl p-4 text-left mb-4 border border-gray-800 font-mono text-xs">
                <p class="text-[11px] text-gray-400 mb-2 font-bold uppercase tracking-wider">// Saved Specifications:</p>
                <ul id="savedFilesList" class="space-y-1.5 mb-4 max-h-40 overflow-y-auto hide-scrollbar"></ul>
                <p class="text-[11px] text-gray-400 mb-2 font-bold uppercase tracking-wider">// Claude Code Command:</p>
                <div class="flex items-center gap-2 bg-gray-900 p-2.5 rounded-lg border border-gray-800">
                    <code class="text-orange-400 font-mono text-xs flex-1 truncate" id="modalCommandText">baca dan jalankan @docs/install.md</code>
                    <button onclick="copyAndLaunchClaude()" class="px-3 py-1.5 bg-gradient-brand text-white rounded text-[11px] font-bold transition-all hover:opacity-90 shrink-0 flex items-center gap-1.5 shadow-md"><i class="ph ph-copy"></i> Copy & Jalankan</button>
                </div>
                <p class="text-[10px] text-gray-500 mt-2 leading-relaxed">Command akan disalin ke clipboard, lalu PowerShell terbuka otomatis dengan <code class="text-gray-400">claude</code> siap dijalankan — tinggal paste (Ctrl+V) dan Enter.</p>
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
    // =====================================================
    // Vibeforge Setup Wizard — 4-Step Unified Flow
    // =====================================================

    var csrfToken = '<?= $csrfToken ?>';
    var currentStep = 1;
    var totalSteps = 4;
    var editor = null;
    var autoSaveTimeout = null;
    var isNavigating = false;
    var savedFiles = new Set();
    var refFiles = [];
    var hasReferences = false; // YA/TIDAK state — locks Tahap 3 & 4 when false
    var brandingMode = 'manual'; // 'auto' | 'manual' — for Tahap 3 when YA
    var prdMode = 'manual'; // 'auto' | 'manual' — for Tahap 4 when YA
    var pageStructure = { landing: true, login: true, register: true, manajemen: true, admin: true, client: true };

    // Auto-detected environment data from PHP
    var envData = {
        appName: <?= $jsAppName ?>,
        projectPath: <?= $jsProjectPath ?>,
        localDomain: <?= $jsLocalDomain ?>,
        phpVersion: <?= $jsPhpVersion ?>,
        serverType: <?= $jsServerType ?>,
        serverLabel: <?= $jsServerLabel ?>,
        docRoot: <?= $jsDocRoot ?>
    };

    // Pre-loaded file data
    var formData = <?= json_encode($filesData) ?>;
    formData.logo = null;

    // =====================================================
    // Step Definitions — 4-Step Unified Flow
    // =====================================================
    var steps = [
        { id: 1, name: 'Install',    icon: 'ph-hard-drives',    type: 'install' },
        { id: 2, name: 'Referensi',  icon: 'ph-folder-simple',  type: 'references' },
        { id: 3, name: 'Branding',   icon: 'ph-palette',        type: 'branding' },
        { id: 4, name: 'PRD',        icon: 'ph-file-text',      type: 'prd' }
    ];

    // Default PRD template (7 bagian)
    var prdTemplate = '# PRD: ' + envData.appName + '\n' +
        '\n' +
        '## 1. Problem Statement\n' +
        '[Masalah apa yang mau diberesin? Siapa yang ngerasain? Kenapa solusi yang ada sekarang belum cukup?]\n' +
        '\n' +
        '## 2. Goals\n' +
        '- G1: [Tujuan] -> ukurannya: [metrik yang kelihatan]\n' +
        '- G2: [Tujuan] -> ukurannya: [metrik]\n' +
        '\n' +
        '## 3. Target User\n' +
        '- [Siapa mereka? Peran? Butuh apa? Masalah mereka apa? Harus konsisten dengan halaman yang dicentang di Tahap 3B.]\n' +
        '\n' +
        '## 4. User Stories\n' +
        '- US-1 (P1): Sebagai [user], saya ingin [aksi] supaya [manfaat].\n' +
        '- US-2 (P1): Sebagai [user], saya ingin [aksi] supaya [manfaat].\n' +
        '\n' +
        '## 5. Functional Requirements\n' +
        '- FR-1 (P1): [Sistem harus bisa ...]\n' +
        '- FR-2 (P1): [Sistem harus bisa ...]\n' +
        '- FR-3 (P2): [Sistem harus bisa ...]\n' +
        '\n' +
        '## 6. Non-Functional Requirements\n' +
        '- NFR-1 (P1): [Kecepatan / keamanan / skala ...]\n' +
        '\n' +
        '## 7. Scope\n' +
        'IN (versi 1.0): [fitur yang masuk sekarang]\n' +
        'OUT (nanti): [fitur yang ditunda]\n';

    // Default branding template (form fields as markdown)
    var brandingTemplate = '# Branding: ' + envData.appName + '\n' +
        '\n' +
        '## 1. Nama Aplikasi & Tagline\n' +
        '- Nama: ' + envData.appName + '\n' +
        '- Tagline: [Tagline aplikasi Anda]\n' +
        '\n' +
        '## 2. Deskripsi Singkat / Value Proposition\n' +
        '[Apa yang membuat aplikasi ini berbeda dan bernilai bagi pengguna?]\n' +
        '\n' +
        '## 3. Target Audience & Tone of Voice\n' +
        '- Target: [Siapa pengguna utama?]\n' +
        '- Tone: [formal / santai / profesional / kreatif]\n' +
        '\n' +
        '## 4. Palet Warna\n' +
        '- Primary: #F97316\n' +
        '- Secondary: #1E293B\n' +
        '- Accent: #10B981\n' +
        '\n' +
        '## 5. Typography\n' +
        '- Heading: Plus Jakarta Sans\n' +
        '- Body: Inter\n' +
        '\n' +
        '## 6. Logo & Asset Guidelines\n' +
        '[Upload logo di atas. Format: PNG/SVG, rekomendasi 512x512px.]\n';

    // =====================================================
    // Step Navigation
    // =====================================================
    function initSteps() {
        var dotsContainer = document.getElementById('stepsDots');
        if (!dotsContainer) return;
        dotsContainer.innerHTML = '';

        steps.forEach(function(s, i) {
            var dot = document.createElement('div');
            dot.className = 'step-dot border shrink-0 w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs cursor-pointer';
            dot.setAttribute('data-step', s.id);
            dot.setAttribute('onclick', 'jumpToStep(' + s.id + ')');
            dot.setAttribute('title', s.name);
            dot.innerHTML = '<i class="ph ' + s.icon + ' text-sm"></i>';
            dotsContainer.appendChild(dot);
            if (i < steps.length - 1) {
                var connector = document.createElement('div');
                connector.className = 'step-connector';
                dotsContainer.appendChild(connector);
            }
        });
    }

    function updateStepUI() {
        document.getElementById('stepLabel').textContent = 'TAHAP ' + currentStep + '/4';
        var step = steps[currentStep - 1];
        document.getElementById('stepName').innerHTML = '<i class="ph ' + step.icon + ' text-[var(--brand-primary)]"></i> ' + step.name;

        // Block steps beyond 2 if references YA but folder empty
        var refsBlocked = hasReferences === true && refFiles.length === 0;

        var dots = document.querySelectorAll('.step-dot');
        var connectors = document.querySelectorAll('.step-connector');
        dots.forEach(function(dot, i) {
            dot.className = 'step-dot border shrink-0 w-8 h-8 rounded-lg flex items-center justify-center font-mono font-bold text-xs cursor-pointer';
            if (i + 1 < currentStep) dot.classList.add('completed');
            else if (i + 1 === currentStep) dot.classList.add('active');
            else dot.classList.add('inactive');
            // Disable dots beyond step 2 if refs blocked
            if (refsBlocked && i + 1 > 2) {
                dot.classList.add('opacity-40', 'pointer-events-none');
            }
        });
        connectors.forEach(function(c, i) {
            c.className = 'step-connector';
            if (i + 1 < currentStep) c.classList.add('completed');
            if (refsBlocked && i + 1 >= 2) {
                c.classList.add('opacity-40');
            }
        });

        // Show/hide navigation buttons
        var isFirst = currentStep === 1;
        var isLast = currentStep === totalSteps;
        var prevBtn = document.getElementById('prevBtn');
        var nextBtn = document.getElementById('nextBtn');
        var executeBtn = document.getElementById('executeBtn');
        var navContainer = document.getElementById('navButtons');

        prevBtn.classList.toggle('hidden', isFirst);
        nextBtn.classList.toggle('hidden', isLast);

        // Step 4: hide nextBtn, show prevBtn (KEMBALI to step 3), show executeBtn
        if (isLast) {
            nextBtn.classList.add('hidden'); // Sembunyikan "SELANJUTNYA"
            // prevBtn tetap visible (tidak di-hide) -> menjadi "KEMBALI"
        }

        executeBtn.classList.toggle('hidden', !isLast);

        // Disable next if refs blocked on step 2
        if (currentStep === 2 && refsBlocked) {
            nextBtn.classList.add('opacity-40', 'pointer-events-none');
        } else {
            nextBtn.classList.remove('opacity-40', 'pointer-events-none');
        }

        // Step 1: centered CTA, steps 2-3: between layout, step 4: centered
        if (navContainer) {
            navContainer.style.justifyContent = (isFirst || isLast) ? 'center' : 'space-between';
        }

        // Update button text based on step
        if (isFirst) {
            nextBtn.innerHTML = '<i class="ph ph-sparkle text-lg"></i> MULAI BUAT APLIKASIMU';
            nextBtn.className = 'px-10 py-4 bg-gradient-brand text-white text-sm font-extrabold rounded-2xl hover:opacity-95 transition-all shadow-xl glow-orange flex items-center gap-2.5 tracking-wide';
        } else if (isLast) {
            // Step 4: prevBtn becomes "KEMBALI"
            prevBtn.innerHTML = '<i class="ph ph-arrow-left text-sm"></i> KEMBALI';
            nextBtn.classList.add('hidden');
        } else {
            nextBtn.innerHTML = 'SELANJUTNYA <i class="ph ph-arrow-right text-sm"></i>';
            nextBtn.className = 'px-6 py-3 bg-gradient-brand text-white text-xs font-bold rounded-xl hover:opacity-90 transition-all shadow-md glow-orange-sm flex items-center gap-2';
        }

        // Save status indicator
        var status = document.getElementById('saveStatus');
        if (step.type === 'install') {
            status.classList.add('hidden');
        }
    }

    function renderStep() {
        var content = document.getElementById('wizardContent');
        var step = steps[currentStep - 1];

        // Dispose Monaco editor if leaving an editor step
        if (editor) { editor.dispose(); editor = null; }

        switch (step.type) {
            case 'install':
                content.innerHTML = renderInstallStep();
                break;
            case 'references':
                content.innerHTML = renderReferencesStep();
                loadReferencesList();
                setupRefDropZone();
                break;
            case 'branding':
                content.innerHTML = renderBrandingStep();
                // Only init Monaco when manual mode with branding template (not form fields)
                // Form fields are used for manual branding now, so no Monaco needed
                break;
            case 'prd':
                content.innerHTML = renderPrdStep();
                // PRD uses form fields, no Monaco needed
                break;
        }

        updateStepUI();
    }

    function nextStep() {
        // Block if references step: YA selected but no files uploaded
        if (currentStep === 2 && hasReferences === true && refFiles.length === 0) {
            showToast('Referensi Kosong!', 'Upload minimal 1 file referensi, atau pilih TIDAK.', true);
            return;
        }
        if (currentStep < totalSteps) {
            saveCurrentStep();
            currentStep++;
            renderStep();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function prevStep() {
        if (currentStep > 1) {
            saveCurrentStep();
            currentStep--;
            renderStep();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    function jumpToStep(stepId) {
        if (stepId >= 1 && stepId <= totalSteps) {
            // Block if references step incomplete
            if (hasReferences === true && refFiles.length === 0 && stepId > 2) {
                showToast('Referensi Kosong!', 'Upload minimal 1 file referensi sebelum lanjut.', true);
                return;
            }
            saveCurrentStep();
            currentStep = stepId;
            renderStep();
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }
    }

    // =====================================================
    // TAHAP 1 — Install (Auto-Detect)
    // =====================================================
    function renderInstallStep() {
        var phpOk = compareVersions(envData.phpVersion, '8.3.0') >= 0;
        var domainOk = envData.localDomain && envData.localDomain.indexOf('.test') !== -1;
        var serverOk = envData.serverType === 'laragon' || envData.serverType === 'xampp';

        var checks = [
            { label: 'PHP Version', value: envData.phpVersion, ok: phpOk, detail: phpOk ? 'PHP 8.3+ terpenuhi' : 'Minimal PHP 8.3 diperlukan' },
            { label: 'Web Server', value: envData.serverLabel, ok: serverOk, detail: serverOk ? 'Server terdeteksi' : 'Laragon/XAMPP tidak terdeteksi' },
            { label: 'Local Domain', value: envData.localDomain, ok: domainOk, detail: domainOk ? 'Virtual host aktif' : 'Domain .test tidak terdeteksi' },
            { label: 'Project Path', value: envData.projectPath, ok: true, detail: 'Document root terdeteksi' }
        ];

        var allPass = checks.every(function(c) { return c.ok; });

        var checksHtml = checks.map(function(c) {
            var icon = c.ok ? '<i class="ph ph-check-circle text-emerald-400 text-xl"></i>' : '<i class="ph ph-warning-circle text-red-400 text-xl"></i>';
            var borderClass = c.ok ? 'check-pass' : 'check-fail';
            return '<div class="check-item flex items-center gap-4 p-4 bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] ' + borderClass + '">' +
                '<div class="w-10 h-10 rounded-lg ' + (c.ok ? 'bg-emerald-500/10 border border-emerald-500/20' : 'bg-red-500/10 border border-red-500/20') + ' flex items-center justify-center shrink-0">' + icon + '</div>' +
                '<div class="flex-1 min-w-0">' +
                    '<div class="flex items-center justify-between gap-2">' +
                        '<span class="font-mono text-xs font-bold text-[var(--text-primary)]">' + c.label + '</span>' +
                        '<span class="font-mono text-xs font-bold ' + (c.ok ? 'text-emerald-400' : 'text-red-400') + '">' + c.value + '</span>' +
                    '</div>' +
                    '<span class="text-[11px] text-[var(--text-muted)]">' + c.detail + '</span>' +
                '</div>' +
            '</div>';
        }).join('');

        // App name row — editable with inline rename
        var appNameRow = '<div class="check-item flex items-center gap-4 p-4 bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)] check-pass">' +
            '<div class="w-10 h-10 rounded-lg bg-orange-500/10 border border-orange-500/20 flex items-center justify-center shrink-0">' +
                '<i class="ph ph-app-window text-xl text-[var(--brand-primary)]"></i>' +
            '</div>' +
            '<div class="flex-1 min-w-0">' +
                '<div class="flex items-center gap-2 mb-1.5">' +
                    '<span class="font-mono text-xs font-bold text-[var(--text-primary)]">Nama Aplikasi</span>' +
                    '<span class="font-mono text-[10px] text-[var(--text-muted)]">(klik untuk ganti)</span>' +
                '</div>' +
                '<div class="flex items-center gap-2">' +
                    '<input type="text" id="appNameInput" value="' + envData.appName + '" ' +
                        'class="flex-1 px-3 py-2 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs font-bold text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors" ' +
                        'placeholder="nama-aplikasi" ' +
                        'pattern="[a-z][a-z0-9_-]*" ' +
                        'onkeydown="if(event.key===\'Enter\')renameApp()">' +
                    '<button onclick="renameApp()" id="renameBtn" class="px-4 py-2 bg-gradient-brand text-white text-xs font-bold rounded-lg hover:opacity-90 transition-all shadow-md flex items-center gap-1.5 shrink-0">' +
                        '<i class="ph ph-floppy-disk text-sm"></i> Ganti' +
                    '</button>' +
                '</div>' +
                '<div id="renameStatus" class="mt-1.5 hidden"></div>' +
            '</div>' +
        '</div>';

        return '<div class="space-y-8">' +
            '<div class="text-center max-w-xl mx-auto space-y-3">' +
                '<div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-orange-500/10 border border-orange-500/30 mb-2 glow-orange">' +
                    '<i class="ph ph-hard-drives text-4xl text-[var(--brand-primary)]"></i>' +
                '</div>' +
                '<h2 class="text-3xl sm:text-4xl font-heading font-extrabold tracking-tight">Tahap 1 — Install</h2>' +
                '<p class="text-[var(--text-secondary)] text-sm leading-relaxed">Status deteksi otomatis lingkungan server Anda. Ganti nama aplikasi jika perlu, lalu mulai.</p>' +
            '</div>' +

            '<div class="max-w-2xl mx-auto space-y-3">' +
                '<div class="flex items-center gap-2 mb-2">' +
                    '<span class="font-mono text-[10px] font-bold text-[var(--brand-primary)] tracking-widest uppercase">// AUTO-DETECT STATUS //</span>' +
                '</div>' +

                appNameRow +
                checksHtml +

            '</div>' +

            (allPass
                ? '<div class="text-center max-w-2xl mx-auto pt-4">' +
                    '<div class="p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-xl flex items-center gap-3">' +
                        '<i class="ph ph-shield-check text-2xl text-emerald-400"></i>' +
                        '<div class="text-left">' +
                            '<p class="font-heading font-bold text-sm text-emerald-400">Semua persyaratan terpenuhi</p>' +
                            '<p class="text-xs text-[var(--text-secondary)]">Lingkungan siap — klik tombol di bawah untuk mulai membuat aplikasi.</p>' +
                        '</div>' +
                    '</div>' +
                  '</div>'
                : '<div class="text-center max-w-2xl mx-auto pt-4">' +
                    '<div class="p-4 bg-red-500/5 border border-red-500/20 rounded-xl flex items-center gap-3">' +
                        '<i class="ph ph-warning text-2xl text-red-400"></i>' +
                        '<div class="text-left">' +
                            '<p class="font-heading font-bold text-sm text-red-400">Ada persyaratan yang belum terpenuhi</p>' +
                            '<p class="text-xs text-[var(--text-secondary)]">Perbaiki item yang ditandai merah sebelum melanjutkan.</p>' +
                        '</div>' +
                    '</div>' +
                  '</div>'
            ) +
        '</div>';
    }

    function renameApp() {
        var input = document.getElementById('appNameInput');
        var btn = document.getElementById('renameBtn');
        var status = document.getElementById('renameStatus');
        var newName = input.value.trim().toLowerCase();

        if (!newName || !/^[a-z][a-z0-9_-]*$/.test(newName)) {
            status.className = 'mt-1.5 text-xs font-mono text-red-400';
            status.textContent = 'Nama hanya boleh huruf kecil, angka, strip, underscore. Harus diawali huruf.';
            status.classList.remove('hidden');
            return;
        }

        if (newName === envData.appName) {
            status.className = 'mt-1.5 text-xs font-mono text-amber-400';
            status.textContent = 'Nama baru sama dengan nama saat ini.';
            status.classList.remove('hidden');
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="ph ph-circle-notch text-sm animate-spin"></i> Mengganti...';
        status.classList.add('hidden');

        fetch('/core/router.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module: 'install',
                action: 'rename_app',
                newName: newName,
                oldName: envData.appName,
                csrf_token: csrfToken
            })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                btn.innerHTML = '<i class="ph ph-check text-sm"></i> Berhasil!';
                status.className = 'mt-1.5 text-xs font-mono text-emerald-400';
                status.innerHTML = '<i class="ph ph-check-circle"></i> Aplikasi di-rename menjadi <strong>' + newName + '</strong>. Halaman akan dialihkan ke <code class="px-1 py-0.5 bg-[var(--bg-card)] rounded">' + data.newUrl + '</code> dalam 5 detik...';
                status.classList.remove('hidden');

                // Redirect to new domain after delay (folder rename + Laragon reload takes ~5s)
                setTimeout(function() {
                    window.location.href = data.newUrl;
                }, 5000);
            } else {
                btn.disabled = false;
                btn.innerHTML = '<i class="ph ph-floppy-disk text-sm"></i> Ganti';
                status.className = 'mt-1.5 text-xs font-mono text-red-400';
                status.textContent = data.error || 'Gagal mengganti nama aplikasi';
                status.classList.remove('hidden');
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            btn.innerHTML = '<i class="ph ph-floppy-disk text-sm"></i> Ganti';
            status.className = 'mt-1.5 text-xs font-mono text-red-400';
            status.textContent = 'Gagal menghubungi server';
            status.classList.remove('hidden');
        });
    }

    function compareVersions(a, b) {
        var pa = a.split('.').map(Number);
        var pb = b.split('.').map(Number);
        for (var i = 0; i < Math.max(pa.length, pb.length); i++) {
            var na = pa[i] || 0;
            var nb = pb[i] || 0;
            if (na > nb) return 1;
            if (na < nb) return -1;
        }
        return 0;
    }

    // =====================================================
    // TAHAP 2 — Referensi (Opsional)
    // =====================================================
    function renderReferencesStep() {
        var yaSelected = hasReferences === true;
        var tidakSelected = hasReferences === false;

        return '<div class="space-y-8">' +
            '<div class="text-center max-w-xl mx-auto space-y-3">' +
                '<div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-amber-500/10 border border-amber-500/30 mb-2">' +
                    '<i class="ph ph-folder-simple text-4xl text-amber-400"></i>' +
                '</div>' +
                '<h2 class="text-3xl sm:text-4xl font-heading font-extrabold tracking-tight">Tahap 2 — Referensi</h2>' +
                '<p class="text-[var(--text-secondary)] text-sm leading-relaxed">Sudah punya referensi HTML/CSS/JS atau PHP? Jawaban menentukan alur Tahap 3 & 4.</p>' +
            '</div>' +

            '<div class="max-w-2xl mx-auto space-y-4">' +

                // ===== YA / TIDAK Checklist =====
                '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +

                    // YA card
                    '<div onclick="setHasReferences(true)" class="cursor-pointer p-6 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-[var(--brand-primary)] glow-box-cyber ' + (yaSelected ? 'border-[var(--brand-primary)] bg-orange-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                        '<div class="flex items-center justify-between mb-4">' +
                            '<div class="w-12 h-12 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)]"><i class="ph ph-upload-simple text-2xl"></i></div>' +
                            '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (yaSelected ? 'bg-[var(--brand-primary)] text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">YA</span>' +
                        '</div>' +
                        '<h3 class="font-heading font-bold text-lg mb-2">Sudah Punya Referensi</h3>' +
                        '<p class="text-xs text-[var(--text-secondary)] leading-relaxed">Upload file HTML/CSS/JS/PHP — bisa desain baru maupun source aplikasi lama yang mau diredesain.</p>' +
                    '</div>' +

                    // TIDAK card
                    '<div onclick="setHasReferences(false)" class="cursor-pointer p-6 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-emerald-500 glow-box-cyber ' + (tidakSelected ? 'border-emerald-500 bg-emerald-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                        '<div class="flex items-center justify-between mb-4">' +
                            '<div class="w-12 h-12 rounded-xl bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center text-emerald-400"><i class="ph ph-sparkle text-2xl"></i></div>' +
                            '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (tidakSelected ? 'bg-emerald-500 text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">TIDAK</span>' +
                        '</div>' +
                        '<h3 class="font-heading font-bold text-lg mb-2">Mulai dari Nol</h3>' +
                        '<p class="text-xs text-[var(--text-secondary)] leading-relaxed">Struktur, halaman, dan role diturunkan sepenuhnya dari PRD di Tahap 4. Tidak ada template generik tetap.</p>' +
                    '</div>' +

                '</div>' +

                // ===== TIDAK info box =====
                (tidakSelected ?
                    '<div class="p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-xl flex items-start gap-3">' +
                        '<i class="ph ph-info text-xl text-emerald-400 mt-0.5 shrink-0"></i>' +
                        '<div class="text-xs text-[var(--text-secondary)] leading-relaxed">' +
                            '<p class="font-bold text-emerald-400 mb-1">Mode: Generate dari Nol</p>' +
                            '<p>Tidak ada file referensi di-upload. AI akan <strong>mengenerate otomatis</strong>:</p>' +
                            '<ul class="mt-1.5 space-y-1">' +
                                '<li>• `references/*.html` — template HTML untuk setiap halaman</li>' +
                                '<li>• `docs/branding.md` — brand identity otomatis</li>' +
                                '<li>• `docs/prd.md` — PRD 7 bagian otomatis</li>' +
                            '</ul>' +
                            '<p class="mt-1.5 text-amber-400 font-bold">⚠ Centang halaman yang diperlukan di Tahap 3B — yang tidak dicentang tidak akan di-generate.</p>' +
                        '</div>' +
                    '</div>'
                : '') +

                // ===== YA: Upload zone + folder browse + file list =====
                (yaSelected ?
                    '<div class="space-y-4">' +

                        // Upload zone (drag-drop + file input + folder input)
                        '<div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] glow-box-cyber">' +
                            '<div class="flex items-center justify-between mb-4">' +
                                '<h3 class="font-heading font-bold text-sm text-[var(--text-primary)]">Upload File Referensi</h3>' +
                                '<span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-amber-500/10 text-amber-400 border border-amber-500/20">OPSIONAL</span>' +
                            '</div>' +

                            // Drag-drop zone
                            '<div id="refDropZone" class="border-2 border-dashed border-[var(--border-default)] rounded-xl p-8 text-center hover:border-[var(--brand-primary)] transition-colors cursor-pointer" onclick="document.getElementById(\'refUploadInput\').click()">' +
                                '<i class="ph ph-cloud-arrow-up text-4xl text-[var(--text-muted)] mb-2"></i>' +
                                '<p class="text-xs text-[var(--text-secondary)]">Klik untuk upload atau drag file ke sini</p>' +
                                '<p class="text-[10px] text-[var(--text-muted)] mt-1">HTML, CSS, JS, PHP, atau file lain ke folder references/</p>' +
                            '</div>' +
                            '<input type="file" id="refUploadInput" class="hidden" multiple accept=".html,.css,.js,.json,.md,.txt,.png,.svg,.jpg,.php" onchange="uploadReferenceFiles(this)">' +

                            // Action buttons row
                            '<div class="flex items-center gap-3 mt-4">' +
                                '<button onclick="document.getElementById(\'refFolderInput\').click()" class="px-4 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] text-[var(--text-primary)] text-xs font-bold rounded-lg transition-all flex items-center gap-2">' +
                                    '<i class="ph ph-folder-open text-base text-[var(--brand-primary)]"></i> Upload Folder' +
                                '</button>' +
                                '<input type="file" id="refFolderInput" class="hidden" webkitdirectory multiple onchange="uploadReferenceFiles(this)">' +
                                '<button onclick="openReferencesFolder()" class="px-4 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] text-[var(--text-primary)] text-xs font-bold rounded-lg transition-all flex items-center gap-2">' +
                                    '<i class="ph ph-folder-open text-base text-amber-400"></i> Buka Folder Lokal' +
                                '</button>' +
                            '</div>' +
                        '</div>' +

                        // File list
                        '<div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)]">' +
                            '<div class="flex items-center justify-between mb-4">' +
                                '<h3 class="font-heading font-bold text-sm text-[var(--text-primary)]">File Referensi Saat Ini</h3>' +
                                '<span class="font-mono text-[10px] text-[var(--text-muted)]" id="refCountLabel">0 file</span>' +
                            '</div>' +
                            '<div id="refListContainer" class="space-y-2 max-h-64 overflow-y-auto hide-scrollbar">' +
                                '<p class="text-xs text-[var(--text-muted)] text-center py-4">Belum ada file referensi</p>' +
                            '</div>' +
                        '</div>' +

                    '</div>'
                : '') +

            '</div>' +
        '</div>';
    }

    function setHasReferences(value) {
        hasReferences = value;
        renderStep();
    }

    function openReferencesFolder() {
        fetch('/core/router.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module: 'install',
                action: 'open_folder',
                folder: 'references',
                csrf_token: csrfToken
            })
        }).catch(function(err) { console.error('Open folder error:', err); });
    }

    function setupRefDropZone() {
        var zone = document.getElementById('refDropZone');
        if (!zone) return;

        zone.addEventListener('dragover', function(e) {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.add('border-[var(--brand-primary)]', 'bg-orange-500/5');
        });
        zone.addEventListener('dragleave', function(e) {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('border-[var(--brand-primary)]', 'bg-orange-500/5');
        });
        zone.addEventListener('drop', function(e) {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('border-[var(--brand-primary)]', 'bg-orange-500/5');

            if (!e.dataTransfer || !e.dataTransfer.files.length) return;

            // Build a fake input-like object for uploadReferenceFiles
            var dtFiles = e.dataTransfer.files;
            uploadReferenceFilesFromList(dtFiles);
        });
    }

    function uploadReferenceFilesFromList(fileList) {
        if (!fileList || fileList.length === 0) return;
        showSavingOverlay();

        var promises = [];
        for (var i = 0; i < fileList.length; i++) {
            (function(file) {
                promises.push(new Promise(function(resolve) {
                    var reader = new FileReader();
                    reader.onload = function(e) {
                        // Preserve relative path for webkitdirectory uploads
                        var relativePath = file.webkitRelativePath || file.name;
                        fetch('/core/router.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({
                                module: 'install',
                                action: 'save',
                                file: 'references/' + relativePath,
                                content: e.target.result,
                                csrf_token: csrfToken
                            })
                        }).then(resolve).catch(resolve);
                    };
                    reader.readAsText(file);
                }));
            })(fileList[i]);
        }

        Promise.all(promises).then(function() {
            hideSavingOverlay();
            showToast('Berhasil!', fileList.length + ' item berhasil diunggah');
            loadReferencesList();
        });
    }

    function loadReferencesList() {
        var container = document.getElementById('refListContainer');
        if (!container) return;

        fetch('/core/router.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ module: 'install', action: 'ref_list', csrf_token: csrfToken })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            refFiles = data.files || [];
            var countLabel = document.getElementById('refCountLabel');
            if (countLabel) countLabel.textContent = refFiles.length + ' file';

            // Auto-detect: if files exist, set hasReferences = true
            if (refFiles.length > 0 && hasReferences === false) {
                hasReferences = true;
            }

            if (refFiles.length === 0) {
                container.innerHTML = '<p class="text-xs text-[var(--text-muted)] text-center py-4">Belum ada file referensi</p>';
            } else {
                container.innerHTML = refFiles.map(function(f) {
                    return '<div class="flex items-center justify-between p-3 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)]">' +
                        '<div class="flex items-center gap-2.5 min-w-0">' +
                            '<i class="ph ph-file text-base text-[var(--text-muted)] shrink-0"></i>' +
                            '<span class="font-mono text-xs text-[var(--text-primary)] truncate">' + f.name + '</span>' +
                        '</div>' +
                        '<button onclick="deleteReferenceFile(\'' + f.name + '\')" class="shrink-0 px-2 py-1 text-red-400 hover:bg-red-500/10 rounded transition-colors"><i class="ph ph-trash text-xs"></i></button>' +
                    '</div>';
                }).join('');
            }
            // Refresh step dots / next button state
            updateStepUI();
        })
        .catch(function() {
            container.innerHTML = '<p class="text-xs text-[var(--text-muted)] text-center py-4">Tidak dapat memuat daftar file</p>';
        });
    }

    async function uploadReferenceFiles(input) {
        if (!input.files || input.files.length === 0) return;
        uploadReferenceFilesFromList(input.files);
        input.value = '';
    }

    function deleteReferenceFile(name) {
        if (!confirm('Hapus file referensi "' + name + '"?')) return;

        fetch('/core/router.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module: 'install',
                action: 'delete_ref',
                file: name,
                csrf_token: csrfToken
            })
        })
        .then(function() { loadReferencesList(); })
        .catch(function() { showToast('Gagal!', 'Tidak dapat menghapus file', true); });
    }

    // =====================================================
    // TAHAP 3 — Branding & Logo
    // =====================================================
    function renderBrandingStep() {
        var noRef = hasReferences === false; // Tahap 2 = TIDAK
        var yaSelected = hasReferences === true;

        // When no refs: only manual form, auto option disabled
        // When has refs: show both auto/manual options
        var modeSelectorHtml = '';
        if (yaSelected) {
            var autoActive = brandingMode === 'auto';
            var manualActive = brandingMode === 'manual';
            modeSelectorHtml = '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">' +
                // AUTO card
                '<div onclick="setBrandingMode(\'auto\')" class="cursor-pointer p-5 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-[var(--brand-primary)] glow-box-cyber ' + (autoActive ? 'border-[var(--brand-primary)] bg-orange-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-3">' +
                        '<div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)]"><i class="ph ph-sparkle text-xl"></i></div>' +
                        '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (autoActive ? 'bg-[var(--brand-primary)] text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">OTOMATIS</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-sm mb-1">Branding Otomatis</h3>' +
                    '<p class="text-[11px] text-[var(--text-secondary)] leading-relaxed">Brand identity digenerate otomatis dari PRD final. Cukup upload logo — sisanya diturunkan saat AI mengeksekusi.</p>' +
                '</div>' +
                // MANUAL card
                '<div onclick="setBrandingMode(\'manual\')" class="cursor-pointer p-5 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-purple-500 glow-box-cyber ' + (manualActive ? 'border-purple-500 bg-purple-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-3">' +
                        '<div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400"><i class="ph ph-pencil-simple text-xl"></i></div>' +
                        '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (manualActive ? 'bg-purple-600 text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">MANUAL</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-sm mb-1">Isi Branding Manual</h3>' +
                    '<p class="text-[11px] text-[var(--text-secondary)] leading-relaxed">Edit form branding lengkap — nama, tagline, warna, typography. Template sudah terisi, tinggal ditimpa.</p>' +
                '</div>' +
            '</div>';
        }

        // No refs info banner
        var noRefBanner = noRef ?
            '<div class="p-4 bg-amber-500/5 border border-amber-500/20 rounded-xl flex items-start gap-3 mb-6">' +
                '<i class="ph ph-info text-xl text-amber-400 mt-0.5 shrink-0"></i>' +
                '<div class="text-xs text-[var(--text-secondary)] leading-relaxed">' +
                    '<p class="font-bold text-amber-400 mb-1">Mode: Branding Manual (Tahap 2 → TIDAK)</p>' +
                    '<p>Tidak ada referensi untuk di-audit — branding otomatis tidak tersedia. Isi form manual di bawah. Template sudah terisi sebagai placeholder, tinggal edit.</p>' +
                '</div>' +
            '</div>'
        : '';

        // Auto mode: only logo upload
        var autoContent = brandingMode === 'auto' && yaSelected ?
            '<div class="p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-xl flex items-start gap-3 mb-6">' +
                '<i class="ph ph-sparkle text-xl text-emerald-400 mt-0.5 shrink-0"></i>' +
                '<div class="text-xs text-[var(--text-secondary)] leading-relaxed">' +
                    '<p class="font-bold text-emerald-400 mb-1">Branding Otomatis Aktif</p>' +
                    '<p>Brand identity (tagline, deskripsi, tone, palet warna) akan digenerate otomatis dari hasil audit referensi saat AI mengeksekusi TAHAP 2.</p>' +
                    '<p class="mt-1">Upload logo di bawah — boleh skip pakai placeholder default.</p>' +
                '</div>' +
            '</div>'
        : '';

        // Manual form (always shown when noRef, or when manual selected)
        var showManual = noRef || brandingMode === 'manual';
        var manualFormHtml = showManual ?
            '<div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] glow-box-cyber">' +
                '<div class="flex items-center justify-between mb-5">' +
                    '<h3 class="font-heading font-bold text-sm text-[var(--text-primary)]">Brand Identity</h3>' +
                    '<span class="font-mono text-[10px] text-[var(--text-muted)]">Target: <code class="px-1.5 py-0.5 bg-[var(--bg-primary)] rounded text-[var(--brand-primary)]">docs/branding.md</code></span>' +
                '</div>' +
                '<div class="space-y-4">' +
                    // 1. Nama & Tagline
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                        '<div>' +
                            '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Nama Aplikasi</label>' +
                            '<input type="text" id="brandAppName" value="' + envData.appName + '" class="w-full px-3 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs font-bold text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors">' +
                        '</div>' +
                        '<div>' +
                            '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Tagline</label>' +
                            '<input type="text" id="brandTagline" value="[Tagline aplikasi Anda]" class="w-full px-3 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors">' +
                        '</div>' +
                    '</div>' +
                    // 2. Deskripsi Singkat
                    '<div>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Deskripsi Singkat / Value Proposition</label>' +
                        '<textarea id="brandDesc" rows="2" class="w-full px-3 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors resize-none" placeholder="Apa yang membuat aplikasi ini berbeda dan bernilai bagi pengguna?">[Apa yang membuat aplikasi ini berbeda dan bernilai bagi pengguna?]</textarea>' +
                    '</div>' +
                    // 3. Target Audience & Tone
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                        '<div>' +
                            '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Target Audience</label>' +
                            '<input type="text" id="brandAudience" value="[Siapa pengguna utama?]" class="w-full px-3 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors">' +
                        '</div>' +
                        '<div>' +
                            '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Tone of Voice</label>' +
                            '<select id="brandTone" class="w-full px-3 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors">' +
                                '<option value="formal">Formal</option>' +
                                '<option value="santai">Santai</option>' +
                                '<option value="profesional" selected>Profesional</option>' +
                                '<option value="kreatif">Kreatif</option>' +
                            '</select>' +
                        '</div>' +
                    '</div>' +
                    // 4. Palet Warna
                    '<div>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Palet Warna</label>' +
                        '<div class="grid grid-cols-3 gap-4">' +
                            '<div class="flex items-center gap-2">' +
                                '<input type="color" id="brandColorPrimary" value="#F97316" class="w-10 h-10 rounded-lg border border-[var(--border-default)] cursor-pointer shrink-0">' +
                                '<div class="flex-1 min-w-0">' +
                                    '<span class="font-mono text-[10px] font-bold text-[var(--text-primary)]">Primary</span>' +
                                    '<input type="text" id="brandColorPrimaryHex" value="#F97316" class="w-full px-2 py-1 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded font-mono text-[10px] text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none">' +
                                '</div>' +
                            '</div>' +
                            '<div class="flex items-center gap-2">' +
                                '<input type="color" id="brandColorSecondary" value="#1E293B" class="w-10 h-10 rounded-lg border border-[var(--border-default)] cursor-pointer shrink-0">' +
                                '<div class="flex-1 min-w-0">' +
                                    '<span class="font-mono text-[10px] font-bold text-[var(--text-primary)]">Secondary</span>' +
                                    '<input type="text" id="brandColorSecondaryHex" value="#1E293B" class="w-full px-2 py-1 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded font-mono text-[10px] text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none">' +
                                '</div>' +
                            '</div>' +
                            '<div class="flex items-center gap-2">' +
                                '<input type="color" id="brandColorAccent" value="#10B981" class="w-10 h-10 rounded-lg border border-[var(--border-default)] cursor-pointer shrink-0">' +
                                '<div class="flex-1 min-w-0">' +
                                    '<span class="font-mono text-[10px] font-bold text-[var(--text-primary)]">Accent</span>' +
                                    '<input type="text" id="brandColorAccentHex" value="#10B981" class="w-full px-2 py-1 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded font-mono text-[10px] text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none">' +
                                '</div>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                    // 5. Typography
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4">' +
                        '<div>' +
                            '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Heading Font</label>' +
                            '<input type="text" id="brandFontHeading" value="Plus Jakarta Sans" class="w-full px-3 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors">' +
                        '</div>' +
                        '<div>' +
                            '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider mb-1.5 block">Body Font</label>' +
                            '<input type="text" id="brandFontBody" value="Inter" class="w-full px-3 py-2.5 bg-[var(--bg-primary)] border border-[var(--border-default)] rounded-lg font-mono text-xs text-[var(--text-primary)] focus:border-[var(--brand-primary)] focus:outline-none transition-colors">' +
                        '</div>' +
                    '</div>' +
                '</div>' +
            '</div>'
        : '';

        // Logo upload (always available)
        var logoUploadHtml = '<div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] glow-box-cyber">' +
            '<div class="flex items-center justify-between mb-4">' +
                '<h3 class="font-heading font-bold text-sm text-[var(--text-primary)]">Logo Aplikasi</h3>' +
                '<span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-purple-500/10 text-purple-400 border border-purple-500/20">' + (brandingMode === 'auto' && yaSelected ? 'RECOMMENDED' : 'RECOMMENDED') + '</span>' +
            '</div>' +
            '<div class="flex items-center gap-6">' +
                '<div id="logoPreviewContainer" class="' + (formData.logoBase64 ? '' : 'hidden') + ' w-24 h-24 rounded-xl bg-[var(--bg-primary)] border border-[var(--border-default)] flex items-center justify-center overflow-hidden shrink-0">' +
                    '<img id="logoPreview" src="' + (formData.logoBase64 ? 'data:image/png;base64,' + formData.logoBase64 : '') + '" class="max-w-full max-h-full object-contain">' +
                '</div>' +
                '<div id="logoPlaceholder" class="' + (formData.logoBase64 ? 'hidden' : '') + ' w-24 h-24 rounded-xl bg-[var(--bg-primary)] border-2 border-dashed border-[var(--border-default)] flex items-center justify-center shrink-0">' +
                    '<i class="ph ph-image text-3xl text-[var(--text-muted)]"></i>' +
                '</div>' +
                '<div class="flex-1 space-y-2">' +
                    '<input type="file" id="logoInput" class="hidden" accept="image/png,image/svg+xml,image/jpeg" onchange="handleLogoUpload(this)">' +
                    '<button onclick="document.getElementById(\'logoInput\').click()" class="px-4 py-2.5 bg-gradient-brand text-white text-xs font-bold rounded-lg hover:opacity-90 transition-all shadow-md flex items-center gap-2">' +
                        '<i class="ph ph-upload-simple text-sm"></i> Upload Logo' +
                    '</button>' +
                    '<p class="text-[10px] text-[var(--text-muted)]">PNG, SVG, atau JPG. Disimpan ke docs/logo.png. Boleh skip pakai placeholder default.</p>' +
                '</div>' +
            '</div>' +
        '</div>';

        // === Tahap 3B — Struktur Halaman ===
        var structureHtml = '<div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)]">' +
            '<div class="flex items-center justify-between mb-4">' +
                '<h3 class="font-heading font-bold text-sm text-[var(--text-primary)]">Struktur Halaman</h3>' +
                '<span class="font-mono text-[10px] font-bold px-2 py-0.5 rounded bg-orange-500/10 text-orange-400 border border-orange-500/20">TAHAP 3B</span>' +
            '</div>' +
            '<p class="text-xs text-[var(--text-secondary)] mb-4">Centang halaman yang mau dibuat. Yang tidak dicentang tidak akan dibuat sama sekali, walau PRD menyebutnya.</p>' +
            '<div class="space-y-3">' +
                // Landing Page
                '<label class="flex items-center gap-3 p-3 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors cursor-pointer">' +
                    '<input type="checkbox" id="pageLanding" ' + (pageStructure.landing ? 'checked' : '') + ' onchange="updatePageStructure(\'landing\', this.checked)" class="w-4 h-4 rounded border-[var(--border-default)] text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><i class="ph ph-browser text-base text-[var(--brand-primary)]"></i><span class="font-mono text-xs font-bold text-[var(--text-primary)]">Landing Page</span></div>' +
                        '<span class="text-[10px] text-[var(--text-muted)]">Tidak dicentang = domain lokal langsung mengarah ke halaman Login.</span>' +
                    '</div>' +
                '</label>' +
                // Login (wajib)
                '<label class="flex items-center gap-3 p-3 bg-[var(--bg-primary)] rounded-xl border border-emerald-500/30 cursor-not-allowed opacity-80">' +
                    '<input type="checkbox" checked disabled class="w-4 h-4 rounded border-emerald-500 text-emerald-500">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><i class="ph ph-sign-in text-base text-emerald-400"></i><span class="font-mono text-xs font-bold text-[var(--text-primary)]">Login</span><span class="font-mono text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">WAJIB</span></div>' +
                        '<span class="text-[10px] text-[var(--text-muted)]">Halaman login selalu ada.</span>' +
                    '</div>' +
                '</label>' +
                // Register
                '<label class="flex items-center gap-3 p-3 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors cursor-pointer">' +
                    '<input type="checkbox" id="pageRegister" ' + (pageStructure.register ? 'checked' : '') + ' onchange="updatePageStructure(\'register\', this.checked)" class="w-4 h-4 rounded border-[var(--border-default)] text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><i class="ph ph-user-plus text-base text-[var(--brand-primary)]"></i><span class="font-mono text-xs font-bold text-[var(--text-primary)]">Register</span></div>' +
                        '<span class="text-[10px] text-[var(--text-muted)]">Tidak dicentang = akun baru hanya dibuat lewat Super Admin.</span>' +
                    '</div>' +
                '</label>' +
                // Manajemen (wajib)
                '<label class="flex items-center gap-3 p-3 bg-[var(--bg-primary)] rounded-xl border border-emerald-500/30 cursor-not-allowed opacity-80">' +
                    '<input type="checkbox" checked disabled class="w-4 h-4 rounded border-emerald-500 text-emerald-500">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><i class="ph ph-crown text-base text-emerald-400"></i><span class="font-mono text-xs font-bold text-[var(--text-primary)]">Halaman Manajemen / Super Admin</span><span class="font-mono text-[9px] px-1.5 py-0.5 rounded bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">WAJIB</span></div>' +
                        '<span class="text-[10px] text-[var(--text-muted)]">Dashboard Super Admin selalu ada.</span>' +
                    '</div>' +
                '</label>' +
                // Admin
                '<label class="flex items-center gap-3 p-3 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors cursor-pointer">' +
                    '<input type="checkbox" id="pageAdmin" ' + (pageStructure.admin ? 'checked' : '') + ' onchange="updatePageStructure(\'admin\', this.checked)" class="w-4 h-4 rounded border-[var(--border-default)] text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><i class="ph ph-rocket-launch text-base text-[var(--brand-primary)]"></i><span class="font-mono text-xs font-bold text-[var(--text-primary)]">Halaman Admin (Biasa)</span></div>' +
                        '<span class="text-[10px] text-[var(--text-muted)]">Tidak dicentang = hanya ada Super Admin, tidak ada level "admin biasa" terpisah.</span>' +
                    '</div>' +
                '</label>' +
                // Client
                '<label class="flex items-center gap-3 p-3 bg-[var(--bg-primary)] rounded-xl border border-[var(--border-default)] hover:border-[var(--brand-primary)] transition-colors cursor-pointer">' +
                    '<input type="checkbox" id="pageClient" ' + (pageStructure.client ? 'checked' : '') + ' onchange="updatePageStructure(\'client\', this.checked)" class="w-4 h-4 rounded border-[var(--border-default)] text-[var(--brand-primary)] focus:ring-[var(--brand-primary)]">' +
                    '<div class="flex-1 min-w-0">' +
                        '<div class="flex items-center gap-2"><i class="ph ph-headphones text-base text-[var(--brand-primary)]"></i><span class="font-mono text-xs font-bold text-[var(--text-primary)]">Halaman Client</span></div>' +
                        '<span class="text-[10px] text-[var(--text-muted)]">Tidak dicentang = tidak ada halaman client terpisah.</span>' +
                    '</div>' +
                '</label>' +
            '</div>' +
        '</div>';

        return '<div class="space-y-8">' +
            '<div class="text-center max-w-xl mx-auto space-y-3">' +
                '<div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-purple-500/10 border border-purple-500/30 mb-2">' +
                    '<i class="ph ph-palette text-4xl text-purple-400"></i>' +
                '</div>' +
                '<h2 class="text-3xl sm:text-4xl font-heading font-extrabold tracking-tight">Tahap 3 — Branding & Logo</h2>' +
                '<p class="text-[var(--text-secondary)] text-sm leading-relaxed">' + (noRef ? 'Isi form branding manual di bawah. Template sudah terisi — tinggal edit.' : 'Pilih mode branding: otomatis dari PRD atau isi manual. Lalu upload logo.') + '</p>' +
                '<button onclick="copyBrandPrompt()" class="mt-2 px-4 py-2 bg-[var(--bg-card)] hover:bg-[var(--bg-hover)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] rounded-xl text-xs font-bold text-[var(--text-primary)] transition-all inline-flex items-center gap-2 font-mono"><i class="ph ph-clipboard-text text-base text-[var(--brand-primary)]"></i> Copy Prompt AI</button>' +
            '</div>' +

            '<div class="max-w-2xl mx-auto space-y-6">' +

                noRefBanner +
                modeSelectorHtml +
                autoContent +

                logoUploadHtml +
                manualFormHtml +
                structureHtml +

            '</div>' +
        '</div>';
    }

    function setBrandingMode(mode) {
        brandingMode = mode;
        renderStep();
    }

    function updatePageStructure(key, checked) {
        pageStructure[key] = checked;
    }

    // Sync color picker with hex input
    document.addEventListener('input', function(e) {
        if (e.target.id === 'brandColorPrimary') { var hex = document.getElementById('brandColorPrimaryHex'); if (hex) hex.value = e.target.value; }
        if (e.target.id === 'brandColorSecondary') { var hex = document.getElementById('brandColorSecondaryHex'); if (hex) hex.value = e.target.value; }
        if (e.target.id === 'brandColorAccent') { var hex = document.getElementById('brandColorAccentHex'); if (hex) hex.value = e.target.value; }
        if (e.target.id === 'brandColorPrimaryHex' && /^#[0-9a-fA-F]{6}$/.test(e.target.value)) { var picker = document.getElementById('brandColorPrimary'); if (picker) picker.value = e.target.value; }
        if (e.target.id === 'brandColorSecondaryHex' && /^#[0-9a-fA-F]{6}$/.test(e.target.value)) { var picker = document.getElementById('brandColorSecondary'); if (picker) picker.value = e.target.value; }
        if (e.target.id === 'brandColorAccentHex' && /^#[0-9a-fA-F]{6}$/.test(e.target.value)) { var picker = document.getElementById('brandColorAccent'); if (picker) picker.value = e.target.value; }
    });

    function handleLogoUpload(input) {
        if (!input.files || !input.files[0]) return;
        var file = input.files[0];
        formData.logo = file;

        var reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('logoPreview').src = e.target.result;
            document.getElementById('logoPreviewContainer').classList.remove('hidden');
            document.getElementById('logoPlaceholder').classList.add('hidden');
        };
        reader.readAsDataURL(file);
    }

    function handleFileUpload(input, dataKey) {
        if (!input.files || !input.files[0]) return;
        var file = input.files[0];
        var reader = new FileReader();
        reader.onload = function(e) {
            formData[dataKey] = e.target.result;
            if (editor && typeof editor.setValue === 'function') {
                editor.setValue(e.target.result);
            }
            triggerAutoSave(dataKey, e.target.result);
        };
        reader.readAsText(file);
        input.value = '';
    }

    // =====================================================
    // TAHAP 4 — PRD (7 Bagian)
    // =====================================================
    function renderPrdStep() {
        var noRef = hasReferences === false; // Tahap 2 = TIDAK
        var yaSelected = hasReferences === true;

        // When no refs: only manual, auto disabled
        // When has refs: show both auto/manual options
        var modeSelectorHtml = '';
        if (yaSelected) {
            var autoActive = prdMode === 'auto';
            var manualActive = prdMode === 'manual';
            modeSelectorHtml = '<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6">' +
                // AUTO card
                '<div onclick="setPrdMode(\'auto\')" class="cursor-pointer p-5 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-[var(--brand-primary)] glow-box-cyber ' + (autoActive ? 'border-[var(--brand-primary)] bg-orange-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-3">' +
                        '<div class="w-10 h-10 rounded-xl bg-orange-500/10 border border-orange-500/30 flex items-center justify-center text-[var(--brand-primary)]"><i class="ph ph-sparkle text-xl"></i></div>' +
                        '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (autoActive ? 'bg-[var(--brand-primary)] text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">OTOMATIS</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-sm mb-1">Generate PRD Otomatis</h3>' +
                    '<p class="text-[11px] text-[var(--text-secondary)] leading-relaxed">AI akan generate PRD lengkap 7 bagian dari hasil audit referensi. Sertakan self-review 4 pertanyaan sebelum final.</p>' +
                '</div>' +
                // MANUAL card
                '<div onclick="setPrdMode(\'manual\')" class="cursor-pointer p-5 bg-[var(--bg-card)] rounded-2xl border-2 transition-all hover:border-purple-500 glow-box-cyber ' + (manualActive ? 'border-purple-500 bg-purple-500/5 shadow-lg' : 'border-[var(--border-default)]') + '">' +
                    '<div class="flex items-center justify-between mb-3">' +
                        '<div class="w-10 h-10 rounded-xl bg-purple-500/10 border border-purple-500/30 flex items-center justify-center text-purple-400"><i class="ph ph-pencil-simple text-xl"></i></div>' +
                        '<span class="font-mono text-[10px] font-bold px-2.5 py-1 rounded-full ' + (manualActive ? 'bg-purple-600 text-white' : 'bg-[var(--bg-surface)] text-[var(--text-muted)]') + '">MANUAL</span>' +
                    '</div>' +
                    '<h3 class="font-heading font-bold text-sm mb-1">Edit PRD Manual</h3>' +
                    '<p class="text-[11px] text-[var(--text-secondary)] leading-relaxed">Isi form 7 bagian sesuai kebutuhan aplikasi. Peringatan jika ada bagian belum diisi.</p>' +
                '</div>' +
            '</div>';
        }

        // No refs info banner
        var noRefBanner = noRef ?
            '<div class="p-4 bg-amber-500/5 border border-amber-500/20 rounded-xl flex items-start gap-3">' +
                '<i class="ph ph-info text-xl text-amber-400 mt-0.5 shrink-0"></i>' +
                '<div class="text-xs text-[var(--text-secondary)] leading-relaxed">' +
                    '<p class="font-bold text-amber-400 mb-1">Mode: PRD Manual (Tahap 2 → TIDAK)</p>' +
                    '<p>Tidak ada referensi untuk di-audit — PRD otomatis tidak tersedia. Isi form 7 bagian di bawah. Pastikan tiap bagian tidak kosong sebelum klik Jalankan.</p>' +
                '</div>' +
            '</div>'
        : '';

        // Auto mode content
        var autoContent = prdMode === 'auto' && yaSelected ?
            '<div class="p-4 bg-emerald-500/5 border border-emerald-500/20 rounded-xl flex items-start gap-3 mb-4">' +
                '<i class="ph ph-sparkle text-xl text-emerald-400 mt-0.5 shrink-0"></i>' +
                '<div class="text-xs text-[var(--text-secondary)] leading-relaxed">' +
                    '<p class="font-bold text-emerald-400 mb-1">PRD Otomatis Aktif</p>' +
                    '<p>AI akan generate PRD 7 bagian lengkap dari hasil audit referensi. Setelah generate, self-review 4 pertanyaan akan dijalankan sebelum PRD dianggap final.</p>' +
                    '<p class="mt-1">Upload logo di Tahap 3, lalu klik <strong>Jalankan AI</strong> untuk eksekusi.</p>' +
                '</div>' +
            '</div>'
        : '';

        // Manual mode: show structured form
        var showManual = noRef || prdMode === 'manual';

        // Initialize PRD content if empty
        if (!formData.prd || formData.prd.trim() === '') {
            formData.prd = prdTemplate;
        }

        // Parse existing PRD into sections for form pre-fill
        var prdSections = parsePrdToSections(formData.prd);

        var manualEditorHtml = showManual ?
            '<div class="p-6 bg-[var(--bg-card)] rounded-2xl border border-[var(--border-default)] glow-box-cyber space-y-5">' +
                '<div class="flex items-center justify-between">' +
                    '<h3 class="font-heading font-bold text-sm text-[var(--text-primary)]">Product Requirements Document</h3>' +
                    '<span class="font-mono text-[10px] text-[var(--text-muted)]">Target: <code class="px-1.5 py-0.5 bg-[var(--bg-primary)] rounded text-[var(--brand-primary)]">docs/prd.md</code></span>' +
                '</div>' +

                // Section 1: Problem Statement
                '<div class="space-y-2">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="w-6 h-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-[10px] font-bold text-emerald-400">1</span>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Problem Statement</label>' +
                    '</div>' +
                    '<textarea id="prdProblem" rows="3" class="prd-form-field" placeholder="Masalah apa yang mau diberesin? Siapa yang ngerasain? Kenapa solusi yang ada sekarang belum cukup?">' + escapeHtml(prdSections.problem) + '</textarea>' +
                '</div>' +

                // Section 2: Goals
                '<div class="space-y-2">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="w-6 h-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-[10px] font-bold text-emerald-400">2</span>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Goals</label>' +
                    '</div>' +
                    '<textarea id="prdGoals" rows="3" class="prd-form-field" placeholder="- G1: [Tujuan] -> ukurannya: [metrik yang kelihatan]&#10;- G2: [Tujuan] -> ukurannya: [metrik]">' + escapeHtml(prdSections.goals) + '</textarea>' +
                '</div>' +

                // Section 3: Target User
                '<div class="space-y-2">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="w-6 h-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-[10px] font-bold text-emerald-400">3</span>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Target User</label>' +
                    '</div>' +
                    '<textarea id="prdUsers" rows="3" class="prd-form-field" placeholder="Siapa mereka? Peran? Butuh apa? Masalah mereka apa?">' + escapeHtml(prdSections.users) + '</textarea>' +
                '</div>' +

                // Section 4: User Stories
                '<div class="space-y-2">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="w-6 h-6 rounded-md bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-[10px] font-bold text-emerald-400">4</span>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">User Stories</label>' +
                    '</div>' +
                    '<textarea id="prdStories" rows="3" class="prd-form-field" placeholder="- US-1 (P1): Sebagai [user], saya ingin [aksi] supaya [manfaat].&#10;- US-2 (P1): Sebagai [user], saya ingin [aksi] supaya [manfaat].">' + escapeHtml(prdSections.stories) + '</textarea>' +
                '</div>' +

                // Section 5: Functional Requirements
                '<div class="space-y-2">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="w-6 h-6 rounded-md bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-[10px] font-bold text-amber-400">5</span>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Functional Requirements</label>' +
                    '</div>' +
                    '<textarea id="prdFR" rows="3" class="prd-form-field" placeholder="- FR-1 (P1): [Sistem harus bisa ...]&#10;- FR-2 (P1): [Sistem harus bisa ...]&#10;- FR-3 (P2): [Sistem harus bisa ...]">' + escapeHtml(prdSections.fr) + '</textarea>' +
                '</div>' +

                // Section 6: Non-Functional Requirements
                '<div class="space-y-2">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="w-6 h-6 rounded-md bg-amber-500/10 border border-amber-500/20 flex items-center justify-center text-[10px] font-bold text-amber-400">6</span>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Non-Functional Requirements</label>' +
                    '</div>' +
                    '<textarea id="prdNFR" rows="2" class="prd-form-field" placeholder="- NFR-1 (P1): [Kecepatan / keamanan / skala ...]">' + escapeHtml(prdSections.nfr) + '</textarea>' +
                '</div>' +

                // Section 7: Scope
                '<div class="space-y-2">' +
                    '<div class="flex items-center gap-2">' +
                        '<span class="w-6 h-6 rounded-md bg-orange-500/10 border border-orange-500/20 flex items-center justify-center text-[10px] font-bold text-orange-400">7</span>' +
                        '<label class="font-mono text-[10px] font-bold text-[var(--text-muted)] uppercase tracking-wider">Scope</label>' +
                    '</div>' +
                    '<div class="grid grid-cols-1 sm:grid-cols-2 gap-3">' +
                        '<div>' +
                            '<label class="font-mono text-[9px] font-bold text-emerald-400 uppercase tracking-wider mb-1 block">IN (versi 1.0)</label>' +
                            '<textarea id="prdScopeIn" rows="2" class="prd-form-field-sm" placeholder="Fitur yang masuk sekarang">' + escapeHtml(prdSections.scopeIn) + '</textarea>' +
                        '</div>' +
                        '<div>' +
                            '<label class="font-mono text-[9px] font-bold text-red-400 uppercase tracking-wider mb-1 block">OUT (nanti)</label>' +
                            '<textarea id="prdScopeOut" rows="2" class="prd-form-field-sm" placeholder="Fitur yang ditunda">' + escapeHtml(prdSections.scopeOut) + '</textarea>' +
                        '</div>' +
                    '</div>' +
                '</div>' +

                // Upload file option
                '<div class="flex items-center gap-3 pt-2 border-t border-[var(--border-default)]">' +
                    '<span class="font-mono text-[10px] text-[var(--text-muted)]">Atau timpa dengan file lokal:</span>' +
                    '<input type="file" id="prdFileInput" class="hidden" accept=".md,.txt" onchange="handleFileUpload(this, \'prd\')">' +
                    '<button onclick="document.getElementById(\'prdFileInput\').click()" class="px-3.5 py-1.5 bg-[var(--bg-primary)] hover:bg-[var(--bg-hover)] border border-[var(--border-default)] rounded-lg flex items-center gap-1.5 transition-colors text-xs font-bold text-gray-300"><i class="ph ph-upload-simple"></i> Upload File</button>' +
                '</div>' +

                '<div id="prdValidation" class="hidden"></div>' +
            '</div>'
        : '';

        return '<div class="space-y-8">' +
            '<div class="text-center max-w-xl mx-auto space-y-3">' +
                '<div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-orange-500/10 border border-orange-500/30 mb-2 glow-orange">' +
                    '<i class="ph ph-file-text text-4xl text-[var(--brand-primary)]"></i>' +
                '</div>' +
                '<h2 class="text-3xl sm:text-4xl font-heading font-extrabold tracking-tight">Tahap 4 — PRD</h2>' +
                '<p class="text-[var(--text-secondary)] text-sm leading-relaxed">' + (noRef ? 'Isi form 7 bagian di bawah. Pastikan semua bagian terisi sebelum klik Jalankan.' : 'Pilih mode: generate otomatis dari audit referensi, atau isi manual. Lalu klik Jalankan AI.') + '</p>' +
                '<button onclick="copyPrdPrompt()" class="mt-2 px-4 py-2 bg-[var(--bg-card)] hover:bg-[var(--bg-hover)] border border-[var(--border-default)] hover:border-[var(--brand-primary)] rounded-xl text-xs font-bold text-[var(--text-primary)] transition-all inline-flex items-center gap-2 font-mono"><i class="ph ph-clipboard-text text-base text-[var(--brand-primary)]"></i> Copy Prompt AI</button>' +
            '</div>' +

            '<div class="max-w-2xl mx-auto space-y-4">' +

                noRefBanner +
                modeSelectorHtml +
                autoContent +

                // 7-bagian visual guide
                '<div class="p-4 bg-[var(--bg-card)] rounded-xl border border-[var(--border-default)]">' +
                    '<div class="flex items-center gap-3 mb-3">' +
                        '<span class="font-mono text-[10px] font-bold text-[var(--brand-primary)] tracking-widest uppercase">// PRD 7-BAGIAN //</span>' +
                    '</div>' +
                    '<div class="grid grid-cols-2 sm:grid-cols-4 gap-2">' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)] text-center"><span class="font-mono text-[10px] font-bold text-emerald-400">1</span><p class="text-[10px] text-[var(--text-muted)]">Problem</p></div>' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)] text-center"><span class="font-mono text-[10px] font-bold text-emerald-400">2</span><p class="text-[10px] text-[var(--text-muted)]">Goals</p></div>' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)] text-center"><span class="font-mono text-[10px] font-bold text-emerald-400">3</span><p class="text-[10px] text-[var(--text-muted)]">Users</p></div>' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)] text-center"><span class="font-mono text-[10px] font-bold text-emerald-400">4</span><p class="text-[10px] text-[var(--text-muted)]">Stories</p></div>' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)] text-center"><span class="font-mono text-[10px] font-bold text-amber-400">5</span><p class="text-[10px] text-[var(--text-muted)]">FR</p></div>' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)] text-center"><span class="font-mono text-[10px] font-bold text-amber-400">6</span><p class="text-[10px] text-[var(--text-muted)]">NFR</p></div>' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-[var(--border-default)] text-center"><span class="font-mono text-[10px] font-bold text-orange-400">7</span><p class="text-[10px] text-[var(--text-muted)]">Scope</p></div>' +
                        '<div class="p-2 bg-[var(--bg-primary)] rounded-lg border border-orange-500/20 text-center"><span class="font-mono text-[10px] font-bold text-[var(--brand-primary)]">✓</span><p class="text-[10px] text-[var(--brand-primary)]">Jalankan</p></div>' +
                    '</div>' +
                '</div>' +

                manualEditorHtml +

            '</div>' +
        '</div>';
    }

    function setPrdMode(mode) {
        prdMode = mode;
        if (mode === 'manual' && (!formData.prd || formData.prd.trim() === '')) {
            formData.prd = prdTemplate;
        }
        renderStep();
    }

    // =====================================================
    // PRD Form Helpers
    // =====================================================
    function escapeHtml(text) {
        if (!text) return '';
        var div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    function parsePrdToSections(md) {
        var sections = { problem: '', goals: '', users: '', stories: '', fr: '', nfr: '', scopeIn: '', scopeOut: '' };
        if (!md) return sections;

        var headingMap = {
            'Problem Statement': 'problem',
            'Goals': 'goals',
            'Target User': 'users',
            'User Stories': 'stories',
            'Functional Requirements': 'fr',
            'Non-Functional Requirements': 'nfr',
            'Scope': 'scope'
        };

        var lines = md.split('\n');
        var currentSection = null;
        var scopePart = null;

        for (var i = 0; i < lines.length; i++) {
            var line = lines[i];
            var headingMatch = line.match(/^##\s+(\d+\.?\s*)?(.+)/);
            if (headingMatch) {
                var headingText = headingMatch[2].trim();
                currentSection = null;
                scopePart = null;
                for (var key in headingMap) {
                    if (headingText.indexOf(key) !== -1) {
                        currentSection = headingMap[key];
                        break;
                    }
                }
                continue;
            }
            if (currentSection === 'scope') {
                if (line.match(/^IN\s/i) || line.match(/^\*?\*?IN/i)) {
                    scopePart = 'in';
                    continue;
                }
                if (line.match(/^OUT\s/i) || line.match(/^\*?\*?OUT/i)) {
                    scopePart = 'out';
                    continue;
                }
                if (scopePart === 'in') {
                    sections.scopeIn += (sections.scopeIn ? '\n' : '') + line;
                } else if (scopePart === 'out') {
                    sections.scopeOut += (sections.scopeOut ? '\n' : '') + line;
                }
            } else if (currentSection) {
                sections[currentSection] += (sections[currentSection] ? '\n' : '') + line;
            }
        }
        for (var k in sections) { sections[k] = sections[k].trim(); }
        return sections;
    }

    function collectPrdFromForm() {
        var appName = envData.appName || 'Aplikasi';
        var problem = (document.getElementById('prdProblem') || {}).value || '';
        var goals = (document.getElementById('prdGoals') || {}).value || '';
        var users = (document.getElementById('prdUsers') || {}).value || '';
        var stories = (document.getElementById('prdStories') || {}).value || '';
        var fr = (document.getElementById('prdFR') || {}).value || '';
        var nfr = (document.getElementById('prdNFR') || {}).value || '';
        var scopeIn = (document.getElementById('prdScopeIn') || {}).value || '';
        var scopeOut = (document.getElementById('prdScopeOut') || {}).value || '';

        return '# PRD: ' + appName + '\n' +
            '\n' +
            '## 1. Problem Statement\n' +
            problem + '\n' +
            '\n' +
            '## 2. Goals\n' +
            goals + '\n' +
            '\n' +
            '## 3. Target User\n' +
            users + '\n' +
            '\n' +
            '## 4. User Stories\n' +
            stories + '\n' +
            '\n' +
            '## 5. Functional Requirements\n' +
            fr + '\n' +
            '\n' +
            '## 6. Non-Functional Requirements\n' +
            nfr + '\n' +
            '\n' +
            '## 7. Scope\n' +
            'IN (versi 1.0): ' + scopeIn + '\n' +
            'OUT (nanti): ' + scopeOut + '\n';
    }

    // =====================================================
    // Copy Prompt Helpers
    // =====================================================
    function copyBrandPrompt() {
        var prompt = 'Baca CLAUDE.md dan docs/prd.md, lalu isi docs/branding.md secara lengkap.\n' +
            'Isi semua bagian: Nama Aplikasi & Tagline, Deskripsi Singkat, Target Audience & Tone, Palet Warna (hex), Typography (Google Fonts), dan Logo Guidelines.\n' +
            'Pastikan palet warna konsisten — gunakan warna yang relevan dengan aplikasi di PRD.\n' +
            'Format: markdown dengan heading ## per bagian.';
        copyToClipboard(prompt, 'Prompt Branding disalin!', 'Paste ke AI untuk mengisi form branding.');
    }

    function copyPrdPrompt() {
        var prompt = 'Baca CLAUDE.md dan references/ (jika ada), lalu isi docs/prd.md secara lengkap.\n' +
            'Isi semua 7 bagian: Problem Statement, Goals, Target User, User Stories, Functional Requirements, Non-Functional Requirements, dan Scope.\n' +
            'Pastikan:\n' +
            '- Target User konsisten dengan halaman yang aktif (lihat docs/install.md Section 2).\n' +
            '- User Stories mencakup semua role yang ada.\n' +
            '- Functional Requirements spesifik dan terukur.\n' +
            '- Scope membedakan IN (versi 1.0) vs OUT (nanti) dengan jelas.\n' +
            'Format: markdown dengan heading ## per bagian, gunakan bullet points.';
        copyToClipboard(prompt, 'Prompt PRD disalin!', 'Paste ke AI untuk mengisi form PRD.');
    }

    async function copyToClipboard(text, successTitle, successMsg) {
        var ok = false;
        if (navigator.clipboard && window.isSecureContext) {
            try { await navigator.clipboard.writeText(text); ok = true; } catch(e) {}
        }
        if (!ok) {
            var ta = document.createElement('textarea');
            ta.value = text; ta.style.position = 'fixed'; ta.style.opacity = '0';
            document.body.appendChild(ta); ta.select();
            try { document.execCommand('copy'); ok = true; } catch(e) {}
            document.body.removeChild(ta);
        }
        if (ok) showToast(successTitle, successMsg);
        else showToast('Gagal!', 'Tidak dapat menyalin ke clipboard', true);
    }

    function validatePrd() {
        // Collect from form fields if manual mode
        var content = '';
        if (prdMode === 'manual' || hasReferences === false) {
            content = collectPrdFromForm();
            formData.prd = content;
        } else {
            content = formData.prd || '';
        }

        if (!content) return { valid: false, missing: ['Semua bagian kosong'] };

        // Check each section has actual content (not just heading)
        var requiredSections = [
            { name: 'Problem Statement', field: 'prdProblem' },
            { name: 'Goals', field: 'prdGoals' },
            { name: 'Target User', field: 'prdUsers' },
            { name: 'User Stories', field: 'prdStories' },
            { name: 'Functional Requirements', field: 'prdFR' },
            { name: 'Non-Functional Requirements', field: 'prdNFR' }
        ];

        var missing = [];
        requiredSections.forEach(function(s) {
            var field = document.getElementById(s.field);
            var value = field ? field.value.trim() : '';
            if (!value || value.indexOf('[') !== -1) {
                missing.push(s.name);
            }
        });

        // Check scope
        var scopeIn = (document.getElementById('prdScopeIn') || {}).value || '';
        var scopeOut = (document.getElementById('prdScopeOut') || {}).value || '';
        if (!scopeIn.trim() && !scopeOut.trim()) {
            missing.push('Scope');
        }

        return { valid: missing.length === 0, missing: missing, hasPlaceholder: false };
    }

    // =====================================================
    // Monaco Editor
    // =====================================================
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
        });
    }

    function initTextareaFallback(container, dataKey, language) {
        container.innerHTML = '<textarea id="fallbackEditor" class="w-full h-full p-4 font-mono text-xs resize-none border-0 focus:outline-none" style="min-height: 400px; background-color: #0B0F17; color: #F0F6FC; line-height: 1.6;" oninput="handleTextareaInput(\'' + dataKey + '\', this.value)">' + (formData[dataKey] || '') + '</textarea>';
    }

    function handleTextareaInput(dataKey, value) {
        formData[dataKey] = value;
        triggerAutoSave(dataKey, value);
    }

    // =====================================================
    // Auto-Save
    // =====================================================
    function triggerAutoSave(dataKey, value) {
        var status = document.getElementById('saveStatus');
        var statusText = document.getElementById('saveStatusText');

        if (dataKey !== 'prd' && dataKey !== 'branding') {
            if (status) status.classList.add('hidden');
            return;
        }

        if (status) {
            statusText.textContent = 'Saving...';
            status.className = 'text-xs font-mono text-amber-400 flex items-center gap-1.5';
        }

        clearTimeout(autoSaveTimeout);
        autoSaveTimeout = setTimeout(async function() {
            var targetFile = dataKey === 'prd' ? 'docs/prd.md' : 'docs/branding.md';
            try {
                var res = await fetch('/core/router.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ module: 'install', action: 'save', file: targetFile, content: value, csrf_token: csrfToken })
                });
                if (res.ok) {
                    savedFiles.add(targetFile);
                    if (status) {
                        statusText.textContent = 'Auto-Saved';
                        status.className = 'text-xs font-mono text-emerald-400 flex items-center gap-1.5 font-bold';
                    }
                }
            } catch(e) {
                if (status) {
                    statusText.textContent = 'Save Failed';
                    status.className = 'text-xs font-mono text-red-400 flex items-center gap-1.5';
                }
            }
        }, 1500);
    }

    // =====================================================
    // Save Current Step — always saves ALL docs on navigation
    // =====================================================
    async function saveCurrentStep() {
        var step = steps[currentStep - 1];

        // Save logo
        if (formData.logo) {
            var formDataObj = new FormData();
            formDataObj.append('module', 'install');
            formDataObj.append('action', 'save');
            formDataObj.append('logo', formData.logo);
            formDataObj.append('csrf_token', csrfToken);
            var response = await fetch('/core/router.php', { method: 'POST', body: formDataObj });
            if (response.ok) savedFiles.add('docs/logo.png');
        }

        // === Always save branding.md ===
        if (hasReferences === false || brandingMode === 'manual') {
            var brandAppName = document.getElementById('brandAppName');
            var brandTagline = document.getElementById('brandTagline');
            var brandDesc = document.getElementById('brandDesc');
            var brandAudience = document.getElementById('brandAudience');
            var brandTone = document.getElementById('brandTone');
            var brandColorPrimaryHex = document.getElementById('brandColorPrimaryHex');
            var brandColorSecondaryHex = document.getElementById('brandColorSecondaryHex');
            var brandColorAccentHex = document.getElementById('brandColorAccentHex');
            var brandFontHeading = document.getElementById('brandFontHeading');
            var brandFontBody = document.getElementById('brandFontBody');

            if (brandAppName) {
                var brandingMd = '# Branding: ' + (brandAppName.value || envData.appName) + '\n' +
                    '\n' +
                    '## 1. Nama Aplikasi & Tagline\n' +
                    '- Nama: ' + (brandAppName.value || envData.appName) + '\n' +
                    '- Tagline: ' + (brandTagline ? brandTagline.value : '') + '\n' +
                    '\n' +
                    '## 2. Deskripsi Singkat / Value Proposition\n' +
                    (brandDesc ? brandDesc.value : '') + '\n' +
                    '\n' +
                    '## 3. Target Audience & Tone of Voice\n' +
                    '- Target: ' + (brandAudience ? brandAudience.value : '') + '\n' +
                    '- Tone: ' + (brandTone ? brandTone.value : 'profesional') + '\n' +
                    '\n' +
                    '## 4. Palet Warna\n' +
                    '- Primary: ' + (brandColorPrimaryHex ? brandColorPrimaryHex.value : '#F97316') + '\n' +
                    '- Secondary: ' + (brandColorSecondaryHex ? brandColorSecondaryHex.value : '#1E293B') + '\n' +
                    '- Accent: ' + (brandColorAccentHex ? brandColorAccentHex.value : '#10B981') + '\n' +
                    '\n' +
                    '## 5. Typography\n' +
                    '- Heading: ' + (brandFontHeading ? brandFontHeading.value : 'Plus Jakarta Sans') + '\n' +
                    '- Body: ' + (brandFontBody ? brandFontBody.value : 'Inter') + '\n' +
                    '\n' +
                    '## 6. Logo & Asset Guidelines\n' +
                    '[Upload logo di atas. Format: PNG/SVG, rekomendasi 512x512px.]\n';

                formData.branding = brandingMd;
                var res = await fetch('/core/router.php', {
                    method: 'POST', headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ module: 'install', action: 'save', file: 'docs/branding.md', content: brandingMd, csrf_token: csrfToken })
                });
                if (res.ok) savedFiles.add('docs/branding.md');
            }
        } else if (brandingMode === 'auto' && hasReferences === true) {
            var autoBrandingMd = '# Branding: ' + envData.appName + '\n\n## Mode: Otomatis\n\nBrand identity akan digenerate otomatis dari PRD final saat AI mengeksekusi.\n';
            var res = await fetch('/core/router.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module: 'install', action: 'save', file: 'docs/branding.md', content: autoBrandingMd, csrf_token: csrfToken })
            });
            if (res.ok) savedFiles.add('docs/branding.md');
        }

        // === Always save page_structure.json ===
        var structureJson = JSON.stringify(pageStructure);
        await fetch('/core/router.php', {
            method: 'POST', headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ module: 'install', action: 'save', file: 'docs/page_structure.json', content: structureJson, csrf_token: csrfToken })
        });

        // === Always save prd.md ===
        var prdContent = '';
        if (prdMode === 'manual' || hasReferences === false) {
            prdContent = collectPrdFromForm();
            formData.prd = prdContent;
        } else {
            prdContent = formData.prd || '';
        }
        if (prdContent) {
            var res = await fetch('/core/router.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module: 'install', action: 'save', file: 'docs/prd.md', content: prdContent, csrf_token: csrfToken })
            });
            if (res.ok) savedFiles.add('docs/prd.md');
        }

        // Save PRD mode marker
        if (prdMode === 'auto' && hasReferences === true) {
            var autoPrdMarker = JSON.stringify({ mode: 'auto', hasReferences: true });
            await fetch('/core/router.php', {
                method: 'POST', headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ module: 'install', action: 'save', file: 'docs/prd_mode.json', content: autoPrdMarker, csrf_token: csrfToken })
            });
            savedFiles.add('docs/prd_mode.json');
        }

        // === Always regenerate install.md ===
        await fetch('/core/router.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                module: 'install',
                action: 'generate_install_md',
                serverType: envData.serverType,
                drive: envData.projectPath.charAt(0),
                installPath: envData.projectPath,
                appMode: 'unified',
                projectName: envData.appName,
                pageStructure: pageStructure,
                brandingMode: brandingMode,
                prdMode: prdMode,
                csrf_token: csrfToken
            })
        });
        savedFiles.add('docs/install.md');
    }

    // =====================================================
    // Execute Terminal — Launch AI
    // =====================================================
    async function executeTerminal() {
        // Validate PRD if manual mode
        if (prdMode === 'manual' || hasReferences === false) {
            var validation = validatePrd();
            if (!validation.valid) {
                var proceed = confirm('PRD belum lengkap — bagian berikut belum ditemukan:\n\n' + validation.missing.join('\n') + '\n\nLanjutkan juga? (Risiko: AI mungkin mengarang bagian yang kosong)');
                if (!proceed) return;
            }
        }

        var btn = document.getElementById('executeBtn');
        if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ph ph-circle-notch text-base animate-spin"></i> MEMPROSES...'; }

        try {
            await saveCurrentStep();

            // Generate install.md
            await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    module: 'install',
                    action: 'generate_install_md',
                    serverType: envData.serverType,
                    drive: envData.projectPath.charAt(0),
                    installPath: envData.projectPath,
                    appMode: 'unified',
                    projectName: envData.appName,
                    pageStructure: pageStructure,
                    brandingMode: brandingMode,
                    prdMode: prdMode,
                    csrf_token: csrfToken
                })
            });
            savedFiles.add('data/install_config.json');
            savedFiles.add('docs/install.md (static template)');

            // Tandai file yang akan di-generate otomatis berdasarkan mode
            var autoGenerated = [];
            if (brandingMode === 'auto' && hasReferences === true) {
                autoGenerated.push('docs/branding.md (auto-generate oleh AI saat TAHAP 2)');
            }
            if (prdMode === 'auto' && hasReferences === true) {
                autoGenerated.push('docs/prd.md (auto-generate oleh AI saat TAHAP 2)');
            }
            if (hasReferences === false) {
                autoGenerated.push('references/*.html (auto-generate oleh AI saat TAHAP 2)');
            }

            // Tampilkan modal sukses dengan info auto-generated
            showSuccessModal(autoGenerated);

        } catch(e) {
            console.error('Execute terminal error:', e);
            showSuccessModal([]);
        } finally {
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ph ph-terminal-window text-base"></i> JALANKAN AI'; }
        }
    }

    // =====================================================
    // Copy & Launch Claude
    // =====================================================
    async function copyAndLaunchClaude() {
        var commandText = 'baca dan jalankan @docs/install.md';

        // Copy to clipboard
        var copySuccess = false;
        if (navigator.clipboard && window.isSecureContext) {
            try {
                await navigator.clipboard.writeText(commandText);
                copySuccess = true;
            } catch(e) { /* fallback */ }
        }
        if (!copySuccess) {
            var textarea = document.createElement('textarea');
            textarea.value = commandText;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try { document.execCommand('copy'); copySuccess = true; } catch(e) {}
            document.body.removeChild(textarea);
        }

        if (copySuccess) {
            showToast('Disalin!', 'Command disalin ke clipboard');
        }

        // Launch PowerShell with claude in project directory
        try {
            await fetch('/core/router.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    module: 'install',
                    action: 'execute',
                    drive: envData.projectPath.charAt(0),
                    serverType: envData.serverType,
                    projectPath: envData.projectPath,
                    csrf_token: csrfToken
                })
            });
        } catch(err) {
            console.error('Launch terminal error:', err);
        }

        closeSuccessModal();
    }

    // =====================================================
    // UI Helpers
    // =====================================================
    function showSavingOverlay() {
        document.getElementById('savingOverlay').classList.remove('hidden');
    }
    function hideSavingOverlay() {
        document.getElementById('savingOverlay').classList.add('hidden');
    }

    function showSuccessModal(autoGenerated) {
        autoGenerated = autoGenerated || [];
        var list = document.getElementById('savedFilesList');
        if (list) {
            list.innerHTML = '';
            if (savedFiles.size === 0 && autoGenerated.length === 0) {
                list.innerHTML = '<li class="flex items-center gap-2 text-gray-400 font-mono"><i class="ph ph-info"></i> Belum ada file yang diubah</li>';
            } else {
                // Display saved files
                savedFiles.forEach(function(f) {
                    var li = document.createElement('li');
                    li.className = 'flex items-center gap-2 text-emerald-400 font-mono';
                    li.innerHTML = '<i class="ph ph-check-circle"></i> ' + f;
                    list.appendChild(li);
                });
                // Display auto-generated files
                if (autoGenerated.length > 0) {
                    var divider = document.createElement('li');
                    divider.className = 'flex items-center gap-2 text-amber-400 font-mono mt-2 pt-2 border-t border-gray-800';
                    divider.innerHTML = '<i class="ph ph-sparkle"></i> <strong>File yang akan di-generate otomatis:</strong>';
                    list.appendChild(divider);
                    autoGenerated.forEach(function(f) {
                        var li = document.createElement('li');
                        li.className = 'flex items-center gap-2 text-amber-400/80 font-mono';
                        li.innerHTML = '<i class="ph ph-arrow-right text-xs"></i> ' + f;
                        list.appendChild(li);
                    });
                }
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

        toastTitle.textContent = title;
        toastMessage.textContent = message;

        if (isError) {
            toastIcon.className = 'ph ph-warning-circle text-lg text-red-400';
            toastIcon.parentElement.className = 'w-8 h-8 rounded-lg bg-red-500/10 border border-red-500/30 flex items-center justify-center shrink-0';
        } else {
            toastIcon.className = 'ph ph-check-circle text-lg text-emerald-400';
            toastIcon.parentElement.className = 'w-8 h-8 rounded-lg bg-emerald-500/10 border border-emerald-500/30 flex items-center justify-center shrink-0';
        }

        toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-950 border border-gray-800 rounded-xl px-5 py-3.5 shadow-2xl flex items-center gap-3 z-50 transition-all duration-300 opacity-100 translate-y-0 font-mono';

        setTimeout(function() {
            toast.className = 'fixed bottom-6 left-1/2 -translate-x-1/2 bg-gray-950 border border-gray-800 rounded-xl px-5 py-3.5 shadow-2xl flex items-center gap-3 z-50 transition-all duration-300 opacity-0 translate-y-4 pointer-events-none font-mono';
        }, 3000);
    }

    // Theme toggle
    var htmlTheme = document.documentElement;
    document.getElementById('themeToggle')?.addEventListener('click', function() {
        var isDark = htmlTheme.classList.toggle('dark');
        localStorage.setItem('theme', isDark ? 'dark' : 'light');
        if (editor) editor.updateOptions({ theme: isDark ? 'vibeforgeDark' : 'vs' });
    });

    window.onMonacoReady = function() {
        if (_pendingMonacoInit) initMonacoEditor(_pendingMonacoInit.language, _pendingMonacoInit.dataKey);
    };

    // =====================================================
    // Initialize
    // =====================================================
    initSteps();
    renderStep();
    // Detect existing references on page load
    detectExistingReferences();

    function detectExistingReferences() {
        fetch('/core/router.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ module: 'install', action: 'ref_list', csrf_token: csrfToken })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            refFiles = data.files || [];
            if (refFiles.length > 0) {
                hasReferences = true;
                updateStepUI();
            }
        })
        .catch(function() {});
    }
    </script>
</body>
</html>
