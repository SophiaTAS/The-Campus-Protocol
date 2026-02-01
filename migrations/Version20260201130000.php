<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add type_effet, cible, cible_stat to statut_effet';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE statut_effet ADD COLUMN type_effet VARCHAR(50) NOT NULL DEFAULT "degats_tour"');
        $this->addSql('ALTER TABLE statut_effet ADD COLUMN cible VARCHAR(20) NOT NULL DEFAULT "cible"');
        $this->addSql('ALTER TABLE statut_effet ADD COLUMN cible_stat VARCHAR(20) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE statut_effet');
        $this->addSql('CREATE TABLE statut_effet (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(160) NOT NULL, description CLOB NOT NULL, icone VARCHAR(20) NOT NULL, duree INTEGER NOT NULL)');
    }
}
