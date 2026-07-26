# Script Setup Proyek Vibeforge
$appName = Read-Host "Masukkan nama aplikasi Anda (tanpa spasi, gunakan _ atau -)"
$serverType = Read-Host "Apakah Anda menggunakan Laragon atau XAMPP? (tulis: Laragon/XAMPP)"

Write-Host "Mengunduh template..."
npx -y degit iqbalmurtadho24/vibeforge $appName
Set-Location $appName

if ($serverType -eq "Laragon") {
    Write-Host "Setup Virtual Host Laragon..."
    # Laragon vhost logic (simplified for script)
    # Ini butuh admin privilege untuk modify hosts/vhost config
    # Placeholder: arahkan user ke langkah manual jika tidak bisa auto-modify config
    Write-Host "Untuk Laragon, mohon tambahkan $appName.test di menu Virtual Host (Sites Enabled) dan Reload Apache."
} else {
    Write-Host "Setup Virtual Host XAMPP..."
    Write-Host "Untuk XAMPP, tambahkan VirtualHost di httpd-vhosts.conf untuk $appName"
}
Write-Host "Selesai!"
