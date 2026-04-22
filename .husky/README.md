# Git Hooks with Husky

This project uses [Husky](https://typicode.github.io/husky/) to manage Git hooks for code quality.

## Active Hooks

### 🚫 pre-push
**Prevents pushing code with lint errors**

- Runs `composer run lint` before every push
- If lint errors are found, the push is blocked
- Ensures only clean code reaches the repository

**To fix lint errors:**
```bash
# Auto-fix most issues
composer run lint:fix

# Or manually fix and run
composer run lint
```

## Installation

Husky is automatically installed when you run:
```bash
npm install
```

The `prepare` script in package.json sets up the hooks.

## Bypass (Emergency Only)

If you absolutely need to push without fixing lint errors (not recommended):
```bash
git push --no-verify
```

⚠️ **Warning:** Only use `--no-verify` in emergencies. Lint errors should be fixed before pushing.

## Configuration

- **Hook files:** `.husky/pre-push`
- **Lint command:** `composer run lint` (defined in composer.json)
- **Auto-fix:** `composer run lint:fix`

## Workflow

```
Developer tries to push → Husky runs pre-push hook
                       ↓
              composer run lint
                       ↓
        ┌──────────────┴──────────────┐
        │                             │
   Lint Pass ✅                  Lint Fail ❌
        │                             │
   Push succeeds              Push is blocked
                                      │
                          Fix errors & try again
```

## Benefits

✅ Catches code quality issues early  
✅ Enforces WordPress coding standards  
✅ Prevents broken code from reaching production  
✅ Maintains consistent code quality across the team  
✅ Reduces code review time
