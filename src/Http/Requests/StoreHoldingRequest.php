<?php

namespace Whilesmart\Holdings\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Whilesmart\Holdings\Enums\PriceSource;
use Whilesmart\OwnerAccess\Concerns\AuthorizesOwnerRequest;

class StoreHoldingRequest extends FormRequest
{
    use AuthorizesOwnerRequest;

    public function authorize(): bool
    {
        return $this->authorizeOwnerInRequest();
    }

    public function rules(): array
    {
        return [
            'owner_type' => ['required', 'string'],
            'owner_id' => ['required'],
            'name' => ['required', 'string', 'max:200'],
            'symbol' => ['nullable', 'string', 'max:60'],
            'quantity' => ['required', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'unit_price' => ['nullable', 'numeric', 'min:0'],
            'price_source' => ['nullable', Rule::in(PriceSource::values())],
            'provider' => ['nullable', 'string', 'max:60', 'required_if:price_source,auto'],
            'external_ref' => ['nullable', 'string', 'max:120', 'required_if:price_source,auto'],
            'metadata' => ['nullable', 'array'],
        ];
    }
}
