<?php

namespace Whilesmart\Holdings\PriceProviders;

use Whilesmart\Holdings\Contracts\HoldingPriceProvider;

/**
 * Default provider. Resolves no prices, so a host that has not bound a real
 * provider keeps holdings at their last known price instead of erroring.
 */
class NullPriceProvider implements HoldingPriceProvider
{
    public function prices(string $provider, array $externalRefs, string $currency): array
    {
        return [];
    }
}
