<?php

declare(strict_types=1);

namespace Tests\Jkindly\SyliusDigitalProductPlugin\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\OrderItem as BaseOrderItem;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductOrderItemFile;
use Jkindly\SyliusDigitalProductPlugin\Entity\Trait\DigitalProductFilesAwareTrait;

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
