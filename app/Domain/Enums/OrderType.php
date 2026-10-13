<?php

namespace App\Domain\Enums;

enum OrderType: string
{
    case EatIn = 'eat_in';
    case Takeaway = 'takeaway';

    /**
     * The order tag applied in Shopify, also used as the visible cart attribute value.
     */
    public function label(): string
    {
        return match ($this) {
            self::EatIn => 'Eat In',
            self::Takeaway => 'Takeaway',
        };
    }

    /**
     * Resolve the configured fallback order type.
     */
    public static function default(): self
    {
        return self::tryFrom((string) config('eat-in.default_order_type')) ?? self::Takeaway;
    }
}
