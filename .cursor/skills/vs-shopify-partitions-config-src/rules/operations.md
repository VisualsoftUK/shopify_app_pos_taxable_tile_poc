# Operations

## Manual operations

- Run `php artisan partitions:create-future` to pre-create missing upcoming partitions.
- Run `php artisan partitions:prune-old` to remove partitions older than configured retention.

## Scheduled operations

- The package service provider registers both tasks with cron expressions from config.
- Overlap guards are configurable and should be tuned for database size and runtime.

## Monitoring expectations

- Scheduled runs emit lifecycle logs before execution, and on success/failure.
- Use these logs to confirm partition maintenance is healthy in production.

## Safe usage guidance

- Prefer package commands over custom SQL scripts.
- If changing cadence, update only config values first and observe task runtime.
