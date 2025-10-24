---
id: writing-custom-rules
title: 🧠 Writing Custom Rules
sidebar_position: 6
---

## 🧠 Writing Custom Rules

Laravel Migration Linter is designed to be extensible.  
You can create your own rule classes to handle specific migration patterns.

---

## 🧩 1️⃣ Create a Rule Class

Inside your app or a custom package, create a new class that extends the base rule:

```php
namespace App\MigrationRules;

use Sufyandev\MigrationLinter\Rules\BaseRule;

class NoUnsignedBigIntRule extends BaseRule
{
    public function check($migration)
    {
        if (str_contains($migration, 'unsignedBigInteger')) {
            return $this->warn('Avoid using unsignedBigInteger for portability.');
        }

        return $this->pass();
    }
}
```
## 🧩 2️⃣ Register the Rule

Add your new rule to the configuration file:
```bash
// config/migration-linter.php
'rules' => [
    // existing rules...
    'NoUnsignedBigIntRule' => [
        'enabled' => true,
        'severity' => 'warning',
    ],
],
```

## 🧪 3️⃣ Run the Linter
```bash
php artisan migrate:lint
```
Your new rule will now be included in the linting process.