<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220310223252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE abstract_content (id INT NOT NULL, series_id INT DEFAULT NULL, uuid BINARY(16) NOT NULL COMMENT \'(DC2Type:uuid)\', title VARCHAR(50) NOT NULL, thumbnail VARCHAR(100) NOT NULL, release_date DATE DEFAULT NULL, short_description VARCHAR(200) DEFAULT NULL, long_description VARCHAR(500) DEFAULT NULL, date_added DATETIME DEFAULT NULL, genres LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:simple_array)\', media_type VARCHAR(14) NOT NULL, discr VARCHAR(255) NOT NULL, season_number SMALLINT DEFAULT NULL, episode_number SMALLINT DEFAULT NULL, duration INT DEFAULT NULL, language VARCHAR(5) DEFAULT NULL, UNIQUE INDEX UNIQ_5C725639D17F50A6 (uuid), INDEX IDX_5C7256395278319C (series_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(20) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE tag_abstract_content (tag_id INT NOT NULL, abstract_content_id INT NOT NULL, INDEX IDX_82D218CBAD26311 (tag_id), INDEX IDX_82D218C1F11F1B1 (abstract_content_id), PRIMARY KEY(tag_id, abstract_content_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE video (id INT AUTO_INCREMENT NOT NULL, content_id INT DEFAULT NULL, url VARCHAR(100) NOT NULL, quality VARCHAR(3) NOT NULL, video_type VARCHAR(6) NOT NULL, INDEX IDX_7CC7DA2C84A0A3ED (content_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE abstract_content ADD CONSTRAINT FK_5C7256395278319C FOREIGN KEY (series_id) REFERENCES abstract_content (id)');
        $this->addSql('ALTER TABLE tag_abstract_content ADD CONSTRAINT FK_82D218CBAD26311 FOREIGN KEY (tag_id) REFERENCES tag (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tag_abstract_content ADD CONSTRAINT FK_82D218C1F11F1B1 FOREIGN KEY (abstract_content_id) REFERENCES abstract_content (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE video ADD CONSTRAINT FK_7CC7DA2C84A0A3ED FOREIGN KEY (content_id) REFERENCES abstract_content (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_content DROP FOREIGN KEY FK_5C7256395278319C');
        $this->addSql('ALTER TABLE tag_abstract_content DROP FOREIGN KEY FK_82D218C1F11F1B1');
        $this->addSql('ALTER TABLE video DROP FOREIGN KEY FK_7CC7DA2C84A0A3ED');
        $this->addSql('ALTER TABLE tag_abstract_content DROP FOREIGN KEY FK_82D218CBAD26311');
        $this->addSql('DROP TABLE abstract_content');
        $this->addSql('DROP TABLE tag');
        $this->addSql('DROP TABLE tag_abstract_content');
        $this->addSql('DROP TABLE video');
    }
}
