<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantAwareTrait;
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
