Feature:

  A contractor can generate a timesheet report based on task filters.

  Background:
    Given a contractor is working on projects
      | Project ID                           | Title            | Tags              |
      | 6e87f68c-0000-0000-0000-000000000000 | cheesecake-agile | BAKE, PREP, CHILL |
      | 6e87f68c-0000-0000-0000-000000000001 | nope-never       |                   |
    And has recorded following tasks
      | Task ID                              | Project          | Description                   | Imported At              | Last modified at         |
      | 0abc4e01-0000-0000-0000-000000000000 | cheesecake-agile | 'Get the groceries'           | 2024-09-18T06:36:54.000Z | 2024-09-18T06:36:54.000Z |
      | 0abc4e01-0000-0000-0000-000000000001 | cheesecake-agile | 'Prepare working environment' | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 0abc4e01-0000-0000-0000-000000000002 | cheesecake-agile | 'Prepare the crust'           | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 0abc4e01-0000-0000-0000-000000000003 | cheesecake-agile | 'Make the cheesecake filling' | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 0abc4e01-0000-0000-0000-000000000004 | cheesecake-agile | 'Assemble the cheesecake'     | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 0abc4e01-0000-0000-0000-000000000005 | cheesecake-agile | 'Let it bake'                 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 0abc4e01-0000-0000-0000-000000000006 | cheesecake-agile | 'Cool and chill'              | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 0abc4e01-0000-0000-0000-000000000007 | nope-never       | 'Do nothing'                  | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
    And has tagged the tasks by
      | Task ID                              | Tags        |
      | 0abc4e01-0000-0000-0000-000000000000 | PREP        |
      | 0abc4e01-0000-0000-0000-000000000001 | PREP        |
      | 0abc4e01-0000-0000-0000-000000000002 | BAKE, PREP  |
      | 0abc4e01-0000-0000-0000-000000000003 | BAKE        |
      | 0abc4e01-0000-0000-0000-000000000004 | BAKE        |
      | 0abc4e01-0000-0000-0000-000000000005 | BAKE, CHILL |
      | 0abc4e01-0000-0000-0000-000000000006 | CHILL       |
    And tracked following timings
      | Timing ID                            | Task ID                              | Duration | Start Time               | End Time                 |
      | 7638a4d4-0000-0000-0000-000000000000 | 0abc4e01-0000-0000-0000-000000000000 | 00:07:02 | 2024-09-18T06:36:54.000Z | 2024-09-18T06:36:54.000Z |
      | 7638a4d4-0000-0000-0000-000000000001 | 0abc4e01-0000-0000-0000-000000000000 | 01:02:58 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000002 | 0abc4e01-0000-0000-0000-000000000001 | 00:01:00 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000003 | 0abc4e01-0000-0000-0000-000000000001 | 00:03:58 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000004 | 0abc4e01-0000-0000-0000-000000000002 | 00:08:00 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000005 | 0abc4e01-0000-0000-0000-000000000003 | 00:24:42 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000006 | 0abc4e01-0000-0000-0000-000000000003 | 00:00:07 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000007 | 0abc4e01-0000-0000-0000-000000000004 | 03:01:23 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000008 | 0abc4e01-0000-0000-0000-000000000005 | 01:00:10 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000009 | 0abc4e01-0000-0000-0000-000000000006 | 08:17:42 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |
      | 7638a4d4-0000-0000-0000-000000000010 | 0abc4e01-0000-0000-0000-000000000007 | 01:02:03 | 2024-09-20T03:06:11.000Z | 2024-09-20T03:06:11.000Z |

  Scenario: The timesheet report was generated
    When the contractor has filtered for "(startTime >= 2024-09-01) AND (endTime >= 2024-09-30) AND (project = cheesecake-agile)"
    Then the report should be generated with the tasks
      | Task                          | Project          |
      | 'Get the groceries'           | cheesecake-agile |
      | 'Prepare working environment' | cheesecake-agile |
      | 'Prepare the crust'           | cheesecake-agile |
      | 'Make the cheesecake filling' | cheesecake-agile |
      | 'Assemble the cheesecake'     | cheesecake-agile |
      | 'Let it bake'                 | cheesecake-agile |
    And the report should have a total duration of "14:07"
    And on day "2024-09-20" the task "Get the groceries" in project "cheesecake-agile" should have a duration of "4:58"
