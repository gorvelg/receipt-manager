<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240303211220 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE user_home (user_id INT NOT NULL, home_id INT NOT NULL, INDEX IDX_B574019A76ED395 (user_id), INDEX IDX_B57401928CDC89C (home_id), PRIMARY KEY(user_id, home_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_home ADD CONSTRAINT FK_B574019A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_home ADD CONSTRAINT FK_B57401928CDC89C FOREIGN KEY (home_id) REFERENCES home (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_home DROP FOREIGN KEY FK_B574019A76ED395');
        $this->addSql('ALTER TABLE user_home DROP FOREIGN KEY FK_B57401928CDC89C');
        $this->addSql('DROP TABLE user_home');
    }
}
