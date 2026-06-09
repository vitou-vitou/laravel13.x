@echo off
cd /d "%~dp0"
powershell -ExecutionPolicy Bypass -File "%~dp0check.ps1"
pause
