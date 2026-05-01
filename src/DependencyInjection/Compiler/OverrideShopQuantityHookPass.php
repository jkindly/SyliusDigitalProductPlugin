<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class OverrideShopQuantityHookPass implements CompilerPassInterface
{
    private const SHOP_QUANTITY_HOOK_SERVICE_ID = 'sylius_twig_hooks.hook.sylius_shop.product.show.content.info.summary.add_to_cart.hookable.quantity';

    public function process(ContainerBuilder $container): void
    {
        if (!$container->hasDefinition(self::SHOP_QUANTITY_HOOK_SERVICE_ID)) {
            return;
        }

        $container
            ->getDefinition(self::SHOP_QUANTITY_HOOK_SERVICE_ID)
            ->setArgument(2, '@SyliusDigitalProductPlugin/shop/product/show/content/info/summary/add_to_cart/quantity.html.twig')
        ;
    }
}
