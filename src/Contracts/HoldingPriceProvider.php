<?php

namespace Whilesmart\Holdings\Contracts;

interface HoldingPriceProvider
{
    /**
     * Current unit price for each external reference, denominated in $currency.
     *
     * The package stays provider-agnostic: it passes the holding's opaque
     * `provider` (e.g. "coingecko") and `external_ref` (the provider's own id),
     * and the host's implementation decides how to resolve them. References
     * with no available price are simply omitted from the result.
     *
     * @param  string[]  $externalRefs
     * @return array<string, float> price keyed by external reference
     */
    public function prices(string $provider, array $externalRefs, string $currency): array;
}
