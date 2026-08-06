# AGENTS.md

## Lint / Format
- **PHP formatting**: `vendor/bin/pint`
- **PHP syntax check**: `php -l <file>`
- **Blade compile check**: `php artisan view:clear` (recompiles all Blade views)

## Build
- **Frontend assets**: `npm run build` (Vite)

## Test
- **All tests**: `php artisan test`
- **Feature tests**: `php artisan test --testsuite=Feature`
