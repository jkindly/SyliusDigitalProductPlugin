<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SyliusDigitalProductPlugin\Entity\DigitalFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalFilesAwareTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantAwareTrait;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantSettingsAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements DigitalProductVariantInterface
{
    use DigitalProductVariantAwareTrait;
    use DigitalProductVariantSettingsAwareTrait;
    use DigitalFilesAwareTrait;

    #[ORM\Column(name: 'is_digital', type: 'boolean')]
    protected bool $isDigital = false;

    #[ORM\OneToMany(targetEntity: DigitalFile::class, mappedBy: 'productVariant', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $digitalFiles;

    #[ORM\OneToOne(targetEntity: DigitalProductVariantSettings::class, mappedBy: 'productVariant', cascade: ['persist', 'remove'])]
    protected ?DigitalProductVariantSettingsInterface $digitalProductVariantSettings = null;

    public function __construct()
    {
        parent::__construct();
        $this->initializeFilesCollection();
    }
}
