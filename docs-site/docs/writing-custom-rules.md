---
id: writing-custom-rules
title: 🧠 Writing Custom Rules
sidebar_position: 6
---

Laravel Migration Linter is designed to be **fully extensible** —  
you can easily create your own rule classes to lint for specific schema patterns or organization-specific policies.

---

## 🧩 1️⃣ Create a Custom Rule Class

Create a new rule class anywhere in your app or within a separate package.  
Each rule must extend the core `Sufyan\MigrationLinter\Rules\AbstractRule` class and implement:

- `id()` → unique identifier (string)
- `description()` → short explanation of what the rule checks
- `check(Operation $operation)` → main logic (returns an array of `Issue` objects)

Example:

```php
<?php

namespace App\MigrationRules;

use Sufyan\MigrationLinter\Rules\AbstractRule;
use Sufyan\MigrationLinter\Support\Operation;

class NoUnsignedBigIntRule extends AbstractRule
{
    public function id(): string
    {
        return 'NoUnsignedBigIntRule';
    }

    public function defaultSeverity(): string
    {
        return 'warning';
    }

    public function description(): string
    {
        return 'Discourages the use of unsignedBigInteger for portability reasons.';
    }

    /**
     * @return \Sufyan\MigrationLinter\Support\Issue[]
     */
    public function check(Operation $operation): array
    {
        // Inspect the migration operation
        if ($operation->method === 'unsignedBigInteger') {
            return [
                $this->warn(
                    $operation,
                    "Avoid using unsignedBigInteger on table '{$operation->table}'. Use bigInteger() instead for better portability.",
                    column: $operation->args,
                    suggestion: 'Replace unsignedBigInteger() with bigInteger() for better cross-database compatibility.',
                    docsUrl: 'https://laravel.com/docs/migrations#column-method-bigInteger'
                ),
            ];
        }

        return [];
    }
}
```
## ⚙️ 2️⃣ Register the Rule

Once your rule is ready, add it to the configuration file:

```php
// config/migration-linter.php

return [
    'rules' => [
        // Existing built-in rules...

        'NoUnsignedBigIntRule' => [
            'enabled'  => true,
            'severity' => 'warning', // can be 'error' for strict enforcement
        ],
    ],
];
```
You can also publish the config file using:
```bash
php artisan vendor:publish --tag="migration-linter-config"
```
## 🧪 3️⃣ Run the Linter
Simply run:

```bash
php artisan migrate:lint
```
Your custom rule will now be executed alongside the built-in ones.
If it finds any violations, they’ll appear in the standard lint report.

## 💡 Tips for Rule Authors
- Use `warn()`, `error()`, or other helpers to create issues that respect severity from the config.
- **Provide Suggestions** — Each `warn()` call can include `$suggestion` and `$docsUrl` parameters (optional):
  ```php
  $this->warn($operation, "Your message", column: $col, 
              suggestion: "Here's what to do instead...", 
              docsUrl: "https://docs-url.com")
  ```
- Suggestions appear in CLI output and are included in JSON reports for tool integration.
- Keep rules focused — each rule should detect one kind of problem clearly.
- Leverage Operation properties like:
    - $operation->method → migration method (e.g. string, dropColumn)
    - $operation->args → raw arguments as string
    - $operation->table → table name
    - $operation->file → file path
- Return an empty array if no issue is detected.
- You can group your own rules in a namespace like App\MigrationRules or App\LinterRules.

🧠 Example Output

```bash
[warning] NoUnsignedBigIntRule  
→ Avoid using unsignedBigInteger on table 'users'. Use bigInteger() instead for better portability.

[Suggestion #1] NoUnsignedBigIntRule:
  Replace unsignedBigInteger() with bigInteger() for better cross-database compatibility.
  📖 Learn more: https://laravel.com/docs/migrations#column-method-bigInteger
```
✅ That’s it!
Your custom rules will now integrate seamlessly with Laravel Migration Linter’s reporting, severity configuration, and CI workflows.

---