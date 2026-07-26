# Script Setup Proyek Vibeforge & Auto-VirtualHost

[CmdletBinding()]
param()

$appName = Read-Host "Masukkan nama aplikasi Anda (contoh: toko_online, tanpa spasi)"
$appName = $appName.Trim().ToLower() -replace '\s+', '_'

if (-not $appName) {
    Write-Error "Nama aplikasi tidak boleh kosong."
    exit 1
}

$currentDir = (Get-Location).Path

# Otomatis deteksi web server berdasarkan lokasi direktori (CD)
if ($currentDir -like "*xampp*" -or $currentDir -like "*htdocs*") {
    $serverType = "XAMPP"
} elseif ($currentDir -like "*laragon*" -or $currentDir -like "*www*") {
    $serverType = "Laragon"
} else {
    # Fallback jika tidak terdeteksi dari path saat ini
    if (Test-Path "C:\laragon") {
        $serverType = "Laragon"
    } else {
        $serverType = "XAMPP"
    }
}

Write-Host "Terdeteksi Web Server: $serverType ($currentDir)" -ForegroundColor Cyan

$targetDir = Join-Path $currentDir $appName

Write-Host "`n[1/4] Mengunduh template Vibeforge..." -ForegroundColor Green
npx -y degit iqbalmurtadho24/vibeforge $targetDir

if (-not (Test-Path $targetDir)) {
    Write-Error "Gagal mendownload template."
    exit 1
}

Set-Location $targetDir
$publicDir = (Join-Path $targetDir "public").Replace('\', '/')

Write-Host "`n[2/4] Membuat Virtual Host file..." -ForegroundColor Green

if ($serverType -eq "Laragon") {
    $laragonVhostDir = "C:\laragon\etc\apache2\sites-enabled"
    if (-not (Test-Path $laragonVhostDir)) {
        $parentDir = Split-Path $currentDir -Parent
        $laragonVhostDir = Join-Path $parentDir "etc\apache2\sites-enabled"
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
        Write-Warning "Folder sites-enabled Laragon tidak ditemukan."
    }

    $domain = "$appName.test"
} else {
    $xamppVhostFile = "C:\xampp\apache\conf\extra\httpd-vhosts.conf"
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
        Write-Warning "File httpd-vhosts.conf tidak ditemukan di $xamppVhostFile."
    }

    $domain = "$appName.test"
}

Write-Host "`n[3/4] Mengupdate file Windows hosts (127.0.0.1 $domain)..." -ForegroundColor Green
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
    Write-Warning "Tidak dapat menulis ke C:\Windows\System32\drivers\etc\hosts (butuh privilege Administrator)."
    Write-Host "Silakan tambahkan secara manual baris berikut ke file hosts Anda:" -ForegroundColor Yellow
    Write-Host "127.0.0.1 $domain" -ForegroundColor White
}

Write-Host "`n[4/4] Selesai!" -ForegroundColor Green
Write-Host "==========================================" -ForegroundColor Header
Write-Host "Proyek  : $appName" -ForegroundColor White
Write-Host "Lokasi  : $targetDir" -ForegroundColor White
Write-Host "URL App : http://$domain" -ForegroundColor Cyan
Write-Host "==========================================" -ForegroundColor Header
Write-Host "Jangan lupa untuk meng-reload/restart Apache di $serverType!" -ForegroundColor Yellow
