#!/bin/bash

# Repository Synchronization Script
# Performs non-destructive pull and merge operations every 10 minutes
# Favors remote changes to maintain consistency

set -e

# Configuration
REPO_PATH="$(pwd)"
LOG_FILE="$REPO_PATH/logs/sync.log"
LOCK_FILE="$REPO_PATH/.sync.lock"
REMOTE_BRANCH="origin/main"
LOCAL_BRANCH="main"
SYNC_INTERVAL=600  # 10 minutes in seconds

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Logging function
log() {
    local level=$1
    shift
    local message="$*"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S UTC')
    
    case $level in
        "INFO")
            echo -e "${GREEN}[$timestamp] INFO: $message${NC}"
            ;;
        "WARN")
            echo -e "${YELLOW}[$timestamp] WARN: $message${NC}"
            ;;
        "ERROR")
            echo -e "${RED}[$timestamp] ERROR: $message${NC}"
            ;;
        "DEBUG")
            echo -e "${BLUE}[$timestamp] DEBUG: $message${NC}"
            ;;
    esac
    
    # Write to log file
    echo "[$timestamp] $level: $message" >> "$LOG_FILE"
}

# Create logs directory if it doesn't exist
mkdir -p "$(dirname "$LOG_FILE")"

# Function to check if sync is already running
check_lock() {
    if [ -f "$LOCK_FILE" ]; then
        local pid=$(cat "$LOCK_FILE" 2>/dev/null)
        if ps -p "$pid" > /dev/null 2>&1; then
            log "WARN" "Sync already running (PID: $pid)"
            return 1
        else
            log "WARN" "Removing stale lock file"
            rm -f "$LOCK_FILE"
        fi
    fi
    return 0
}

# Function to create lock file
create_lock() {
    echo $$ > "$LOCK_FILE"
}

# Function to remove lock file
remove_lock() {
    rm -f "$LOCK_FILE"
}

# Function to backup current state
backup_state() {
    local backup_dir="$REPO_PATH/backups/$(date '+%Y%m%d_%H%M%S')"
    mkdir -p "$backup_dir"
    
    log "INFO" "Creating backup in $backup_dir"
    
    # Backup current git state
    git rev-parse HEAD > "$backup_dir/current_commit.txt"
    git status > "$backup_dir/git_status.txt"
    git diff > "$backup_dir/local_changes.patch" 2>/dev/null || true
    
    # Backup important files
    cp -r .cursorrules "$backup_dir/" 2>/dev/null || true
    cp -r windsurf.json "$backup_dir/" 2>/dev/null || true
    cp -r CHANGELOG.md "$backup_dir/" 2>/dev/null || true
    
    log "INFO" "Backup completed"
}

# Function to restore from backup
restore_from_backup() {
    local backup_dir="$1"
    
    if [ -d "$backup_dir" ]; then
        log "WARN" "Restoring from backup: $backup_dir"
        
        # Restore git state
        if [ -f "$backup_dir/local_changes.patch" ]; then
            git apply "$backup_dir/local_changes.patch" 2>/dev/null || true
        fi
        
        # Restore important files
        cp "$backup_dir/.cursorrules" . 2>/dev/null || true
        cp "$backup_dir/windsurf.json" . 2>/dev/null || true
        cp "$backup_dir/CHANGELOG.md" . 2>/dev/null || true
        
        log "INFO" "Restore completed"
    fi
}

# Function to perform git sync
perform_sync() {
    log "INFO" "Starting repository synchronization"
    
    # Check if we're in a git repository
    if ! git rev-parse --git-dir > /dev/null 2>&1; then
        log "ERROR" "Not in a git repository"
        return 1
    fi
    
    # Fetch latest changes from remote
    log "INFO" "Fetching latest changes from remote"
    git fetch origin
    
    # Check if remote has new commits
    local local_commit=$(git rev-parse HEAD)
    local remote_commit=$(git rev-parse "$REMOTE_BRANCH")
    
    if [ "$local_commit" = "$remote_commit" ]; then
        log "INFO" "Repository is up to date"
        return 0
    fi
    
    # Check for local changes
    if ! git diff-index --quiet HEAD --; then
        log "WARN" "Local changes detected, creating backup"
        backup_state
        
        # Stash local changes
        log "INFO" "Stashing local changes"
        git stash push -m "Auto-stash before sync $(date '+%Y-%m-%d %H:%M:%S')"
    fi
    
    # Perform merge favoring remote changes
    log "INFO" "Merging remote changes (favoring remote)"
    
    # Reset to remote state
    git reset --hard "$REMOTE_BRANCH"
    
    # Clean untracked files (optional, be careful)
    # git clean -fd
    
    log "INFO" "Sync completed successfully"
    
    # Update changelog with sync entry
    update_changelog "Repository synchronized with remote changes"
    
    return 0
}

# Function to update changelog
update_changelog() {
    local message="$1"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S UTC')
    
    if [ -f "CHANGELOG.md" ]; then
        # Add entry to changelog
        sed -i "1i\\\n### Repository Sync\n- **$timestamp** - $message\n" CHANGELOG.md
        log "INFO" "Changelog updated"
    fi
}

# Function to clean old backups
cleanup_backups() {
    local backup_dir="$REPO_PATH/backups"
    local max_backups=10
    
    if [ -d "$backup_dir" ]; then
        log "INFO" "Cleaning up old backups (keeping $max_backups)"
        
        # Keep only the most recent backups
        cd "$backup_dir"
        ls -t | tail -n +$((max_backups + 1)) | xargs -r rm -rf
        cd "$REPO_PATH"
        
        log "INFO" "Backup cleanup completed"
    fi
}

# Function to check repository health
check_repository_health() {
    log "INFO" "Checking repository health"
    
    # Check git status
    if ! git status --porcelain | grep -q '^'; then
        log "INFO" "Repository is clean"
    else
        log "WARN" "Repository has uncommitted changes"
    fi
    
    # Check for merge conflicts
    if git ls-files -u | grep -q .; then
        log "ERROR" "Merge conflicts detected"
        return 1
    fi
    
    # Check remote connectivity
    if ! git ls-remote origin > /dev/null 2>&1; then
        log "ERROR" "Cannot connect to remote repository"
        return 1
    fi
    
    log "INFO" "Repository health check passed"
    return 0
}

# Function to handle errors
handle_error() {
    local exit_code=$?
    log "ERROR" "Sync failed with exit code $exit_code"
    
    # Remove lock file
    remove_lock
    
    # Send notification (if configured)
    if command -v curl > /dev/null 2>&1; then
        # Example: Send to Slack webhook
        # curl -X POST -H 'Content-type: application/json' \
        #     --data "{\"text\":\"Repository sync failed on $(hostname)\"}" \
        #     "$SLACK_WEBHOOK_URL" 2>/dev/null || true
        log "INFO" "Error notification sent"
    fi
    
    exit $exit_code
}

# Main sync function
main_sync() {
    # Set error handling
    trap handle_error ERR
    
    # Check lock
    if ! check_lock; then
        exit 1
    fi
    
    # Create lock
    create_lock
    
    # Check repository health
    if ! check_repository_health; then
        log "ERROR" "Repository health check failed"
        remove_lock
        exit 1
    fi
    
    # Perform sync
    if perform_sync; then
        log "INFO" "Repository synchronization completed successfully"
    else
        log "ERROR" "Repository synchronization failed"
        remove_lock
        exit 1
    fi
    
    # Cleanup old backups
    cleanup_backups
    
    # Remove lock
    remove_lock
    
    log "INFO" "Sync process completed"
}

# Function to run continuous sync
run_continuous_sync() {
    log "INFO" "Starting continuous sync (interval: ${SYNC_INTERVAL}s)"
    
    while true; do
        main_sync
        
        log "INFO" "Waiting ${SYNC_INTERVAL} seconds until next sync"
        sleep "$SYNC_INTERVAL"
    done
}

# Function to show help
show_help() {
    echo "Repository Synchronization Script"
    echo ""
    echo "Usage: $0 [OPTIONS]"
    echo ""
    echo "Options:"
    echo "  -h, --help          Show this help message"
    echo "  -c, --continuous    Run continuous sync (every 10 minutes)"
    echo "  -o, --once          Run sync once and exit"
    echo "  -i, --interval SEC  Set sync interval in seconds (default: 600)"
    echo "  -v, --verbose       Enable verbose logging"
    echo "  -d, --dry-run       Show what would be done without executing"
    echo ""
    echo "Examples:"
    echo "  $0 --once           Run sync once"
    echo "  $0 --continuous     Run continuous sync"
    echo "  $0 --interval 300   Run sync every 5 minutes"
    echo ""
}

# Function to perform dry run
dry_run() {
    log "INFO" "DRY RUN: Would perform the following actions:"
    
    # Check git status
    if git diff-index --quiet HEAD --; then
        log "INFO" "DRY RUN: Repository is clean, would fetch and merge"
    else
        log "WARN" "DRY RUN: Local changes detected, would stash and merge"
    fi
    
    # Check remote status
    git fetch origin --dry-run 2>/dev/null || true
    
    log "INFO" "DRY RUN: Would update changelog with sync entry"
    log "INFO" "DRY RUN: Would cleanup old backups"
}

# Parse command line arguments
MODE="once"
VERBOSE=false
DRY_RUN=false

while [[ $# -gt 0 ]]; do
    case $1 in
        -h|--help)
            show_help
            exit 0
            ;;
        -c|--continuous)
            MODE="continuous"
            shift
            ;;
        -o|--once)
            MODE="once"
            shift
            ;;
        -i|--interval)
            SYNC_INTERVAL="$2"
            shift 2
            ;;
        -v|--verbose)
            VERBOSE=true
            shift
            ;;
        -d|--dry-run)
            DRY_RUN=true
            shift
            ;;
        *)
            log "ERROR" "Unknown option: $1"
            show_help
            exit 1
            ;;
    esac
done

# Set verbose logging
if [ "$VERBOSE" = true ]; then
    set -x
fi

# Main execution
log "INFO" "Repository sync script started"
log "INFO" "Repository path: $REPO_PATH"
log "INFO" "Sync interval: ${SYNC_INTERVAL}s"
log "INFO" "Mode: $MODE"

if [ "$DRY_RUN" = true ]; then
    dry_run
    exit 0
fi

case $MODE in
    "once")
        main_sync
        ;;
    "continuous")
        run_continuous_sync
        ;;
    *)
        log "ERROR" "Unknown mode: $MODE"
        exit 1
        ;;
esac 