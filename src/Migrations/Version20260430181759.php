<?php

declare(strict_types=1);

namespace Jkindly\SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Sylius\Bundle\CoreBundle\Doctrine\Migrations\AbstractPostgreSQLMigration;

final class Version20260430181759 extends AbstractPostgreSQLMigration
{
    public function getDescription(): string
    {
        return 'This migration creates PostgreSQL tables for digital products, including channel settings, file management, and order item files.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE sylius_digital_product_channel_settings (id SERIAL NOT NULL, channel_id INT NOT NULL, download_limit INT DEFAULT NULL, days_available INT DEFAULT NULL, hidden_quantity BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_77D83A5872F5A1AA ON sylius_digital_product_channel_settings (channel_id)');
        $this->addSql('CREATE TABLE sylius_digital_product_file (id SERIAL NOT NULL, channel_id INT NOT NULL, product_variant_id INT NOT NULL, uuid VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, configuration JSON DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_936063B8D17F50A6 ON sylius_digital_product_file (uuid)');
        $this->addSql('CREATE INDEX IDX_936063B872F5A1AA ON sylius_digital_product_file (channel_id)');
        $this->addSql('CREATE INDEX IDX_936063B8A80EF684 ON sylius_digital_product_file (product_variant_id)');
        $this->addSql('CREATE TABLE sylius_digital_product_file_settings (id SERIAL NOT NULL, file_id INT NOT NULL, download_limit INT DEFAULT NULL, days_available INT DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7619087093CB796C ON sylius_digital_product_file_settings (file_id)');
        $this->addSql('CREATE TABLE sylius_digital_product_order_item_file (id SERIAL NOT NULL, order_item_id INT NOT NULL, uuid VARCHAR(36) NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, download_limit INT DEFAULT NULL, download_count INT DEFAULT NULL, configuration JSON DEFAULT NULL, available_until TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7F30DED17F50A6 ON sylius_digital_product_order_item_file (uuid)');
        $this->addSql('CREATE INDEX IDX_7F30DEE415FB15 ON sylius_digital_product_order_item_file (order_item_id)');
        $this->addSql('CREATE TABLE sylius_digital_product_variant_settings (id SERIAL NOT NULL, variant_id INT NOT NULL, enabled BOOLEAN NOT NULL, hidden_quantity BOOLEAN DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9FB808793B69A9AF ON sylius_digital_product_variant_settings (variant_id)');
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings ADD CONSTRAINT FK_77D83A5872F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_digital_product_file ADD CONSTRAINT FK_936063B872F5A1AA FOREIGN KEY (channel_id) REFERENCES sylius_channel (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_digital_product_file ADD CONSTRAINT FK_936063B8A80EF684 FOREIGN KEY (product_variant_id) REFERENCES sylius_product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD CONSTRAINT FK_7619087093CB796C FOREIGN KEY (file_id) REFERENCES sylius_digital_product_file (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_digital_product_order_item_file ADD CONSTRAINT FK_7F30DEE415FB15 FOREIGN KEY (order_item_id) REFERENCES sylius_order_item (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings ADD CONSTRAINT FK_9FB808793B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_digital_product_channel_settings DROP CONSTRAINT FK_77D83A5872F5A1AA');
        $this->addSql('ALTER TABLE sylius_digital_product_file DROP CONSTRAINT FK_936063B872F5A1AA');
        $this->addSql('ALTER TABLE sylius_digital_product_file DROP CONSTRAINT FK_936063B8A80EF684');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP CONSTRAINT FK_7619087093CB796C');
        $this->addSql('ALTER TABLE sylius_digital_product_order_item_file DROP CONSTRAINT FK_7F30DEE415FB15');
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings DROP CONSTRAINT FK_9FB808793B69A9AF');
        $this->addSql('DROP TABLE sylius_digital_product_channel_settings');
        $this->addSql('DROP TABLE sylius_digital_product_file');
        $this->addSql('DROP TABLE sylius_digital_product_file_settings');
        $this->addSql('DROP TABLE sylius_digital_product_order_item_file');
        $this->addSql('DROP TABLE sylius_digital_product_variant_settings');
    }
}
