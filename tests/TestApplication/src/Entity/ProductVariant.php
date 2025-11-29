<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileChannelSettings;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileChannelSettingsInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantAwareTrait;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantSettingsAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product_variant')]
class ProductVariant extends BaseProductVariant implements DigitalProductVariantInterface
{
    use DigitalProductVariantAwareTrait;
    use DigitalProductFilesAwareTrait;

    #[ORM\Column(name: 'is_digital', type: 'boolean')]
    protected bool $isDigital = false;

    #[ORM\OneToMany(targetEntity: DigitalProductFile::class, mappedBy: 'productVariant', cascade: ['persist', 'remove'])]
    protected Collection $files;

    public function __construct()
    {
        parent::__construct();
        $this->initializeFilesCollection();
    }
}
