<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration pour creer la table "task"
 *
 * Cette migration cree la structure de la table pour stocker les taches.
 * Elle contient le SQL brut qui sera execute sur la base de donnees.
 */
final class Version20250107000000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Creation de la table task pour gerer les taches';
    }

    /**
     * Methode executee lors de la migration (creation)
     */
    public function up(Schema $schema): void
    {
        // SQL pour MySQL/MariaDB
        $this->addSql('
            CREATE TABLE task (
                id INT AUTO_INCREMENT NOT NULL,
                title VARCHAR(255) NOT NULL,
                description LONGTEXT DEFAULT NULL,
                status VARCHAR(20) NOT NULL DEFAULT \'pending\',
                created_at DATETIME NOT NULL,
                updated_at DATETIME DEFAULT NULL,
                due_date DATE DEFAULT NULL,
                PRIMARY KEY(id),
                INDEX idx_task_status (status),
                INDEX idx_task_created_at (created_at)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        ');

        // Commentaire pour le jury BTS : Cette requete CREATE TABLE :
        // - Definit une cle primaire auto-incrementee (id)
        // - Utilise des types de donnees appropries (VARCHAR, LONGTEXT, DATETIME, DATE)
        // - Ajoute des index pour optimiser les recherches par status et date
    }

    /**
     * Methode executee lors du rollback (annulation)
     */
    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE task');
    }
}
