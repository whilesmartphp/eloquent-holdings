<?php

namespace Whilesmart\Holdings\Services;

use Illuminate\Support\Facades\Event;
use Whilesmart\Holdings\Contracts\HoldingPriceProvider;
use Whilesmart\Holdings\Events\HoldingsRepriced;
use Whilesmart\Holdings\Models\Holding;

/**
 * Refreshes the unit price of auto-priced holdings via the bound
 * HoldingPriceProvider. Provider-agnostic: it groups holdings by their opaque
 * (provider, currency) pair and asks the provider for current prices.
 */
class HoldingRepricer
{
    public function __construct(private readonly HoldingPriceProvider $provider) {}

    /**
     * @return int number of holdings whose price was updated
     */
    public function reprice(): int
    {
        $changed = [];

        Holding::query()
            ->autoPriced()
            ->get()
            ->groupBy(fn (Holding $h) => $h->provider.'|'.strtoupper((string) $h->currency))
            ->each(function ($group, string $key) use (&$changed) {
                [$provider, $currency] = explode('|', $key, 2);

                $refs = $group->pluck('external_ref')->unique()->values()->all();
                $prices = $this->provider->prices($provider, $refs, $currency);

                foreach ($group as $holding) {
                    $price = $prices[$holding->external_ref] ?? null;
                    if ($price === null) {
                        continue;
                    }

                    $holding->forceFill([
                        'unit_price' => $price,
                        'last_priced_at' => now(),
                    ])->save();

                    $changed[] = $holding->getKey();
                }
            });

        if ($changed !== []) {
            Event::dispatch(new HoldingsRepriced($changed));
        }

        return count($changed);
    }
}
