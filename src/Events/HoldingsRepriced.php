<?php

namespace Whilesmart\Holdings\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Emitted after a reprice run so the host app can bridge it to its own systems
 * (invalidate caches, recompute net worth, notify, ...). Carries the ids of the
 * holdings whose price changed.
 */
class HoldingsRepriced
{
    use Dispatchable;

    /**
     * @param  array<int, int|string>  $holdingIds
     */
    public function __construct(public readonly array $holdingIds) {}
}
