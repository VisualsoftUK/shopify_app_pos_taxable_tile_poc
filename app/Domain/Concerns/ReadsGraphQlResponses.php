<?php

namespace App\Domain\Concerns;

use App\Domain\Exceptions\ShopifyUserErrorsException;
use Illuminate\Support\Arr;

trait ReadsGraphQlResponses
{
    /**
     * Read a value from a GraphQL response, normalising the API client's
     * ResponseAccess objects into plain arrays.
     */
    protected function responseValue(array $response, string $path, mixed $default = null): mixed
    {
        $value = Arr::get($response, $path, $default);

        if (is_object($value) === true && method_exists($value, 'toArray') === true) {
            return $value->toArray();
        }

        return $value;
    }

    /**
     * Throw when a mutation payload contains userErrors, optionally ignoring
     * specific error codes (for example TAKEN on idempotent creates).
     *
     * @param  array<int, string>  $ignoredCodes
     *
     * @throws ShopifyUserErrorsException
     */
    protected function assertNoUserErrors(array $response, string $mutation, array $ignoredCodes = []): void
    {
        $userErrors = collect($this->responseValue($response, "body.data.{$mutation}.userErrors", []))
            ->reject(fn (array $error): bool => in_array($error['code'] ?? null, $ignoredCodes, true))
            ->values()
            ->all();

        if (empty($userErrors) === false) {
            throw new ShopifyUserErrorsException($mutation, $userErrors);
        }
    }
}
