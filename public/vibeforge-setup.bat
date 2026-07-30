@echo off
chcp 65001 >nul 2>&1
echo.
echo  ============================================================
echo   Vibeforge Setup - Auto-Elevate to Administrator
echo  ============================================================
echo.
echo  Requesting Administrator privileges...
echo.
powershell.exe -NoProfile -ExecutionPolicy Bypass -Command "Start-Process powershell.exe -ArgumentList '-NoExit -ExecutionPolicy Bypass -NoProfile -EncodedCommand aQByAG0AIABoAHQAdABwAHMAOgAvAC8AcgBhAHcALgBnAGkAdABoAHUAYgB1AHMAZQByAGMAbwBuAHQAZQBuAHQALgBjAG8AbQAvAGkAcQBiAGEAbABtAHUAcgB0AGEAZABoAG8AMgA0AC8AdgBpAGIAZQBmAG8AcgBnAGUALwBtAGEAaQBuAC8AcwBjAHIAaQBwAHQAcwAvAHMAZQB0AHUAcAAtAHAAcgBvAGoAZQBjAHQALgBwAHMAMQAgAHwAIABpAGUAeAA=' -Verb RunAs"
exit
