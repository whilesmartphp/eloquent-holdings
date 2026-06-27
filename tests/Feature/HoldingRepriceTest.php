<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Whilesmart\Holdings\Contracts\HoldingPriceProvider;
use Whilesmart\Holdings\Events\HoldingsRepriced;
use Whilesmart\Holdings\Models\Holding;
use Whilesmart\Holdings\Services\HoldingRepricer;

class HoldingRepriceTest extends TestCase
{
    private const OWNER = 'App\\Models\\Workspace';

    #[Test]
    public function it_reprices_auto_holdings_via_the_bound_provider_and_leaves_manual_ones(): void
    {
        $this->app->bind(HoldingPriceProvider::class, fn () => new class implements HoldingPriceProvider
        {
            public function prices(string $provider, array $externalRefs, string $currency): array
            {
                return $provider === 'coingecko' ? ['bitcoin' => 65000.0] : [];
            }
        });

        $auto = Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Bitcoin', 'quantity' => 0.5, 'currency' => 'USD', 'unit_price' => 1, 'price_source' => 'auto', 'provider' => 'coingecko', 'external_ref' => 'bitcoin']);
        $manual = Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Flat', 'quantity' => 1, 'currency' => 'USD', 'unit_price' => 120000, 'price_source' => 'manual']);

        Event::fake([HoldingsRepriced::class]);

        $count = app(HoldingRepricer::class)->reprice();

        $this->assertSame(1, $count);
        $this->assertEquals(65000, $auto->fresh()->unit_price);
        $this->assertNotNull($auto->fresh()->last_priced_at);
        $this->assertEquals(120000, $manual->fresh()->unit_price);
        Event::assertDispatched(HoldingsRepriced::class);
    }

    #[Test]
    public function the_null_provider_leaves_prices_untouched(): void
    {
        $auto = Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Bitcoin', 'quantity' => 1, 'currency' => 'USD', 'unit_price' => 42, 'price_source' => 'auto', 'provider' => 'coingecko', 'external_ref' => 'bitcoin']);

        $count = app(HoldingRepricer::class)->reprice();

        $this->assertSame(0, $count);
        $this->assertEquals(42, $auto->fresh()->unit_price);
    }
}
