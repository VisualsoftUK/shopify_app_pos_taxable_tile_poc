---
name: vs-shopify-partitions-config-src
description: Guides package consumers on configuring partitioned tables, scheduler cadence, and operational commands without modifying package internals.
---

# VS Shopify Partitions (Consumer)

## Quick reference

| Topic | Path/command |
|---|---|
| Package config file | `config/vs-partitions.php` |
| Create future partitions now | `php artisan partitions:create-future` |
| Prune old partitions now | `php artisan partitions:prune-old` |
| Schedule knobs | `create_future_cron_expression`, `prune_old_cron_expression`, overlap settings |

## Rule files

Read the rule file that matches the integration task.

1. Configuration setup and retention policy → `rules/configuration.md`
2. Operations and scheduling behavior → `rules/operations.md`
