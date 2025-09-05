# 🔧 PHPStan Auto-Fix Solution

This repository includes an automated solution for fixing PHPStan issues when the CI pipeline fails. The solution consists of multiple approaches to handle different scenarios.

## 📁 Files Overview

### Workflows
- **`.github/workflows/phpstan.yml`** - Updated main PHPStan workflow with memory limit fix
- **`.github/workflows/auto-fix-phpstan.yml`** - Advanced workflow with sophisticated fixing
- **`.github/workflows/phpstan-auto-fix-simple.yml`** - Simple, reliable workflow for common issues
- **`.github/workflows/phpstan-auto-fix-reusable.yml`** - Uses the reusable action

### Scripts
- **`.github/scripts/fix-phpstan-issues.sh`** - Bash script for basic fixes
- **`.github/scripts/fix-phpstan-issues.php`** - PHP script for advanced fixes

### Reusable Action
- **`.github/actions/phpstan-auto-fix/action.yml`** - Reusable GitHub Action

## 🚀 How It Works

### 1. Detection
The workflows are triggered when the main PHPStan workflow fails:
```yaml
on:
  workflow_run:
    workflows: ["PHPStan"]
    types:
      - completed
```

### 2. Error Analysis
The system runs PHPStan with increased memory limit and captures errors in JSON format:
```bash
./vendor/bin/phpstan analyse --memory-limit=512M --error-format=json
```

### 3. Automated Fixes
The system applies common fixes for typical PHPStan issues:

#### Type Declarations
- Adds missing return types (`: void`, `: mixed`)
- Adds type hints for class properties
- Fixes parameter type declarations

#### Docblock Annotations
- Adds `@return` annotations
- Adds `@var` annotations for properties
- Adds `@param` annotations

#### Array Types
- Converts generic `array` to `array<int, mixed>`
- Fixes array type declarations

### 4. PR Creation
When fixes are applied, the system:
1. Creates a new branch: `fix/phpstan-issues-{run_number}`
2. Commits the changes with a descriptive message
3. Pushes the branch
4. Creates a pull request with:
   - Descriptive title and body
   - Appropriate labels
   - Assignment to the repository owner
   - Comment on the original workflow run

## 🛠️ Usage

### Option 1: Simple Workflow (Recommended)
Use the simple workflow for reliable, basic fixes:
```yaml
# Enable this workflow by renaming it to remove the "-simple" suffix
# or by adding it to your workflow triggers
```

### Option 2: Advanced Workflow
Use the advanced workflow for more sophisticated fixes:
```yaml
# The advanced workflow uses both shell and PHP scripts
# for more comprehensive fixing
```

### Option 3: Reusable Action
Use the reusable action in your own workflows:
```yaml
- name: Run PHPStan Auto-Fix
  uses: ./.github/actions/phpstan-auto-fix
  with:
    php-version: '8.4'
    memory-limit: '512M'
    assignee: 'your-username'
    labels: 'bug,phpstan,auto-generated'
```

## 🔧 Configuration

### Memory Limit
The workflows use a 512M memory limit for PHPStan. You can adjust this in the workflow files:
```yaml
run: ./vendor/bin/phpstan analyse --memory-limit=512M
```

### Fix Patterns
The fixing scripts target common patterns. You can customize them by editing:
- `.github/scripts/fix-phpstan-issues.sh`
- `.github/scripts/fix-phpstan-issues.php`

### PR Settings
Customize PR creation by modifying the workflow files:
- Labels
- Assignees
- PR title and body
- Branch naming

## 🎯 Supported Fixes

### ✅ Currently Fixed
- Missing return types
- Missing property type hints
- Basic docblock annotations
- Array type declarations
- Memory limit issues

### 🔄 Future Improvements
- More sophisticated type inference
- Better docblock generation
- Parameter type fixing
- Null check additions
- Custom rule fixes

## 🚨 Limitations

1. **Pattern-based**: The fixes are based on common patterns and may not catch all edge cases
2. **Conservative**: The system prioritizes safety over completeness
3. **Manual Review**: All fixes should be manually reviewed before merging
4. **PHP Version**: Currently optimized for PHP 8.4

## 🔍 Monitoring

### Workflow Status
Check the Actions tab to see:
- When auto-fix workflows are triggered
- Success/failure status
- PR creation logs

### PR Tracking
Created PRs will have:
- `auto-generated` label
- `phpstan` label
- Assignment to repository owner
- Comments on original workflow runs

## 🛡️ Safety Features

1. **Backup Creation**: Original files are backed up before modification
2. **Change Detection**: Only commits if actual changes were made
3. **Error Handling**: Graceful fallback between different fixing methods
4. **Manual Review**: All changes require manual review and approval

## 📝 Customization

### Adding New Fix Patterns
Edit the fixing scripts to add new patterns:

```bash
# In fix-phpstan-issues.sh
sed -i 's/pattern/replacement/g' "$file"
```

```php
// In fix-phpstan-issues.php
$content = preg_replace($pattern, $replacement, $content);
```

### Customizing PR Content
Modify the PR creation section in workflow files:

```yaml
- name: Create Pull Request
  uses: actions/github-script@v7
  with:
    script: |
      const { data: pr } = await github.rest.pulls.create({
        # ... PR configuration
      });
```

## 🤝 Contributing

To improve the auto-fix solution:

1. Test new patterns on sample code
2. Add tests for new fixing logic
3. Update documentation
4. Submit a pull request

## 📚 Related

- [PHPStan Documentation](https://phpstan.org/)
- [GitHub Actions Documentation](https://docs.github.com/en/actions)
- [Laravel Pint](https://laravel.com/docs/pint) (for code style fixes)

---

*This solution is designed to work alongside your existing CI/CD pipeline and provides a safety net for common PHPStan issues.*
