<?php

namespace Whilesmart\Holdings\Enums;

enum PriceSource: string
{
    /** The owner sets and maintains the price by hand (stocks, property, ...). */
    case Manual = 'manual';

    /** The price is refreshed from a bound provider keyed by external_ref. */
    case Auto = 'auto';

    /**
     * @return string[]
     */
    public static function values(): array
    {
        return array_map(fn (self $c) => $c->value, self::cases());
    }
}
