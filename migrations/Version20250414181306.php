<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250414181306 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bills (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', total_price DOUBLE PRECISION NOT NULL, status SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, rentals_id INT NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', rating INT NOT NULL, is_active TINYINT(1) NOT NULL, INDEX IDX_5F9E962AA76ED395 (user_id), INDEX IDX_5F9E962AA564EA6A (rentals_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', nb_places INT NOT NULL, is_active TINYINT(1) NOT NULL, age_requirement INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', location VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, posts_id INT DEFAULT NULL, rentals_id INT DEFAULT NULL, events_id INT DEFAULT NULL, src VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, is_home_page TINYINT(1) NOT NULL, INDEX IDX_E01FBE6AD5E258C5 (posts_id), INDEX IDX_E01FBE6AA564EA6A (rentals_id), INDEX IDX_E01FBE6A9D6A1065 (events_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, INDEX IDX_885DBAFA9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rentals (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, nb_double_bed INT NOT NULL, nb_simple_bed INT NOT NULL, has_shower TINYINT(1) NOT NULL, has_toilet TINYINT(1) NOT NULL, has_kitchen TINYINT(1) NOT NULL, has_fridge TINYINT(1) NOT NULL, has_heating TINYINT(1) NOT NULL, pets_accepted TINYINT(1) NOT NULL, price_per_day DOUBLE PRECISION NOT NULL, is_on_promotion TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, content LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations_events (id INT AUTO_INCREMENT NOT NULL, bill_id_id INT NOT NULL, user_id_id INT NOT NULL, event_id_id INT NOT NULL, date_reservation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_start DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_63F87C5549B4CBC9 (bill_id_id), INDEX IDX_63F87C559D86650F (user_id_id), INDEX IDX_63F87C553E5F2F7B (event_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations_rentals (id INT AUTO_INCREMENT NOT NULL, bill_id_id INT NOT NULL, user_id INT NOT NULL, rentals_id INT NOT NULL, has_cleaning_deposit TINYINT(1) NOT NULL, total_deposit_returned DOUBLE PRECISION NOT NULL, status_base_deposit SMALLINT NOT NULL, date_reservation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_start DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_end DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', status_reservation SMALLINT NOT NULL, UNIQUE INDEX UNIQ_B894A99649B4CBC9 (bill_id_id), INDEX IDX_B894A996A76ED395 (user_id), INDEX IDX_B894A996A564EA6A (rentals_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, image_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, nb_points INT NOT NULL, account_number VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_log_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, is_email_authentificated TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E93DA5256D (image_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE comments ADD CONSTRAINT FK_5F9E962AA564EA6A FOREIGN KEY (rentals_id) REFERENCES rentals (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AD5E258C5 FOREIGN KEY (posts_id) REFERENCES posts (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6AA564EA6A FOREIGN KEY (rentals_id) REFERENCES rentals (id)');
        $this->addSql('ALTER TABLE images ADD CONSTRAINT FK_E01FBE6A9D6A1065 FOREIGN KEY (events_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C5549B4CBC9 FOREIGN KEY (bill_id_id) REFERENCES bills (id)');
        $this->addSql('ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C559D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C553E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A99649B4CBC9 FOREIGN KEY (bill_id_id) REFERENCES bills (id)');
        $this->addSql('ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A996A76ED395 FOREIGN KEY (user_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A996A564EA6A FOREIGN KEY (rentals_id) REFERENCES rentals (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E93DA5256D FOREIGN KEY (image_id) REFERENCES images (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AA76ED395');
        $this->addSql('ALTER TABLE comments DROP FOREIGN KEY FK_5F9E962AA564EA6A');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6AD5E258C5');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6AA564EA6A');
        $this->addSql('ALTER TABLE images DROP FOREIGN KEY FK_E01FBE6A9D6A1065');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA9D86650F');
        $this->addSql('ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C5549B4CBC9');
        $this->addSql('ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C559D86650F');
        $this->addSql('ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C553E5F2F7B');
        $this->addSql('ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A99649B4CBC9');
        $this->addSql('ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A996A76ED395');
        $this->addSql('ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A996A564EA6A');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93DA5256D');
        $this->addSql('DROP TABLE bills');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE rentals');
        $this->addSql('DROP TABLE reservations_events');
        $this->addSql('DROP TABLE reservations_rentals');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
