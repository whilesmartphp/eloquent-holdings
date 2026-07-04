<?php

namespace Whilesmart\Holdings\Http\Requests\Concerns;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Whilesmart\Holdings\Contracts\ResponseFormatter;

trait FormatsFailedRequests
{
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(
            app(ResponseFormatter::class)->failure('Validation failed.', 422, $validator->errors()->toArray())
        );
    }

    protected function failedAuthorization(): void
    {
        throw new HttpResponseException(
            app(ResponseFormatter::class)->failure('This action is unauthorized.', 403)
        );
    }
}
