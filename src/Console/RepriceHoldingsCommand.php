<?php

namespace Whilesmart\Holdings\Console;

use Illuminate\Console\Command;
use Whilesmart\Holdings\Services\HoldingRepricer;

class RepriceHoldingsCommand extends Command
{
    protected $signature = 'holdings:reprice';

    protected $description = 'Refresh prices for auto-priced holdings via the bound price provider';

    public function handle(HoldingRepricer $repricer): int
    {
        $count = $repricer->reprice();

        $this->info("Repriced {$count} holding(s).");

        return self::SUCCESS;
    }
}
