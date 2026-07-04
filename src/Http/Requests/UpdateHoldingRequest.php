<?php

namespace Whilesmart\Holdings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Whilesmart\Holdings\Enums\PriceSource;
use Whilesmart\Holdings\Http\Requests\Concerns\FormatsFailedRequests;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerRequest;

class UpdateHoldingRequest extends FormRequest
{
    use AuthorizesOwnerRequest;
    use FormatsFailedRequests;

    public function authorize(): bool
    {
        return $this->authorizeOwnerOfBoundModel('holding');
    }

    public function rules(): array
    {
        return [
            'name' => ['sometimes', 'string', 'max:200'],
            'symbol' => ['nullable', 'string', 'max:60'],
            'quantity' => ['sometimes', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'price_source' => ['nullable', Rule::in(PriceSource::values())],
            'provider' => ['nullable', 'string', 'max:60', 'required_if:price_source,auto'],
            'external_ref' => ['nullable', 'string', 'max:120', 'required_if:price_source,auto'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
