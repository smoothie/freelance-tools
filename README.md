# smoothie's Freelance Tools

A highly opinionated package that automates my generation of timesheets, invoices, and freelancer profiles.

Currently, this package provides the following features:

1. Generate timesheets
2. Generate an e-invoice out of that timesheet
3. Combine selected projects into a customer invoice or enter manual package/hour positions
4. Generate advance and final invoices with received-payment deductions
5. Export PDF offers and choose the classic or new Marc document layout

Timesheets are generated based on tracked work hours from an external time-tracking provider.

At the moment I'm using Toggl for that purpose.

So what this package does is get some timings from a work time provider, transform that data and
print it out into either a timesheet or an e-invoice.

## Installation

PHP 8.3 or newer is required. The application uses Symfony 7.4 LTS.
For a checkout, run `composer install` to install the audited versions recorded in `composer.lock`.

Run

```shell
composer require smoothie/freelance-tools

```

## Usage

Before we get started need to configure some things.

### 1. Configuring the .env

The following environment variables must be set to configure your integration with Toggl and personalize invoice details:

```bash
## Some information about Toggl -> the API key and related API URLs. 
TOOLS_TOGGL_API_KEY=change_me
TOOLS_TOGGL_API_URL=https://localhost/nope/
TOOLS_TOGGL_REPORT_URL=https://localhost/nope/
## The related Toggl Workspace ID where we can find the timings to track.
TOOLS_TOGGL_WORKSPACE_ID=1337

## The freelancers details, which gonna be used on the documents e.g. on the footer or header.
TOOLS_PROVIDED_BY_NAME=change_me
TOOLS_PROVIDED_BY_STREET=change_me
TOOLS_PROVIDED_BY_LOCATION=change_me
TOOLS_PROVIDED_BY_VATID=change_me
TOOLS_PROVIDED_BY_PHONE=change_me
TOOLS_PROVIDED_BY_MAIL=change_me
TOOLS_PROVIDED_BY_WEB=change_me
TOOLS_PROVIDED_BY_BANK=change_me
TOOLS_PROVIDED_BY_IBAN=change_me
TOOLS_PROVIDED_BY_BIC=change_me
TOOLS_PROVIDED_BY_COUNTRY=change_me
```

### 2. Setting up a map for related project contact person

We also need at some point more customer related information.

For example the address where an invoice should be sent to. To make my live easier and not highly
coupled to Toggl I decided to make that stuff configurable and map stuff out on the application side

The map basically works like:
We get a project from Toggl, look in our configuration and use the first we can find for that
project.

The config can be placed under `./var/config/tools.yaml`.

The mapping below helps associate Toggl projects with corresponding customer details, ensuring invoices are generated correctly:

```yaml
tools:
  organizations:
    - {
      project: cheesecake-agile,
      name: 'Cheesecake Factory',
      street: 'CakeLake 1',
      location: '66113 Saarbrücken',
      country: 'DE',
      vatId: 'DE000000001',
      description: 'Generating and destructing cheesecakes',
      taxRate: 19.00,
      pricePerHour: 133.7,
      termOfPaymentInDays: 30,
      orderNumber: 'PO-2025-001',
    }

```

Once that is done we can start using the application.

Run the following command to generate a timesheet and an invoice. The generated files will be saved in `./var/pdf/`:

```

bin/console tools:generate-timesheet
  cheesecake-agile
  --approvedBy="SomePerson Name"
  --approvedByCompany="Cheesecake Factory"

```

`orderNumber` is optional. When configured, it appears in the invoice PDF and as the buyer's order reference in the e-invoice XML.

The `--performancePeriod` option accepts `CURRENT_MONTH`, `LAST_MONTH` (default), and `LAST_TWO_MONTHS`.
`LAST_TWO_MONTHS` covers the two completed calendar months before `--startDate`, including year boundaries:

```shell
bin/console tools:generate-timesheet cheesecake-agile --performancePeriod=LAST_TWO_MONTHS --startDate=2025-01-15 --approvedBy="SomePerson Name" --approvedByCompany="Cheesecake Factory"
```

This example covers November and December 2024. Both months appear on the invoice.
Invoice numbers use the billing month (`YYYYMM`), not the following month.

## Position-based invoices, advances and offers

```shell
bin/console tools:generate-document --input=invoice.yaml --template=marc
```

The optional CLI accepts YAML, JSON or standard input. The same typed application API works without the CLI.
Configure a shared `customerId` on related projects to combine them; the existing timesheet command remains unchanged.
Manual positions use decimal strings, for example `quantity: '1'`, `unit: C62`, `pricePerItem: '1499.00'`.
Offers export PDF only. Invoice XML retains the EN16931 CII profile; XSD validation alone is **not** XRechnung certification.

See [the billing interface and workflow guide](docs/billing.md) for configuration, PHP API, CLI overrides, rounding rules, limitations and runnable input examples.

## Quality checks

Run `make check-ci` (or `make check-quality`) for dependency validation and audit, linters, code style, static analysis, architecture checks, and all test suites.
PHPStan runs from Composer's locked dependencies so its Symfony extension and analyzer remain compatible.
These checks run on the current PHP installation; this repository has no application image-build target.

## Contributing

While I do not really know why anyone would, but contributions are welcome! As well as feedback.

## License

This project is licensed under the [Apache License 2.0](LICENSE).
