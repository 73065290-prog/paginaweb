@echo off
REM Prepara reportes PHPUnit/Clover/JUnit para SonarQube.
REM No ejecuta sonar-scanner (lo hace el estudiante).

setlocal
cd /d "%~dp0.."

echo === [1/2] composer install ===
call composer install --no-interaction
if errorlevel 1 exit /b 1

echo === [2/2] PHPUnit + cobertura (PCOV) ===
call composer test:coverage
if errorlevel 1 exit /b 1

echo.
echo Listo. Verifica:
echo   docs\pruebas\evidencias\coverage\clover.xml
echo   docs\pruebas\evidencias\junit.xml
echo.
echo Luego ejecuta (con SonarQube arriba y SONAR_TOKEN):
echo   scripts\scan-sonar.bat
echo.
endlocal
