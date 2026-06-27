<?php

namespace Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Whilesmart\Holdings\Models\Holding;

class HoldingApiTest extends TestCase
{
    private const OWNER = 'App\\Models\\Workspace';

    #[Test]
    public function it_creates_a_manual_holding_and_computes_value(): void
    {
        $this->postJson('/api/holdings', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'name' => 'Rental flat',
            'quantity' => 1,
            'currency' => 'USD',
            'unit_price' => 120000,
            'price_source' => 'manual',
        ])->assertCreated()
            ->assertJsonPath('data.name', 'Rental flat')
            ->assertJsonPath('data.value', 120000)
            ->assertJsonPath('data.price_source', 'manual');

        $this->assertDatabaseHas('holdings', [
            'owner_id' => 1,
            'name' => 'Rental flat',
            'price_source' => 'manual',
        ]);
    }

    #[Test]
    public function an_auto_holding_requires_a_provider_and_ref(): void
    {
        $this->postJson('/api/holdings', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'name' => 'Bitcoin',
            'quantity' => 0.5,
            'price_source' => 'auto',
        ])->assertStatus(422);
    }

    #[Test]
    public function it_filters_holdings_by_owner_and_price_source(): void
    {
        Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Flat', 'quantity' => 1, 'unit_price' => 100, 'price_source' => 'manual']);
        Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Bitcoin', 'quantity' => 1, 'unit_price' => 200, 'price_source' => 'auto', 'provider' => 'coingecko', 'external_ref' => 'bitcoin']);

        $this->getJson('/api/holdings?owner_type='.urlencode(self::OWNER).'&owner_id=1&price_source=auto')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Bitcoin');
    }

    #[Test]
    public function it_updates_and_deletes_a_holding(): void
    {
        $holding = Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Flat', 'quantity' => 1, 'unit_price' => 100]);

        $this->putJson("/api/holdings/{$holding->id}", ['quantity' => 3])
            ->assertOk()
            ->assertJsonPath('data.value', 300);

        $this->deleteJson("/api/holdings/{$holding->id}")->assertOk();
        $this->assertSoftDeleted('holdings', ['id' => $holding->id]);
    }
}
