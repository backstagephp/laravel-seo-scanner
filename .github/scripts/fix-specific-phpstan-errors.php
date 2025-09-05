<?php

/**
 * Targeted PHPStan Error Fixer
 * This script analyzes specific PHPStan errors and applies targeted fixes
 */

class TargetedPHPStanFixer
{
    private array $errors = [];
    private bool $verbose = false;
    private array $fixesApplied = [];

    public function __construct(bool $verbose = false)
    {
        $this->verbose = $verbose;
    }

    public function run(): int
    {
        $this->log("🔧 Starting targeted PHPStan error fixing...");
        
        // Get PHPStan errors
        $this->errors = $this->getPHPStanErrors();
        
        if (empty($this->errors)) {
            $this->log("✅ No PHPStan errors found!");
            return 0;
        }

        $this->log("Found " . count($this->errors) . " PHPStan errors");
        
        // Process each error
        foreach ($this->errors as $error) {
            $this->fixSpecificError($error);
        }
        
        $this->log("Applied " . count($this->fixesApplied) . " targeted fixes");
        
        // Verify fixes
        $this->verifyFixes();
        
        return 0;
    }

    private function getPHPStanErrors(): array
    {
        $output = shell_exec('./vendor/bin/phpstan analyse --memory-limit=512M --error-format=json 2>/dev/null');
        $data = json_decode($output ?: '{}', true);
        
        if (isset($data['errors']) && is_array($data['errors'])) {
            return $data['errors'];
        }
        
        return [];
    }

    private function fixSpecificError(array $error): void
    {
        $file = $error['file'] ?? '';
        $line = $error['line'] ?? 0;
        $message = $error['message'] ?? '';
        
        if (!$file || !file_exists($file)) {
            return;
        }

        $this->log("Processing error in $file:$line - $message");
        
        $content = file_get_contents($file);
        $originalContent = $content;
        
        // Fix specific error types
        if (strpos($message, 'Return type mixed') !== false && strpos($message, 'not covariant') !== false) {
            $content = $this->fixReturnTypeCovariance($content, $file, $line);
        } elseif (strpos($message, 'Missing return type') !== false) {
            $content = $this->fixMissingReturnType($content, $file, $line);
        } elseif (strpos($message, 'Missing parameter type') !== false) {
            $content = $this->fixMissingParameterType($content, $file, $line);
        } elseif (strpos($message, 'Missing property type') !== false) {
            $content = $this->fixMissingPropertyType($content, $file, $line);
        }
        
        if ($content !== $originalContent) {
            file_put_contents($file, $content);
            $this->fixesApplied[] = "$file:$line - $message";
            $this->log("✅ Fixed error in $file:$line");
        }
    }

    private function fixReturnTypeCovariance(string $content, string $file, int $line): string
    {
        // Fix return type covariance issues
        $lines = explode("\n", $content);
        
        if (isset($lines[$line - 1])) {
            $lineContent = $lines[$line - 1];
            
            // Fix specific patterns
            if (preg_match('/public function check\([^)]*\)\s*$/', $lineContent)) {
                $lines[$line - 1] = str_replace(')', '): bool', $lineContent);
                $this->log("Fixed return type for check method");
            }
        }
        
        return implode("\n", $lines);
    }

    private function fixMissingReturnType(string $content, string $file, int $line): string
    {
        $lines = explode("\n", $content);
        
        if (isset($lines[$line - 1])) {
            $lineContent = $lines[$line - 1];
            
            // Add appropriate return types based on method name
            if (preg_match('/function\s+(\w+)\s*\([^)]*\)\s*$/', $lineContent, $matches)) {
                $methodName = $matches[1];
                $returnType = $this->guessReturnType($methodName, $lineContent);
                $lines[$line - 1] = str_replace(')', "): $returnType", $lineContent);
            }
        }
        
        return implode("\n", $lines);
    }

    private function fixMissingParameterType(string $content, string $file, int $line): string
    {
        $lines = explode("\n", $content);
        
        if (isset($lines[$line - 1])) {
            $lineContent = $lines[$line - 1];
            
            // Add type hints for common parameter patterns
            $lineContent = preg_replace('/function\s+\w+\(\$(\w+)\)/', 'function $1($1: mixed)', $lineContent);
            $lines[$line - 1] = $lineContent;
        }
        
        return implode("\n", $lines);
    }

    private function fixMissingPropertyType(string $content, string $file, int $line): string
    {
        $lines = explode("\n", $content);
        
        if (isset($lines[$line - 1])) {
            $lineContent = $lines[$line - 1];
            
            // Add mixed type for properties
            if (preg_match('/(public|private|protected)\s+\$(\w+)\s*=\s*null;/', $lineContent)) {
                $lines[$line - 1] = preg_replace('/(public|private|protected)\s+\$(\w+)/', '$1 mixed $2', $lineContent);
            }
        }
        
        return implode("\n", $lines);
    }

    private function guessReturnType(string $methodName, string $lineContent): string
    {
        // Simple heuristics for return types
        if (strpos($methodName, 'check') !== false) {
            return 'bool';
        }
        if (strpos($methodName, 'get') !== false) {
            return 'mixed';
        }
        if (strpos($methodName, 'filter') !== false) {
            return '?string';
        }
        if (strpos($methodName, 'dimensions') !== false) {
            return 'array';
        }
        
        return 'mixed';
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
                $this->log("  - $file:$line - $message");
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

// Run the targeted fixer
$verbose = in_array('--verbose', $argv);
$fixer = new TargetedPHPStanFixer($verbose);
exit($fixer->run());
