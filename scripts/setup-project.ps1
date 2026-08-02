# Script Setup Proyek Vibeforge & Auto-VirtualHost

[CmdletBinding()]
param(
    [switch]$Elevated
)

# Fungsi helper untuk warna yang valid
function Write-Header($text) {
    Write-Host $text -ForegroundColor Yellow
}

# Cek apakah sudah running sebagai Administrator
function Test-IsAdmin {
    $principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# Cek Administrator — jika bukan admin, beri loading 3 detik lalu alihkan ke Administrator
if (-not (Test-IsAdmin)) {
    Write-Host "==========================================" -ForegroundColor Yellow
    Write-Host "  MEMERLUKAN HAK AKSES ADMINISTRATOR" -ForegroundColor Yellow
    Write-Host "==========================================" -ForegroundColor Yellow
    Write-Host ""
    Write-Host "Script ini memerlukan hak Administrator untuk:" -ForegroundColor White
    Write-Host "  - Update file Windows hosts (C:\Windows\System32\drivers\etc\hosts)" -ForegroundColor Gray
    Write-Host "  - Restart service Apache" -ForegroundColor Gray
    Write-Host ""
    Write-Host "Mengalihkan ke jendela Administrator dalam 3 detik..." -ForegroundColor Cyan
    Write-Host ""

    for ($i = 3; $i -gt 0; $i--) {
        Write-Host "   [+] Mohon tunggu ($i detik)..." -ForegroundColor Yellow
        Start-Sleep -Seconds 1
    }

    $scriptPath = $PSCommandPath
    if (-not $scriptPath) { $scriptPath = $MyInvocation.MyCommand.Definition }

    # Resolve to absolute path so the elevated process can find the file
<<<<<<< HEAD
    $resolvedPath = $null
    if ($scriptPath) {
        $resolvedPath = (Resolve-Path -Path $scriptPath -ErrorAction SilentlyContinue).Path
    }

    if ($resolvedPath -and (Test-Path -Path $resolvedPath -ErrorAction SilentlyContinue)) {
        Start-Process powershell.exe -ArgumentList "-NoExit -ExecutionPolicy Bypass -NoProfile -File `"$resolvedPath`"" -Verb RunAs
    } else {
        # Fallback: download and run remotely in the elevated window
        Start-Process powershell.exe -ArgumentList "-NoExit -ExecutionPolicy Bypass -NoProfile -Command ""irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex""" -Verb RunAs
=======
    if ($scriptPath) {
        $scriptPath = (Resolve-Path -Path $scriptPath -ErrorAction SilentlyContinue).Path
    }

    if ($scriptPath -and (Test-Path -Path $scriptPath -ErrorAction SilentlyContinue)) {
        Start-Process powershell.exe -ArgumentList "-NoExit -ExecutionPolicy Bypass -NoProfile -File `"$scriptPath`" -Elevated" -Verb RunAs
    } else {
        # Fallback: download and run remotely in the elevated window
        $remoteCmd = "Start-Process powershell.exe -ArgumentList '-NoExit -ExecutionPolicy Bypass -NoProfile -Command ""irm https://raw.githubusercontent.com/iqbalmurtadho24/vibeforge/main/scripts/setup-project.ps1 | iex""' -Verb RunAs"
        Invoke-Expression $remoteCmd
>>>>>>> d29072fbcef103dfbb262621351b267bbaabede6
    }
    exit 0
}

Write-Header "=========================================="
Write-Host "  Vibeforge Setup Wizard" -ForegroundColor White
Write-Header "=========================================="

# 1. Pilih Local Disk
$disk = Read-Host "Masukkan Local Disk (contoh: C, D, E)"
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
    Write-Error "Nama aplikasi tidak boleh kosong."
    exit 1
}

$targetDir = Join-Path $baseDir $appName

# Cek apakah folder base sudah ada
if (-not (Test-Path $baseDir)) {
    Write-Warning "Folder $baseDir tidak ditemukan. Pastikan $serverType sudah terinstall."
    $proceed = Read-Host "Lanjutkan tetap? (y/n)"
    if ($proceed -ne "y") { exit 1 }
}

Write-Host "`n[1/5] Mengunduh template Vibeforge..." -ForegroundColor Green
if (-not (Test-Path $baseDir)) {
    New-Item -ItemType Directory -Path $baseDir -Force | Out-Null
}

Set-Location -Path $baseDir
npx -y degit iqbalmurtadho24/vibeforge $appName

if (-not (Test-Path $targetDir)) {
    Write-Error "Gagal mendownload template."
    exit 1
}

Set-Location $targetDir
$publicDir = (Join-Path $targetDir "public").Replace('\', '/')

Write-Host "`n[2/5] Membuat Virtual Host file..." -ForegroundColor Green
$domain = "$appName.test"

if ($serverType -eq "Laragon") {
    $laragonVhostDir = "$($disk):\laragon\etc\apache2\sites-enabled"
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
        $existingContent = Get-Content $xamppVhostFile -Raw -ErrorAction SilentlyContinue
        if ($existingContent -notlike "*$domain*") {
            Add-Content -Path $xamppVhostFile -Value $vhostContent -Encoding UTF8
            Write-Host "Virtual Host ditambahkan ke: $xamppVhostFile" -ForegroundColor Cyan
        } else {
            Write-Host "Virtual Host untuk $domain sudah ada di file $xamppVhostFile." -ForegroundColor Yellow
        }
    } else {
        Write-Warning "File httpd-vhosts.conf tidak ditemukan di $xamppVhostFile"
    }
}

# Update hosts file (sudah dipastikan admin dari awal script)
Write-Host "`n[3/5] Mengupdate file Windows hosts (127.0.0.1 $domain)..." -ForegroundColor Green
$hostsPath = "C:\Windows\System32\drivers\etc\hosts"
$hostsEntry = "127.0.0.1 $domain"

try {
    $hostsContent = Get-Content $hostsPath -ErrorAction Stop
    if ($hostsContent -notcontains $hostsEntry -and ($hostsContent -match [regex]::Escape($domain)).Length -eq 0) {
        Add-Content -Path $hostsPath -Value "`n$hostsEntry" -ErrorAction Stop
        Write-Host "Domain $domain berhasil ditambahkan ke C:\Windows\System32\drivers\etc\hosts" -ForegroundColor Cyan
    } else {
        Write-Host "Domain $domain sudah ada di file hosts." -ForegroundColor Yellow
    }
} catch {
    Write-Warning "Gagal menulis ke file hosts: $_"
}

# Flush DNS cache agar domain baru langsung dapat dikenali oleh Windows
Write-Host "Flushing DNS cache..." -ForegroundColor Gray
ipconfig /flushdns | Out-Null

Write-Host "`n[4/5] Membuat file .env..." -ForegroundColor Green
$envExamplePath = Join-Path $targetDir ".env.example"
$envPath = Join-Path $targetDir ".env"

if (Test-Path $envExamplePath) {
    $envContent = Get-Content -Path $envExamplePath -Raw

    # Generate random cryptographic keys
    $appKeyBytes = New-Object byte[] 32
    (New-Object Security.Cryptography.RNGCryptoServiceProvider).GetBytes($appKeyBytes)
    $appKey = [System.BitConverter]::ToString($appKeyBytes).Replace("-", "").ToLower()

    $csrfKeyBytes = New-Object byte[] 32
    (New-Object Security.Cryptography.RNGCryptoServiceProvider).GetBytes($csrfKeyBytes)
    $csrfKey = [System.BitConverter]::ToString($csrfKeyBytes).Replace("-", "").ToLower()

    $rememberBytes = New-Object byte[] 64
    (New-Object Security.Cryptography.RNGCryptoServiceProvider).GetBytes($rememberBytes)
    $rememberSecret = [System.BitConverter]::ToString($rememberBytes).Replace("-", "").ToLower()

    # Formatted display name (e.g. abdulqodir -> Abdulqodir)
    $formattedAppName = (Get-Culture).TextInfo.ToTitleCase($appName.Replace("_", " ").Replace("-", " "))

    # Update placeholders
    $envContent = $envContent -replace 'APP_DISPLAY_NAME=".*?"', "APP_DISPLAY_NAME=`"$formattedAppName`""
    $envContent = $envContent -replace 'DB_MODE=".*?"', 'DB_MODE="json"'
    $envContent = $envContent -replace 'APP_KEY=".*?"', "APP_KEY=`"$appKey`""
    $envContent = $envContent -replace 'CSRF_KEY=".*?"', "CSRF_KEY=`"$csrfKey`""
    $envContent = $envContent -replace 'REMEMBER_ME_SECRET=".*?"', "REMEMBER_ME_SECRET=`"$rememberSecret`""

    Set-Content -Path $envPath -Value $envContent -Encoding UTF8
    Write-Host "File .env berhasil dibuat dan dikonfigurasi!" -ForegroundColor Cyan
} else {
    Write-Warning "File .env.example tidak ditemukan di $targetDir."
}

Write-Header "=========================================="
Write-Host "Proyek  : $appName" -ForegroundColor White
Write-Host "Lokasi  : $targetDir" -ForegroundColor White
Write-Host "URL App : http://$domain" -ForegroundColor Cyan
Write-Header "=========================================="

# 5. Reload/Restart Apache & Buka Browser
Write-Host "`n[5/5] Reload & Restart Apache service/process..." -ForegroundColor Green

$apacheReloaded = $false

if ($serverType -eq "Laragon") {
    # 1. Cek Service Windows lebih dulu
    $service = Get-Service -Name "*apache*" -ErrorAction SilentlyContinue | Select-Object -First 1
    if ($service -and $service.Status -eq 'Running') {
        try {
            Restart-Service -Name $service.Name -Force -ErrorAction Stop
            $apacheReloaded = $true
            Write-Host "Service Apache ($($service.Name)) berhasil di-restart!" -ForegroundColor Cyan
        } catch {
            Write-Warning "Gagal restart service Apache: $_"
        }
    }

    # 2. Jika bukan service atau restart service gagal, cari httpd.exe di folder Laragon secara otomatis
    if (-not $apacheReloaded) {
        $laragonApacheDir = "$($disk):\laragon\bin\apache"
        $httpdExe = Get-ChildItem -Path $laragonApacheDir -Filter "httpd.exe" -Recurse -ErrorAction SilentlyContinue | Select-Object -First 1
        if ($httpdExe) {
            $apacheRootDir = $httpdExe.Directory.Parent.FullName
            try {
                Get-Process -Name "httpd" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
                Start-Sleep -Seconds 1
                Start-Process -FilePath $httpdExe.FullName -ArgumentList "-d", "`"$apacheRootDir`"" -WorkingDirectory $apacheRootDir -WindowStyle Hidden
                $apacheReloaded = $true
                Write-Host "Proses Apache Laragon ($($httpdExe.FullName)) berhasil di-restart secara langsung!" -ForegroundColor Cyan
            } catch {
                Write-Warning "Gagal me-restart proses httpd.exe: $_"
            }
        }
    }
} else {
    # XAMPP Apache
    $service = Get-Service -Name "Apache2.4" -ErrorAction SilentlyContinue
    if ($service -and $service.Status -eq 'Running') {
        try {
            Restart-Service -Name "Apache2.4" -Force -ErrorAction Stop
            $apacheReloaded = $true
            Write-Host "Service Apache XAMPP berhasil di-restart!" -ForegroundColor Cyan
        } catch {
            Write-Warning "Gagal restart service XAMPP Apache: $_"
        }
    }

    if (-not $apacheReloaded) {
        $xamppHttpdPath = "$($disk):\xampp\apache\bin\httpd.exe"
        $xamppApacheDir = "$($disk):\xampp\apache"
        if (Test-Path $xamppHttpdPath) {
            try {
                Get-Process -Name "httpd" -ErrorAction SilentlyContinue | Stop-Process -Force -ErrorAction SilentlyContinue
                Start-Sleep -Seconds 1
                Start-Process -FilePath $xamppHttpdPath -ArgumentList "-d", "`"$xamppApacheDir`"" -WorkingDirectory $xamppApacheDir -WindowStyle Hidden
                $apacheReloaded = $true
                Write-Host "Proses Apache XAMPP berhasil di-restart secara langsung!" -ForegroundColor Cyan
            } catch {
                Write-Warning "Gagal me-restart proses Apache XAMPP: $_"
            }
        }
    }
}

if (-not $apacheReloaded) {
    Write-Host "PERINGATAN: Silakan reload Apache secara manual agar Virtual Host aktif!" -ForegroundColor Yellow
    if ($serverType -eq "Laragon") {
        Write-Host "  - Laragon: Menu -> Apache -> Reload" -ForegroundColor White
    } else {
        Write-Host "  - XAMPP: Stop Apache -> Start Apache" -ForegroundColor White
    }
}

Write-Host "`nMembuka browser..." -ForegroundColor Cyan
Start-Sleep -Seconds 3
Start-Process "http://$domain"

Write-Host ""
Write-Host "Setup selesai! Tab browser telah dibuka." -ForegroundColor Green
Write-Host "Terminal ini akan tertutup otomatis dalam 3 detik..." -ForegroundColor Gray
Start-Sleep -Seconds 3
Stop-Process -Id $PID -Force
