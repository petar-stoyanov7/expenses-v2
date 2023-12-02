<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231202143431 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE car_fuels (id INT AUTO_INCREMENT NOT NULL, car_id INT NOT NULL, fuel_id INT NOT NULL, INDEX IDX_1D42AD6FC3C6F69F (car_id), INDEX IDX_1D42AD6F97C79677 (fuel_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE `utf8_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE car_fuels ADD CONSTRAINT FK_1D42AD6FC3C6F69F FOREIGN KEY (car_id) REFERENCES cars (id)');
        $this->addSql('ALTER TABLE car_fuels ADD CONSTRAINT FK_1D42AD6F97C79677 FOREIGN KEY (fuel_id) REFERENCES fuel_types (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE car_fuels DROP FOREIGN KEY FK_1D42AD6FC3C6F69F');
        $this->addSql('ALTER TABLE car_fuels DROP FOREIGN KEY FK_1D42AD6F97C79677');
        $this->addSql('DROP TABLE car_fuels');
    }
}
