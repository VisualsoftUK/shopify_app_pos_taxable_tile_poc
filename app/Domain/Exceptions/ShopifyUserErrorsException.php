<?php

namespace App\Domain\Exceptions;

use RuntimeException;

class ShopifyUserErrorsException extends RuntimeException
{
    /**
     * @param  array<int, array{field?: array<int, string>|null, message: string, code?: string|null}>  $userErrors
     */
    public function __construct(
        public readonly string $operation,
        public readonly array $userErrors,
    ) {
        $messages = collect($userErrors)->pluck('message')->implode('; ');

        parent::__construct("Shopify {$operation} returned user errors: {$messages}");
    }
}
