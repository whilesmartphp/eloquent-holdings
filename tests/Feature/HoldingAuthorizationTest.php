<?php

namespace Tests\Feature;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Whilesmart\Holdings\Models\Holding;
use Whilesmart\OwnerAccess\Contracts\OwnerAuthorizer;

class HoldingAuthorizationTest extends TestCase
{
    private const OWNER = 'App\\Models\\Workspace';

    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        // A strict authorizer that only permits owner_id = 1.
        $app->bind(OwnerAuthorizer::class, fn () => new class implements OwnerAuthorizer
        {
            public function authorize(?Authenticatable $user, string $ownerType, mixed $ownerId): bool
            {
                return (int) $ownerId === 1;
            }

            public function scope(Builder $query, ?Authenticatable $user, string $ownerTypeColumn = 'owner_type', string $ownerIdColumn = 'owner_id'): Builder
            {
                return $query->where($ownerIdColumn, 1);
            }
        });
    }

    #[Test]
    public function index_only_returns_owners_the_user_may_access(): void
    {
        Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Mine', 'quantity' => 1, 'unit_price' => 1]);
        Holding::create(['owner_type' => self::OWNER, 'owner_id' => 2, 'name' => 'Theirs', 'quantity' => 1, 'unit_price' => 1]);

        $this->getJson('/api/holdings')
            ->assertOk()
            ->assertJsonCount(1, 'data.data')
            ->assertJsonPath('data.data.0.name', 'Mine');
    }

    #[Test]
    public function showing_a_forbidden_owners_holding_is_denied(): void
    {
        $other = Holding::create(['owner_type' => self::OWNER, 'owner_id' => 2, 'name' => 'Theirs', 'quantity' => 1, 'unit_price' => 1]);

        $this->getJson("/api/holdings/{$other->id}")
            ->assertForbidden()
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message']);
    }

    #[Test]
    public function deleting_a_forbidden_owners_holding_is_denied(): void
    {
        $other = Holding::create(['owner_type' => self::OWNER, 'owner_id' => 2, 'name' => 'Theirs', 'quantity' => 1, 'unit_price' => 1]);

        $this->deleteJson("/api/holdings/{$other->id}")
            ->assertForbidden()
            ->assertJsonPath('success', false);

        $this->assertDatabaseHas('holdings', ['id' => $other->id, 'deleted_at' => null]);
    }

    #[Test]
    public function creating_for_a_forbidden_owner_is_denied(): void
    {
        $this->postJson('/api/holdings', [
            'owner_type' => self::OWNER,
            'owner_id' => 2,
            'name' => 'Theirs',
            'quantity' => 1,
            'unit_price' => 1,
        ])->assertForbidden()
            ->assertJsonPath('success', false);
    }
}
