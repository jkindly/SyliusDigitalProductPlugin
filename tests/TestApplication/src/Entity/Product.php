<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use SyliusDigitalProductPlugin\Entity\DigitalProductInterface;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalFilesAwareTrait;
use Sylius\Component\Core\Model\Product as BaseProduct;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_product')]
final class Product extends BaseProduct implements DigitalProductInterface
{
    use DigitalFilesAwareTrait;

    public function __construct()
    {
        parent::__construct();
        $this->initializeFilesCollection();
    }
}
