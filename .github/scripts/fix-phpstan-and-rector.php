<?php

/**
 * Combined PHPStan and Rector Auto-Fixer
 * This script runs both Rector and custom PHPStan fixes
 */

class CombinedFixer
{
    private bool $verbose = false;
    private array $changes = [];

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function run(): int
    {
        $this->log("🔧 Starting combined PHPStan and Rector fixing...");
        
        // Step 1: Run Rector first (more sophisticated fixes)
        $this->log("Step 1: Running Rector...");
        $rectorResult = $this->runRector();
        
        // Step 2: Run custom PHPStan fixes for remaining issues
        $this->log("Step 2: Running custom PHPStan fixes...");
        $phpstanResult = $this->runCustomPHPStanFixes();
        
        // Step 3: Run PHPStan to check final status
        $this->log("Step 3: Verifying fixes...");
        $this->verifyFixes();
        
        $this->log("✅ Combined fixing completed!");
        $this->log("Changes made: " . count($this->changes));
        
        return 0;
    }

    private function runRector(): bool
    {
        $this->log("Running Rector...");
        
        // Run Rector with dry-run first to see what would change
        $dryRunOutput = shell_exec('./vendor/bin/rector process --dry-run --no-progress-bar 2>&1');
        
        if (strpos($dryRunOutput, 'would be changed') !== false || strpos($dryRunOutput, 'would be renamed') !== false) {
            $this->log("Rector found changes to apply");
            
            // Run Rector for real
            $output = shell_exec('./vendor/bin/rector process --no-progress-bar 2>&1');
            
            if (strpos($output, 'changed') !== false || strpos($output, 'renamed') !== false) {
                $this->log("✅ Rector applied changes");
                $this->changes[] = 'Rector fixes applied';
                return true;
            }
        } else {
            $this->log("Rector found no changes to apply");
        }
        
        return false;
    }

    private function runCustomPHPStanFixes(): bool
    {
        // Run our custom PHPStan fixer
        $output = shell_exec('php .github/scripts/fix-phpstan-issues.php --verbose 2>&1');
        
        if (strpos($output, 'Applied fixes') !== false || strpos($output, 'Fixed issues') !== false) {
            $this->log("✅ Custom PHPStan fixes applied");
            $this->changes[] = 'Custom PHPStan fixes applied';
            return true;
        }
        
        return false;
    }

    private function verifyFixes(): void
    {
        $this->log("Running PHPStan to verify all fixes...");
        
        $output = shell_exec('./vendor/bin/phpstan analyse --memory-limit=512M --no-progress 2>&1');
        
        if (strpos($output, 'No errors') !== false) {
            $this->log("🎉 All PHPStan issues have been fixed!");
        } else {
            $this->log("⚠️  Some issues may still remain:");
            $this->log($output);
        }
    }

    private function log(string $message): void
    {
        if ($this->verbose) {
            echo "[" . date('Y-m-d H:i:s') . "] $message\n";
        }
    }
}

// Run the combined fixer
$verbose = in_array('--verbose', $argv);
$fixer = new CombinedFixer($verbose);
exit($fixer->run());
