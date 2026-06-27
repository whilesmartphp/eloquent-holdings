<?php

namespace Whilesmart\Holdings\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Whilesmart\Holdings\Models\Holding;

trait HasHoldings
{
    public function holdings(): MorphMany
    {
        return $this->morphMany(Holding::class, 'owner');
    }
}
