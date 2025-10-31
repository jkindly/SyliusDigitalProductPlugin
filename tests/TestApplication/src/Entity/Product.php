<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use SyliusDigitalProductPlugin\Entity\DigitalFile;
use SyliusDigitalProductPlugin\Entity\DigitalProductInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalFilesAwareTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
class Product extends BaseProduct implements DigitalProductInterface
{
    use DigitalFilesAwareTrait;

    #[ORM\OneToMany(targetEntity: DigitalFile::class, mappedBy: 'product', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $digitalFiles;

    public function __construct()
    {
        parent::__construct();
        $this->initializeFilesCollection();
    }
}
