# WordPress Theme Coding Standards

This is a WordPress theme. All PHP must pass `composer run lint` (PHPCS with WordPress-Core, WordPress-Extra, WordPress-Docs).

## Naming

- Functions and variables: `snake_case`
- Classes: `PascalCase`
- Constants: `UPPER_SNAKE_CASE`
- Files containing a class: `class-product-helper.php`

## Indentation

2 spaces. No tabs.

## Security

- Sanitize all input: `sanitize_text_field()`, `absint()`, `sanitize_email()`
- Escape all output: `esc_html()`, `esc_url()`, `esc_attr()`, `wp_kses()`
- Verify nonces: `check_ajax_referer()`, `wp_verify_nonce()`
- Check capabilities: `current_user_can()` before privileged actions
- Use `$wpdb->prepare()` for all queries — never raw SQL
- No hardcoded API keys, tokens, or credentials

## Complexity

- Cyclomatic complexity per function: warning at 10, error at 20
- Max nesting depth: warning at 3 levels, error at 5
- Use early return / guard clauses to reduce nesting

## Code Quality

- No commented-out code
- No TODO or FIXME comments — convert to tracked issues
- All user-facing strings use `__()` or `_e()` with text domain `custom-theme`
- Enqueue scripts via `wp_enqueue_scripts` — no inline `<script>` tags

## Lint

```bash
composer run lint        # check
composer run lint:fix    # auto-fix
composer run lint:run    # security scan, fix twice, then check
```
