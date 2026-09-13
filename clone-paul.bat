@echo off
cd /d D:\laravel13.x
git clone https://github.com/ChristopherKahler/paul.git paul-cursor
if %errorlevel% == 0 (
    echo Clone succeeded
    dir paul-cursor
) else (
    echo Clone failed with error %errorlevel%
)
