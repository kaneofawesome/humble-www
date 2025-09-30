# Project Constitution

> **Applies to:** This repository and all subpackages.
>
> **Audience:** Contributors, reviewers, and maintainers.
>
> **Goal:** Ship a secure, maintainable, high‑performance Symfony application with a world‑class developer experience.

---

## 1) Principles

* **User value first:** Every change must trace to a user impact or business objective.
* **Security & privacy by default:** Least privilege, zero secrets in Git, regular audits.
* **Small, reversible steps:** Prefer many small PRs over one large PR.
* **Automate the boring:** CI enforces what humans shouldn’t have to remember.
* **1 source of truth:** Code > docs > oral history. If it’s important, encode it in CI or documentation.

---

## 2) Roles & Decision Making

* **Maintainers** own releases, CI, and standards. They can merge with approvals satisfied.
* **Reviewers** ensure quality; every PR needs a reviewer who is not the author, or is a code owner.
* **Contributors** follow this constitution and the contribution guide.
* **Decision process:**

    1. Try to resolve in PR discussion.
    2. If blocked, open an **RFC** (docs/rfcs/####-short-title.md) with context, options, trade‑offs, and a preferred path.
    3. Maintainers decide within 5 business days or delegate to a working group.

---

## 3) Branching, Versioning, and Releases

* **Default branch:** `main` (always deployable).
* **Branch naming:**

    * Features: `feat/<scope>-<short-slug>`
    * Fixes: `fix/<scope>-<short-slug>`
    * Chores/CI: `chore/<scope>-<short-slug>`
    * Experiments: `exp/<scope>-<short-slug>`
* **Versioning:** Semantic Versioning (SemVer): `MAJOR.MINOR.PATCH`.
* **Release cadence:** As needed, but prefer small, frequent releases.
* **Changelog:** Keep a Changelog format in `CHANGELOG.md`; generated from Conventional Commits.
* **Tags:** Annotated Git tags for releases (e.g., `v1.4.2`).

---

## 4) Commit Messages (Conventional Commits)

**Format:**

```
<type>(<optional scope>)!: <short summary>

<body – motivation, contrasts>

<footer – BREAKING CHANGE: ..., closes #123, see #456>
```

**Types:** `feat`, `fix`, `perf`, `refactor`, `docs`, `test`, `build`, `ci`, `chore`, `revert`.

**Rules:**

* Use imperative mood: “add login redirect,” not “added.”
* Include a scope when useful (e.g., `feat(auth):`).
* Use `!` or `BREAKING CHANGE:` footer for breaking changes.
* Reference issues with `closes #123` where applicable.

**Examples:**

* `feat(auth): add passwordless login via magic links`
* `fix(cache): avoid stampede by locking warmers (closes #421)`
* `refactor(controller): extract DTO mapping`

---

## 5) Code Quality Standards

* **Language & style:** PHP ≥ 8.2, PSR‑12. Use short arrays, typed properties, readonly where sensible.
* **Static analysis:**

    * **Psalm** (preferred) at **level 3** or better; no baseline drift. OR **PHPStan** at **level 8**.
    * All new code must pass without suppressions; legacy suppressions require TODO with link to issue.
* **Formatting:** `php-cs-fixer` (or `friendsofphp/php-cs-fixer`). CI enforces formatting; no manual bikeshedding.
* **Architecture:**

    * Favor **constructor injection** and **final** classes by default.
    * Keep controllers thin; move logic to services.
    * Use **DTOs** for request/response mapping; validate using Symfony Validator.
    * Avoid static state and facades; prefer explicit dependencies.
* **Security:**

    * Run `composer audit` in CI.
    * Deny known‑vulnerable packages via `roave/security-advisories` (conflict rule) in dev.
    * No secrets in repo; use environment variables and `vault`/secret manager.
    * Validate and sanitize all inputs; encode outputs (Twig auto‑escape on).
* **Performance:**

    * Cache where appropriate (HTTP caching, Symfony Cache, Doctrine 2nd level cache only with care).
    * Avoid N+1 queries; enable Doctrine logging for tests and check query counts in critical paths.
    * Establish **performance budgets**; verify with profiling (e.g., Blackfire).

---

## 6) Testing Policy (Pyramid)

**General:** CI must run the full suite on every PR. New code requires tests. Bug fixes require a failing test first.

* **Unit tests (fast):** `PHPUnit` for pure logic. Expectation: > **80%** coverage for units touched by the PR.
* **Integration tests:** Services + container + DB (SQLite or test DB). Expectation: meaningful path coverage; avoid flakiness.
* **Functional tests:** HTTP‑level via `WebTestCase`/`BrowserKit` to exercise controllers, routing, security, templates.
* **End‑to‑End (E2E) UI tests:** `symfony/panther` for a few critical journeys (auth, checkout, etc.). Keep these minimal and stable.
* **Mutation testing (optional for critical modules):** `infection/infection` with a baseline ≥ **60% MSI** for designated packages.

**Data & isolation:**

* Use **Zenstruck Foundry** (or Doctrine fixtures) for factories.
* Wrap DB tests with **DAMA\DoctrineTestBundle** or transactions for isolation.
* Reset the kernel between tests that modify global state.

**Parallelization:** `brianium/paratest` recommended; ensure tests are hermetic.

**Test naming:** `methodName_shouldDoX_whenY` or GWT style in docblocks.

**Minimum gates to merge:**

* Lint + style pass
* Static analysis pass
* All tests green
* Coverage thresholds met (project‑level CI gate: **≥ 80% lines**, **≥ 70% branches**)

---

## 7) Pull Requests

**General requirements:**

* Small, focused PRs (< ~300 LOC diff when possible).
* Title must follow Conventional Commits (used for changelog).
* Description must answer: *What / Why / How / Risks / Rollback*.
* Include screenshots for UI changes and **before/after** for performance‑sensitive paths.
* Mark breaking changes.

**Checklist (template):**

* [ ] Title uses Conventional Commits
* [ ] Tests added/updated and pass locally
* [ ] Static analysis clean
* [ ] No secrets; configs documented
* [ ] Backwards compatibility evaluated
* [ ] Performance implications considered
* [ ] Docs/CHANGELOG updated if needed

**Reviews:**

* At least **1 approval** by a code owner for touched area; **2** for security‑sensitive changes.
* Reviewer uses **suggested changes** generously and requests focused follow‑ups rather than blocking on nits.
* “Request changes” when correctness, security, or architecture concerns exist.

---

## 8) CI/CD

**Minimum CI jobs:**

1. **Composer**: validate lock, `composer validate --strict`
2. **Lint/Style**: `php-cs-fixer --diff --dry-run`
3. **Static analysis**: `psalm` (or `phpstan analyse`)
4. **Unit/Integration tests**: `phpunit` (with coverage)
5. **E2E (smoke)**: Panther critical paths (optional on PR, required on main)
6. **Security**: `composer audit` and advisories check

**Artifacts & quality:** publish coverage (Cobertura/LCOV) and junit test reports.

**Branch protections:**

* Require all checks; disallow force‑push to `main`.
* Require up‑to‑date branches before merging.

**Deployments:**

* Main merges trigger staging deploy; tag releases trigger production deploy.
* Use blue/green or canary when possible; include a **rollback plan**.
* Keep infra-as-code (e.g., Terraform/Ansible) versioned and reviewed.

---

## 9) Documentation & Knowledge

* **README.md**: quick start for devs; **CONTRIBUTING.md**: contribution steps.
* **/docs**: architecture decision records (ADRs) and RFCs.
* **Runbook**: operations playbooks for incidents, on-call, and recovery.
* Keep **`bin/console about`** output clean; document custom commands.

---

## 10) Security & Compliance

* Threat model critical features; log auth/authorization events (without PII).
* Regular dependency updates (weekly bot PRs); fast‑track security patches.
* Vulnerability disclosure: security@… mailbox; acknowledge within 72 hours.
* Data handling: classify data, encrypt at rest and in transit; rotate keys.

---

## 11) Local Development

* **Environment:** use `.env` + `.env.local` (ignored) for developer overrides.
* **Bootstrap:** `make setup` or `composer setup` to install tools and pre-commit hooks.
* **Database:** use Doctrine migrations; **never** modify schema without a migration.
* **Fixtures:** `make fixtures` seeds minimal data for local dev.

---

## 12) Tooling & Scripts (suggested)

Add these Composer scripts to standardize commands:

```json
{
  "scripts": {
    "lint": "php-cs-fixer fix --diff --dry-run",
    "lint:fix": "php-cs-fixer fix",
    "analyse": "psalm --output-format=github",
    "test": "phpunit",
    "test:coverage": "phpunit --coverage-clover=var/coverage/clover.xml",
    "infection": "infection --min-msi=60 --threads=4",
    "audit": "composer audit --format=json",
    "ci": [
      "@lint",
      "@analyse",
      "@test"
    ]
  }
}
```

**Makefile (optional):**

```makefile
setup: ## Install tools & pre-commit hooks
	composer install
	vendor/bin/simple-phpunit --version >/dev/null || true
	@echo "✔ Dev setup complete"

qa: ## Full quality gate
	composer lint
	composer analyse
	composer test
```

---

## 13) Code Ownership

* Use `CODEOWNERS` to map directories to owners (teams or individuals).
* Security‑sensitive paths require code owner review.

---

## 14) Issue Tracking & Labels

* **Labels:** `type:feat`, `type:bug`, `type:chore`, `priority:p0/p1/p2`, `area:<domain>`, `good first issue`.
* **SLA:**

    * `p0`: acknowledge within 2h, fix asap
    * `p1`: plan within 1 day
    * `p2`: triage within 3 days

---

## 15) Acceptance Criteria Definition of Done (DoD)

A change is **Done** when:

* Functionality works and is documented.
* Tests (unit/integration/functional) exist and pass.
* Observability (logs/metrics/traces) is adequate for support.
* Security, performance, and accessibility reviewed where relevant.
* Deployed to the target environment and behind feature flags if risky.

---

## 16) Accessibility (if UI)

* keyboard navigable; adequate contrast.

---

## 17) Exceptions

* Exceptions to this constitution require an RFC with explicit scope and timeline. Time‑bound waivers only.

---

## 18) Maintenance

* This document is living. Propose changes via PR titled `docs(constitution): ...`.
* Maintainers review governance changes within 5 business days.

---

