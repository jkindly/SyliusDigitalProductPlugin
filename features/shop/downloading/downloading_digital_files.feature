@sylius_digital_product_downloading
Feature: Downloading digital files from order
    In order to receive the digital content I purchased
    As a Customer
    I want to be able to download digital files using my order download links

    Background:
        Given the store operates on a single channel in "United States"
        And the store has a product "Digital eBook" priced at "$9.99"
        And the store ships everywhere for Free
        And the store allows paying with "Cash on Delivery"

    @ui
    Scenario: Downloading a file increments the download count
        Given the "Digital eBook" product has digital files
        And I am a logged in customer
        And I placed an order "#00000001"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        And I store the download URL for "Sample Digital File"
        When I access the download URL for "Sample Digital File"
        Then the download count for "Sample Digital File" should be 1

    @ui
    Scenario: Downloading a file multiple times increments the count accordingly
        Given the "Digital eBook" product has digital files with download limit 3
        And I am a logged in customer
        And I placed an order "#00000002"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        And I store the download URL for "Sample Digital File"
        When I access the download URL for "Sample Digital File"
        And I access the download URL for "Sample Digital File"
        Then the download count for "Sample Digital File" should be 2

    @ui
    Scenario: Download does not increment count when limit is exceeded
        Given the "Digital eBook" product has digital files with download limit 1
        And I am a logged in customer
        And I placed an order "#00000003"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        And the download limit for "Sample Digital File" in this order has been reached
        And I store the download URL for "Sample Digital File"
        When I access the download URL for "Sample Digital File"
        Then the download count for "Sample Digital File" should be 1

    @ui
    Scenario: Guest customer can download a file without being logged in
        Given the "Digital eBook" product has digital files
        And I am a logged in customer
        And I placed an order "#00000004"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        And I store the download URL for "Sample Digital File"
        When I view the summary of my order "#00000004"
        And I log out
        And I access the download URL for "Sample Digital File"
        Then the download count for "Sample Digital File" should be 1

    @ui
    Scenario: Clicking the download button from the order page increments the download count
        Given the "Digital eBook" product has digital files
        And I am a logged in customer
        And I placed an order "#00000005"
        And I bought a single "Digital eBook"
        And I addressed it to "John Doe", "Main Street", "12345" "New York" in the "United States"
        And for the billing address of "John Doe" in the "Main Street", "12345" "New York", "United States"
        And I chose "Free" shipping method with "Cash on Delivery" payment
        And this order is already paid
        And I store the download URL for "Sample Digital File"
        When I view the summary of my order "#00000005"
        And I download "Sample Digital File" from my order
        Then the download count for "Sample Digital File" should be 1
