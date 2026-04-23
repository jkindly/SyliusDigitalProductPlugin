<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Element\Admin;

use Behat\Mink\Element\NodeElement;
use Sylius\Behat\Element\Admin\NotificationsElement as BaseNotificationsElement;
use Sylius\Behat\Service\DriverHelper;

final class NotificationsElement extends BaseNotificationsElement
{
    public function hasNotification(string $type, string $message): bool
    {
        $flashesContainer = $this->getElement('flashes_container');

        if (DriverHelper::isJavascript($this->getDriver())) {
            $flashesContainer->waitFor(5000, static function () use ($flashesContainer): bool {
                return $flashesContainer->isVisible();
            });
        }

        /** @var array<NodeElement> $flashes */
        $flashes = $flashesContainer->findAll('css', '[data-test-sylius-flash-message]');

        foreach ($flashes as $flash) {
            if (str_contains($flash->getText(), $message) && $flash->getAttribute('data-test-sylius-flash-message-type') === $type) {
                return true;
            }
        }

        return false;
    }
}
