@echo off
REM Prepara reportes PHPUnit/Clover/JUnit para SonarQube.
REM No ejecuta sonar-scanner (lo hace scripts\scan-sonar.bat).

setlocal
cd /d "%~dp0.."

where composer >nul 2>&1
if errorlevel 1 goto sin_composer

echo === [1/2] composer install ===
call composer install --no-interaction
if errorlevel 1 exit /b 1

echo === [2/2] PHPUnit + cobertura (PCOV) ===
call composer test:coverage
if errorlevel 1 exit /b 1
goto listo

:sin_composer
echo composer no esta en el PATH. Se usa el PHP de XAMPP si vendor\bin\phpunit existe.
if not exist "vendor\bin\phpunit" (
  echo ERROR: falta vendor\bin\phpunit y composer no esta instalado.
  exit /b 1
)
set "PHP_EXE="
where php >nul 2>&1 && set "PHP_EXE=php"
if not defined PHP_EXE if exist "C:\xampp\php\php.exe" set "PHP_EXE=C:\xampp\php\php.exe"
if not defined PHP_EXE (
  echo ERROR: php no esta en el PATH.
  exit /b 1
)
echo === PHPUnit + cobertura (PCOV) ===
"%PHP_EXE%" -d extension=pcov -d pcov.enabled=1 vendor\bin\phpunit --coverage-text --coverage-clover=docs\pruebas\evidencias\coverage\clover.xml --coverage-html=docs\pruebas\evidencias\coverage\html --log-junit=docs\pruebas\evidencias\junit.xml --testdox-html=docs\pruebas\evidencias\testdox.html
if errorlevel 1 exit /b 1

:listo
echo.
echo Listo. Verifica:
echo   docs\pruebas\evidencias\coverage\clover.xml
echo   docs\pruebas\evidencias\junit.xml
echo.
echo Luego ejecuta (con SonarQube arriba y SONAR_TOKEN):
echo   scripts\scan-sonar.bat
echo.
endlocal
