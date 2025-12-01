<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251130080940 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_digital_product_variant_settings (id INT AUTO_INCREMENT NOT NULL, variant_id INT NOT NULL, hidden_quantity TINYINT(1) DEFAULT NULL, UNIQUE INDEX UNIQ_9FB808793B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings ADD CONSTRAINT FK_9FB808793B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id)');
        $this->addSql('ALTER TABLE sylius_digital_product_file_channel_settings DROP hidden_quantity');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP FOREIGN KEY FK_7619087093CB796C');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP hidden_quantity');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD CONSTRAINT FK_7619087093CB796C FOREIGN KEY (file_id) REFERENCES sylius_digital_product_file (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings DROP FOREIGN KEY FK_9FB808793B69A9AF');
        $this->addSql('DROP TABLE sylius_digital_product_variant_settings');
        $this->addSql('ALTER TABLE sylius_digital_product_file_channel_settings ADD hidden_quantity TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP FOREIGN KEY FK_7619087093CB796C');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD hidden_quantity TINYINT(1) DEFAULT NULL');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD CONSTRAINT FK_7619087093CB796C FOREIGN KEY (file_id) REFERENCES sylius_digital_product_file (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
