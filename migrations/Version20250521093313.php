<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250521093313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE category (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(100) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE event (id INT AUTO_INCREMENT NOT NULL, organizer_id INT NOT NULL, name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, date_event DATETIME NOT NULL, adress VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, capacity INT DEFAULT NULL, INDEX IDX_3BAE0AA7876C4DDA (organizer_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE instant_message (id INT AUTO_INCREMENT NOT NULL, sender_id INT NOT NULL, receiver_id INT NOT NULL, content LONGTEXT NOT NULL, date_message DATETIME NOT NULL, INDEX IDX_D047C08AF624B39D (sender_id), INDEX IDX_D047C08ACD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE level_run (id INT AUTO_INCREMENT NOT NULL, level INT NOT NULL, description VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE photo (id INT AUTO_INCREMENT NOT NULL, event_id INT NOT NULL, url VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, INDEX IDX_14B7841871F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE post (id INT AUTO_INCREMENT NOT NULL, topic_id INT NOT NULL, user_id INT NOT NULL, message LONGTEXT NOT NULL, date_message DATETIME NOT NULL, INDEX IDX_5A8A6C8D1F55203D (topic_id), INDEX IDX_5A8A6C8DA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE registration_event (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, quantity INT NOT NULL, INDEX IDX_B404AA4FA76ED395 (user_id), INDEX IDX_B404AA4F71F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE topic (id INT AUTO_INCREMENT NOT NULL, category_id INT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, date_creation DATETIME NOT NULL, is_closed TINYINT(1) NOT NULL, INDEX IDX_9D40DE1B12469DE2 (category_id), INDEX IDX_9D40DE1BA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, level_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, last_name VARCHAR(100) NOT NULL, first_name VARCHAR(100) NOT NULL, date_of_register DATETIME NOT NULL, date_of_birth DATETIME NOT NULL, is_verified TINYINT(1) NOT NULL, is_banned TINYINT(1) NOT NULL, picture_profil VARCHAR(255) NOT NULL, postal_code VARCHAR(10) NOT NULL, city VARCHAR(100) NOT NULL, bio LONGTEXT DEFAULT NULL, INDEX IDX_8D93D6495FB14BA7 (level_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE event ADD CONSTRAINT FK_3BAE0AA7876C4DDA FOREIGN KEY (organizer_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE instant_message ADD CONSTRAINT FK_D047C08AF624B39D FOREIGN KEY (sender_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE instant_message ADD CONSTRAINT FK_D047C08ACD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE photo ADD CONSTRAINT FK_14B7841871F7E88B FOREIGN KEY (event_id) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8D1F55203D FOREIGN KEY (topic_id) REFERENCES topic (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post ADD CONSTRAINT FK_5A8A6C8DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE registration_event ADD CONSTRAINT FK_B404AA4FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE registration_event ADD CONSTRAINT FK_B404AA4F71F7E88B FOREIGN KEY (event_id) REFERENCES event (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1B12469DE2 FOREIGN KEY (category_id) REFERENCES category (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic ADD CONSTRAINT FK_9D40DE1BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user ADD CONSTRAINT FK_8D93D6495FB14BA7 FOREIGN KEY (level_id) REFERENCES level_run (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE event DROP FOREIGN KEY FK_3BAE0AA7876C4DDA
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE instant_message DROP FOREIGN KEY FK_D047C08AF624B39D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE instant_message DROP FOREIGN KEY FK_D047C08ACD53EDB6
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE photo DROP FOREIGN KEY FK_14B7841871F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8D1F55203D
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE post DROP FOREIGN KEY FK_5A8A6C8DA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE registration_event DROP FOREIGN KEY FK_B404AA4FA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE registration_event DROP FOREIGN KEY FK_B404AA4F71F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1B12469DE2
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE topic DROP FOREIGN KEY FK_9D40DE1BA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE user DROP FOREIGN KEY FK_8D93D6495FB14BA7
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE category
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE instant_message
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE level_run
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE photo
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE post
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE registration_event
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE topic
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE user
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
