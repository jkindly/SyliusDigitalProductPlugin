@sylius_digital_product_managing_products
Feature: Adding files with predefined settings
    In order to adding files faster to simple digital products
    As an Administrator
    I want to add a new files with predefined settings to the digital product

    Background:
        Given the store operates on a channel named "United States"
        And the store operates on another channel named "Europe"
        And "United States" channel has predefined digital file settings:
            | Download limit per customer | Days available after purchase | Hide quantity on product page |
            | 5                           | 30                            | 0                          |
        And "Europe" channel has predefined digital file settings:
            | Download limit per customer | Days available after purchase | Hide quantity on product page |
            | 10                          | 60                            | 1                         |
        And the store has a product "Digital simple product"
        And this product is available in "United States" channel and "Europe" channel
        And this product is also priced at "$80.00" in "United States" channel
        And I am logged in as an administrator

    @ui
    Scenario: Adding uploaded file to product with predefined settings to United States channel
        When I want to modify the "Digital simple product" product
        And I open the Digital section
        And I select accordion for "United States" channel
        And I upload a digital file "funky_cat.png" with name "Funky cat"
        Then I should see "5" in "Download limit per customer" field
        And I should see "30" in "Days available after purchase" field

    @ui
    Scenario: Adding external URL to product with predefined settings to United States channel
        When I want to modify the "Digital simple product" product
        And I open the Digital section
        And I select accordion for "United States" channel
        And I add an external URL file "https://example.com/download.pdf" with name "External URL"
        Then I should see "5" in "Download limit per customer" field
        And I should see "30" in "Days available after purchase" field

    @ui
    Scenario: Adding uploaded file to product with predefined settings to Europe channel
        When I want to modify the "Digital simple product" product
        And I open the Digital section
        And I select accordion for "Europe" channel
        And I upload a digital file "funky_cat.png" with name "Funky cat"
        Then I should see "10" in "Download limit per customer" field
        And I should see "60" in "Days available after purchase" field

    @ui
    Scenario: Adding external URL to product with predefined settings to Europe channel
        When I want to modify the "Digital simple product" product
        And I open the Digital section
        And I select accordion for "Europe" channel
        And I add an external URL file "https://example.com/download.pdf" with name "External URL"
        Then I should see "10" in "Download limit per customer" field
        And I should see "60" in "Days available after purchase" field

    @ui
    Scenario: Adding files to product with predefined settings to both channels
        When I want to modify the "Digital simple product" product
        And I open the Digital section
        And I select accordion for "United States" channel
        And I upload a digital file "funky_cat.png" with name "Funky cat US"
        And I select accordion for "Europe" channel
        And I add an external URL file "https://example.com/download.pdf" with name "External URL"
        Then I should see "5" in "Download limit per customer" field in "United States" channel accordion
        And I should see "30" in "Days available after purchase" field in "United States" channel accordion
        And I should see "10" in "Download limit per customer" field in "Europe" channel accordion
        And I should see "60" in "Days available after purchase" field in "Europe" channel accordion

    @ui
    Scenario: Adding uploaded file to product and check if quantity exists on product page
        When I check "Digital simple product" product details in the "United States" channel and "en_US" locale
        Then I should see quantity on product page

    @ui
    Scenario: Adding uploaded file to product and overriding predefined settings
        When I want to modify the "Digital simple product" product
        And I open the Digital section
        And I enable settings for this product
        And I check "Hide quantity on product page" field
        And I save my changes
        Then I check "Digital simple product" product details in the "United States" channel and "en_US" locale
        And I should not see quantity on product page
