@echo off
echo ========================================
echo   PHP Development Server
echo ========================================
echo.
echo Server startet auf Port 8000...
echo.
echo URLs:
echo   http://localhost:8000/
echo   http://localhost:8000/viewer.html
echo   http://localhost:8000/watch.html
echo.
echo Druecke Strg+C zum Beenden
echo.

cd /d "%~dp0"
php -S localhost:8000

pause
