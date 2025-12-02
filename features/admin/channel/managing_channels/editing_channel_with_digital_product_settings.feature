@sylius_digital_product_managing_channels
Feature: Editing channel with digital product settings
    In order to manage channel digital product settings
    As an Administrator
    I want to be able to edit a channel settings related to digital products

    Background:
        Given the store operates on a channel named "Web Channel"
        And I am logged in as an administrator

    @ui
    Scenario: Settings values in digital product section
        When I want to modify a channel "Web Channel"
        And I set download limit per customer to "10"
        And I set days available after purchase to "180"
        And I check hide quantity on product page
        And I save my changes
        Then I should be notified that it has been successfully edited
        And I should see value "10" in "Download limit per customer" field
        And I should see value "180" in "Days available after purchase" field
        And the "Hide quantity on product page" option should be checked

    @ui
    Scenario: Trying to set invalid data in digital product section
        When I want to modify a channel "Web Channel"
        And I set download limit per customer to "-5"
        And I set days available after purchase to 0
        And I try to save my changes
        Then I should be notified that "Download limit per customer" should be positive
        And I should be notified that "Days available after purchase" should be positive
