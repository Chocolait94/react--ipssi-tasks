<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour creer la table "user" (authentification JWT)
 */
final class Version20250107000001 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creation de la table user pour l authentification JWT';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE `user` (
                id INT AUTO_INCREMENT NOT NULL,
                email VARCHAR(180) NOT NULL,
                roles JSON NOT NULL,
                password VARCHAR(255) NOT NULL,
                firstname VARCHAR(100) DEFAULT NULL,
                lastname VARCHAR(100) DEFAULT NULL,
                created_at DATETIME NOT NULL,
                PRIMARY KEY(id),
                UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE `user`');
    }
}
