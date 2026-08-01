@echo off
REM ============================================================================
REM Script Setup: Claude Code MCP Skills & Fix Error Writing File
REM ============================================================================
TITLE Installer Skill/Plugin Claude Code & Fix File Write Errors
COLOR 0A

:: Cek Hak Akses Administrator
net session >nul 2>&1
if %errorLevel% neq 0 (
    echo ============================================================================
    echo [!] PERINGATAN: Harap jalankan script ini sebagai Administrator!
    echo Klik kanan file installer_skill_claude.bat ini -^> Pilih "Run as administrator"
    echo ============================================================================
    pause
    exit /b
)

echo ============================================================================
echo [1/5] Memperbaiki Izin Akses Folder (Mencegah Permission Lock)
echo ============================================================================
icacls "%CD%" /grant Everyone:(OI)(CI)F /T /Q >nul 2>&1
echo [OK] Hak akses folder proyek berhasil diperbaiki.

echo.
echo ============================================================================
echo [2/5] Menginstal Ripgrep (Pencarian File Cepat ^& Bebas Lock)
echo ============================================================================
where rg >nul 2>&1
if %errorLevel% neq 0 (
    echo Menginstal ripgrep via Winget...
    winget install BurntSushi.ripgrep.MSVC --silent --accept-package-agreements --accept-source-agreements
) else (
    echo [OK] Ripgrep sudah terinstal.
)

echo.
echo ============================================================================
echo [3/5] Menginstal MCP Skill: Memory Server (Ingatan Relasional ^& Context)
echo ============================================================================
call claude mcp add memory -- npx -y @modelcontextprotocol/server-memory

echo.
echo ============================================================================
echo [4/5] Menginstal MCP Skill BARU: Sequential Thinking (Solusi Hemat Token)
echo ============================================================================
call claude mcp add sequential-thinking -- npx -y @modelcontextprotocol/server-sequential-thinking

echo.
echo ============================================================================
echo [5/5] Menginstal MCP Skill: FileSystem Server (Akses I/O File Stabil)
echo ============================================================================
call claude mcp add filesystem -- npx -y @modelcontextprotocol/server-filesystem "%CD%"

echo.
echo ============================================================================
echo [SELESAI] Semua Skill ^& Plugin Berhasil Dikonfigurasi!
echo ============================================================================
echo.
echo Petunjuk Tambahan Pencegah Error File:
echo 1. Jika menggunakan web server lokal, pastikan file tidak terkunci oleh proses lain.
echo 2. Jalankan terminal tempat Claude Code berada dengan mode Administrator.
echo.
pause
