<?php

namespace Whilesmart\Holdings\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Whilesmart\Holdings\Database\Factories\HoldingFactory;
use Whilesmart\Holdings\Enums\PriceSource;

class Holding extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:8',
        'unit_price' => 'decimal:4',
        'price_source' => PriceSource::class,
        'last_priced_at' => 'datetime',
        'metadata' => 'array',
    ];

    public function getTable(): string
    {
        return config('holdings.holdings_table', 'holdings');
    }

    public function owner(): MorphTo
    {
        return $this->morphTo();
    }

    /** Current value in the holding's own currency: quantity x unit_price. */
    public function getValueAttribute(): float
    {
        return (float) $this->quantity * (float) $this->unit_price;
    }

    /** Holdings whose price is refreshed from a provider (have a provider + ref). */
    public function scopeAutoPriced(Builder $query): Builder
    {
        return $query->where('price_source', PriceSource::Auto->value)
            ->whereNotNull('provider')
            ->whereNotNull('external_ref');
    }

    protected static function newFactory(): HoldingFactory
    {
        return HoldingFactory::new();
    }
}
