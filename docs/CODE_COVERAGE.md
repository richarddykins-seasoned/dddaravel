# Code Coverage Guide

This project uses **PCOV** for fast code coverage analysis with **Pest** testing framework. Code coverage helps ensure your tests are comprehensive and identify untested code paths.

## 📊 Current Coverage

Run this command to see current coverage:

```bash
composer test:coverage
```

Current project coverage: **70.7%** (as of last update)

## 🎯 Coverage Commands

### Basic Coverage
```bash
# Show coverage in terminal
composer test:coverage

# Generate HTML coverage report
composer test:coverage:html

# Run tests with minimum coverage threshold (70%)
composer test:coverage:min
```

### Quality Checks with Coverage
```bash
# Run all quality checks including coverage validation
composer quality:check:coverage
```

## 🔧 Coverage Configuration

### Minimum Coverage Threshold
- **Current threshold**: 70%
- **Location**: Set in `composer.json` scripts
- **Enforcement**: Optional in pre-commit hooks

### Coverage Driver
- **Driver**: PCOV (faster than Xdebug for coverage)
- **Installation**: `sudo apt install php8.4-pcov`
- **Status**: ✅ Installed and configured

## 📋 Pre-commit Coverage Validation

Code coverage can be enforced in pre-commit hooks by setting an environment variable:

```bash
# Enable coverage validation in pre-commit hook
export REQUIRE_COVERAGE=1
git commit -m "Your commit message"
```

This will:
- Run coverage analysis before commit
- Fail commit if coverage is below 70%
- Show current coverage report on failure

## 📈 Coverage Reports

### Terminal Report
```bash
composer test:coverage
```

### HTML Report
```bash
composer test:coverage:html
# Open: storage/coverage/index.html
```

The HTML report provides:
- Line-by-line coverage visualization
- Detailed file analysis
- Interactive coverage exploration
- Uncovered code highlighting

## 🎯 Coverage Targets by Component

| Component | Current | Target | Status |
|-----------|---------|---------|---------|
| Controllers | 62.3% | 80% | ⚠️ Needs improvement |
| Models | 100% | 70% | ✅ **Excellent** |
| Middleware | 100% | 80% | ✅ **Excellent** |
| Providers | 100% | 90% | ✅ Good |
| **Overall** | **70.7%** | **80%** | 🟢 **Good** |

## 📝 Improving Coverage

### 1. Identify Uncovered Code
```bash
# Generate detailed coverage report
composer test:coverage:html
# Review: storage/coverage/index.html
```

### 2. Add Tests for Uncovered Areas
Focus on:
- **Controllers**: Add feature tests for all endpoints
- **Models**: Add unit tests for model methods
- **Middleware**: Test authentication and validation logic
- **Services**: Unit test business logic

### 3. Coverage Best Practices
- Aim for **meaningful coverage**, not just high numbers
- Test **edge cases** and **error conditions**
- Cover **business logic** thoroughly
- Use **feature tests** for integration coverage
- Use **unit tests** for isolated component coverage

## 🚀 Advanced Coverage Analysis

### Mutation Testing (Advanced)
```bash
# Test the quality of your tests
composer test:mutate
```

### Drift Detection
```bash
# Detect changes that break existing tests
composer test:drift
```

## 🔄 CI/CD Integration

For continuous integration, use:

```yaml
# In your CI pipeline
- name: Run Tests with Coverage
  run: composer test:coverage:min

- name: Generate Coverage Report
  run: composer test:coverage:html

- name: Upload Coverage
  # Upload storage/coverage/ directory
```

## 📚 Resources

- [Pest Coverage Documentation](https://pestphp.com/docs/coverage)
- [PCOV Extension](https://github.com/krakjoe/pcov)
- [Laravel Testing Guide](https://laravel.com/docs/testing)

## 💡 Tips

1. **Start with feature tests** - they provide broad coverage quickly
2. **Focus on critical paths** - cover your most important business logic first
3. **Don't chase 100%** - aim for meaningful coverage of important code
4. **Use coverage to find gaps** - not as the only measure of test quality
5. **Combine with mutation testing** - to ensure your tests are actually effective

---

**Current Status**: 🟢 **Good** - Solid coverage achieved at 70.7%, targeting 80% overall coverage.
