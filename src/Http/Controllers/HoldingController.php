<?php

namespace Whilesmart\Holdings\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Whilesmart\Holdings\Http\Requests\StoreHoldingRequest;
use Whilesmart\Holdings\Http\Requests\UpdateHoldingRequest;
use Whilesmart\Holdings\Http\Resources\HoldingResource;
use Whilesmart\Holdings\Models\Holding;
use Whilesmart\Holdings\Services\HoldingRepricer;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerController;

class HoldingController extends Controller
{
    use AuthorizesOwnerController;

    public function index(Request $request): JsonResponse
    {
        $query = $this->scopeAccessibleOwners(Holding::query(), $request->user());

        if ($request->filled('owner_type') && $request->filled('owner_id')) {
            $query->where('owner_type', $request->input('owner_type'))
                ->where('owner_id', $request->input('owner_id'));
        }

        if ($request->filled('price_source')) {
            $query->where('price_source', $request->input('price_source'));
        }

        if ($request->filled('q')) {
            $term = '%'.strtolower($request->input('q')).'%';
            $query->where(function ($q) use ($term) {
                $q->whereRaw('lower(name) like ?', [$term])
                    ->orWhereRaw('lower(symbol) like ?', [$term]);
            });
        }

        $holdings = $query->orderByDesc('updated_at')
            ->paginate((int) $request->input('per_page', 25));

        return response()->json([
            'success' => true,
            'data' => HoldingResource::collection($holdings)->response()->getData(true),
        ]);
    }

    public function store(StoreHoldingRequest $request): JsonResponse
    {
        $holding = Holding::create($request->validated());

        return response()->json([
            'success' => true,
            'data' => new HoldingResource($holding),
        ], 201);
    }

    public function show(Holding $holding, Request $request): JsonResponse
    {
        $this->authorizeAccessTo($holding, $request->user());

        return response()->json([
            'success' => true,
            'data' => new HoldingResource($holding),
        ]);
    }

    public function update(UpdateHoldingRequest $request, Holding $holding): JsonResponse
    {
        $this->authorizeAccessTo($holding, $request->user());
        $holding->update($request->validated());

        return response()->json([
            'success' => true,
            'data' => new HoldingResource($holding->fresh()),
        ]);
    }

    public function destroy(Holding $holding, Request $request): JsonResponse
    {
        $this->authorizeAccessTo($holding, $request->user());
        $holding->delete();

        return response()->json([
            'success' => true,
            'message' => 'Holding deleted.',
        ]);
    }

    /** Refresh prices for the caller's auto-priced holdings via the bound provider. */
    public function reprice(Request $request, HoldingRepricer $repricer): JsonResponse
    {
        $repricer->reprice();

        return response()->json([
            'success' => true,
            'message' => 'Prices refreshed.',
        ]);
    }
}
