<p align="center">
    <a href="https://sylius.com" target="_blank">
        <picture>
            <source media="(prefers-color-scheme: dark)" srcset="https://media.sylius.com/sylius-logo-800-dark.png">
            <source media="(prefers-color-scheme: light)" srcset="https://media.sylius.com/sylius-logo-800.png">
            <img alt="Sylius Logo." src="https://media.sylius.com/sylius-logo-800.png">
        </picture>
    </a>
</p>

<h1 align="center">Sylius Digital Product Plugin</h1>

<p align="center">Sell digital products in Sylius with uploaded files, external URLs, download limits, and post-payment delivery.</p>

## Overview

This plugin adds digital product support to Sylius 2.x.

It lets you:

- mark product variants as digital
- attach multiple files to a variant
- scope files per channel
- use built-in file types: uploaded files and external URLs
- configure download limits and availability windows
- send download links automatically after payment
- let customers download files from the storefront order area
- resend download emails from the admin panel
- upload large files in chunks

For uploaded files, the plugin copies the original product file into an order-specific storage when payment is completed. This keeps customer downloads independent from later catalog changes.

## Requirements

- PHP 8.2+
- Symfony 6.4
- Sylius 2.x
- League Flysystem Bundle 3.x

## Installation

### 1. Require the plugin

```bash
composer require jkindly/sylius-digital-product-plugin
```

### 2. Register the bundle

Add the plugin bundle to `config/bundles.php` if it is not registered automatically:

```php
<?php

return [
    SyliusDigitalProductPlugin\SyliusDigitalProductPlugin::class => ['all' => true],
];
```

### 3. Import the plugin routes

```yaml
sylius_digital_product_admin:
    resource: "@SyliusDigitalProductPlugin/config/routes/admin.yaml"
    prefix: /admin

sylius_digital_product_shop:
    resource: "@SyliusDigitalProductPlugin/config/routes/shop.yaml"
```

### 4. Extend your Sylius models

The plugin expects your application models to implement its interfaces and use its traits.

#### Product variant

Your product variant model should:

- implement `SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface`
- use `SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait`
- use `SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantSettingsAwareTrait`
- add the matching Doctrine relations for:
  - `DigitalProductVariantSettings`
  - `DigitalProductFile`

Example:

```php
<?php

declare(strict_types=1);

namespace App\Entity\Product;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantSettingsAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements DigitalProductVariantInterface
{
    use DigitalProductFilesAwareTrait;
    use DigitalProductVariantSettingsAwareTrait;

    #[ORM\OneToOne(targetEntity: DigitalProductVariantSettings::class, mappedBy: 'productVariant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected ?DigitalProductVariantSettingsInterface $digitalProductVariantSettings = null;

    #[ORM\OneToMany(targetEntity: DigitalProductFile::class, mappedBy: 'productVariant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $files;

    public function __construct()
    {
        parent::__construct();
        $this->initializeFilesCollection();
    }
}
```

#### Channel

Your channel model should:

- implement `SyliusDigitalProductPlugin\Entity\DigitalProductChannelInterface`
- use `SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFileChannelSettingsAwareTrait`
- add the relation for `DigitalProductChannelSettings`

#### Order

Your order model should:

- implement `SyliusDigitalProductPlugin\Entity\DigitalProductOrderInterface`
- use `SyliusDigitalProductPlugin\Entity\Trait\DigitalProductOrderAwareTrait`

#### Order item

Your order item model should:

- implement `SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemInterface`
- use `SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait`
- add the relation for `DigitalProductOrderItemFile`

The test application in `tests/TestApplication/src/Entity/` shows the full working setup.

### 5. Point Sylius resources to your extended models

Example:

```yaml
sylius_product:
    resources:
        product_variant:
            classes:
                model: App\Entity\Product\ProductVariant

sylius_channel:
    resources:
        channel:
            classes:
                model: App\Entity\Channel\Channel

sylius_order:
    resources:
        order:
            classes:
                model: App\Entity\Order\Order
        order_item:
            classes:
                model: App\Entity\Order\OrderItem
```

### 6. Run migrations

The plugin prepends its Doctrine migrations automatically. Run:

```bash
bin/console doctrine:migrations:migrate
```

### 7. Configure mailer and storage if needed

The plugin ships with:

- Sylius Mailer configuration for the digital download email
- Flysystem storages for product files, order files, and upload chunks
- Twig hooks for admin and shop UI integration

In a standard Sylius app, those are loaded automatically from the plugin bundle. You only need extra configuration if you want to override the defaults.

## Configuration

Root key:

```yaml
sylius_digital_product:
```

Available options:

```yaml
sylius_digital_product:
    uploaded_file:
        delete_from_storage_on_remove: false
        chunk_size: 5242880
        product_files_path: null
        order_files_path: null
        chunks_path: null
```

Default storage directories:

- `var/uploads/product_files`
- `var/uploads/order_files`
- `var/uploads/tmp/chunks`

## Built-in File Types

The plugin provides two file types out of the box:

- `uploaded_file`  
  A physical file stored through Flysystem.
- `external_url`  
  A redirect to a remote URL.

Each file type has its own:

- DTO
- form type
- validator
- serializer
- provider
- response generator

## How It Works

### Admin side

- channel forms expose default digital-product settings
- product and variant forms expose digital settings and file collections
- uploaded files can be sent directly or through chunked upload
- admins can download uploaded files for preview

### Payment flow

When the `workflow.sylius_order_payment.completed.pay` event is triggered, the plugin:

1. creates `DigitalProductOrderItemFile` records for every digital file in the order
2. copies uploaded files into order-specific storage
3. calculates download limits and expiration dates
4. dispatches a message that sends the digital download email

### Shop side

Customers receive download links after payment and can also access files from the order view.

The public download route uses a UUID token:

```text
/download/{uuid}
```

Before returning the response, the plugin:

- checks whether the file exists for the order
- verifies the download limit
- verifies the availability window
- increments the download count
- returns either a streamed file download or a redirect, depending on file type

Guest customers are supported because the UUID acts as the download token.

## Operational Notes

### Resend download email

The admin order page includes an action for resending the digital download email after the order has been paid.

### Cleanup abandoned chunks

Large uploads can leave temporary chunk directories behind. Use:

```bash
bin/console sylius:digital-product:cleanup-chunks
```

Options:

- `--hours=24` to remove only old chunks
- `--force` to remove all chunk directories

## Extending the Plugin

The file type system is extensible. To add a custom type, create and register:

1. a DTO implementing `FileDtoInterface`
2. a form type extending `AbstractFileType`
3. a data transformer for DTO <-> array conversion
4. a serializer for the configuration payload
5. a response generator
6. a provider implementing `FileProviderInterface`

Register the provider, serializer, and response generator with the plugin tags defined in `config/services/`.

## Development

### Test application setup

```bash
composer install
vendor/bin/console doctrine:database:create
vendor/bin/console doctrine:migrations:migrate -n
vendor/bin/console sylius:fixtures:load -n
(cd vendor/sylius/test-application && yarn install)
(cd vendor/sylius/test-application && yarn build)
vendor/bin/console assets:install
```

### Tests

```bash
vendor/bin/phpunit
vendor/bin/behat --strict --tags="~@javascript&&~@mink:chromedriver"
vendor/bin/phpstan analyse -c phpstan.neon -l max src
vendor/bin/ecs check
```

For JavaScript Behat scenarios, start a browser driver and the Symfony test server first, as in the test application workflow already used in this repository.

## License

This plugin is released under the [MIT License](LICENSE).
