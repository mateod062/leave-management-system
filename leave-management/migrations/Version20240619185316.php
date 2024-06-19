<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240619185316 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment CHANGE comments comment LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FFF2C34BA');
        $this->addSql('DROP INDEX UNIQ_C4E0A61FFF2C34BA ON team');
        $this->addSql('ALTER TABLE team ADD project_manager_id BIGINT NOT NULL, CHANGE team_leader_id team_lead_id BIGINT NOT NULL');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61F60984F51 FOREIGN KEY (project_manager_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FFF2C34BA FOREIGN KEY (team_lead_id) REFERENCES team (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX IDX_C4E0A61F60984F51 ON team (project_manager_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61FFF2C34BA ON team (team_lead_id)');
        $this->addSql('ALTER TABLE user DROP INDEX UNIQ_8D93D649296CD8AE, ADD INDEX IDX_8D93D649296CD8AE (team_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C4E4CCA1');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C4E4CCA1 FOREIGN KEY (leading_team_id) REFERENCES team (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment CHANGE comment comments LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61F60984F51');
        $this->addSql('ALTER TABLE team DROP FOREIGN KEY FK_C4E0A61FFF2C34BA');
        $this->addSql('DROP INDEX IDX_C4E0A61F60984F51 ON team');
        $this->addSql('DROP INDEX UNIQ_C4E0A61FFF2C34BA ON team');
        $this->addSql('ALTER TABLE team ADD team_leader_id BIGINT NOT NULL, DROP team_lead_id, DROP project_manager_id');
        $this->addSql('ALTER TABLE team ADD CONSTRAINT FK_C4E0A61FFF2C34BA FOREIGN KEY (team_leader_id) REFERENCES user (id) ON UPDATE NO ACTION ON DELETE CASCADE');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_C4E0A61FFF2C34BA ON team (team_leader_id)');
        $this->addSql('ALTER TABLE user DROP INDEX IDX_8D93D649296CD8AE, ADD UNIQUE INDEX UNIQ_8D93D649296CD8AE (team_id)');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649C4E4CCA1');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649C4E4CCA1 FOREIGN KEY (leading_team_id) REFERENCES team (id) ON UPDATE NO ACTION ON DELETE NO ACTION');
    }
}
