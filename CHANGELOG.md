## [1.2.0] - 2026-07-22
- Auto-priced holdings now get their first price from the bound provider at creation instead of sitting at 0 until the next scheduled reprice run; a caller-supplied `unit_price` still wins, and a provider failure leaves 0 for that run to pick up
- The reprice endpoint now refreshes only holdings whose owner the caller may access (the `holdings:reprice` command still refreshes all): `HoldingRepricer::reprice()` takes an optional base query
- New `HoldingRepricer::price($holding)` to price a single holding and `Holding::isAutoPriced()` instance check

## [1.1.0] - 2026-07-04
- Pluggable response formatting: a `ResponseFormatter` contract (with a `DefaultResponseFormatter`) bound from `config('holdings.response_formatter')`, so host apps can shape every holdings response to match the rest of their API
- All controller actions now return a consistent `{ success, message, data }` envelope
- Validation and authorization failures now return the formatter's error envelope (`{ success, message, errors }`) instead of the framework default
- List responses now use a flat pagination shape (`data`, `current_page`, `last_page`, `per_page`, `total`) instead of Laravel's `links`/`meta` collection wrapper (response-shape change for list consumers)

## [1.0.0] - 2026-06-24
- Polymorphic holdings register (crypto, stocks, property, any asset), scoped per owner via owner-access
- Quantity x unit price valuation in the holding's own currency
- Provider-agnostic pricing: manual, or auto via a host-bound `HoldingPriceProvider` keyed by `provider` + `external_ref` (no integration hard coded in the package)
- `holdings:reprice` command and `HoldingRepricer` service that refresh auto-priced holdings and emit `HoldingsRepriced`
- API resource controller with owner-scoped authorization and a reprice action
