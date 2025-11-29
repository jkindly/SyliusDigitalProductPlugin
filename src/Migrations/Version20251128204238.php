<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128204238 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings CHANGE download_limit download_limit INT DEFAULT NULL, CHANGE days_available days_available INT DEFAULT NULL, CHANGE hidden_quantity hidden_quantity TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_digital_product_settings CHANGE download_limit download_limit INT DEFAULT NULL, CHANGE days_available days_available INT DEFAULT NULL, CHANGE hidden_quantity hidden_quantity TINYINT(1) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings CHANGE download_limit download_limit INT NOT NULL, CHANGE days_available days_available INT NOT NULL, CHANGE hidden_quantity hidden_quantity TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE sylius_digital_product_settings CHANGE download_limit download_limit INT NOT NULL, CHANGE days_available days_available INT NOT NULL, CHANGE hidden_quantity hidden_quantity TINYINT(1) NOT NULL');
    }
}
