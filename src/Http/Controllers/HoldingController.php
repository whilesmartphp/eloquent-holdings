<?php

namespace Whilesmart\Holdings\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Whilesmart\Holdings\Http\Requests\StoreHoldingRequest;
use Whilesmart\Holdings\Http\Requests\UpdateHoldingRequest;
use Whilesmart\Holdings\Http\Resources\HoldingResource;
use Whilesmart\Holdings\Models\Holding;
use Whilesmart\Holdings\Services\HoldingRepricer;
use Whilesmart\Holdings\Traits\ApiResponse;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerController;

class HoldingController extends Controller
{
    use ApiResponse;
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

        return $this->paginated($holdings, HoldingResource::class);
    }

    public function store(StoreHoldingRequest $request): JsonResponse
    {
        $holding = Holding::create($request->validated());

        return $this->success(new HoldingResource($holding), 'Holding created.', 201);
    }

    public function show(Holding $holding, Request $request): JsonResponse
    {
        $this->authorizeHoldingAccess($holding, $request->user());

        return $this->success(new HoldingResource($holding));
    }

    public function update(UpdateHoldingRequest $request, Holding $holding): JsonResponse
    {
        $this->authorizeHoldingAccess($holding, $request->user());
        $holding->update($request->validated());

        return $this->success(new HoldingResource($holding->fresh()), 'Holding updated.');
    }

    public function destroy(Holding $holding, Request $request): JsonResponse
    {
        $this->authorizeHoldingAccess($holding, $request->user());
        $holding->delete();

        return $this->success(null, 'Holding deleted.');
    }

    /** Refresh prices for the caller's auto-priced holdings via the bound provider. */
    public function reprice(Request $request, HoldingRepricer $repricer): JsonResponse
    {
        $repricer->reprice();

        return $this->success(null, 'Prices refreshed.');
    }

    /**
     * Owner-access raises a bare AccessDeniedHttpException; re-throw it through
     * the formatter so denials share the envelope of every other response.
     */
    protected function authorizeHoldingAccess(Holding $holding, ?Authenticatable $user): void
    {
        try {
            $this->authorizeAccessTo($holding, $user);
        } catch (AccessDeniedHttpException) {
            throw new HttpResponseException($this->failure('This action is unauthorized.', 403));
        }
    }
}
