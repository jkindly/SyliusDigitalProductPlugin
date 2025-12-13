@sylius_digital_product_managing_channels
Feature: Creating channel with digital product settings
    In order to manage channel digital product settings during creation
    As an Administrator
    I want to be able to create a channel with digital product settings

    Background:
        Given the store has currency "Euro"
        And the store has locale "English (United States)"
        And I am logged in as an administrator

    @ui
    Scenario: Creating a channel with digital product settings
        When I want to create a new channel
        And I specify its code as "DIGITAL"
        And I name it "Digital Channel"
        And I choose "Euro" as the base currency
        And I make it available in "English (United States)"
        And I choose "English (United States)" as a default locale
        And I select the "Order items based" as tax calculation strategy
        And I set download limit per customer to "5"
        And I set days available after purchase to "90"
        And I check hide quantity on product page
        And I add it
        Then I should be notified that it has been successfully created
        And the channel "Digital Channel" should appear in the registry

    @ui
    Scenario: Creating a channel with digital product settings and without optional fields
        When I want to create a new channel
        And I specify its code as "DIGITAL"
        And I name it "Digital Channel"
        And I choose "Euro" as the base currency
        And I make it available in "English (United States)"
        And I choose "English (United States)" as a default locale
        And I select the "Order items based" as tax calculation strategy
        And I add it
        Then I should be notified that it has been successfully created
        And the channel "Digital Channel" should appear in the registry

    @ui
    Scenario: Trying to create a channel with invalid digital product settings
        When I want to create a new channel
        And I specify its code as "DIGITAL"
        And I name it "Digital Channel"
        And I choose "Euro" as the base currency
        And I make it available in "English (United States)"
        And I choose "English (United States)" as a default locale
        And I select the "Order items based" as tax calculation strategy
        And I set download limit per customer to "-3"
        And I set days available after purchase to 0
        And I try to add it
        Then I should be notified that "Download limit per customer" should be positive
        And I should be notified that "Days available after purchase" should be positive
