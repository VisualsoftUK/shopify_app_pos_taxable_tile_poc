# Tables

## Overview

`GetList` is an abstract action in the package providing Spatie `QueryBuilder`-backed pagination with consistent response shaping. Extend it in the consuming app for each paginated resource.

## Extending `GetList`

```php
use Visualsoft\ShopifyAdmin\Actions\Tables\GetList;
use Visualsoft\ShopifyAdmin\DataObjects\Pagination\PaginatedData;
use Illuminate\Database\Eloquent\Builder;

class GetOrderList extends GetList
{
    protected function getBaseQuery(): Builder
    {
        return Order::query()->where('shop_id', auth()->id());
    }

    protected function getDataClass(): string
    {
        return OrderData::class; // a Spatie Data class
    }

    // Optional overrides:
    protected function allowedSorts(): array
    {
        return ['created_at', 'total_price', '-total_price'];
    }

    protected function allowedFilters(): array
    {
        return ['status', AllowedFilter::scope('date_range')];
    }

    protected function defaultSort(): string
    {
        return '-created_at';
    }

    protected function perPage(): int
    {
        return (int) config('vs-admin-tables.max_items_per_page', 50);
    }
}
```

Call it from a controller:

```php
return GetOrderList::for($request)->get();
```

## `modifyQueryBuilder` — eager loading / enrichment

Override `modifyQueryBuilder` to add eager loads or subquery selects before pagination:

```php
protected function modifyQueryBuilder(QueryBuilder $queryBuilder): QueryBuilder
{
    return $queryBuilder->with(['customer', 'lineItems']);
}
```

## `transformResults` — post-pagination enrichment

Override `transformResults` to enrich result rows with external API data after pagination (avoids N+1 on the full table):

```php
protected function transformResults(LengthAwarePaginator $results): LengthAwarePaginator
{
    $enriched = $results->through(fn ($order) => $this->enrich($order));
    return $enriched;
}
```

## `PaginatedData` response shape

`PaginatedData::fromPaginator($paginator, $dataClass, $request)` returns:

```json
{
    "data": [ /* array of $dataClass items */ ],
    "links": {
        "first": "...",
        "last":  "...",
        "prev":  null,
        "next":  "..."
    },
    "meta": {
        "current_page": 1,
        "from":         1,
        "last_page":    5,
        "per_page":     50,
        "to":           50,
        "total":        234
    }
}
```

The shape is defined by `PaginatedData`, `PaginationLinksData`, and `PaginationMetaData` (all Spatie Data classes).

## Config

`vs-admin-tables.max_items_per_page` (env `VS_ADMIN_MAX_ITEMS_PER_PAGE`, default `50`) controls the default `perPage()`. Override `perPage()` in subclasses for resource-specific limits.

## Notes

- The `getDataClass()` return must be a Spatie `Data` class; it is passed to `PaginatedData::fromPaginator` for collection casting.
- Sorting and filtering use Spatie `QueryBuilder` conventions — pass sort/filter query params as `?sort=-created_at&filter[status]=active`.

## Frontend (Polaris + Inertia)

For **`IndexTable` / `Filters`** wired to the same sort/filter params via Inertia **`partialReload`**, use the package JS module — see [index-table-utils.md](index-table-utils.md).
