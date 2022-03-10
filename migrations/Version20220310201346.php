<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220310201346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_content ADD date_added DATETIME NOT NULL, ADD duration INT DEFAULT NULL, ADD language VARCHAR(5) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE abstract_content DROP date_added, DROP duration, DROP language, CHANGE title title VARCHAR(50) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE thumbnail thumbnail VARCHAR(100) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE short_description short_description VARCHAR(200) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE long_description long_description VARCHAR(500) DEFAULT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE discr discr VARCHAR(255) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE tag CHANGE name name VARCHAR(20) NOT NULL COLLATE `utf8mb4_unicode_ci`');
        $this->addSql('ALTER TABLE video CHANGE url url VARCHAR(100) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE quality quality VARCHAR(3) NOT NULL COLLATE `utf8mb4_unicode_ci`, CHANGE video_type video_type VARCHAR(6) NOT NULL COLLATE `utf8mb4_unicode_ci`');
    }
}
