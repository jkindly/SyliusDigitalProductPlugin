<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250920191704 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_digital_file_plugin_uploaded_digital_file (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, uuid VARBINARY(16) NOT NULL, position INT NOT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, path VARCHAR(255) NOT NULL, mime_type VARCHAR(255) NOT NULL, original_file_name VARCHAR(255) NOT NULL, size BIGINT UNSIGNED NOT NULL, UNIQUE INDEX UNIQ_FBF3D84DD17F50A6 (uuid), INDEX IDX_FBF3D84D4584665A (product_id), INDEX IDX_FBF3D84DB548B0F (path), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE sylius_digital_file_plugin_uploaded_digital_file ADD CONSTRAINT FK_FBF3D84D4584665A FOREIGN KEY (product_id) REFERENCES sylius_product (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_file_plugin_uploaded_digital_file DROP FOREIGN KEY FK_FBF3D84D4584665A');
        $this->addSql('DROP TABLE sylius_digital_file_plugin_uploaded_digital_file');
    }
}
