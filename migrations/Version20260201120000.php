<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260201120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create arenes table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE arenes (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, nom VARCHAR(100) NOT NULL, inspiration VARCHAR(50) DEFAULT NULL, style VARCHAR(50) DEFAULT NULL, image_path VARCHAR(255) DEFAULT NULL)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE arenes');
    }
}
