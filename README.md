# Vendor packages and autoload

This theme uses Composer and loads `vendor/autoload.php` for:

- **yahnis-elsts/plugin-update-checker** (PucFactory) – update checks from GitHub.

## Adding or changing vendor packages

1. Run `composer require <package>` or edit `composer.json` and run `composer update`.
2. For packages that might be loaded by other plugins/themes (e.g. PUC), keep the `class_exists()` guard before `require_once ... vendor/autoload.php` so only one autoload runs.

## Linting (WordPress PHP standards)

- Run: `composer run lint` or `./vendor/bin/phpcs`
- Auto-fix: `composer run lint:fix` or `./vendor/bin/phpcbf`
- Security scan (token/credential leak check): `composer run lint:security`
- All-in-one (security + fix twice + lint): `composer run lint:run`

To block merges to any branch when lint fails, enable branch protection and require the **Lint** status check; see [.github/BRANCH_PROTECTION.md](.github/BRANCH_PROTECTION.md).
