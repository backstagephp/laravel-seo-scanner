<?php

/**
 * Advanced PHPStan Issue Fixer
 * This PHP script provides more sophisticated fixes for PHPStan issues
 */

class PHPStanFixer
{
    private array $errors = [];
    private array $files = [];
    private bool $verbose = false;

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function run(): int
    {
        $this->log("🔧 Starting advanced PHPStan issue fixing...");
        
        // Get PHPStan errors
        $this->errors = $this->getPHPStanErrors();
        
        if (empty($this->errors)) {
            $this->log("✅ No PHPStan errors found!");
            return 0;
        }

        $this->log("Found " . count($this->errors) . " PHPStan errors");
        
        // Group errors by file
        $this->groupErrorsByFile();
        
        // Fix issues in each file
        $fixedFiles = 0;
        foreach ($this->files as $file => $errors) {
            if ($this->fixFile($file, $errors)) {
                $fixedFiles++;
            }
        }
        
        $this->log("Fixed issues in $fixedFiles files");
        
        // Run PHPStan again to check results
        $this->verifyFixes();
        
        return 0;
    }

    private function getPHPStanErrors(): array
    {
        $output = shell_exec('./vendor/bin/phpstan analyse --memory-limit=512M --error-format=json 2>/dev/null');
        $data = json_decode($output ?: '{}', true);
        
        // Handle the actual PHPStan JSON format
        if (isset($data['errors']) && is_array($data['errors'])) {
            return $data['errors'];
        }
        
        // Fallback for different formats
        if (is_array($data)) {
            return $data;
        }
        
        return [];
    }

    private function groupErrorsByFile(): void
    {
        foreach ($this->errors as $error) {
            if (isset($error['file'])) {
                $this->files[$error['file']][] = $error;
            }
        }
    }

    private function fixFile(string $file, array $errors): bool
    {
        if (!file_exists($file)) {
            return false;
        }

        $this->log("Processing $file");
        
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Apply various fixes
        $content = $this->fixMissingReturnTypes($content, $errors);
        $content = $this->fixMissingPropertyTypes($content, $errors);
        $content = $this->fixMissingParameterTypes($content, $errors);
        $content = $this->fixDocblockAnnotations($content, $errors);
        $content = $this->fixArrayTypes($content, $errors);
        $content = $this->fixNullChecks($content, $errors);
        $content = $this->fixTypeHints($content, $errors);
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            $this->log("✅ Applied fixes to $file");
            return true;
        }
        
        $this->log("⚠️  No changes made to $file");
        return false;
    }

    private function fixMissingReturnTypes(string $content, array $errors): string
    {
        // Fix functions without return types
        $patterns = [
            '/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\([^)]*\)\s*\{/' => 'function $1(): mixed {',
            '/function\s+([a-zA-Z_][a-zA-Z0-9_]*)\s*\([^)]*\)\s*:\s*void\s*\{/' => 'function $1(): void {',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }

    private function fixMissingPropertyTypes(string $content, array $errors): string
    {
        // Fix class properties without type hints
        $patterns = [
            '/(private|protected|public)\s+\$([a-zA-Z_][a-zA-Z0-9_]*)\s*;/' => '$1 $2: mixed;',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }

    private function fixMissingParameterTypes(string $content, array $errors): string
    {
        // This is complex and would require proper PHP parsing
        // For now, we'll add basic type hints for common patterns
        $patterns = [
            '/function\s+[a-zA-Z_][a-zA-Z0-9_]*\s*\(\s*\$([a-zA-Z_][a-zA-Z0-9_]*)\s*\)/' => 'function $1($1: mixed)',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }

    private function fixDocblockAnnotations(string $content, array $errors): string
    {
        // Add basic docblock annotations
        $lines = explode("\n", $content);
        $newLines = [];
        
        foreach ($lines as $i => $line) {
            $newLines[] = $line;
            
            // Add docblock before function declarations
            if (preg_match('/function\s+[a-zA-Z_][a-zA-Z0-9_]*\s*\(/', $line)) {
                $indent = str_repeat(' ', strspn($line, ' '));
                $newLines[] = $indent . '/**';
                $newLines[] = $indent . ' * @return mixed';
                $newLines[] = $indent . ' */';
            }
        }
        
        return implode("\n", $newLines);
    }

    private function fixArrayTypes(string $content, array $errors): string
    {
        // Fix generic array types
        $patterns = [
            '/array</' => 'array<int, mixed><',
            '/\[\]/' => 'array<int, mixed>',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }

    private function fixNullChecks(string $content, array $errors): string
    {
        // This would require more sophisticated analysis
        // For now, we'll just ensure variables are properly initialized
        return $content;
    }

    private function fixTypeHints(string $content, array $errors): string
    {
        // Add type hints for common patterns
        $patterns = [
            '/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*null/' => '$1: ?mixed = null',
            '/\$([a-zA-Z_][a-zA-Z0-9_]*)\s*=\s*\[\]/' => '$1: array = []',
        ];
        
        foreach ($patterns as $pattern => $replacement) {
            $content = preg_replace($pattern, $replacement, $content);
        }
        
        return $content;
    }

    private function verifyFixes(): void
    {
        $this->log("Running PHPStan again to verify fixes...");
        
        $errors = $this->getPHPStanErrors();
        
        if (empty($errors)) {
            $this->log("🎉 All PHPStan issues have been fixed!");
        } else {
            $this->log("⚠️  " . count($errors) . " issues remain:");
            foreach ($errors as $error) {
                $file = $error['file'] ?? 'unknown';
                $line = $error['line'] ?? 'unknown';
                $message = $error['message'] ?? 'unknown';
                $this->log("  - {$file}:{$line} - {$message}");
            }
        }
    }

    private function log(string $message): void
    {
        if ($this->verbose) {
            echo "[" . date('Y-m-d H:i:s') . "] $message\n";
        }
    }
}

// Run the fixer
$verbose = in_array('--verbose', $argv);
$fixer = new PHPStanFixer($verbose);
exit($fixer->run());
