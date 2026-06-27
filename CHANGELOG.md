## [1.0.0] - 2026-06-24
- Polymorphic holdings register (crypto, stocks, property, any asset), scoped per owner via owner-access
- Quantity x unit price valuation in the holding's own currency
- Provider-agnostic pricing: manual, or auto via a host-bound `HoldingPriceProvider` keyed by `provider` + `external_ref` (no integration hard coded in the package)
- `holdings:reprice` command and `HoldingRepricer` service that refresh auto-priced holdings and emit `HoldingsRepriced`
- API resource controller with owner-scoped authorization and a reprice action
