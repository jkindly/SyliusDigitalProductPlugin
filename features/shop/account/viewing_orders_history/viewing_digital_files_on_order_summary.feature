@sylius_digital_product_customer_account
Feature: Viewing digital files on order summary page
    In order to see what digital files are included in my order
    As a Customer
    I want to view digital files list in my order summary

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "Digital eBook" priced at "$9.99"
        And the store has a product "Physical Book" priced at "$19.99"
        And the store ships everywhere for Free
        And the store allows paying with "Cash on Delivery"
        And I am a logged in customer

    @ui
    Scenario: Viewing order with digital product files after success payment
        Given the "Digital eBook" product has digital files
        And I placed an order "#00000001"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        When I view the summary of my order "#00000001"
        Then I should see digital files table

    @ui
    Scenario: Viewing order without digital product files
        Given I placed an order "#00000002"
        And I bought a single "Physical Book"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        When I view the summary of my order "#00000002"
        Then I should not see digital files table

    @ui
    Scenario: Viewing order with digital product files but not paid
        Given the "Digital eBook" product has digital files
        And I placed an order "#00000003"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        When I view the summary of my order "#00000003"
        Then I should not see digital files table

    @ui
    Scenario: Viewing digital file details in paid order
        Given the "Digital eBook" product has digital files
        And I placed an order "#00000004"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        When I view the summary of my order "#00000004"
        Then I should see digital files table
        And I should see "Sample Digital File" in the digital files table
        And I should see download button for "Sample Digital File"

    @ui
    Scenario: Download button is disabled when download limit is reached
        Given the "Digital eBook" product has digital files with download limit 1
        And I placed an order "#00000005"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        And the download limit for "Sample Digital File" in this order has been reached
        When I view the summary of my order "#00000005"
        Then I should see digital files table
        And the download button for "Sample Digital File" should be disabled

    @ui
    Scenario: Download button is available when download limit is not reached
        Given the "Digital eBook" product has digital files with download limit 3
        And I placed an order "#00000006"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        When I view the summary of my order "#00000006"
        Then I should see digital files table
        And the download button for "Sample Digital File" should be enabled
