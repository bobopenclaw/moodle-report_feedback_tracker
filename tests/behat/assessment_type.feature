@report @report_feedback_tracker @rft_assesstype
Feature: As an admin I want to be able to set an assessment type for a grade item

  @javascript
  Scenario: Formative/summative/dummy are saved and display a badge.
    Given the following "courses" exist:
      | fullname | shortname | format | customfield_course_year |
      | Course 1 | C1        | topics | ##now##%Y##             |
    And the following "users" exist:
      | username | firstname | lastname | email                |
      | teacher1 | teacher   | 1        | teacher1@example.com |
      | student1 | Student   | 1        | student1@example.com |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
    And I log in as "admin"
    And I add a assign activity to course "Course 1" section "2" and I fill the form with:
      | Assignment name         | Test assignment                                |
      | Formative or summative? | Formative - does not contribute to course mark |
      | Maximum grade           | 100                                            |
    And I add a quiz activity to course "Course 1" section "3" and I fill the form with:
      | Name                    | Test quiz                                      |
      | Formative or summative? | Formative - does not contribute to course mark |
      | Grade to pass           | 8                                              |

    Given I am on the "Course 1" "course" page logged in as "admin"
    When I navigate to "Reports" in current page administration
    And I click on "Feedback tracker" "link"
    Then "Report" "field" should exist in the "tertiary-navigation" "region"
    And I should see "Feedback tracker" in the "tertiary-navigation" "region"
    And I should see "Test quiz"
    And I should see "Formative"
    And I should not see "Summative"

    # Test hiding formative.
    When I click on the "Edit" button in the "Test quiz" module
    Then I should see "Formative - does not contribute to course mark"
    When I click on "Hide from student report" "checkbox"
    And I click on "Save" "button" in the "Edit Test quiz" "dialogue"
    Then I should see "Hidden from report"

    # Test unhiding formative.
    When I click on the "Edit" button in the "Test quiz" module
    Then I should see "Formative - does not contribute to course mark"
    When I click on "Hide from student report" "checkbox"
    And I click on "Save" "button" in the "Edit Test quiz" "dialogue"
    Then I should not see "Hidden from report"

    # Test set as summative and hide it.
    When I click on the "Edit" button in the "Test quiz" module
    Then I should see "Formative - does not contribute to course mark"
    When I set the field "assesstype" to "Summative - counts towards the final module mark"
    # Setting an assessment type other than Dummy will unhide the assessment from student report - so hide again.
    And I click on "Hide from student report" "checkbox"
#    Then the "hidden" checkbox should be checked
    And I click on "Save" "button" in the "Edit Test quiz" "dialogue"
    Then I should see "Summative"
    And I should see "Hidden from report"

    # Test unhiding summative.
    When I click on the "Edit" button in the "Test quiz" module
    Then I should see "Summative - counts towards the final module mark"
#    And the "hidden" checkbox should be checked
    When I click on "Hide from student report" "checkbox"
    And I click on "Save" "button" in the "Edit Test quiz" "dialogue"
    Then I should not see "Hidden from report"

    # Test Dummy. It should hide and the checkbox should be disabled.
    When I click on the "Edit" button in the "Test quiz" module
    Then I should see "Summative - counts towards the final module mark"
    When I set the field "assesstype" to "Dummy activity - neither formative or summative"
#    Then the "hidden" checkbox should be checked
    Then the "hidden" "checkbox" should be disabled
    And I click on "Save" "button" in the "Edit Test quiz" "dialogue"
    Then I should not see "Summative"
    And I should see "Dummy"
    And I should see "Hidden from report"
