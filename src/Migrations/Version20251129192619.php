<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129192619 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings DROP FOREIGN KEY FK_9FB808793B69A9AF');
        $this->addSql('DROP TABLE sylius_digital_product_variant_settings');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE sylius_digital_product_variant_settings (id INT AUTO_INCREMENT NOT NULL, variant_id INT NOT NULL, configuration JSON NOT NULL, UNIQUE INDEX UNIQ_9FB808793B69A9AF (variant_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE sylius_digital_product_variant_settings ADD CONSTRAINT FK_9FB808793B69A9AF FOREIGN KEY (variant_id) REFERENCES sylius_product_variant (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
