<?php

declare(strict_types=1);

namespace SyliusDigitalProductPlugin\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251129200812 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP FOREIGN KEY FK_76190870B349A266');
        $this->addSql('DROP INDEX UNIQ_76190870B349A266 ON sylius_digital_product_file_settings');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings CHANGE digital_file_id file_id INT NOT NULL');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD CONSTRAINT FK_7619087093CB796C FOREIGN KEY (file_id) REFERENCES sylius_digital_product_file (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_7619087093CB796C ON sylius_digital_product_file_settings (file_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings DROP FOREIGN KEY FK_7619087093CB796C');
        $this->addSql('DROP INDEX UNIQ_7619087093CB796C ON sylius_digital_product_file_settings');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings CHANGE file_id digital_file_id INT NOT NULL');
        $this->addSql('ALTER TABLE sylius_digital_product_file_settings ADD CONSTRAINT FK_76190870B349A266 FOREIGN KEY (digital_file_id) REFERENCES sylius_digital_product_file (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_76190870B349A266 ON sylius_digital_product_file_settings (digital_file_id)');
    }
}
