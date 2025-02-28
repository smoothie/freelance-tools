# smoothie's Freelance Tools

A highly opinionated PHP package that automates the generation of timesheets, invoices, and freelancer profiles. For me.

Currently, this package provides the following features:

1. Generate timesheets
2. Generate an e-invoice out of that timesheet

Timesheets are generated based on tracked work hours from an external time-tracking provider.

At the moment I'm using Toggl for that purpose.

So what this package does is get some timings from a work time provider, transform that data and
print it out into either a timesheet or an e-invoice.

## Installation

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

Done.

## Contributing

Contributions are welcome! A contributing guide will be added soon.

## License

This project is licensed under the [Apache License 2.0](LICENSE).
