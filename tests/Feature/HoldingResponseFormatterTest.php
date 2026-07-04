<?php

namespace Tests\Feature;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;
use Whilesmart\Holdings\Contracts\ResponseFormatter;
use Whilesmart\Holdings\Models\Holding;

class HoldingResponseFormatterTest extends TestCase
{
    private const OWNER = 'App\\Models\\Workspace';

    #[Test]
    public function the_default_envelope_carries_success_and_message_on_every_action(): void
    {
        $holding = Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Flat', 'quantity' => 1, 'unit_price' => 100]);

        $this->getJson('/api/holdings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Operation successful');

        $this->putJson("/api/holdings/{$holding->id}", ['quantity' => 2])
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Holding updated.');

        $this->deleteJson("/api/holdings/{$holding->id}")
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('message', 'Holding deleted.')
            ->assertJsonPath('data', null);
    }

    #[Test]
    public function the_default_list_response_uses_flat_pagination_keys(): void
    {
        Holding::create(['owner_type' => self::OWNER, 'owner_id' => 1, 'name' => 'Flat', 'quantity' => 1, 'unit_price' => 100]);

        $this->getJson('/api/holdings')
            ->assertOk()
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'data' => [['id', 'name']],
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ])
            ->assertJsonMissingPath('data.links')
            ->assertJsonMissingPath('data.meta');
    }

    #[Test]
    public function validation_failures_carry_the_standard_error_envelope(): void
    {
        $this->postJson('/api/holdings', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'name' => 'Bitcoin',
            'quantity' => 0.5,
            'price_source' => 'auto',
        ])->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonStructure(['success', 'message', 'errors' => ['provider', 'external_ref']]);
    }

    #[Test]
    public function the_controller_routes_responses_through_the_bound_formatter(): void
    {
        $this->app->bind(ResponseFormatter::class, fn () => new class implements ResponseFormatter
        {
            public function success(mixed $data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
            {
                return response()->json(['formatter' => 'custom', 'kind' => 'success'], $statusCode);
            }

            public function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse
            {
                return response()->json(['formatter' => 'custom', 'kind' => 'failure'], $statusCode);
            }

            public function paginated(LengthAwarePaginator $paginator, string $resourceClass, string $message = 'Operation successful'): JsonResponse
            {
                return response()->json(['formatter' => 'custom', 'kind' => 'paginated']);
            }
        });

        $this->getJson('/api/holdings')
            ->assertOk()
            ->assertExactJson(['formatter' => 'custom', 'kind' => 'paginated']);

        $this->postJson('/api/holdings', [
            'owner_type' => self::OWNER,
            'owner_id' => 1,
            'name' => 'Flat',
            'quantity' => 1,
            'currency' => 'USD',
            'unit_price' => 100,
            'price_source' => 'manual',
        ])->assertCreated()
            ->assertExactJson(['formatter' => 'custom', 'kind' => 'success']);
    }
}
