---
id: configuration
title: 🧱 Configuration
sidebar_position: 4
---

## ⚙️ Configuration

The Laravel Migration Linter ships with a default configuration file that defines all available rules and severity thresholds.

---

## 📄 Default Config File

```php
return [
    'severity_threshold' => 'warning',

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
    ],
];

```
## 🧩 Severity Levels
| Level     | Description                          | Exit Code |
| --------- | ------------------------------------ | --------- |
| `info`    | Informational only                   | 0         |
| `warning` | May cause performance issues or risk | 0         |
| `error`   | Serious migration-breaking issues    | 1         |
