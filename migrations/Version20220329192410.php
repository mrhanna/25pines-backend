<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220329192410 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE playlist (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(30) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE playlist_item (id INT AUTO_INCREMENT NOT NULL, playlist_id INT NOT NULL, content_id INT NOT NULL, sort INT NOT NULL, INDEX IDX_BF02127C6BBD148 (playlist_id), UNIQUE INDEX UNIQ_BF02127C84A0A3ED (content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE playlist_item ADD CONSTRAINT FK_BF02127C6BBD148 FOREIGN KEY (playlist_id) REFERENCES playlist (id)');
        $this->addSql('ALTER TABLE playlist_item ADD CONSTRAINT FK_BF02127C84A0A3ED FOREIGN KEY (content_id) REFERENCES abstract_content (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE playlist_item DROP FOREIGN KEY FK_BF02127C6BBD148');
        $this->addSql('DROP TABLE playlist');
        $this->addSql('DROP TABLE playlist_item');
    }
}
