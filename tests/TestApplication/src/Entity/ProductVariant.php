<?php

declare(strict_types=1);

namespace Tests\Jkindly\SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductFile;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettings;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductChannelSettingsInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductVariantInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettings;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductVariantSettingsInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait;
use Sylius\Component\Core\Model\ProductVariant as BaseProductVariant;
use Jkindly\SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantAwareTrait;
use Jkindly\SyliusDigitalProductPlugin\Entity\Trait\DigitalProductVariantSettingsAwareTrait;

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
