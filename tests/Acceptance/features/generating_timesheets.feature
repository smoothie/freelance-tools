Feature: Generating timesheets

  We can generate a timesheet report.

  Scenario: The timesheet was generated
    Given we have tracked some tasks with timings
    And we have prepared a command
    When we try to generate a timesheet
    Then the timesheet should have been rendered
    And the timesheet should have been generated
