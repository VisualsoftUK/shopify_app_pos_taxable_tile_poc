# Configuration

## Required table fields

Each table entry in `config/vs-partitions.php` should include:

- `name`: existing MySQL table name.
- `type`: `daily` or `monthly`.
- `date_column`: date/datetime column used for RANGE partitioning.
- `prune_after`: retention window in days/months, or `null` to disable pruning.

## Lookahead and retention controls

- `daily_create_days_ahead`: how far ahead daily partitions are pre-created.
- `monthly_create_months_ahead`: how far ahead monthly partitions are pre-created.
- `prune_after` is evaluated by partition date naming, not by table data scans.

## Environment override pattern

- Override scheduling and retention via `VS_PARTITIONS_*` env variables.
- Keep env values numeric where numeric config is expected.

## Important behavior

- `prune_after: null` disables pruning for that table but still allows create-future runs.
- SQLite environments are no-op for partition SQL execution.
