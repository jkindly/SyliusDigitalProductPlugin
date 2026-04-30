<?php

declare(strict_types=1);

namespace Tests\Jkindly\SyliusDigitalProductPlugin\Entity;

use Doctrine\ORM\Mapping as ORM;
use Sylius\Component\Core\Model\Order as BaseOrder;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductOrderInterface;
use Jkindly\SyliusDigitalProductPlugin\Entity\Trait\DigitalProductOrderAwareTrait;

#[ORM\Entity]
#[ORM\Table(name: 'sylius_order')]
class Order extends BaseOrder implements DigitalProductOrderInterface
{
    use DigitalProductOrderAwareTrait;
}
