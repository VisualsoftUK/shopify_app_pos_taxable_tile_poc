# Governance Docs

When to update `.github` binding docs vs app `.ai/guidelines`. Complements the always-on `lifecycle-governance` guideline.

## Binding vs helpers

Lifecycle agents (Policy, Technical Refinement, Implementation) load only:

- `.github/ARCHITECTURE.md` (always binding)
- `.github/PRODUCT_STRATEGY.md` (always binding)
- `.github/adr/*.md` with `status: accepted` (binding). `proposed` / `superseded` / `deprecated` are context only.

They do **not** read `AGENTS.md`, `.ai/guidelines`, or skills. Those are coding-agent helpers. If they contradict `.github`, `.github/ARCHITECTURE.md` wins for paths and invariants. Fix drift in the same PR.

## Which file

**PRODUCT_STRATEGY.md** — product charter. Shipped jobs (In scope / does), out of scope / will never, interaction rules. Not a feature backlog. “In scope / does” is shipped behaviour, not an allowlist of future work.

**ARCHITECTURE.md** — technical ground truth: stack, system boundaries, key paths, i18n masters, CI/deploy, non-negotiables. Not a class catalog.

**ADR** — a decision with context, we will / will not, and trade-offs. Not a changelog of ARCHITECTURE. Retroactive ADRs are fine. Routine path/route updates: ARCHITECTURE only.

## Same PR vs after ship

| Change | When |
|--------|------|
| New route, extension, webhook, package boundary, i18n master, CI/deploy, non-negotiable | `ARCHITECTURE.md` in the **same PR** |
| New shipped merchant/shopper/POS job | `PRODUCT_STRATEGY.md` In scope / does — same PR **or** right after ship |
| Policy **Charter catch-up** note on the Feature | `PRODUCT_STRATEGY.md` when the surface ships (reminder, not an intake halt) |
| New will-never or interaction rule | `PRODUCT_STRATEGY.md` in the same PR |
| Reversing a will-never, new persona, new product identity | Charter PR **before** the work; ADR in that PR |
| Class/job/snippet detail | App `.ai/guidelines` or domain skill. Touch ARCHITECTURE only if paths/boundaries moved |

New jobs that do not contradict a will-never can pass Policy before they are listed in In scope / does.

## Write an ADR when

- The change is an architectural fork (two reasonable options, lock-in)
- You reverse a prior will-never or accepted ADR
- You add a persona or change product identity
- Agents must cite a we-will / we-will-not that does not belong in ARCHITECTURE as a path list

Skip an ADR for routine ARCHITECTURE maintenance (new route, extra webhook, path move).

Copy from org-shared-workflows `.github/adr/TEMPLATE.md`. Keep `status: proposed` until merge, then `accepted`.

## Policy `adr-conflict`

The label is historical. Policy halts with `needs-human-review` + `adr-conflict` when the issue conflicts with **any** binding doc (accepted ADR, ARCHITECTURE, or PRODUCT_STRATEGY), not ADRs only. Citation must be a real file + line range.

Absence from In scope / does is not a conflict. Charter / will-never / interaction conflicts are.
