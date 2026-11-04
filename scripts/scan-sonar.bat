@echo off
REM Ejecuta sonar-scanner contra el proyecto (requiere SonarQube + token).
REM Uso:
REM   set SONAR_TOKEN=tu_token
REM   scripts\scan-sonar.bat
REM Opcional:
REM   set SONAR_HOST_URL=http://localhost:9000

setlocal
cd /d "%~dp0.."

if "%SONAR_TOKEN%"=="" (
  echo ERROR: define SONAR_TOKEN antes de escanear.
  echo   set SONAR_TOKEN=tu_token
  exit /b 1
)

if "%SONAR_HOST_URL%"=="" set SONAR_HOST_URL=http://localhost:9000

if not exist "docs\pruebas\evidencias\coverage\clover.xml" (
  echo AVISO: no hay clover.xml. El analisis sigue sin cobertura.
  echo Genera el reporte con scripts\prepare-sonar.bat cuando PCOV este disponible.
)

set "SCANNER="
where sonar-scanner >nul 2>&1
if not errorlevel 1 set "SCANNER=sonar-scanner"
if not defined SCANNER if exist "%USERPROFILE%\sonar-scanner\bin\sonar-scanner.bat" set "SCANNER=%USERPROFILE%\sonar-scanner\bin\sonar-scanner.bat"
if not defined SCANNER (
  echo ERROR: sonar-scanner no esta en el PATH ni en %%USERPROFILE%%\sonar-scanner\bin.
  echo Instala SonarScanner CLI y vuelve a intentar.
  exit /b 1
)

echo Host: %SONAR_HOST_URL%
echo Project: Pagina_Web_V2
call "%SCANNER%" -Dsonar.host.url=%SONAR_HOST_URL% -Dsonar.token=%SONAR_TOKEN%
set ERR=%ERRORLEVEL%
if %ERR% neq 0 exit /b %ERR%

echo.
echo Analisis enviado. Abre el dashboard en SonarQube y completa:
echo   docs\pruebas\05-guia-sonarqube.md
echo   docs\pruebas\INFORME-FINAL.md
endlocal
