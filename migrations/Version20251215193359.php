<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215193359 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification ADD initiator_id INT DEFAULT NULL, DROP message, DROP subject_type');
        $this->addSql('ALTER TABLE notification ADD CONSTRAINT FK_BF5476CA7DB3B714 FOREIGN KEY (initiator_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_BF5476CA7DB3B714 ON notification (initiator_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE notification DROP FOREIGN KEY FK_BF5476CA7DB3B714');
        $this->addSql('DROP INDEX IDX_BF5476CA7DB3B714 ON notification');
        $this->addSql('ALTER TABLE notification ADD message VARCHAR(255) NOT NULL, ADD subject_type VARCHAR(255) NOT NULL, DROP initiator_id');
    }
}
