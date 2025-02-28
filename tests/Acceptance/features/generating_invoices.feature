Feature: Generating invoices

  We can generate an invoice.

  Scenario: The invoice was generated
    Given we have prepared a command to generate an invoice
    When a timesheet report was generated
    Then the invoice should have been rendered
    And the invoice should have been generated
