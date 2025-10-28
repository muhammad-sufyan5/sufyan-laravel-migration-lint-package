---
id: configuration
title: 🧱 Configuration
sidebar_position: 4
---

The **Laravel Migration Linter** ships with a configurable file that defines which rules are active and how severe their findings should be.  
You can customize this file to match your team's migration safety standards.

---

## ⚙️ Publishing the Config

If you haven’t already, publish the configuration file to your app:

```bash
php artisan vendor:publish --tag="migration-linter-config"
```
This creates:
```arduino
config/migration-linter.php
```
---

### 📄 Default Config File

```php
return [
    /*
    |--------------------------------------------------------------------------
    | Severity Threshold
    |--------------------------------------------------------------------------
    |
    | This controls the exit code behavior of the linter.
    | - If set to "error", the command will exit with 1 whenever an "error" rule triggers.
    | - If set to "warning", only "warning" or higher severities are reported.
    |
    */
    'severity_threshold' => 'warning',

    /*
    |--------------------------------------------------------------------------
    | Rules
    |--------------------------------------------------------------------------
    |
    | Enable, disable, or adjust severity for any built-in or custom rules.
    | You can also register your own rules by adding their class name or short key.
    |
    */
    'rules' => [
        'AddNonNullableColumnWithoutDefault' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],
        'MissingIndexOnForeignKey' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],
        'DropColumnWithoutBackup' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],
        'AddUniqueConstraintOnNonEmptyColumn' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],
        'FloatColumnForMoney' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],

        // Example custom rule (auto-discovered from App\MigrationRules)
        'NoUnsignedBigIntRule' => [
            'enabled'  => true,
            'severity' => 'warning',
        ],
    ],
];
```
---

### 🧩 Severity Levels

| Level     | Meaning                              | Exit Code | Recommended Usage                            |
| --------- | ------------------------------------ | --------- | -------------------------------------------- |
| `info`    | Advisory only; does not affect CI    | 0         | Local hints or code-style warnings           |
| `warning` | Possible performance or safety issue | 0         | Default level for most rules                 |
| `error`   | Migration-breaking or data-loss risk | 1         | Use in CI/CD pipelines to block risky merges |

---

### 🧠 Notes

- You can disable a rule entirely by setting 'enabled' => false.
- To treat warnings as failures, change the global threshold to 'error'.
- Custom rules defined in App\MigrationRules or any namespaced class are automatically discovered — no code changes needed in the package.
- See [🧠 Writing Custom Rules](./writing-custom-rules.md) for detailed examples of defining your own rules.


✅ Pro Tip: Commit your config/migration-linter.php file to version control so your whole team shares the same linting standards.

---