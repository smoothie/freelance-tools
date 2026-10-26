# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- Generate timesheets and invoices for the two previous completed calendar months with `LAST_TWO_MONTHS`.
- Include an optional customer order number in invoice PDFs and e-invoice XML.
- Combine selected tracked projects of an explicitly identified customer into separate invoice positions.
- Create manual package and hourly invoices from YAML/JSON or a typed PHP request, with optional CLI overrides.
- Select the new Marc document design while retaining the classic layout as the default.
- Generate advance and final invoices with received-payment references, VAT breakdowns and remaining balances.
- Export PDF offers with validity dates and reuse agreed positions for subsequent invoices.

### Changed

- Base invoice numbers on the billing month instead of the following month.
- Require PHP 8.3 or newer, matching the typed constants already used by the application.
- Calculate positions and totals with decimal arithmetic and explicit cent rounding.

### Security

- Upgrade to Symfony 7.4 LTS and patched Twig, Dompdf, and FPDI releases to resolve the dependency audit findings.
- Record dependency versions in `composer.lock` and restrict resolution to stable releases.

### Fixed

- Display the calendar year correctly in invoice performance periods around New Year.
- Use the buyer's address and the seller's configured country in invoice XML.
- Export actual service periods rather than using the payment due date as a delivery date.
- Show fully paid final invoices without a redundant payment demand.
- Prevent Classic footer columns from splitting short documents across many pages.
- Keep Classic greetings with preceding content instead of creating a separate greeting-only page.
- Match Marc invoice typography and grid tables to the supplied references; keep short settlements on one page and metadata labels intact.
