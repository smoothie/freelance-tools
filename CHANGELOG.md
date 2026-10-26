# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

## [Unreleased]

### Added

- Generate timesheets and invoices for the two previous completed calendar months with `LAST_TWO_MONTHS`.
- Include an optional customer order number in invoice PDFs and e-invoice XML.

### Changed

- Base invoice numbers on the billing month instead of the following month.
- Require PHP 8.3 or newer, matching the typed constants already used by the application.

### Security

- Upgrade to Symfony 7.4 LTS and patched Twig, Dompdf, and FPDI releases to resolve the dependency audit findings.
- Record dependency versions in `composer.lock` and restrict resolution to stable releases.

### Fixed

- Display the calendar year correctly in invoice performance periods around New Year.
