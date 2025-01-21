<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121141650 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE bills (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_expired DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', total_price DOUBLE PRECISION NOT NULL, status SMALLINT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE comments (id INT AUTO_INCREMENT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE events (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, price DOUBLE PRECISION NOT NULL, date DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', nb_places INT NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE images (id INT AUTO_INCREMENT NOT NULL, src VARCHAR(255) NOT NULL, alt VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE post_images (id INT AUTO_INCREMENT NOT NULL, post_id_id INT NOT NULL, image_id_id INT NOT NULL, INDEX IDX_D03D5A0FE85F12B8 (post_id_id), UNIQUE INDEX UNIQ_D03D5A0F68011AFE (image_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE posts (id INT AUTO_INCREMENT NOT NULL, user_id_id INT NOT NULL, title VARCHAR(255) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, INDEX IDX_885DBAFA9D86650F (user_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rental_images (id INT AUTO_INCREMENT NOT NULL, rental_id_id INT NOT NULL, image_id_id INT NOT NULL, INDEX IDX_D031AA0DE4AF10B8 (rental_id_id), UNIQUE INDEX UNIQ_D031AA0D68011AFE (image_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rentals (id INT AUTO_INCREMENT NOT NULL, nb_double_bed INT NOT NULL, nb_simple_bed INT NOT NULL, has_shower TINYINT(1) NOT NULL, has_toilet TINYINT(1) NOT NULL, has_kitchen TINYINT(1) NOT NULL, has_fridge TINYINT(1) NOT NULL, has_heating TINYINT(1) NOT NULL, base_deposit DOUBLE PRECISION NOT NULL, pets_accepted TINYINT(1) NOT NULL, price_per_day DOUBLE PRECISION NOT NULL, is_on_promotion TINYINT(1) NOT NULL, is_active TINYINT(1) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations_events (id INT AUTO_INCREMENT NOT NULL, bill_id_id INT NOT NULL, user_id_id INT NOT NULL, event_id_id INT NOT NULL, date_reservation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_start DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_end DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', is_active TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_63F87C5549B4CBC9 (bill_id_id), INDEX IDX_63F87C559D86650F (user_id_id), INDEX IDX_63F87C553E5F2F7B (event_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reservations_rentals (id INT AUTO_INCREMENT NOT NULL, bill_id_id INT NOT NULL, user_id_id INT NOT NULL, rental_id_id INT NOT NULL, has_cleaning_deposit TINYINT(1) NOT NULL, total_deposit_returned DOUBLE PRECISION NOT NULL, status_base_deposit SMALLINT NOT NULL, date_reservation DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_start DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', date_end DATE NOT NULL COMMENT \'(DC2Type:date_immutable)\', status_reservation SMALLINT NOT NULL, UNIQUE INDEX UNIQ_B894A99649B4CBC9 (bill_id_id), INDEX IDX_B894A9969D86650F (user_id_id), INDEX IDX_B894A996E4AF10B8 (rental_id_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, image_id INT NOT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, firstname VARCHAR(255) NOT NULL, lastname VARCHAR(255) NOT NULL, nb_points INT NOT NULL, account_number VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', last_log_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', is_active TINYINT(1) NOT NULL, is_email_authentificated TINYINT(1) NOT NULL, UNIQUE INDEX UNIQ_1483A5E93DA5256D (image_id), UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE post_images ADD CONSTRAINT FK_D03D5A0FE85F12B8 FOREIGN KEY (post_id_id) REFERENCES posts (id)');
        $this->addSql('ALTER TABLE post_images ADD CONSTRAINT FK_D03D5A0F68011AFE FOREIGN KEY (image_id_id) REFERENCES images (id)');
        $this->addSql('ALTER TABLE posts ADD CONSTRAINT FK_885DBAFA9D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE rental_images ADD CONSTRAINT FK_D031AA0DE4AF10B8 FOREIGN KEY (rental_id_id) REFERENCES rentals (id)');
        $this->addSql('ALTER TABLE rental_images ADD CONSTRAINT FK_D031AA0D68011AFE FOREIGN KEY (image_id_id) REFERENCES images (id)');
        $this->addSql('ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C5549B4CBC9 FOREIGN KEY (bill_id_id) REFERENCES bills (id)');
        $this->addSql('ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C559D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reservations_events ADD CONSTRAINT FK_63F87C553E5F2F7B FOREIGN KEY (event_id_id) REFERENCES events (id)');
        $this->addSql('ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A99649B4CBC9 FOREIGN KEY (bill_id_id) REFERENCES bills (id)');
        $this->addSql('ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A9969D86650F FOREIGN KEY (user_id_id) REFERENCES users (id)');
        $this->addSql('ALTER TABLE reservations_rentals ADD CONSTRAINT FK_B894A996E4AF10B8 FOREIGN KEY (rental_id_id) REFERENCES rentals (id)');
        $this->addSql('ALTER TABLE users ADD CONSTRAINT FK_1483A5E93DA5256D FOREIGN KEY (image_id) REFERENCES images (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE post_images DROP FOREIGN KEY FK_D03D5A0FE85F12B8');
        $this->addSql('ALTER TABLE post_images DROP FOREIGN KEY FK_D03D5A0F68011AFE');
        $this->addSql('ALTER TABLE posts DROP FOREIGN KEY FK_885DBAFA9D86650F');
        $this->addSql('ALTER TABLE rental_images DROP FOREIGN KEY FK_D031AA0DE4AF10B8');
        $this->addSql('ALTER TABLE rental_images DROP FOREIGN KEY FK_D031AA0D68011AFE');
        $this->addSql('ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C5549B4CBC9');
        $this->addSql('ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C559D86650F');
        $this->addSql('ALTER TABLE reservations_events DROP FOREIGN KEY FK_63F87C553E5F2F7B');
        $this->addSql('ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A99649B4CBC9');
        $this->addSql('ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A9969D86650F');
        $this->addSql('ALTER TABLE reservations_rentals DROP FOREIGN KEY FK_B894A996E4AF10B8');
        $this->addSql('ALTER TABLE users DROP FOREIGN KEY FK_1483A5E93DA5256D');
        $this->addSql('DROP TABLE bills');
        $this->addSql('DROP TABLE comments');
        $this->addSql('DROP TABLE events');
        $this->addSql('DROP TABLE images');
        $this->addSql('DROP TABLE post_images');
        $this->addSql('DROP TABLE posts');
        $this->addSql('DROP TABLE rental_images');
        $this->addSql('DROP TABLE rentals');
        $this->addSql('DROP TABLE reservations_events');
        $this->addSql('DROP TABLE reservations_rentals');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
