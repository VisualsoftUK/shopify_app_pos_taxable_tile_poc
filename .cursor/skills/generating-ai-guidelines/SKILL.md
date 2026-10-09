---
name: generating-ai-guidelines
description: Generates guideline and skill files so AI agents have context on an area of the codebase. Use when asked to document an area, create AI guidance, write guidelines or skills, capture architectural decisions for agents, or generate AGENTS.md content.
---

# Generating AI Guidelines

Help the user generate guideline and skill files that give AI agents context on a specific area of the codebase or package. Work collaboratively: use what is already in the chat, then confirm with the user before writing.

## Step 1 — Determine context

Always ask the user:

> "Are we working on an **app** or a **package**?"

If **package**, also ask:

> "Should this be for **developing** the package, **consuming** it, or **both**?"

Also confirm the **project slug** — this is used to prefix all skill names and avoid conflicts when multiple packages are installed into the same app:

> "What slug should I use for this project? (e.g. `broadcast`, `vs-mantle`)"

Infer it from `composer.json` (`name` field, taking the part after `/`) or the project folder name if obvious. Confirm before proceeding.

## Step 2 — Identify the area

Use the current chat context (conversation, open files, recent edits) to infer the topic. State your assumption:

> "It looks like we're documenting [area] — is that right?"

If the topic is unclear, ask the user to describe the area in one sentence.

## Step 3 — Draft the content

Write the content as directives for an AI agent to act on — concise and actionable, no padding.

### Before you write — check for existing coverage

Before drafting any content, check whether the area is already covered by:
- **Laravel Boost skills**: `laravel-best-practices`, `pest-testing`, `inertia-react-development`, etc
- **Package skills**: `visualsoft-best-practices`, `vs-mantle`, etc
- **Existing guidelines/skills** already present in `.ai/` or `resources/boost/`

If something is already covered, **do not repeat it**. Instead, reference it:
> "This area uses Mantle for billing — activate `vs-mantle` for plan/subscription specifics."

### What to write — focus on the project-specific delta

Only document what makes this area **different from standard Laravel**. Ask: "Would a senior Laravel developer already know this?" If yes, leave it out.

Good candidates:
- Non-obvious architectural patterns unique to this project
- Key constraints or invariants that are easy to violate (e.g. "never update this table directly")
- Entry points and class names the agent would otherwise have to search for
- Async pipelines, event chains, or observer side-effects that aren't visible from a single file

Skip:
- Standard CRUD operations
- Anything already documented in an existing Boost or package skill
- General Laravel conventions (validation, routing, Eloquent relationships)

### Choosing what to produce

Use this decision tree for each area:

**1. Guideline only** — when the area is a thin delta on top of an existing skill.
- The guideline names the area, states the key constraints/entry points (5–10 lines), and references the existing skill.
- No `SKILL.md` needed.
- Use when: the area is mostly covered by `laravel-best-practices`, `vs-mantle`, `vs-shopify-connector`, etc., and there are only a handful of app-specific facts to add.

**2. Guideline + single `SKILL.md`** — when the area has meaningful project-specific depth but covers one coherent domain.
- The guideline is brief and points to the skill. The skill holds the full detail.
- Use when: the area has a clear entry point, a non-obvious pattern or pipeline, but all the content belongs to a single topic (e.g. AI image generation, video processing).
- If the skill would exceed ~200 lines, consider splitting into `rules/`.

**3. Guideline + index `SKILL.md` + `rules/` sub-files** — when the area spans multiple distinct domains that wouldn't all be relevant at the same time.
- The guideline points to the skill. The skill is a short index (under 50 lines) with a quick-reference table and a numbered list of rule files. Each rule file covers one domain in full.
- Use when: the area covers 3+ distinct sub-topics (e.g. content pipeline covering media, collections, playlists, observers), OR a single `SKILL.md` would exceed ~300 lines.
- The agent reads only the rule file relevant to the current task — not the whole skill.

### Guideline file (`.blade.php`)

- 10–20 lines maximum
- Always-on facts: key entry points, critical constraints, references to existing skills
- End with a line telling the agent to activate the relevant skill (if one exists)
- Never put detail here that only matters when actively working in the area

### Skill file (`SKILL.md`)

- Deeper, on-demand knowledge loaded only when working in the area
- Under 500 lines total across all files; use `rules/` for progressive disclosure
- Concrete examples over prose; specific patterns over general advice

### Deciding the split

Put something in the guideline if the agent needs it on every task in this area. Put it in the skill if it only matters when actively making changes. When in doubt, put it in the skill.

## Step 4 — Write the files

Use the paths from the table below based on the answers from Step 1.

| Context | File | Path |
|---|---|---|
| App — guideline | `{area}.blade.php` | `.ai/guidelines/{area}.blade.php` |
| App — skill | `SKILL.md` | `.ai/skills/{slug}-{area}/SKILL.md` |
| Package — contributor guideline | `{area}.blade.php` | `.ai/guidelines/{area}.blade.php` |
| Package — contributor skill | `SKILL.md` | `.ai/skills/{slug}-{area}/SKILL.md` |
| Package — consumer guideline | `{area}.blade.php` | `resources/boost/guidelines/{area}.blade.php` (one file per topic; Boost compiles every `.blade.php` in that directory) |
| Package — consumer skill | `SKILL.md` | `resources/boost/skills/{slug}-{area}/SKILL.md` |

Use `resources/boost/guidelines/core.blade.php` only for a short global pointer (skill activation, “read X before Y”). Do not append long topic sections there.

The `name` field in every `SKILL.md` frontmatter must also use `{slug}-{area}` (e.g. `name: broadcast-content-pipeline`).

Where `{slug}` is the project slug confirmed in Step 1, and `{area}` is a lowercase hyphenated slug of the topic (e.g. `media-uploads`, `checkout-flow`).

Skill names follow the pattern `{slug}-{area}` (e.g. `broadcast-content-pipeline`, `vs-mantle-checkout-flow`). This prevents conflicts when multiple packages are installed into the same app and their skills land in the same `.cursor/skills/` directory.

After writing, show the user the paths and a summary of what was written.

## Step 5 — Sync binding governance

`.ai/guidelines` and skills are implementation helpers. GitHub lifecycle agents load **only** `.github/ARCHITECTURE.md`, `.github/PRODUCT_STRATEGY.md`, and accepted `.github/adr/`.

When the documented area changes **system boundaries, entry points, or package routing**, also update `.github/ARCHITECTURE.md` in the same change (and `.github/PRODUCT_STRATEGY.md` if product scope / does / will-never / interaction rules changed). Do not leave binding facts only in AGENTS.md.

For the update table and ADR vs ARCHITECTURE vs PRODUCT_STRATEGY, follow the `lifecycle-governance` guideline or `visualsoft-best-practices` → `rules/governance-docs.md`.

## Reference

Read only the template(s) relevant to what you are generating:

- App guideline → [templates/app-guideline.md](templates/app-guideline.md)
- App skill → [templates/app-skill.md](templates/app-skill.md)
- Package consumer guideline → [templates/package-consumer-guideline.md](templates/package-consumer-guideline.md)
- Package consumer skill → [templates/package-consumer-skill.md](templates/package-consumer-skill.md)
- Package contributor guideline → [templates/package-contributor-guideline.md](templates/package-contributor-guideline.md)
- Package contributor skill (single domain) → same structure as app skill; see [templates/app-skill.md](templates/app-skill.md)
- Package contributor skill (multiple domains) → [templates/contributor-skill-multi-domain.md](templates/contributor-skill-multi-domain.md)
