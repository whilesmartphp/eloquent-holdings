<?php

namespace Whilesmart\Holdings\Traits;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\JsonResponse;
use Whilesmart\Holdings\Contracts\ResponseFormatter;

trait ApiResponse
{
    protected function success(mixed $data = null, string $message = 'Operation successful', int $statusCode = 200): JsonResponse
    {
        return app(ResponseFormatter::class)->success($data, $message, $statusCode);
    }

    protected function failure(string $message = 'Operation failed', int $statusCode = 400, array $errors = []): JsonResponse
    {
        return app(ResponseFormatter::class)->failure($message, $statusCode, $errors);
    }

    protected function paginated(LengthAwarePaginator $paginator, string $resourceClass, string $message = 'Operation successful'): JsonResponse
    {
        return app(ResponseFormatter::class)->paginated($paginator, $resourceClass, $message);
    }
}
