<?php

namespace Whilesmart\Holdings\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Whilesmart\Holdings\Enums\PriceSource;
use Whilesmart\Holdings\Models\Holding;

class HoldingFactory extends Factory
{
    protected $model = Holding::class;

    public function definition(): array
    {
        return [
            'name' => $this->faker->randomElement(['Bitcoin', 'Ethereum', 'Apple', 'Rental flat']),
            'symbol' => $this->faker->randomElement(['BTC', 'ETH', 'AAPL', null]),
            'quantity' => $this->faker->randomFloat(4, 0.1, 50),
            'currency' => 'USD',
            'unit_price' => $this->faker->randomFloat(2, 10, 60000),
            'price_source' => PriceSource::Manual->value,
        ];
    }

    public function auto(string $provider = 'coingecko', string $externalRef = 'bitcoin'): static
    {
        return $this->state(fn () => [
            'price_source' => PriceSource::Auto->value,
            'provider' => $provider,
            'external_ref' => $externalRef,
        ]);
    }
}
