<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemInterface;
use SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFile;
use SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order_item')]
class OrderItem extends BaseOrderItem implements DigitalProductOrderItemInterface
{
    use DigitalProductFilesAwareTrait;

    #[ORM\OneToMany(targetEntity: DigitalProductOrderItemFile::class, mappedBy: 'orderItem', cascade: ['persist', 'remove'], orphanRemoval: true)]
    protected Collection $files;

    public function __construct()
    {
        $this->initializeFilesCollection();

        parent::__construct();
    }
}
