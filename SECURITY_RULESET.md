# 🔒 Laravel Migration Linter – Security & Branch Protection Ruleset

This document defines the protection, CI, and contribution policies enforced for the **Laravel Migration Linter** repository.

---

## 🧩 1. Protected Branches
The following branches are protected and cannot be directly modified:
- **main** (production release branch)

### Enforcement
- ❌ No direct commits or pushes allowed.
- ✅ All changes must go through a **Pull Request (PR)**.
- 🧱 Force pushes and branch deletions are **blocked**.

---

## 🧩 2. Pull Request Requirements
Every pull request must:
1. Pass all automated **GitHub Actions** checks:
   - ✅ `Tests` (Pest / PHPUnit)
   - ✅ `PHPStan Analysis` (Static analysis)
   - ✅ `Laravel Pint` (Code style)
2. Be reviewed and approved by at least **one collaborator**.
3. Resolve all conversations before merging.

---

## 🧩 3. CI Status Checks (Quality Gates)
Merges to `main` are blocked unless:
| Workflow | Purpose | Requirement |
|-----------|----------|-------------|
| `Tests` | Ensures all unit and feature tests pass | ✅ Required |
| `PHPStan Analysis` | Detects static code issues | ✅ Required |
| `Laravel Pint` | Enforces Laravel code style | ✅ Required |

> All checks are defined under `.github/workflows/`.

---

## 🧩 4. Code Quality & Security
- Code **must pass Laravel Pint** formatting (`composer pint --test`).
- All static analysis warnings must be fixed before merge.
- No secrets, tokens, or credentials may be committed.
- Use **Dependabot** for dependency security updates.
- Use **composer audit** to detect known vulnerabilities.

---

## 🧩 5. Recommended Local Workflow
Developers should run the following before committing:

```bash
composer pint         # Code style
composer phpstan      # Static analysis
composer test         # Run all tests
```
## 🧩 6. Responsible Disclosure

If you discover a security vulnerability:
- Do not create a public issue.
- Email muhammadsufyanwebdeveloper@gmail.com with details.
- You will receive acknowledgment and guidance on resolution.

## 🧩 7. Maintainer Policy

Only maintainers can:

- Merge approved PRs into main
- Publish new releases to Packagist
- Temporarily disable rulesets (for emergency fixes only)

## 🧠 Summary

| Rule                          | Enforced    |
| ----------------------------- | ----------- |
| Require PR for merges         | ✅           |
| Require status checks to pass | ✅           |
| Restrict deletions            | ✅           |
| Block force pushes            | ✅           |
| Require linear history        | ⚙️ Optional |
| Signed commits                | ⚙️ Optional |

> “Security and consistency are not features — they’re defaults.”

--- 

