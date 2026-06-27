<?php

namespace Whilesmart\Holdings\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class HoldingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'owner_type' => $this->owner_type,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'symbol' => $this->symbol,
            'quantity' => (float) $this->quantity,
            'currency' => $this->currency,
            'unit_price' => (float) $this->unit_price,
            'value' => $this->value,
            'price_source' => $this->price_source?->value,
            'provider' => $this->provider,
            'external_ref' => $this->external_ref,
            'last_priced_at' => $this->last_priced_at?->toIso8601String(),
            'metadata' => $this->metadata,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
            'deleted_at' => $this->deleted_at?->toIso8601String(),
        ];
    }
}
