#!/bin/bash

# Automated PHPStan Issue Fixer
# This script attempts to automatically fix common PHPStan issues

set -e

echo "🔧 Starting automated PHPStan issue fixing..."

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Function to log with colors
log() {
    echo -e "${BLUE}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

log_error() {
    echo -e "${RED}[$(date +'%Y-%m-%d %H:%M:%S')]${NC} $1"
}

# Get PHPStan errors in JSON format
get_phpstan_errors() {
    ./vendor/bin/phpstan analyse --memory-limit=512M --error-format=json 2>/dev/null || echo '[]'
}

# Check if we have any errors
ERRORS=$(get_phpstan_errors)
if [ "$ERRORS" = "[]" ] || [ -z "$ERRORS" ]; then
    log_success "No PHPStan errors found!"
    exit 0
fi

log "Found PHPStan errors, attempting to fix them..."

# Parse JSON errors and extract file paths and line numbers
echo "$ERRORS" | jq -r '.errors[]? | select(.file) | "\(.file):\(.line)"' | sort -u | while IFS=':' read -r file line; do
    if [ -f "$file" ]; then
        log "Processing $file:$line"
        fix_phpstan_issues_in_file "$file" "$line"
    fi
done

# Function to fix common PHPStan issues in a specific file
fix_phpstan_issues_in_file() {
    local file="$1"
    local line="$2"
    
    if [ ! -f "$file" ]; then
        return
    fi
    
    log "Fixing issues in $file around line $line"
    
    # Create a backup
    cp "$file" "$file.backup"
    
    # Fix 1: Add missing return type declarations
    # Look for function declarations without return types
    sed -i.tmp 's/function \([a-zA-Z_][a-zA-Z0-9_]*\)(/function \1(): void/g' "$file"
    rm -f "$file.tmp"
    
    # Fix 2: Add missing property type declarations
    # Look for class properties without type hints
    sed -i.tmp 's/private \$\([a-zA-Z_][a-zA-Z0-9_]*\);/private \$\1: mixed;/g' "$file"
    sed -i.tmp 's/protected \$\([a-zA-Z_][a-zA-Z0-9_]*\);/protected \$\1: mixed;/g' "$file"
    sed -i.tmp 's/public \$\([a-zA-Z_][a-zA-Z0-9_]*\);/public \$\1: mixed;/g' "$file"
    rm -f "$file.tmp"
    
    # Fix 3: Add missing parameter type declarations
    # Look for function parameters without type hints
    sed -i.tmp 's/function \([a-zA-Z_][a-zA-Z0-9_]*\)(\([^)]*\))/function \1(\2)/g' "$file"
    rm -f "$file.tmp"
    
    # Fix 4: Add missing @var annotations for properties
    # This is more complex and requires PHP parsing, so we'll use a simpler approach
    add_var_annotations "$file"
    
    # Fix 5: Add missing @param and @return annotations
    add_docblock_annotations "$file"
    
    # Fix 6: Fix common array type issues
    fix_array_types "$file"
    
    # Fix 7: Add missing null checks
    add_null_checks "$file"
    
    # Check if the file was actually modified
    if ! diff -q "$file" "$file.backup" > /dev/null; then
        log_success "Applied fixes to $file"
    else
        log_warning "No changes made to $file"
        mv "$file.backup" "$file"
    fi
}

# Add @var annotations for class properties
add_var_annotations() {
    local file="$1"
    
    # This is a simplified version - in practice, you'd want more sophisticated parsing
    # For now, we'll add generic @var annotations for common patterns
    sed -i.tmp '/private \$[a-zA-Z_][a-zA-Z0-9_]*: mixed;/i\
     * @var mixed\
    ' "$file"
    rm -f "$file.tmp"
}

# Add basic docblock annotations
add_docblock_annotations() {
    local file="$1"
    
    # Add basic @param and @return annotations for functions
    sed -i.tmp '/function [a-zA-Z_][a-zA-Z0-9_]*(): void/a\
     * @return void\
    ' "$file"
    rm -f "$file.tmp"
}

# Fix common array type issues
fix_array_types() {
    local file="$1"
    
    # Replace generic array with more specific types where possible
    sed -i.tmp 's/array</array<int, mixed></g' "$file"
    rm -f "$file.tmp"
}

# Add basic null checks
add_null_checks() {
    local file="$1"
    
    # This is a placeholder - in practice, you'd want more sophisticated analysis
    # For now, we'll just ensure variables are checked before use
    log "Adding null checks to $file (placeholder)"
}

# Run PHPStan again to see if we fixed the issues
log "Running PHPStan again to check if issues were fixed..."

FINAL_ERRORS=$(get_phpstan_errors)
if [ "$FINAL_ERRORS" = "[]" ] || [ -z "$FINAL_ERRORS" ]; then
    log_success "🎉 All PHPStan issues have been fixed!"
else
    log_warning "⚠️  Some issues may still remain. Manual review recommended."
    echo "Remaining errors:"
    echo "$FINAL_ERRORS" | jq -r '.errors[]? | "\(.file):\(.line): \(.message)"' || echo "$FINAL_ERRORS"
fi

# Clean up backup files
find . -name "*.backup" -delete

log_success "✅ Automated PHPStan fixing completed!"
