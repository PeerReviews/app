@core @core_grades @javascript
Feature: View gradebook when single item scales are used
  In order to use single item scales to grade activities
  As an teacher
  I need to be able to view gradebook with single item scales

  Background:
    Given I log in as "admin"
    And the "multilang" filter is "on"
    And the "multilang" filter applies to "content and headings"
    And I set the following administration settings values:
      | grade_report_showranges    | 1 |
      | grade_aggregations_visible | Mean of grades,Weighted mean of grades,Simple weighted mean of grades,Mean of grades (with extra credits),Median of grades,Lowest grade,Highest grade,Mode of grades,Natural |
    And I navigate to "Grades > Scales" in site administration
    And I press "Add a new scale"
    And I set the following fields to these values:
      | Name  | <span lang="en" class="multilang">EN</span><span lang="fr" class="multilang">FR</span> Singleitem |
      | Scale | Ace!                                                                                              |
    And I press "Save changes"
    And I log out
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "users" exist:
      | username | firstname | lastname | email            | idnumber |
      | teacher1 | Teacher   | 1        | teacher1@example.com | t1       |
      | student1 | Student   | 1        | student1@example.com | s1       |
      | student2 | Student   | 2        | student2@example.com | s2       |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
    And the following "grade categories" exist:
      | fullname       | course |
      | <span lang='en' class='multilang'>EN</span><span lang='fr' class='multilang'>FR</span> Sub category 1 | C1     |
    And the following "activities" exist:
      | activity | course | idnumber | name                | intro             | gradecategory  |
      | assign   | C1     | a1       | Test assignment one | Submit something! | <span lang='en' class='multilang'>EN</span><span lang='fr' class='multilang'>FR</span> Sub category 1 |
    And the "multilang" filter is "on"
    And the "multilang" filter applies to "content and headings"
    And I am on the "Test assignment one" "assign activity editing" page logged in as teacher1
    And I expand all fieldsets
    And I set the field "grade[modgrade_type]" to "Scale"
    And I set the field "grade[modgrade_scale]" to "EN Singleitem"
    And I press "Save and display"
    And I follow "View all submissions"
    And I click on "Grade" "link" in the "Student 1" "table_row"
    And I set the field "Grade" to "A"
    And I press "Save changes"
    When I am on the "Course 1" "grades > course grade settings" page
    And I set the field "Show weightings" to "Show"
    And I set the field "Show contribution to course total" to "Show"
    And I press "Save changes"
    And I navigate to "View > Grader report" in the course gradebook
    And I turn editing mode on

  Scenario: Test displaying single item scales in gradebook in aggregation method Natural
    When I turn editing mode off
    Then the following should exist in the "user-grades" table:
      | -1-                | -2-                  | -3-       | -4-            | -5-          |
      | Student 1          | student1@example.com | Ace!      | 1.00           | 1.00         |
    And the following should exist in the "user-grades" table:
      | -1-                | -2-       | -3-            | -4-          |
      | Range              | Ace!–Ace! | 0.00–1.00      | 0.00–1.00    |
      | Overall average    | Ace!      | 1.00           | 1.00         |
    And I navigate to "View > User report" in the course gradebook
    And I click on "Student 1" in the "user" search widget
    And the following should exist in the "user-grade" table:
      | Grade item                | Grade | Range     | Contribution to course total |
      | Test assignment one       | Ace!  | Ace!–Ace! | 100.00 %                     |
      | ENFR Sub category 1 total | 1.00  | 0–1       | -                            |
      | Course total              | 1.00  | 0–1       | -                            |
    And I click on "Student 2" in the "user" search widget
    And the following should exist in the "user-grade" table:
      | Grade item                | Grade | Range     | Contribution to course total |
      | Test assignment one       | -     | Ace!–Ace! | -                            |
      | ENFR Sub category 1 total | -     | 0–1       | -                            |
      | Course total              | -     | 0–1       | -                            |
    And I navigate to "Setup > Gradebook setup" in the course gradebook
    And the following should exist in the "grade_edit_tree_table" table:
      | Name                      | Max grade |
      | Test assignment one       | 1.00      |
      | ENFR Sub category 1 total | 1.00      |
      | Course total              | 1.00      |

  Scenario Outline: Test displaying single item scales in gradebook in all other aggregation methods
    Given I set the following settings for grade item "Course 1" of type "course" on "grader" page:
      | Aggregation | <aggregation> |
    And I set the following settings for grade item "<span lang='en' class='multilang'>EN</span><span lang='fr' class='multilang'>FR</span> Sub category 1" of type "category" on "grader" page:
      | Aggregation     | <aggregation> |
      | Category name   | Sub category (<aggregation>) |
    And I turn editing mode off
    Then the following should exist in the "user-grades" table:
      | -1-                | -2-                  | -3-       | -4-            | -5-            |
      | Student 1          | student1@example.com | Ace!      | <cattotal1>    | <coursetotal1> |
      | Student 2          | student2@example.com | -         | -              | -              |
    And the following should exist in the "user-grades" table:
      | -1-                | -2-       | -3-            | -4-            |
      | Range              | Ace!–Ace! | 0.00–100.0     | 0.00–100.00    |
      | Overall average    | Ace!      | <catavg>       | <overallavg>   |
    And I navigate to "View > User report" in the course gradebook
    And I click on "Student 1" in the "user" search widget
    And the following should exist in the "user-grade" table:
      | Grade item                                       | Grade          | Range       | Contribution to course total |
      | Test assignment one                              | Ace!           | Ace!–Ace!   | <contrib1>                   |
      | Sub category (<aggregation>) total<aggregation>. | <cattotal1>    | 0–100       | -                            |
      | Course total<aggregation>.                       | <coursetotal1> | 0–100       | -                            |
    And I navigate to "Setup > Gradebook setup" in the course gradebook
    And the following should exist in the "grade_edit_tree_table" table:
      | Name                                             | Max grade |
      | Test assignment one                              | Ace! (1)  |
      | Sub category (<aggregation>) total               | 100.00    |
      | Course total                                     | 100.00    |

    Examples:
      | aggregation                         | contrib1 | cattotal1 | coursetotal1 | catavg | overallavg |
      | Mean of grades                      | 100.00 % | 100.00    | 100.00       | 100.00 | 100.00     |
      | Weighted mean of grades             | 0.00 %   | 100.00    | 100.00       | 100.00 | 100.00     |
      | Simple weighted mean of grades      | 0.00 %   | -         | -            | -      | -          |
      | Mean of grades (with extra credits) | 100.00 % | 100.00    | 100.00       | 100.00 | 100.00     |
      | Median of grades                    | 100.00 % | 100.00    | 100.00       | 100.00 | 100.00     |
      | Lowest grade                        | 100.00 % | 100.00    | 100.00       | 100.00 | 100.00     |
      | Highest grade                       | 100.00 % | 100.00    | 100.00       | 100.00 | 100.00     |
      | Mode of grades                      | 100.00 % | 100.00    | 100.00       | 100.00 | 100.00     |
