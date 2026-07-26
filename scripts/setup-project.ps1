# Script Setup Proyek Vibeforge & Auto-VirtualHost

[CmdletBinding()]
param(
    [switch]$Elevated
)

# Helper function untuk format header & output
function Write-Header($text) {
    Write-Host $text -ForegroundColor Yellow
}

function Test-IsAdmin {
    $principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# 0. Cek Hak Akses Administrator & Auto-Elevate jika perlu
if (-not (Test-IsAdmin)) {
    if (-not $Elevated) {
        Write-Host "==========================================================================" -ForegroundColor Red
        Write-Host " [PERINGATAN] PowerShell tidak berjalan sebagai Administrator!" -ForegroundColor Red
        Write-Host " Silakan jalankan PowerShell sebagai Administrator agar file hosts" -ForegroundColor Yellow
        Write-Host " dan Virtual Host Apache dapat diupdate secara otomatis." -ForegroundColor Yellow
        Write-Host "==========================================================================" -ForegroundColor Red
        Write-Host "`nMembuka jendela Administrator baru..." -ForegroundColor Cyan
        Start-Sleep -Seconds 2
        Start-Process powershell.exe -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`" -Elevated" -Verb RunAs
        exit
    } else {
        Write-Host "[PERINGATAN] Gagal mendapatkan hak Administrator. Beberapa langkah mungkin dilewati." -ForegroundColor Red
    }
}

Clear-Host
Write-Header "=========================================="
Write-Host "  Vibeforge Setup Wizard" -ForegroundColor White
Write-Header "=========================================="

# 1. Pilih Local Disk
$disk = Read-Host "Masukkan Local Disk (contoh: C, D, E) [Default: C]"
$disk = $disk.Trim().ToUpper()
if (-not $disk) { $disk = "C" }

# 2. Pilih Web Server
$serverChoice = Read-Host "Pilih Web Server: [l] Laragon (default) | [x] XAMPP"
$serverChoice = $serverChoice.Trim().ToLower()

if ($serverChoice -eq "x") {
    $serverType = "XAMPP"
    $baseDir = "$($disk):\xampp\htdocs"
} else {
    $serverType = "Laragon"
    $baseDir = "$($disk):\laragon\www"
}

Write-Host "`nServer terpilih: $serverType ($baseDir)" -ForegroundColor Cyan

# 3. Masukkan Nama Aplikasi
$appName = Read-Host "Masukkan nama aplikasi (tanpa spasi, gunakan _ atau -)"
$appName = $appName.Trim().ToLower() -replace '\s+', '_'

if (-not $appName) {
    Write-Host "[ERROR] Nama aplikasi tidak boleh kosong." -ForegroundColor Red
    exit 1
}

$targetDir = Join-Path $baseDir $appName

# Cek apakah folder base server ada
if (-not (Test-Path $baseDir)) {
    Write-Host "[WARNING] Folder $baseDir tidak ditemukan. Membuat folder..." -ForegroundColor Yellow
    New-Item -ItemType Directory -Path $baseDir -Force | Out-Null
}

Write-Host "`n[1/5] Mengunduh template Vibeforge..." -ForegroundColor Green
npx -y degit iqbalmurtadho24/vibeforge $targetDir

if (-not (Test-Path $targetDir)) {
    Write-Host "[ERROR] Gagal mendownload template." -ForegroundColor Red
    exit 1
}

Set-Location $targetDir
$publicDir = (Join-Path $targetDir "public").Replace('\', '/')
$domain = "$appName.test"

Write-Host "`n[2/5] Membuat Virtual Host file..." -ForegroundColor Green

if ($serverType -eq "Laragon") {
    $laragonVhostDir = "$($disk):\laragon\etc\apache2\sites-enabled"
    if (-not (Test-Path $laragonVhostDir)) {
        $laragonVhostDir = Join-Path (Split-Path $baseDir -Parent) "etc\apache2\sites-enabled"
    }

    if (-not (Test-Path $laragonVhostDir)) {
        New-Item -ItemType Directory -Path $laragonVhostDir -Force | Out-Null
    }

    $vhostFile = Join-Path $laragonVhostDir "auto.$domain.conf"
    $vhostContent = @"
<VirtualHost *:80>
    DocumentRoot "$publicDir"
    ServerName $domain
    ServerAlias *.$domain
    <Directory "$publicDir">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

    Set-Content -Path $vhostFile -Value $vhostContent -Encoding UTF8
    Write-Host "Virtual Host dibuat di: $vhostFile" -ForegroundColor Cyan
} else {
    $xamppVhostFile = "$($disk):\xampp\apache\conf\extra\httpd-vhosts.conf"
    $vhostContent = @"

# Virtual Host untuk $appName
<VirtualHost *:80>
    DocumentRoot "$publicDir"
    ServerName $domain
    <Directory "$publicDir">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

    if (Test-Path $xamppVhostFile) {
        Add-Content -Path $xamppVhostFile -Value $vhostContent -Encoding UTF8
        Write-Host "Virtual Host ditambahkan ke: $xamppVhostFile" -ForegroundColor Cyan
    }
}

# 3. Update hosts file Windows
Write-Host "`n[3/5] Mengupdate file Windows hosts (127.0.0.1 $domain)..." -ForegroundColor Green
$hostsPath = "C:\Windows\System32\drivers\etc\hosts"
$hostsEntry = "`r`n127.0.0.1`t$domain"

try {
    $hostsContent = [System.IO.File]::ReadAllText($hostsPath)
    if ($hostsContent -notlike "*$domain*") {
        [System.IO.File]::AppendAllText($hostsPath, $hostsEntry)
        Write-Host "Domain $domain berhasil ditambahkan ke file hosts!" -ForegroundColor Cyan
    } else {
        Write-Host "Domain $domain sudah terdaftar di file hosts." -ForegroundColor Yellow
    }
} catch {
    Write-Host "[WARNING] Gagal memperbarui file hosts: $_" -ForegroundColor Yellow
}

# 4. Generate .env
Write-Host "`n[4/5] Membuat file .env..." -ForegroundColor Green
$envExamplePath = Join-Path $targetDir ".env.example"
$envPath = Join-Path $targetDir ".env"

if (Test-Path $envExamplePath) {
    $envContent = Get-Content -Path $envExamplePath -Raw

    $appKeyBytes = New-Object byte[] 32
    (New-Object Security.Cryptography.RNGCryptoServiceProvider).GetBytes($appKeyBytes)
    $appKey = [System.BitConverter]::ToString($appKeyBytes).Replace("-", "").ToLower()

    $csrfKeyBytes = New-Object byte[] 32
    (New-Object Security.Cryptography.RNGCryptoServiceProvider).GetBytes($csrfKeyBytes)
    $csrfKey = [System.BitConverter]::ToString($csrfKeyBytes).Replace("-", "").ToLower()

    $rememberBytes = New-Object byte[] 64
    (New-Object Security.Cryptography.RNGCryptoServiceProvider).GetBytes($rememberBytes)
    $rememberSecret = [System.BitConverter]::ToString($rememberBytes).Replace("-", "").ToLower()

    $formattedAppName = (Get-Culture).TextInfo.ToTitleCase($appName.Replace("_", " ").Replace("-", " "))

    $envContent = $envContent -replace 'APP_DISPLAY_NAME=".*?"', "APP_DISPLAY_NAME=`"$formattedAppName`""
    $envContent = $envContent -replace 'DB_MODE=".*?"', 'DB_MODE="json"'
    $envContent = $envContent -replace 'APP_KEY=".*?"', "APP_KEY=`"$appKey`""
    $envContent = $envContent -replace 'CSRF_KEY=".*?"', "CSRF_KEY=`"$csrfKey`""
    $envContent = $envContent -replace 'REMEMBER_ME_SECRET=".*?"', "REMEMBER_ME_SECRET=`"$rememberSecret`""

    Set-Content -Path $envPath -Value $envContent -Encoding UTF8
    Write-Host "File .env berhasil dibuat dan dikonfigurasi!" -ForegroundColor Cyan
}

# 5. Reload Apache Tanpa Perlu Restart Server Manual
Write-Host "`n[5/5] Memperbarui konfigurasi Apache Web Server..." -ForegroundColor Green

$apacheReloaded = $false

if ($serverType -eq "Laragon") {
    # Cari executable httpd.exe di folder Laragon
    $apacheBins = Get-ChildItem "$disk:\laragon\bin\apache\*\bin\httpd.exe" -ErrorAction SilentlyContinue
    if ($apacheBins) {
        $httpdPath = $apacheBins[0].FullName
        $apacheDir = $apacheBins[0].Directory.Parent.FullName

        # Hentikan proses httpd lama dan jalankan ulang secara silent dengan konfigurasi baru
        Get-Process httpd -ErrorAction SilentlyContinue | Stop-Process -Force
        Start-Sleep -Seconds 1
        Start-Process -FilePath $httpdPath -ArgumentList '-d', "`"$apacheDir`"" -WorkingDirectory $apacheDir -WindowStyle Hidden
        $apacheReloaded = $true
        Write-Host "Apache Laragon berhasil di-reload otomatis!" -ForegroundColor Cyan
    }
} else {
    # XAMPP Apache Service / Process
    $service = Get-Service -Name "Apache2.4" -ErrorAction SilentlyContinue
    if ($service -and $service.Status -eq 'Running') {
        Restart-Service -Name "Apache2.4" -ErrorAction Stop
        $apacheReloaded = $true
        Write-Host "Service XAMPP Apache berhasil di-reload!" -ForegroundColor Cyan
    } else {
        $xamppHttpd = "$disk:\xampp\apache\bin\httpd.exe"
        if (Test-Path $xamppHttpd) {
            Get-Process httpd -ErrorAction SilentlyContinue | Stop-Process -Force
            Start-Sleep -Seconds 1
            $xamppApacheDir = "$disk:\xampp\apache"
            Start-Process -FilePath $xamppHttpd -ArgumentList '-d', "`"$xamppApacheDir`"" -WorkingDirectory $xamppApacheDir -WindowStyle Hidden
            $apacheReloaded = $true
            Write-Host "Proses XAMPP Apache berhasil di-reload!" -ForegroundColor Cyan
        }
    }
}

if (-not $apacheReloaded) {
    Write-Host "[INFO] Silakan reload Apache dari GUI Laragon/XAMPP jika domain belum langsung aktif." -ForegroundColor Yellow
}

Write-Header "`n=========================================="
Write-Host "  Setup Berhasil Dituntaskan!" -ForegroundColor Green
Write-Host "  Proyek  : $appName" -ForegroundColor White
Write-Host "  Lokasi  : $targetDir" -ForegroundColor White
Write-Host "  URL App : http://$domain" -ForegroundColor Cyan
Write-Header "=========================================="

Write-Host "`nMembuka browser dan menutup terminal otomatis dalam 2 detik..." -ForegroundColor Green
Start-Sleep -Seconds 2
Start-Process "http://$domain/install/"
exit
