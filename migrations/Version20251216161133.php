<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251216161133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE connection DROP FOREIGN KEY FK_29F773666C066AFE');
        $this->addSql('DROP INDEX IDX_29F773666C066AFE ON connection');
        $this->addSql('ALTER TABLE connection CHANGE types types VARCHAR(255) NOT NULL, CHANGE target_user_id target_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE connection CHANGE types types VARCHAR(50) NOT NULL, CHANGE target_id target_user_id INT NOT NULL');
        $this->addSql('ALTER TABLE connection ADD CONSTRAINT FK_29F773666C066AFE FOREIGN KEY (target_user_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
        $this->addSql('CREATE INDEX IDX_29F773666C066AFE ON connection (target_user_id)');
    }
}
