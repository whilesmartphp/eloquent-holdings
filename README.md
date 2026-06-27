# whilesmart/eloquent-holdings

A polymorphic holdings register for Laravel: one model for anything you own and want to value, whether crypto, stocks, property, or collectibles. Each holding is scoped to an owner (a workspace, organization, user) through [`whilesmart/eloquent-owner-access`](https://github.com/whilesmartphp/eloquent-owner-access), carries a quantity and a unit price, and can be priced by hand or refreshed automatically from a price provider you bind.

The package never hard codes a price source. It stores an opaque `provider` and `external_ref`, and asks a host-bound `HoldingPriceProvider` to resolve prices, so a CoinGecko, stock, or any other feed lives in your app, not here.

## Install

```
composer require whilesmart/eloquent-holdings
php artisan migrate
```

Routes register automatically under the `api` prefix with `auth:sanctum`. Set `HOLDINGS_REGISTER_ROUTES=false` to mount them yourself.

## Owning model

Add the trait to whatever owns holdings:

```php
use Whilesmart\Holdings\Traits\HasHoldings;

class Workspace extends Model
{
    use HasHoldings;
}

// Manual: you maintain the price
$workspace->holdings()->create([
    'name' => 'Rental flat',
    'quantity' => 1,
    'currency' => 'USD',
    'unit_price' => 120000,
]);

// Auto: refreshed from a bound provider
$workspace->holdings()->create([
    'name' => 'Bitcoin',
    'symbol' => 'BTC',
    'quantity' => 0.5,
    'currency' => 'USD',
    'price_source' => 'auto',
    'provider' => 'coingecko',
    'external_ref' => 'bitcoin',
]);
```

`$holding->value` returns `quantity * unit_price` in the holding's currency.

## Live pricing

Implement `HoldingPriceProvider` in your app and bind it. The package groups auto-priced holdings by `(provider, currency)` and asks for current prices:

```php
use Whilesmart\Holdings\Contracts\HoldingPriceProvider;

class CoingeckoPriceProvider implements HoldingPriceProvider
{
    public function prices(string $provider, array $externalRefs, string $currency): array
    {
        if ($provider !== 'coingecko') {
            return [];
        }
        // ...call CoinGecko, return [externalRef => price] in $currency
    }
}

// AppServiceProvider::register()
$this->app->bind(HoldingPriceProvider::class, CoingeckoPriceProvider::class);
```

Refresh prices via the command (schedule it as you like) or the API:

```
php artisan holdings:reprice
POST /api/holdings/reprice
```

A `HoldingsRepriced` event carries the ids of the holdings that changed, so the host can invalidate caches or recompute net worth.

## Authorization

Records are owner-scoped. Bind an `OwnerAuthorizer` (see `whilesmart/eloquent-owner-access`) to decide who can see and modify each owner's holdings. Without one, the permissive default applies.

## Endpoints

| Method | Path | Action |
|---|---|---|
| GET | `/api/holdings` | List (owner-scoped; filter by `owner_type`/`owner_id`, `price_source`, `q`) |
| POST | `/api/holdings` | Create |
| GET | `/api/holdings/{holding}` | Show |
| PUT/PATCH | `/api/holdings/{holding}` | Update |
| DELETE | `/api/holdings/{holding}` | Delete (soft) |
| POST | `/api/holdings/reprice` | Refresh auto-priced holdings |

## Development

```
make bootstrap   # one-time: pull the package make targets out of the image
make test        # run the test suite in a container
make lint        # pint --test
```
