<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130084802 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_digital_product_channel_settings (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, download_limit INT DEFAULT NULL, days_available INT DEFAULT NULL, hidden_quantity TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_77D83A5872F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings ADD CONSTRAINT FK_77D83A5872F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE sylius_digital_product_file_channel_settings DROP FOREIGN KEY FK_954D03EA72F5A1AA');
        $this->addSql('DROP TABLE sylius_digital_product_file_channel_settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_digital_product_file_channel_settings (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, download_limit INT DEFAULT NULL, days_available INT DEFAULT NULL, UNIQUE INDEX UNIQ_954D03EA72F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sylius_digital_product_file_channel_settings ADD CONSTRAINT FK_954D03EA72F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings DROP FOREIGN KEY FK_77D83A5872F5A1AA');
        $this->addSql('DROP TABLE sylius_digital_product_channel_settings');
    }
}
