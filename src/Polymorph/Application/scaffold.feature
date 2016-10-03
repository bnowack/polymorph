Feature: Application scaffold
  In order to create applications quickly
  As a developer
  I get a working Polymer scaffold out of the box

  Scenario: Accessing the default homepage
    When I go to "/"
    Then the response code should be "200"
    And the response header "Content-Type" should match "text/html"
    And I should see "Polymorph Framework" in the "title" element

  Scenario: Getting a response for non-existing pages
    When I go to "/does-not-exist"
    Then the response code should be "404"
    And the response header "Content-Type" should match "text/html"
    And I should see "404" in the "title" element
    And I should see "No route found" in the "body" element

  Scenario: Retrieving a manifest file for homescreen apps
    When I go to "/manifest.json"
    Then the response code should be "200"
    Then the response header "Content-Type" should match "application/json"

  @active
  Scenario: Navigating views
    When I go to "/does-not-exist"
    And I click on the header logo
    Then I should see the home page
