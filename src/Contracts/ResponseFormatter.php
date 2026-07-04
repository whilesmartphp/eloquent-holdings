<?php

namespace Whilesmart\Holdings\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\JsonResource;

interface ResponseFormatter
{
    /**
     * Format a success response for a single payload.
     */
    public function success(mixed $data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse;

    /**
     * Format a failure response.
     */
    public function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse;

    /**
     * Format a paginated list response.
     *
     * The formatter owns list serialization so host apps can shape pagination
     * (flat keys vs. links/meta, envelope wording) without touching the package.
     *
     * @param  class-string<JsonResource>  $resourceClass
     */
    public function paginated(LengthAwarePaginator $paginator, string $resourceClass, string $message = 'Operation successful'): JsonResponse;
}
