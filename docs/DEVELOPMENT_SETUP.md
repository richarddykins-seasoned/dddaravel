# 🛠️ Development Setup Guide

This guide explains how to set up your development environment for PHPStan-first development on the dddaravel API project.

## 📋 Table of Contents

- [Prerequisites](#prerequisites)
- [PhpStorm Configuration](#phpstorm-configuration)
- [Quality Tools Setup](#quality-tools-setup)
- [Git Hooks](#git-hooks)
- [Live Templates](#live-templates)
- [Code Snippets](#code-snippets)
- [Daily Workflow](#daily-workflow)

## 🎯 Prerequisites

### Required Tools

- **PHP 8.3+** with extensions:
  - `php-mbstring`
  - `php-xml` 
  - `php-curl`
  - `php-zip`
  - `php-sqlite3` (for testing)

- **Composer 2.0+**
- **Git 2.25+**
- **PhpStorm 2023.3+** (recommended IDE)
- **Bash shell** (for git hooks)

### Verify Installation

```bash
# Check PHP version and extensions
php -v
php -m | grep -E "(mbstring|xml|curl|zip|sqlite3)"

# Check Composer
composer --version

# Check Git
git --version
```

## 🚀 PhpStorm Configuration

### 1. **PHPStan Integration**

1. Open PhpStorm Settings (`Ctrl+Alt+S`)
2. Go to **PHP > Quality Tools > PHPStan**
3. Set configuration:
   - **Path to PHPStan:** `./vendor/bin/phpstan`
   - **Configuration file:** `./phpstan.neon`
   - **Level:** `max`
   - **Memory limit:** `2G`

4. Enable PHPStan inspection:
   - Go to **Editor > Inspections**
   - Enable **PHP > Quality Tools > PHPStan validation**

### 2. **Laravel Pint Integration**

1. Go to **PHP > Quality Tools > Laravel Pint**
2. Set **Path to Pint:** `./vendor/bin/pint`
3. Enable **Format on save**

### 3. **Code Style Configuration**

The project includes a `.idea/codeStyles/Project.xml` file with optimized settings:
- 120 character line limit
- 4 spaces indentation
- PSR-12 compliance
- PHPDoc formatting
- Array formatting preferences

### 4. **Live Templates**

PhpStorm live templates are configured in `.idea/template-groups/PHPStan_First.xml`:

| Template | Trigger | Description |
|----------|---------|-------------|
| `phpclass` | PHP class with strict types |
| `phpservice` | Service class with DI |
| `phpcontroller` | Laravel controller |
| `phpformrequest` | Form request with typing |
| `phpmigration` | Migration with typing |
| `pesttest` | Pest test case |
| `nullcheck` | Null safety pattern |
| `arrayguard` | Array type guard |
| `confighelper` | Config helper method |
| `relation` | Typed model relationship |

**Usage:** Type the template name and press `Tab` to expand.

### 5. **File and Code Templates**

Configure PhpStorm to automatically add `declare(strict_types=1);` to new PHP files:

1. Go to **Editor > File and Code Templates**
2. Select **PHP Class**
3. Update template to include:

```php
<?php

declare(strict_types=1);

namespace ${NAMESPACE};

class ${NAME}
{
    ${BODY}
}
```

## 🔧 Quality Tools Setup

### 1. **Install Dependencies**

```bash
# Install PHP dependencies only - no Node.js required!
composer install
```

### 2. **Verify Tool Configuration**

```bash
# PHPStan
./vendor/bin/phpstan analyse --memory-limit=2G

# Laravel Pint
./vendor/bin/pint --test

# Rector
./vendor/bin/rector process --dry-run

# Peck (spell check)
./vendor/bin/peck

# Pest (tests)
./vendor/bin/pest
```

### 3. **Composer Scripts**

The project includes convenient composer scripts:

```bash
# Run all quality checks
composer quality:check

# Fix formatting
composer format

# Apply code modernizations
composer refactor

# Run static analysis
composer analyse

# Run tests
composer test

# Run spell check
composer spell:check
```

## 🪝 Git Hooks

### Setup Pre-commit Hook

```bash
# Make hook executable (already done in repo)
chmod +x .githooks/pre-commit

# Configure git to use custom hooks directory
git config core.hooksPath .githooks
```

### What the Git Hooks Check

**Pre-commit Hook:**
1. **PHP Syntax** - Ensures valid PHP syntax
2. **Code Formatting** - Laravel Pint compliance
3. **Type Safety** - PHPStan analysis at max level
4. **Code Modernization** - Rector suggestions
5. **Spelling** - Technical term validation
6. **Tests** - Runs tests if test files are modified
7. **Strict Types** - Ensures `declare(strict_types=1);` in new files
8. **Return Types** - Warns about missing return type hints

**Commit-msg Hook:**
1. **Conventional Commits** - Enforces proper commit format
2. **Message Length** - First line under 72 characters
3. **Imperative Mood** - Ensures proper grammar

### Hook Output Example

```bash
🔍 Running pre-commit quality checks...
📁 Checking staged PHP files:
app/Http/Controllers/UserController.php

🔧 Checking PHP syntax...
✓ PHP syntax check passed

🎨 Running Laravel Pint...
✓ Code formatting check passed

🔍 Running PHPStan analysis...
✓ PHPStan analysis passed

🚀 Running Rector check...
✓ Rector check completed

📝 Running spell check...
✓ Spell check passed

🔒 Checking for strict types declaration...
✓ Strict types check passed

📋 Checking for return type hints...
✓ Return type hints check completed

✓ All quality checks passed! 🎉

📊 Code Quality Summary:
  ✓ PHP syntax valid  
  ✓ Code properly formatted (Laravel Pint)
  ✓ Type safety verified (PHPStan)
  ✓ Modern PHP practices checked (Rector) 
  ✓ Spelling verified (Peck)
  ✓ Strict types enforced

Ready to commit! 🚀
```

## 📝 Code Snippets (Alternative to PhpStorm)

If using other editors, `.vscode/snippets/php.json` contains equivalent snippets:

- `phpclass` - PHP class with strict types
- `controller-method` - Laravel controller method
- `service-constructor` - Service constructor with DI
- `config-helper` - Type-safe config helper
- `null-safe-user` - Safe user access pattern
- `array-guard` - Array type guard
- `model-relation` - Typed model relationship
- `pest-test` - Pest test case
- `form-request` - Form request with typing
- `migration` - Migration template
- `exception-throw` - Exception with context

## 🔄 Daily Workflow

### 1. **Before Starting Work**

```bash
# Pull latest changes
git pull origin main

# Ensure dependencies are up to date  
composer install

# Run quality checks to ensure clean state
composer quality:check
```

### 2. **While Developing**

- Use PhpStorm live templates for common patterns
- Let PHPStan inspection guide type safety
- Format code automatically with Pint
- Write tests alongside features using Pest

### 3. **Before Committing**

```bash
# Run quality checks manually (optional - pre-commit hook will do this)
composer quality:check

# Stage your changes
git add .

# Commit (pre-commit hook runs automatically)
git commit -m "feat: add user management feature"
```

### 4. **Code Review Checklist**

- ✅ All files have `declare(strict_types=1);`
- ✅ All methods have return type hints
- ✅ PHPDoc comments for complex types
- ✅ Proper null safety patterns used
- ✅ Type guards for mixed data
- ✅ Tests cover new functionality
- ✅ No PHPStan errors
- ✅ Code follows project patterns

## 🚨 Troubleshooting

### PHPStan Memory Issues

```bash
# Increase memory limit
./vendor/bin/phpstan analyse --memory-limit=4G
```

### Pre-commit Hook Not Running

```bash
# Verify hook path configuration
git config --get core.hooksPath

# Should return: .githooks

# Re-configure if needed
git config core.hooksPath .githooks
```

### PhpStorm Not Recognizing Types

1. Clear caches: **File > Invalidate Caches and Restart**
2. Rebuild project: **Build > Rebuild Project**
3. Check if `.phpstorm.meta.php` is present

### Live Templates Not Working

1. Verify templates are enabled: **Editor > Live Templates > PHPStan First**
2. Check template context is set to **PHP**
3. Restart PhpStorm if needed

## 📚 Resources

- [PHPStan Documentation](https://phpstan.org/user-guide/getting-started)
- [Laravel Pint Documentation](https://laravel.com/docs/pint)
- [Pest Testing Framework](https://pestphp.com/)
- [Coding Standards Document](./CODING_STANDARDS.md)
- [PhpStorm Documentation](https://www.jetbrains.com/help/phpstorm/)

## 🆘 Getting Help

If you encounter issues:

1. Check the troubleshooting section above
2. Review the coding standards document
3. Ask team members for guidance
4. Create an issue in the repository

---

*This setup ensures consistent, high-quality code across the team while maintaining productivity.*
