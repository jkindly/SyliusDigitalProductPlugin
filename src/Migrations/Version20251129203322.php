<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129203322 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add ON DELETE CASCADE to file_settings foreign key';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP FOREIGN KEY FK_7619087093CB796C');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD CONSTRAINT FK_7619087093CB796C FOREIGN KEY (file_id) REFERENCES sylius_digital_product_file (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP FOREIGN KEY FK_7619087093CB796C');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD CONSTRAINT FK_7619087093CB796C FOREIGN KEY (file_id) REFERENCES sylius_digital_product_file (id)');
    }
}
