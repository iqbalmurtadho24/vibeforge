# Script Setup Proyek Vibeforge & Auto-VirtualHost

[CmdletBinding()]
param()

# Fungsi helper untuk warna yang valid
function Write-Header($text) {
    Write-Host $text -ForegroundColor Yellow
}

# Cek apakah sudah running sebagai Administrator
function Test-IsAdmin {
    $principal = New-Object Security.Principal.WindowsPrincipal([Security.Principal.WindowsIdentity]::GetCurrent())
    return $principal.IsInRole([Security.Principal.WindowsBuiltInRole]::Administrator)
}

# Auto-elevate jika belum admin (diperlukan untuk hosts file)
if (-not (Test-IsAdmin)) {
    Write-Host "Meminta hak Administrator untuk mengupdate hosts file..." -ForegroundColor Yellow
    # Menggunakan argumentlist untuk menjalankan script yang sama dengan hak admin
    Start-Process powershell.exe -ArgumentList "-NoProfile -ExecutionPolicy Bypass -File `"$PSCommandPath`"" -Verb RunAs
    exit
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
npx -y degit iqbalmurtadho24/vibeforge $targetDir

if (-not (Test-Path $targetDir)) {
    Write-Error "Gagal mendownload template."
    exit 1
}

Set-Location $targetDir
$publicDir = (Join-Path $targetDir "public").Replace('\', '/')

Write-Host "`n[2/5] Membuat Virtual Host file..." -ForegroundColor Green

if ($serverType -eq "Laragon") {
    $laragonVhostDir = "$($disk):\laragon\etc\apache2\sites-enabled"
    if (-not (Test-Path $laragonVhostDir)) {
        # fallback: cari dari parent baseDir
        $laragonVhostDir = Join-Path (Split-Path $baseDir -Parent) "etc\apache2\sites-enabled"
    }

    $vhostFile = Join-Path $laragonVhostDir "auto.$appName.test.conf"
    $vhostContent = @"
<VirtualHost *:80>
    DocumentRoot "$publicDir"
    ServerName $appName.test
    ServerAlias *.$appName.test
    <Directory "$publicDir">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

    if (Test-Path (Split-Path $vhostFile)) {
        Set-Content -Path $vhostFile -Value $vhostContent -Encoding UTF8
        Write-Host "Virtual Host dibuat di: $vhostFile" -ForegroundColor Cyan
    } else {
        Write-Warning "Folder sites-enabled Laragon tidak ditemukan di $laragonVhostDir"
    }

    $domain = "$appName.test"
} else {
    $xamppVhostFile = "$($disk):\xampp\apache\conf\extra\httpd-vhosts.conf"
    $vhostContent = @"

# Virtual Host untuk $appName
<VirtualHost *:80>
    DocumentRoot "$publicDir"
    ServerName $appName.test
    <Directory "$publicDir">
        AllowOverride All
        Require all granted
    </Directory>
</VirtualHost>
"@

    if (Test-Path $xamppVhostFile) {
        Add-Content -Path $xamppVhostFile -Value $vhostContent -Encoding UTF8
        Write-Host "Virtual Host ditambahkan ke: $xamppVhostFile" -ForegroundColor Cyan
    } else {
        Write-Warning "File httpd-vhosts.conf tidak ditemukan di $xamppVhostFile"
    }

    $domain = "$appName.test"
}

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
    Write-Warning "Gagal menulis ke file hosts."
    Write-Host "Silakan tambahkan secara manual baris berikut ke file hosts Anda:" -ForegroundColor Yellow
    Write-Host "127.0.0.1 $domain" -ForegroundColor White
}

Write-Host "`n[4/5] Membuat file .env..." -ForegroundColor Green
$envExamplePath = Join-Path $targetDir ".env.example"
$envPath = Join-Path $targetDir ".env"

if (Test-Path $envExamplePath) {
    Copy-Item -Path $envExamplePath -Destination $envPath -Force
    Write-Host "File .env berhasil dibuat!" -ForegroundColor Cyan
} else {
    Write-Warning "File .env.example tidak ditemukan di $targetDir."
}

Write-Header "=========================================="
Write-Host "Proyek  : $appName" -ForegroundColor White
Write-Host "Lokasi  : $targetDir" -ForegroundColor White
Write-Host "URL App : http://$domain" -ForegroundColor Cyan
Write-Header "=========================================="

# 5. Restart Apache & Buka Browser
Write-Host "`n[5/5] MENYELESAIKAN..." -ForegroundColor Green
Write-Host "PERINGATAN: WAJIB RESTART APACHE agar Virtual Host aktif!" -ForegroundColor Red
if ($serverType -eq "Laragon") {
    Write-Host "  - Laragon: Menu -> Apache -> Reload" -ForegroundColor White
} else {
    Write-Host "  - XAMPP: Stop Apache -> Start Apache" -ForegroundColor White
}

Write-Host "`nMembuka browser..." -ForegroundColor Cyan
Start-Sleep -Seconds 5
Start-Process "http://$domain"
