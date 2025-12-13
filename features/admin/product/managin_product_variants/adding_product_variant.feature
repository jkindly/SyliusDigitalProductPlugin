@sylius_digital_product_managing_product_variants
Feature: Adding a new product variant with digital section
    In order to selling configurable digital products
    As an Administrator
    I want to add a new product variant with digital section to the shop

    Background:
        Given the store operates on a channel named "United States"
        And the store operates on another channel named "Europe"
        And the store has a "Digital configurable product" configurable product
        And this product has option "License" with values "Yes" and "No"
        And this product is available in "United States" channel and "Europe" channel
        And I am logged in as an administrator

    @ui
    Scenario: Adding a new product variant with uploaded file
        When I want to create a new variant of this product
        And I specify its code as "DIGITAL_VARIANT"
        And I name it "Digital variant" in "English (United States)" language
        And I set its price to "10.00" for channel "United States"
        And I set its price to "15.00" for channel "Europe"
        And I open the Digital section
        And I select accordion for "United States" channel
        And I upload a digital file "funky_cat.png" with name "Funky cat"
        And I add it
        Then I should be notified that it has been successfully created
        And I go to edit page of variant created
        And the digital file "Funky cat" should be listed in the "United States" channel accordion
        And the uploaded file should have a download link

    @ui
    Scenario: Adding a new product variant with external URL
        When I want to create a new variant of this product
        And I specify its code as "DIGITAL_VARIANT"
        And I name it "Digital variant" in "English (United States)" language
        And I set its price to "10.00" for channel "United States"
        And I set its price to "15.00" for channel "Europe"
        And I open the Digital section
        And I select accordion for "United States" channel
        And I add an external URL file "https://example.com/download.pdf" with name "External URL"
        And I add it
        Then I should be notified that it has been successfully created
        And I go to edit page of variant created
        And the digital file "External URL" should be listed in the "United States" channel accordion

    @ui
    Scenario: Adding a new product variant with multiple files across two channels
        When I want to create a new variant of this product
        And I specify its code as "DIGITAL_VARIANT"
        And I name it "Digital variant" in "English (United States)" language
        And I set its price to "10.00" for channel "United States"
        And I set its price to "15.00" for channel "Europe"
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
        And I go to edit page of variant created
        And the digital file "User Manual US" should be listed in the "United States" channel accordion
        And the digital file "Quick Start Guide US" should be listed in the "United States" channel accordion
        And the digital file "Video Tutorial US" should be listed in the "United States" channel accordion
        And the digital file "Bonus Content US" should be listed in the "United States" channel accordion
        And the digital file "User Manual EU" should be listed in the "Europe" channel accordion
        And the digital file "Quick Start Guide EU" should be listed in the "Europe" channel accordion
        And the digital file "Video Tutorial EU" should be listed in the "Europe" channel accordion
        And the digital file "Bonus Content EU" should be listed in the "Europe" channel accordion
