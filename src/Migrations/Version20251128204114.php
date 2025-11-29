<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251128204114 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_digital_product_settings (id INT AUTO_INCREMENT NOT NULL, digital_file_id INT NOT NULL, download_limit INT NOT NULL, days_available INT NOT NULL, hidden_quantity TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_19C18DF3B349A266 (digital_file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_digital_product_settings ADD CONSTRAINT FK_19C18DF3B349A266 FOREIGN KEY (digital_file_id) REFERENCES sylius_digital_product_file (id)');
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings ADD download_limit INT NOT NULL, ADD days_available INT NOT NULL, ADD hidden_quantity TINYINT(1) NOT NULL, DROP configuration');
        $this->addSql('ALTER TABLE sylius_digital_product_file DROP days_available, DROP download_limit');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_settings DROP FOREIGN KEY FK_19C18DF3B349A266');
        $this->addSql('DROP TABLE sylius_digital_product_settings');
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings ADD configuration JSON NOT NULL, DROP download_limit, DROP days_available, DROP hidden_quantity');
        $this->addSql('ALTER TABLE sylius_digital_product_file ADD days_available INT DEFAULT NULL, ADD download_limit INT DEFAULT NULL');
    }
}
