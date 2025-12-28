<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Repository;

use Doctrine\Persistence\ObjectRepository;
use SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;

/**
 * @extends ObjectRepository<DigitalProductFileInterface>
 */
interface DigitalProductFileRepositoryInterface extends ObjectRepository
{
}
