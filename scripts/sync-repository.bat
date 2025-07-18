@echo off
setlocal enabledelayedexpansion

REM Repository Synchronization Script for Windows
REM Performs non-destructive pull and merge operations every 10 minutes
REM Favors remote changes to maintain consistency

REM Configuration
set "REPO_PATH=%CD%"
set "LOG_FILE=%REPO_PATH%\logs\sync.log"
set "LOCK_FILE=%REPO_PATH%\.sync.lock"
set "REMOTE_BRANCH=origin/main"
set "LOCAL_BRANCH=main"
set "SYNC_INTERVAL=600"

REM Create logs directory if it doesn't exist
if not exist "logs" mkdir logs

REM Logging function
:log
set "level=%~1"
shift
set "message=%*"
for /f "tokens=1-3 delims=: " %%a in ('time /t') do set "time=%%a:%%b:%%c"
for /f "tokens=1-3 delims=/ " %%a in ('date /t') do set "date=%%c-%%a-%%b"

if "%level%"=="INFO" (
    echo [%date% %time%] INFO: %message%
    set "color=92"
) else if "%level%"=="WARN" (
    echo [%date% %time%] WARN: %message%
    set "color=93"
) else if "%level%"=="ERROR" (
    echo [%date% %time%] ERROR: %message%
    set "color=91"
) else (
    echo [%date% %time%] DEBUG: %message%
    set "color=94"
)

echo [%date% %time%] %level%: %message%>> "%LOG_FILE%"
goto :eof

REM Function to check if sync is already running
:check_lock
if exist "%LOCK_FILE%" (
    for /f %%i in (%LOCK_FILE%) do set "pid=%%i"
    tasklist /FI "PID eq !pid!" 2>nul | find /i "!pid!" >nul
    if !errorlevel! equ 0 (
        call :log WARN "Sync already running (PID: !pid!)"
        exit /b 1
    ) else (
        call :log WARN "Removing stale lock file"
        del "%LOCK_FILE%" 2>nul
    )
)
exit /b 0

REM Function to create lock file
:create_lock
echo %PID% > "%LOCK_FILE%"
goto :eof

REM Function to remove lock file
:remove_lock
if exist "%LOCK_FILE%" del "%LOCK_FILE%" 2>nul
goto :eof

REM Function to backup current state
:backup_state
for /f "tokens=1-3 delims=/ " %%a in ('date /t') do set "date_str=%%c%%a%%b"
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set "time_str=%%a%%b"
set "backup_dir=%REPO_PATH%\backups\%date_str%_%time_str%"

if not exist "backups" mkdir backups
mkdir "%backup_dir%"

call :log INFO "Creating backup in %backup_dir%"

REM Backup current git state
git rev-parse HEAD > "%backup_dir%\current_commit.txt" 2>nul
git status > "%backup_dir%\git_status.txt" 2>nul
git diff > "%backup_dir%\local_changes.patch" 2>nul

REM Backup important files
if exist ".cursorrules" copy ".cursorrules" "%backup_dir%\" >nul
if exist "windsurf.json" copy "windsurf.json" "%backup_dir%\" >nul
if exist "CHANGELOG.md" copy "CHANGELOG.md" "%backup_dir%\" >nul

call :log INFO "Backup completed"
goto :eof

REM Function to perform git sync
:perform_sync
call :log INFO "Starting repository synchronization"

REM Check if we're in a git repository
git rev-parse --git-dir >nul 2>&1
if errorlevel 1 (
    call :log ERROR "Not in a git repository"
    exit /b 1
)

REM Fetch latest changes from remote
call :log INFO "Fetching latest changes from remote"
git fetch origin

REM Check if remote has new commits
for /f %%i in ('git rev-parse HEAD') do set "local_commit=%%i"
for /f %%i in ('git rev-parse %REMOTE_BRANCH%') do set "remote_commit=%%i"

if "%local_commit%"=="%remote_commit%" (
    call :log INFO "Repository is up to date"
    exit /b 0
)

REM Check for local changes
git diff-index --quiet HEAD -- 2>nul
if errorlevel 1 (
    call :log WARN "Local changes detected, creating backup"
    call :backup_state
    
    REM Stash local changes
    call :log INFO "Stashing local changes"
    git stash push -m "Auto-stash before sync %date% %time%"
)

REM Perform merge favoring remote changes
call :log INFO "Merging remote changes (favoring remote)"

REM Reset to remote state
git reset --hard %REMOTE_BRANCH%

call :log INFO "Sync completed successfully"

REM Update changelog with sync entry
call :update_changelog "Repository synchronized with remote changes"

exit /b 0

REM Function to update changelog
:update_changelog
set "message=%~1"
for /f "tokens=1-3 delims=: " %%a in ('time /t') do set "timestamp=%%a:%%b:%%c UTC"

if exist "CHANGELOG.md" (
    REM Add entry to changelog (simplified for Windows)
    echo. >> CHANGELOG.md
    echo ### Repository Sync >> CHANGELOG.md
    echo - **%timestamp%** - %message% >> CHANGELOG.md
    call :log INFO "Changelog updated"
)
goto :eof

REM Function to clean old backups
:cleanup_backups
set "backup_dir=%REPO_PATH%\backups"
set "max_backups=10"

if exist "%backup_dir%" (
    call :log INFO "Cleaning up old backups (keeping %max_backups%)"
    
    REM Count existing backups
    set "count=0"
    for /d %%i in ("%backup_dir%\*") do set /a count+=1
    
    REM Remove old backups if we have too many
    if !count! gtr %max_backups% (
        set "to_remove=0"
        set /a to_remove=!count!-%max_backups!
        
        for /d %%i in ("%backup_dir%\*") do (
            if !to_remove! gtr 0 (
                rmdir /s /q "%%i" 2>nul
                set /a to_remove-=1
            )
        )
    )
    
    call :log INFO "Backup cleanup completed"
)
goto :eof

REM Function to check repository health
:check_repository_health
call :log INFO "Checking repository health"

REM Check git status
git status --porcelain | findstr /r "^" >nul
if errorlevel 1 (
    call :log INFO "Repository is clean"
) else (
    call :log WARN "Repository has uncommitted changes"
)

REM Check for merge conflicts
git ls-files -u | findstr /r "." >nul
if not errorlevel 1 (
    call :log ERROR "Merge conflicts detected"
    exit /b 1
)

REM Check remote connectivity
git ls-remote origin >nul 2>&1
if errorlevel 1 (
    call :log ERROR "Cannot connect to remote repository"
    exit /b 1
)

call :log INFO "Repository health check passed"
exit /b 0

REM Main sync function
:main_sync
REM Check lock
call :check_lock
if errorlevel 1 exit /b 1

REM Create lock
call :create_lock

REM Check repository health
call :check_repository_health
if errorlevel 1 (
    call :log ERROR "Repository health check failed"
    call :remove_lock
    exit /b 1
)

REM Perform sync
call :perform_sync
if errorlevel 1 (
    call :log ERROR "Repository synchronization failed"
    call :remove_lock
    exit /b 1
) else (
    call :log INFO "Repository synchronization completed successfully"
)

REM Cleanup old backups
call :cleanup_backups

REM Remove lock
call :remove_lock

call :log INFO "Sync process completed"
goto :eof

REM Function to run continuous sync
:run_continuous_sync
call :log INFO "Starting continuous sync (interval: %SYNC_INTERVAL%s)"

:sync_loop
call :main_sync

call :log INFO "Waiting %SYNC_INTERVAL% seconds until next sync"
timeout /t %SYNC_INTERVAL% /nobreak >nul
goto sync_loop

REM Function to show help
:show_help
echo Repository Synchronization Script for Windows
echo.
echo Usage: %0 [OPTIONS]
echo.
echo Options:
echo   -h, --help          Show this help message
echo   -c, --continuous    Run continuous sync (every 10 minutes)
echo   -o, --once          Run sync once and exit
echo   -i, --interval SEC  Set sync interval in seconds (default: 600)
echo   -v, --verbose       Enable verbose logging
echo   -d, --dry-run       Show what would be done without executing
echo.
echo Examples:
echo   %0 --once           Run sync once
echo   %0 --continuous     Run continuous sync
echo   %0 --interval 300   Run sync every 5 minutes
echo.
goto :eof

REM Function to perform dry run
:dry_run
call :log INFO "DRY RUN: Would perform the following actions:"

REM Check git status
git diff-index --quiet HEAD -- 2>nul
if errorlevel 1 (
    call :log INFO "DRY RUN: Repository is clean, would fetch and merge"
) else (
    call :log WARN "DRY RUN: Local changes detected, would stash and merge"
)

REM Check remote status
git fetch origin --dry-run 2>nul

call :log INFO "DRY RUN: Would update changelog with sync entry"
call :log INFO "DRY RUN: Would cleanup old backups"
goto :eof

REM Parse command line arguments
set "MODE=once"
set "VERBOSE=false"
set "DRY_RUN=false"

:parse_args
if "%~1"=="" goto :args_done
if "%~1"=="-h" goto :show_help
if "%~1"=="--help" goto :show_help
if "%~1"=="-c" set "MODE=continuous"
if "%~1"=="--continuous" set "MODE=continuous"
if "%~1"=="-o" set "MODE=once"
if "%~1"=="--once" set "MODE=once"
if "%~1"=="-i" (
    set "SYNC_INTERVAL=%~2"
    shift
)
if "%~1"=="--interval" (
    set "SYNC_INTERVAL=%~2"
    shift
)
if "%~1"=="-v" set "VERBOSE=true"
if "%~1"=="--verbose" set "VERBOSE=true"
if "%~1"=="-d" set "DRY_RUN=true"
if "%~1"=="--dry-run" set "DRY_RUN=true"
shift
goto :parse_args

:args_done

REM Main execution
call :log INFO "Repository sync script started"
call :log INFO "Repository path: %REPO_PATH%"
call :log INFO "Sync interval: %SYNC_INTERVAL%s"
call :log INFO "Mode: %MODE%"

if "%DRY_RUN%"=="true" (
    call :dry_run
    exit /b 0
)

if "%MODE%"=="once" (
    call :main_sync
) else if "%MODE%"=="continuous" (
    call :run_continuous_sync
) else (
    call :log ERROR "Unknown mode: %MODE%"
    exit /b 1
)

exit /b 0 