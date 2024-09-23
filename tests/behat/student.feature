@report @report_feedback_tracker @rft_student
Feature: As a student I want to see, sort and filter the results of the feredback tracker report

  Background:
    Given the following custom field exists for feedback tracker:
      | category  | CLC |
      | shortname | course_year |
      | name      | Course Year |
      | type      | text        |
    And the following "courses" exist:
      | fullname | shortname | format | customfield_course_year |
      | Course 1 | C1        | topics | ##now##%Y##             |
    And the following "users" exist:
      | username | firstname  | lastname  | email                 |
      | teacher1 | teacher    | 1         | teacher1@example.com  |
      | student1 | Student    | 1         | student1@example.com  |
    And the following "course enrolments" exist:
      | user      | course  | role            |
      | teacher1  | C1      | editingteacher  |
      | student1  | C1      | student         |
    And I log in as "admin"
    And I add a assign activity to course "Course 1" section "2" and I fill the form with:
      | Assignment name           | Test assignment                                 |
      | Formative or summative?   | Formative - does not contribute to course mark  |
      | Description               | Test assignment description                     |
      | Maximum grade             | 100                                             |
    And I add a quiz activity to course "Course 1" section "3" and I fill the form with:
      | Name                      | Test quiz                                       |
      | Formative or summative?   | Formative - does not contribute to course mark  |
      | Description               | Test quiz description                           |
      | Grade to pass             | 8                                               |

  @javascript
  Scenario: As a student I should be able see contemt.
    Given I am on the "Course 1" "course" page logged in as "student1"
    And I follow "Profile" in the user menu
    And I follow "Feedback tracker"
    Then I should see "Feedback tracker"
    And I should see "Due"
    And I should see "Test assignment"
    And I should see "Test quiz"
