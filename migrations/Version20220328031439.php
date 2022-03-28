<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220328031439 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE thumbnail (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(10) NOT NULL, layout VARCHAR(1) NOT NULL, data LONGBLOB NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abstract_content ADD thumbnail_id INT DEFAULT NULL, DROP thumbnail');
        $this->addSql('ALTER TABLE abstract_content ADD CONSTRAINT FK_5C725639FDFF2E92 FOREIGN KEY (thumbnail_id) REFERENCES thumbnail (id)');
        $this->addSql('CREATE INDEX IDX_5C725639FDFF2E92 ON abstract_content (thumbnail_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_content DROP FOREIGN KEY FK_5C725639FDFF2E92');
        $this->addSql('DROP TABLE thumbnail');
        $this->addSql('DROP INDEX IDX_5C725639FDFF2E92 ON abstract_content');
        $this->addSql('ALTER TABLE abstract_content ADD thumbnail VARCHAR(100) DEFAULT NULL, DROP thumbnail_id');
    }
}
