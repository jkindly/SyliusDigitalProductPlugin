<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin;

use Jkindly\SyliusDigitalProductPlugin\DependencyInjection\Compiler\OverrideShopQuantityHookPass;
use Sylius\Bundle\CoreBundle\Application\SyliusPluginTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

final class SyliusDigitalProductPlugin extends Bundle
{
    use SyliusPluginTrait;

    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new OverrideShopQuantityHookPass());
    }

    public function getPath(): string
    {
        return \dirname(__DIR__);
    }
}
