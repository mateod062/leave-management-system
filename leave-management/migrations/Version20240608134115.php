<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240608134115 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comments (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, leave_request_id BIGINT NOT NULL, parent_comment_id BIGINT DEFAULT NULL, comments LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9474526CA76ED395 (user_id), INDEX IDX_9474526CF2E1C15D (leave_request_id), INDEX IDX_9474526CBF2AF943 (parent_comment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE leave_balances (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, balance INT NOT NULL, year INT NOT NULL, INDEX IDX_EAAB6719A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE leave_requests (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, start_date DATE NOT NULL, end_date DATE NOT NULL, status LONGTEXT NOT NULL, reason LONGTEXT DEFAULT NULL, team_leader_approval TINYINT(1) NOT NULL, project_manager_approval TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_7DC8F778A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notifications (id BIGINT AUTO_INCREMENT NOT NULL, user_id BIGINT NOT NULL, message TINYTEXT NOT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_BF5476CAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teams (id BIGINT AUTO_INCREMENT NOT NULL, team_leader_id BIGINT NOT NULL, name VARCHAR(50) NOT NULL, UNIQUE INDEX UNIQ_C4E0A61FFF2C34BA (team_leader_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id BIGINT AUTO_INCREMENT NOT NULL, leading_team_id BIGINT DEFAULT NULL, team_id BIGINT DEFAULT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(100) NOT NULL, password VARCHAR(255) NOT NULL, role VARCHAR(255) NOT NULL COMMENT \'(DC2Type:user_role)\', UNIQUE INDEX UNIQ_8D93D649F85E0677 (username), UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), UNIQUE INDEX UNIQ_8D93D649C4E4CCA1 (leading_team_id), UNIQUE INDEX UNIQ_8D93D649296CD8AE (team_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_9474526CA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_9474526CF2E1C15D FOREIGN KEY (leave_request_id) REFERENCES leave_requests (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_9474526CBF2AF943 FOREIGN KEY (parent_comment_id) REFERENCES comments (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leave_balances ADD CONSTRAINT FK_EAAB6719A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE leave_requests ADD CONSTRAINT FK_7DC8F778A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_BF5476CAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE teams ADD CONSTRAINT FK_C4E0A61FFF2C34BA FOREIGN KEY (team_leader_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_8D93D649C4E4CCA1 FOREIGN KEY (leading_team_id) REFERENCES teams (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_8D93D649296CD8AE FOREIGN KEY (team_id) REFERENCES teams (id) ON DELETE SET NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_9474526CA76ED395');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_9474526CF2E1C15D');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_9474526CBF2AF943');
        $this->addSql('ALTER TABLE leave_balances DROP FOREIGN KEY FK_EAAB6719A76ED395');
        $this->addSql('ALTER TABLE leave_requests DROP FOREIGN KEY FK_7DC8F778A76ED395');
        $this->addSql('ALTER TABLE notifications DROP FOREIGN KEY FK_BF5476CAA76ED395');
        $this->addSql('ALTER TABLE teams DROP FOREIGN KEY FK_C4E0A61FFF2C34BA');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_8D93D649C4E4CCA1');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_8D93D649296CD8AE');
        $this->addSql('DROP TABLE leave_balances');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE leave_requests');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE teams');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
