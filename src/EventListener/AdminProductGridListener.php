<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\EventListener;

use Sylius\Component\Grid\Event\GridDefinitionConverterEvent;

final class AdminProductGridListener
{
    public function addDigitalCreateLink(GridDefinitionConverterEvent $event): void
    {
        $grid = $event->getGrid();
        if ($grid->getCode() !== 'sylius_admin_product') {
            return;
        }

        $main = $grid->getActionGroup('main');
        $create = $main->getAction('create');

        $links = $create->getOptions()['links'] ?? [];
        $links['digital'] = [
            'label' => 'Digital Product',
            'route' => 'sylius_admin_product_create_digital_simple',
        ];

        $create->setOptions(array_merge($create->getOptions(), ['links' => $links]));
    }
}
