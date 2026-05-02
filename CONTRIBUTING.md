# Contributing Guide

## Commit Message Convention

### Format
```
<type>: <subject>

<body>

<footer>
```

### Types
- **feat**: New feature
- **fix**: Bug fix
- **docs**: Documentation changes
- **style**: Code style/formatting
- **refactor**: Code refactoring
- **test**: Adding/updating tests
- **chore**: Maintenance tasks

### Examples
```
feat: add real-time trading preview API

- Created TradingPreviewController
- Added JavaScript real-time updates
- Implemented debounced API calls

Closes #123
```

```
fix: correct lot size calculation formula

- Fixed decimal precision issue
- Added validation for minimum lot size
- Updated unit tests

Fixes #45
```

## Branch Naming
- `feature/trading-preview` - New features
- `bugfix/lot-calculation` - Bug fixes
- `hotfix/login-issue` - Critical fixes
- `release/v1.0.0` - Release branches

## Pull Request Guidelines
1. One feature/bug per PR
2. Include tests if applicable
3. Update documentation
4. Follow code style
5. Get code review approval