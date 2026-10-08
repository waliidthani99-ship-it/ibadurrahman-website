@echo off
title Madrasatul Ibadu-rrahman - Server
cd /d "%~dp0"
echo ============================================
echo   Inaanzisha server ya website...
echo   Usifunge dirisha hili wakati unatumia site.
echo ============================================
echo.
echo   Fungua:  http://localhost:8000/
echo   Admin:   http://localhost:8000/admin/login.php
echo.
"C:\Users\user\.config\herd-lite\bin\php.exe" -S localhost:8000 -t "%~dp0"
pause