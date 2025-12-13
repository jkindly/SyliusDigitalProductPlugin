@sylius_digital_product_managing_products
Feature: Adding a new simple product with digital section
    In order to selling simple digital products
    As an Administrator
    I want to add a new simple product with digital section to the shop

    Background:
        Given the store operates on a channel named "United States"
        And the store operates on another channel named "Europe"
        And I am logged in as an administrator

    @ui
    Scenario: Adding a new simple product with uploaded file
        When I want to create a new simple product
        And I specify its code as "DIGITAL_PRODUCT"
        And I name it "Digital product" in "English (United States)" locale
        And I set its slug to "digital-product"
        And I set its price to "$10.00" for "United States" channel
        And I set its price to "$12.00" for "Europe" channel
        And I make it available in channel "United States"
        And I open the Digital section
        And I select accordion for "United States" channel
        And I upload a digital file "funky_cat.png" with name "Funky cat"
        And I add it
        Then I should be notified that it has been successfully created
        And the digital file "Funky cat" should be listed in the "United States" channel accordion
        And the uploaded file should have a download link
        And the product "Digital product" should appear in the store

    @ui
    Scenario: Adding a new simple product with external URL file
        When I want to create a new simple product
        And I specify its code as "DIGITAL_PRODUCT_URL"
        And I name it "Digital product with URL" in "English (United States)" locale
        And I set its slug to "digital-product-url"
        And I set its price to "$15.00" for "United States" channel
        And I set its price to "$18.00" for "Europe" channel
        And I open the Digital section
        And I select accordion for "United States" channel
        And I add an external URL file "https://example.com/download.pdf" with name "External URL"
        And I add it
        Then I should be notified that it has been successfully created
        And I open the Digital section
        And I select accordion for "United States" channel
        And the digital file "External URL" should be listed in the "United States" channel accordion
        And the product "Digital product with URL" should appear in the store

    @ui
    Scenario: Adding a new simple product with multiple files across two channels
        When I want to create a new simple product
        And I specify its code as "DIGITAL_PRODUCT_MULTI"
        And I name it "Digital product with multiple files" in "English (United States)" locale
        And I set its slug to "digital-product-multi"
        And I set its price to "$25.00" for "United States" channel
        And I set its price to "$30.00" for "Europe" channel
        And I open the Digital section
        And I select accordion for "United States" channel
        And I upload a digital file "funky_cat.png" with name "User Manual US"
        And I upload a digital file "ford.jpg" with name "Quick Start Guide US"
        And I add an external URL file "https://example.com/video-tutorial-us.mp4" with name "Video Tutorial US"
        And I add an external URL file "https://example.com/bonus-content-us.pdf" with name "Bonus Content US"
        And I select accordion for "Europe" channel
        And I upload a digital file "mugs.jpg" with name "User Manual EU"
        And I upload a digital file "t-shirts.jpg" with name "Quick Start Guide EU"
        And I add an external URL file "https://example.com/video-tutorial-eu.mp4" with name "Video Tutorial EU"
        And I add an external URL file "https://example.com/bonus-content-eu.pdf" with name "Bonus Content EU"
        And I add it
        Then I should be notified that it has been successfully created
        And the digital file "User Manual US" should be listed in the "United States" channel accordion
        And the digital file "Quick Start Guide US" should be listed in the "United States" channel accordion
        And the digital file "Video Tutorial US" should be listed in the "United States" channel accordion
        And the digital file "Bonus Content US" should be listed in the "United States" channel accordion
        And the digital file "User Manual EU" should be listed in the "Europe" channel accordion
        And the digital file "Quick Start Guide EU" should be listed in the "Europe" channel accordion
        And the digital file "Video Tutorial EU" should be listed in the "Europe" channel accordion
        And the digital file "Bonus Content EU" should be listed in the "Europe" channel accordion
        And the product "Digital product with multiple files" should appear in the store
