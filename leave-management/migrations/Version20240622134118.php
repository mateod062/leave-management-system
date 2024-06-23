<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240622134118 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FFF2C34BA');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FFF2C34BA FOREIGN KEY (team_lead_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FFF2C34BA');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FFF2C34BA FOREIGN KEY (team_lead_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE CASCADE');
    }
}
