<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260131170000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create type, creature, attaque, statut_effet, type_multiplicateur tables';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE type (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(120) NOT NULL, icone VARCHAR(20) NOT NULL, couleur VARCHAR(20) NOT NULL)');
        $this->addSql('CREATE TABLE statut_effet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(160) NOT NULL, description CLOB NOT NULL, icone VARCHAR(20) NOT NULL, duree INTEGER NOT NULL)');
        $this->addSql('CREATE TABLE creature (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type_id INTEGER NOT NULL, nom VARCHAR(160) NOT NULL, pv_max INTEGER NOT NULL, attaque INTEGER NOT NULL, defens INTEGER NOT NULL, image VARCHAR(255) NOT NULL, CONSTRAINT FK_4C97C0EAC54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_4C97C0EAC54C8C93 ON creature (type_id)');
        $this->addSql('CREATE TABLE attaque (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, creature_id INTEGER NOT NULL, statut_effet_id INTEGER DEFAULT NULL, nom VARCHAR(160) NOT NULL, degats INTEGER NOT NULL, chance DOUBLE PRECISION NOT NULL, CONSTRAINT FK_B560B20073154ED4 FOREIGN KEY (creature_id) REFERENCES creature (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_B560B200A3E8DBAF FOREIGN KEY (statut_effet_id) REFERENCES statut_effet (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_B560B20073154ED4 ON attaque (creature_id)');
        $this->addSql('CREATE INDEX IDX_B560B200A3E8DBAF ON attaque (statut_effet_id)');
        $this->addSql('CREATE TABLE type_multiplicateur (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, type_source_id INTEGER NOT NULL, type_cible_id INTEGER NOT NULL, multiplicateur DOUBLE PRECISION NOT NULL, CONSTRAINT FK_E60BB4DEB0A64A26 FOREIGN KEY (type_source_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE, CONSTRAINT FK_E60BB4DE24FA0D8F FOREIGN KEY (type_cible_id) REFERENCES type (id) NOT DEFERRABLE INITIALLY IMMEDIATE)');
        $this->addSql('CREATE INDEX IDX_E60BB4DEB0A64A26 ON type_multiplicateur (type_source_id)');
        $this->addSql('CREATE INDEX IDX_E60BB4DE24FA0D8F ON type_multiplicateur (type_cible_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE attaque');
        $this->addSql('DROP TABLE creature');
        $this->addSql('DROP TABLE type_multiplicateur');
        $this->addSql('DROP TABLE statut_effet');
        $this->addSql('DROP TABLE type');
    }
}
