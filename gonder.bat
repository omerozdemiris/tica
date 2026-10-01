@echo off
echo Dosyalar sunucuya aktariliyor...
robocopy . "Z:\projem" /MIR /R:2 /W:5
echo Islem tamamlandi!
pause