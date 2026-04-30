<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Repository;

use Doctrine\Persistence\ObjectRepository;
use Jkindly\SyliusDigitalProductPlugin\Entity\DigitalProductFileInterface;

/**
 * @extends ObjectRepository<DigitalProductFileInterface>
 */
interface DigitalProductFileRepositoryInterface extends ObjectRepository
{
}
