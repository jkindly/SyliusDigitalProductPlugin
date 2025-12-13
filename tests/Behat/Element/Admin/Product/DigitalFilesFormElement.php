<?php

declare(strict_types=1);

namespace Tests\SyliusDigitalProductPlugin\Behat\Element\Admin\Product;

use Behat\Mink\Session;
use Sylius\Behat\Element\Admin\Crud\FormElement;
use Sylius\Behat\Service\DriverHelper;
use Sylius\Behat\Service\SharedStorageInterface;
use Sylius\Component\Core\Model\ChannelInterface;

final class DigitalFilesFormElement extends FormElement implements DigitalFilesFormElementInterface
{
    public function __construct(
        Session $session,
        $minkParameters,
        private readonly SharedStorageInterface $sharedStorage,
    ) {
        parent::__construct($session, $minkParameters);
    }

    public function uploadDigitalFile(string $path, string $name): void
    {
        if (DriverHelper::isNotJavascript($this->getDriver())) {
            return;
        }

        /** @var ChannelInterface $channel */
        $channel = $this->sharedStorage->get('channel');

        $this->getElement('add_uploaded_file_button', ['%channel_code%' => $channel->getCode()])->click();

        $this->waitForFormUpdate();

        $entries = $this->getDocument()->findAll('css', '[data-test-entry-row]');
        $lastEntry = end($entries);

        $nameField = $lastEntry->find('css', '[data-test-name]');
        $nameField->setValue($name);

        $filesPath = $this->getParameter('files_path');
        $fileInput = $lastEntry->find('css', '[data-test-uploaded-file]');
        $fileInput->attachFile($filesPath . $path);
    }

    public function addExternalUrlFile(string $url, string $name): void
    {
        if (DriverHelper::isNotJavascript($this->getDriver())) {
            return;
        }

        /** @var ChannelInterface $channel */
        $channel = $this->sharedStorage->get('channel');

        $this->getElement('add_external_url_button', ['%channel_code%' => $channel->getCode()])->click();

        $this->waitForFormUpdate();

        $entries = $this->getDocument()->findAll('css', '[data-test-entry-row]');
        $lastEntry = end($entries);

        $nameField = $lastEntry->find('css', '[data-test-name]');
        $nameField->setValue($name);

        $urlField = $lastEntry->find('css', '[data-test-external-url]');
        $urlField->setValue($url);
    }

    public function openChannelAccordion(ChannelInterface $channel): void
    {
        if (DriverHelper::isNotJavascript($this->getDriver())) {
            return;
        }

        $accordion = $this->getElement('channel_accordion', ['%channel_code%' => $channel->getCode()]);

        if ($accordion->hasClass('collapsed')) {
            $accordion->click();
            $this->getDocument()->waitFor(2, fn () => !$accordion->hasClass('collapsed'));
        }

        $this->sharedStorage->set('channel', $channel);
    }

    public function hasFileWithName(string $name): bool
    {
        $this->getDocument()->waitFor(2, function () {
            return 0 < count($this->getDocument()->findAll('css', '[data-test-entry-row]'));
        });

        $entries = $this->getDocument()->findAll('css', '[data-test-entry-row]');

        foreach ($entries as $entry) {
            if (!$entry->isVisible()) {
                continue;
            }

            $nameField = $entry->find('css', '[data-test-name]');
            if (null === $nameField) {
                continue;
            }

            $value = $nameField->getValue();

            if ($name === $value) {
                return true;
            }
        }

        return false;
    }

    public function hasUploadedFileDownloadLink(): bool
    {
        return null !== $this->getDocument()->find('css', '[data-test-uploaded-file-download-link]');
    }

    public function enableSettingsForThisProduct(): void
    {
        $this->getElement('settings')->check();
    }

    protected function getDefinedElements(): array
    {
        return array_merge(
            parent::getDefinedElements(),
            [
                'add_external_url_button' => '[data-test-add-external_url="%channel_code%"]',
                'add_uploaded_file_button' => '[data-test-add-uploaded_file="%channel_code%"]',
                'channel_accordion' => '[data-test-digital-product-files-accordion="%channel_code%"]',
                'settings' => '[data-test-override-channel-settings]',
            ],
        );
    }
}
