---
name: update-internal-packages
description: >-
  Bumps selected Visualsoft internal Composer packages, refreshes lockfiles, and opens GitHub PRs titled
  "Update Internal Packages" across one or more consumer apps. Never activate unless the user explicitly
  invoked this workflow by name (e.g. "run the update-internal-packages skill", "internal packages PR workflow")
  or clearly asked to update multiple internal Visualsoft packages and open PRs with that exact title. Do not
  activate for generic "update dependencies", a single package bump, or routine composer.json edits without
  this explicit commission. In a multi-root Cursor workspace with two or more consumers, prefer a three-phase
  flow (confirm once, parallel generalPurpose or shell subagents per repo, then aggregate results)—see Orchestration.
---

# Update internal Visualsoft packages

## Activation gate

Do **not** follow this document unless **all** of the below are true:

1. The user **explicitly** asked to run this workflow in the current message, **or** a prior message in the same session clearly commissioned the full multi-consumer / PR workflow (not a one-off dependency tweak).
2. The user’s intent matches: bump **internal** `visualsoft/*` packages (from the table below), refresh `composer.lock`, and open GitHub PR(s) titled **Update Internal Packages**.

If the task sounds similar (e.g. "upgrade a package", "bump vs-shopify-connector") but the user did **not** ask for **this** workflow, **stop** and ask whether they want the internal multi-repo PR workflow before proceeding.

Do **not** activate or chain this skill from other skills or from inferred intent alone.

## Version source

These Visualsoft packages are **not on Packagist**. They resolve via the repo’s private Composer configuration (VCS `repositories`, etc.). **Do not** suggest looking up versions on Packagist.

The **user supplies** target version constraints (e.g. `^1.16.1`) per package or a rule they state. Apply those constraints in `composer.json`, then run `composer update` so the lockfile reflects what Composer resolves.

## Supported packages

Confirm with the user which of these to update and the **target constraint** for each (unless they give one rule for all selected packages).

| Short name | Composer package | Notes |
|------------|------------------|-------|
| vs-shopify-admin | `visualsoft/vs-shopify-admin` | |
| vs-shopify-connector | `visualsoft/vs-shopify-connector` | |
| vs-shopify-partitions | `visualsoft/vs-shopify-partitions` | |
| vs-mantle | `visualsoft/vs-mantle` | |
| vs-shopify-beacons | `visualsoft/vs-shopify-beacons` | |
| vs-laravel-security-base | `visualsoft/vs-laravel-security-base` | |
| vs-laravel-monitoring | `visualsoft/vs-laravel-monitoring` | |
| composer-package-template | `visualsoft/composer-package-template` | |
| vs-ai-rules | `visualsoft/vs-ai-rules` | **Always `require-dev`** — never `require`. Shared AI guidelines/skills for [Laravel Boost](https://github.com/laravel/boost); consumers use `composer require --dev visualsoft/vs-ai-rules` (or edit `require-dev` only). |

All other packages in this table are production dependencies unless a given consumer already chose to list them under `require-dev` for a local reason — **except `vs-ai-rules`, which must stay dev-only.**

## Find consumer projects (workspace vs single repo)

1. **Multi-root Cursor workspace**: Each workspace folder that is a **git repository root** and has a **root-level** `composer.json` is one **consumer app**. Process each independently (own branch, commit, push, PR).
2. **Single folder open**: One root `composer.json` → one consumer.
3. **Monorepo** (multiple `composer.json` files inside one git repo): Do **not** assume paths. Ask the user which `composer.json` paths are consumers, or only update the repository root `composer.json` unless they name others.

**Filter**: For each consumer, read `require` and `require-dev`. Skip repos that do not list any of the **user-selected** packages.

## Orchestration: single agent vs parallel subagents (Cursor workspace)

### When to parallelize

Use **parallel subagents (one per consumer)** when **all** of the following hold:

- The session is a **multi-root Cursor workspace** where each consumer is a **separate git repository root** with a root-level `composer.json`.
- After the filter above, **two or more** consumers remain.

Do **not** default to this model for a **monorepo** (one git repo, multiple `composer.json` paths) unless the user explicitly splits work across named paths the same way—keep asking which paths matter when in doubt.

### Hard requirement: Phase 2 must use parallel `Task` (not a shell loop)

When **two or more** consumers remain, the main agent **must** execute Phase 2 by issuing **one Cursor `Task` tool call per consumer**, all in the **same** assistant turn (`subagent_type`: `generalPurpose` or `shell` — see below). Each subagent runs the full **Per-consumer workflow** in **that** repo’s absolute root only.

**Forbidden (anti-pattern, does not satisfy this skill):** a single terminal script, `for`/`while` over repo paths, `xargs`, or any approach that `cd`s through multiple separate git roots in **one** `run_terminal_cmd` (or chained commands) instead of parallel `Task`s. That is **not** an acceptable substitute even if it would “work”; it violates this orchestration contract.

**If `Task` is unavailable** in the environment, **stop** and tell the user you cannot run parallel subagents here; **do not** silently fall back to a multi-repo shell loop.

**Self-check before Phase 3:** Let N = number of consumers after filtering. If N ≥ 2, you must have launched **N** parallel `Task` invocations for Phase 2. If you only used generic terminal commands across repos, you **failed** this skill — redo Phase 2 with `Task` before summarizing.

### When the main agent runs everything sequentially

- **One** consumer after filtering.
- The user **asks for sequential** handling.

### Benefits of parallel subagents (per repo)

- **Wall-clock**: `composer update` is often slow (network, resolution); overlapping runs cuts total wait time.
- **Context**: Each subagent focuses on one repo, reducing mistakes (branch names, paths, wrong `composer.json`).
- **Partial success**: If one repo fails (e.g. conflict), others can still finish with PRs.

### Guardrails

- Finish **Supported packages** confirmation (which packages, which constraints) **before** spawning subagents. Pass every subagent the **same** canonical block: selected Composer packages → constraints, exact PR title rule, that `-W` retries follow this skill’s lockfile step, and that Laravel consumers must run `php artisan vendor:publish --tag=visualsoft-assets-migrations --no-interaction` when `artisan` exists (step 5 below).
- Subagents must **not** re-negotiate versions or package selection with the user.

### Cursor Task tool: subagent type

- **`best-of-n-runner`** is for isolated **git worktrees** inside a **single** repository. Do **not** use it for multi-root folders that are already separate clones.
- For multi-root **separate repos**, use **`generalPurpose`** or **`shell`** subagents—**one per consumer**—with that consumer’s **absolute** repository root as the working context in the task prompt.
- The parent agent **must not** run `composer update` / `git push` / `gh pr create` across multiple consumers itself when N ≥ 2; that work belongs **inside** each `Task` subagent’s prompt for its single repo.

### Three-phase flow (2+ consumers, parallel)

1. **Phase 1 — Main agent**: Discover consumers, apply the filter, confirm packages and constraints with the user. List which repos will receive subagents vs were skipped (no matching deps).
2. **Phase 2 — Parallel**: Launch **only** via **multiple `Task` tool uses in one message** (one subagent per remaining consumer) so runs overlap. Each subagent executes **Per-consumer workflow** (steps below) end-to-end in **that repo’s root only**, including optional verify, commit, push, and `gh pr create`. Each task prompt must embed the canonical parameter block and the absolute path to that consumer. **Do not** replace this step with one multi-repo shell command.
3. **Phase 3 — Main agent**: Read each subagent’s final return, then produce the user summary per **After multiple consumers** (successes, skips, failures).

## Per-consumer workflow

Used by the **main agent** when there is a single consumer (or the user chose sequential), or by **each Phase-2 subagent** when running consumers in parallel.

Repeat for every consumer that passed the filter (main agent loop), or assign **one** consumer per subagent (parallel).

### Prerequisites

- `gh` CLI installed and authenticated.
- Remote is **GitHub**; use `gh pr create` as below.

### Steps

1. **Git baseline**: `git fetch`; checkout default branch (`git symbolic-ref refs/remotes/origin/HEAD` → e.g. `main`); `git pull`. Working tree should be clean; if not, stash with a clear message or ask the user.
2. **Branch**: Create e.g. `update/internal-packages` (use a consistent name; delete/recreate or choose a fresh name if it already exists and was abandoned).
3. **Edit `composer.json`**: Set `require` / `require-dev` constraints only for the user-selected packages to the user-provided values. For `visualsoft/vs-ai-rules`, only touch **`require-dev`** (see table above).
4. **Lockfile**: Run:

   ```bash
   composer update visualsoft/pkg-one visualsoft/pkg-two ... --no-interaction
   ```

   List every selected package that exists in this consumer’s `composer.json`.

   If Composer fails because **transitive** internal or related packages must move (e.g. a new admin release requires a newer `vs-mantle`), retry with:

   ```bash
   composer update visualsoft/pkg-one ... -W --no-interaction
   ```

   (`-W` is `--with-all-dependencies`.)

5. **Visualsoft package migrations (Laravel)**: When the consumer root has **`artisan`** (Laravel app), from the app root run:

   ```bash
   php artisan vendor:publish --tag=visualsoft-assets-migrations --no-interaction
   ```

   This publishes migration stubs from Visualsoft packages so the app picks up new or changed package migrations after the bump. **Include any new or updated files under `database/migrations/`** (and related publish output) in the same commit as the lockfile change. If `artisan` is absent, skip this step.

6. **Laravel Boost** (when relevant):
   - If **`visualsoft/vs-ai-rules`** was updated: from the app root, run `php artisan boost:update` so Boost recompiles `AGENTS.md` and `.cursor/skills/` from the newly installed package. Without this, the repo can still resolve the new Composer version while editors/agents keep stale guidance.
   - If the user also asked to bump **Laravel Boost itself** (`laravel/boost`, dev dependency): update its constraint in `require-dev`, include it in the `composer update …` package list, then run `php artisan boost:update` after a successful install so local Boost output matches the new Boost version. Confirm the exact package name/constraint with the user if unclear.

7. **Verify** (optional unless user declines): Run a minimal test pass for this app, e.g. `php artisan test --compact` with a file or `--filter` when applicable.
8. **Commit**: e.g. `chore: bump internal Visualsoft packages` — message should name packages or point to the PR body.
9. **Push** and open the PR:

   ```bash
   gh pr create --title "Update Internal Packages" --body "..."
   ```

   Use **`--title "Update Internal Packages"`** exactly. The body must list packages, new constraints, whether `-W` was used, whether `php artisan vendor:publish --tag=visualsoft-assets-migrations` ran (and if it produced new migrations), and if `php artisan boost:update` was run (when `vs-ai-rules` and/or `laravel/boost` changed).

## After multiple consumers (Phase 3 aggregation)

After **sequential** runs or **parallel** subagents return, merge outcomes into one reply for the user:

- **Succeeded**: repo name, branch name, PR URL (`gh pr create` link).
- **Skipped before work** (filter): repos that never listed the selected packages—no branch/PR.
- **Failed mid-run**: repo name, which step failed (e.g. `composer update`, `vendor:publish` / `visualsoft-assets-migrations`, `boost:update`, `gh pr create`), and a short error excerpt so the user can fix or retry.

Do not report success for a repo unless push and PR creation completed (or the user explicitly agreed to stop short).
