# Pickleball domain mapping

The application presents a multivendor pickleball court marketplace while retaining its proven booking and payment schema.

| Pickleball concept | Compatible storage/model |
| --- | --- |
| Site | `hotels` / `Hotel` |
| Court | `rooms` / `Room` |
| Site vendor | `vendors` / `Vendor` |
| Court booking | `bookings` / `Booking` |

New code may use the `Site` and `Court` model aliases and the `site`, `sites`, `court`, and `courts` relationships. Legacy names remain available for payment integrations, installed data, and upgrade compatibility.

Public discovery URLs use `/sites`, `/site/{slug}/{id}`, `/courts`, and `/court/{slug}/{id}`. The former hotel and room detail/list URLs redirect to their new public equivalents.

Translation keys intentionally retain their legacy identifiers. Their English display values contain the pickleball terminology, which avoids breaking existing Blade templates and database-backed language keywords.
