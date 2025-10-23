## 🧩 Usage
Run the built-in Artisan command to lint all migration files:

```bash
php artisan migrate:lint
```
### You can use the following flags and options to customize behavior:

| Option / Flag         | Description                                                                                                                                                                        |
| --------------------- | ---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| `--generate-baseline` | Create a JSON file (`migration-linter-baseline.json`) that records all current issues so they can be ignored in future runs. Useful for introducing the linter to legacy projects. |
| `--path=`             | Lint a specific migration file or directory instead of the default `database/migrations` folder.                                                                                   |
| `--json`              | Output results in structured JSON format (great for CI/CD pipelines).                                                                                                              |
| `--baseline=`         | Provide a custom path to a baseline file for ignoring known issues (overrides the default `migration-linter-baseline.json`).                                                       |


Example Usage

### Lint all migrations
```bash
php artisan migrate:lint
```
### Generate a new baseline file (ignore current issues)
```bash
php artisan migrate:lint --generate-baseline
```
### Run only on a specific path
```bash
php artisan migrate:lint --path=database/migrations
```
### Export lint report as JSON (for CI)
```bash
php artisan migrate:lint --json > storage/lint-report.json
```
### Use a custom baseline file
```bash
php artisan migrate:lint --baseline=storage/custom-baseline.json
```
### Improving usability for smaller terminal sizes
```bash
php artisan migrate:lint --compact
```