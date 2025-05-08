<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250507122953 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE bills (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', total_price DOUBLE PRECISION NOT NULL, status SMALLINT NOT NULL, INDEX IDX_22775DD0A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, rentals_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', rating INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_5F9E962AA76ED395 (user_id), INDEX IDX_5F9E962AA564EA6A (rentals_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', nb_places INT NOT NULL, is_active TINYINT(1) NOT NULL, age_requirement INT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', location VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, posts_id INT DEFAULT NULL, rentals_id INT DEFAULT NULL, events_id INT DEFAULT NULL, src VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, is_home_page TINYINT(1) NOT NULL, INDEX IDX_E01FBE6AD5E258C5 (posts_id), INDEX IDX_E01FBE6AA564EA6A (rentals_id), INDEX IDX_E01FBE6A9D6A1065 (events_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_active TINYINT(1) NOT NULL, INDEX IDX_885DBAFAA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE rentals (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, nb_double_bed INT NOT NULL, nb_simple_bed INT NOT NULL, has_shower TINYINT(1) NOT NULL, has_toilet TINYINT(1) NOT NULL, has_kitchen TINYINT(1) NOT NULL, has_fridge TINYINT(1) NOT NULL, has_heating TINYINT(1) NOT NULL, pets_accepted TINYINT(1) NOT NULL, price_per_day DOUBLE PRECISION NOT NULL, is_on_promotion TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservations_events (id INT AUTO_INCREMENT NOT NULL, bill_id INT NOT NULL, user_id INT NOT NULL, event_id INT NOT NULL, date_reservation DATE NOT NULL COMMENT '(DC2Type:date_immutable)', is_active TINYINT(1) NOT NULL, nb_places INT NOT NULL, total_deposit DOUBLE PRECISION NOT NULL, INDEX IDX_63F87C551A8C12F5 (bill_id), INDEX IDX_63F87C55A76ED395 (user_id), INDEX IDX_63F87C5571F7E88B (event_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE reservations_rentals (id INT AUTO_INCREMENT NOT NULL, bill_id INT NOT NULL, user_id INT NOT NULL, rentals_id INT NOT NULL, has_cleaning_deposit TINYINT(1) NOT NULL, total_deposit_returned DOUBLE PRECISION NOT NULL, status_base_deposit SMALLINT NOT NULL, date_reservation DATE NOT NULL COMMENT '(DC2Type:date_immutable)', date_start DATE NOT NULL COMMENT '(DC2Type:date_immutable)', date_end DATE NOT NULL COMMENT '(DC2Type:date_immutable)', status_reservation TINYINT(1) NOT NULL, total_price DOUBLE PRECISION NOT NULL, INDEX IDX_B894A9961A8C12F5 (bill_id), INDEX IDX_B894A996A76ED395 (user_id), INDEX IDX_B894A996A564EA6A (rentals_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, nb_points INT NOT NULL, account_number VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', updated_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', last_log_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', is_active TINYINT(1) NOT NULL, is_email_authentificated TINYINT(1) NOT NULL, birth_date DATE NOT NULL COMMENT '(DC2Type:date_immutable)', UNIQUE INDEX UNIQ_1483A5E93DA5256D (image_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', available_at DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)', delivered_at DATETIME DEFAULT NULL COMMENT '(DC2Type:datetime_immutable)', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE bills ADD CONSTRAINT FK_22775DD0A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA564EA6A FOREIGN KEY (rentals_id) REFERENCES rentals (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AD5E258C5 FOREIGN KEY (posts_id) REFERENCES posts (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AA564EA6A FOREIGN KEY (rentals_id) REFERENCES rentals (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images ADD CONSTRAINT FK_E01FBE6A9D6A1065 FOREIGN KEY (events_id) REFERENCES events (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts ADD CONSTRAINT FK_885DBAFAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C551A8C12F5 FOREIGN KEY (bill_id) REFERENCES bills (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C55A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C5571F7E88B FOREIGN KEY (event_id) REFERENCES events (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A9961A8C12F5 FOREIGN KEY (bill_id) REFERENCES bills (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A996A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A996A564EA6A FOREIGN KEY (rentals_id) REFERENCES rentals (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users ADD CONSTRAINT FK_1483A5E93DA5256D FOREIGN KEY (image_id) REFERENCES images (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            ALTER TABLE bills DROP FOREIGN KEY FK_22775DD0A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AA564EA6A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6AD5E258C5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6AA564EA6A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6A9D6A1065
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFAA76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C551A8C12F5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C55A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C5571F7E88B
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A9961A8C12F5
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A996A76ED395
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A996A564EA6A
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93DA5256D
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE bills
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE comments
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE images
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE posts
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE rentals
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservations_events
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE reservations_rentals
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE users
        SQL);
        $this->addSql(<<<'SQL'
            DROP TABLE messenger_messages
        SQL);
    }
}
