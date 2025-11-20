<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251118195935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_digital_product_channel_settings (id INT AUTO_INCREMENT NOT NULL, channel_id INT NOT NULL, configuration JSON NOT NULL, UNIQUE INDEX UNIQ_77D83A5872F5A1AA (channel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_digital_product_file (id INT AUTO_INCREMENT NOT NULL, product_variant_id INT NOT NULL, uuid VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, position INT NOT NULL, configuration JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_936063B8D17F50A6 (uuid), INDEX IDX_936063B8A80EF684 (product_variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_digital_product_order_item_file (id INT AUTO_INCREMENT NOT NULL, order_item_id INT NOT NULL, uuid VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, download_limit INT DEFAULT NULL, download_count INT DEFAULT NULL, configuration JSON DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX UNIQ_7F30DED17F50A6 (uuid), INDEX IDX_7F30DEE415FB15 (order_item_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE sylius_digital_product_variant_settings (id INT AUTO_INCREMENT NOT NULL, variant_id INT NOT NULL, configuration JSON NOT NULL, UNIQUE INDEX UNIQ_9FB808793B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings ADD CONSTRAINT FK_77D83A5872F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id)');
        $this->addSql('ALTER TABLE sylius_digital_product_file ADD CONSTRAINT FK_936063B8A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE sylius_digital_product_order_item_file ADD CONSTRAINT FK_7F30DEE415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id)');
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings ADD CONSTRAINT FK_9FB808793B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings DROP FOREIGN KEY FK_77D83A5872F5A1AA');
        $this->addSql('ALTER TABLE sylius_digital_product_file DROP FOREIGN KEY FK_936063B8A80EF684');
        $this->addSql('ALTER TABLE sylius_digital_product_order_item_file DROP FOREIGN KEY FK_7F30DEE415FB15');
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings DROP FOREIGN KEY FK_9FB808793B69A9AF');
        $this->addSql('DROP TABLE sylius_digital_product_channel_settings');
        $this->addSql('DROP TABLE sylius_digital_product_file');
        $this->addSql('DROP TABLE sylius_digital_product_order_item_file');
        $this->addSql('DROP TABLE sylius_digital_product_variant_settings');
    }
}
