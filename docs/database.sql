-- ============================================================================
-- SCRIPT SQL DE CREATION DE LA BASE DE DONNEES
-- API Demo Tasks - BTS SIO SLAM
-- ============================================================================

-- Ce script peut etre execute directement dans phpMyAdmin ou MySQL Workbench
-- pour creer la base de donnees et la table necessaires au projet.

-- ============================================================================
-- 1. CREATION DE LA BASE DE DONNEES
-- ============================================================================

CREATE DATABASE IF NOT EXISTS api_demo_tasks
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE api_demo_tasks;

-- ============================================================================
-- 2. CREATION DE LA TABLE TASK
-- ============================================================================

-- Suppression de la table si elle existe (pour reset)
DROP TABLE IF EXISTS task;

-- Creation de la table task
CREATE TABLE task (
    -- Cle primaire auto-incrementee
    id INT AUTO_INCREMENT NOT NULL,

    -- Titre de la tache (obligatoire)
    title VARCHAR(255) NOT NULL,

    -- Description detaillee (optionnelle)
    description LONGTEXT DEFAULT NULL,

    -- Statut de la tache : 'pending', 'in_progress', 'completed'
    status VARCHAR(20) NOT NULL DEFAULT 'pending',

    -- Date de creation (remplie automatiquement)
    created_at DATETIME NOT NULL,

    -- Date de derniere modification (remplie automatiquement)
    updated_at DATETIME DEFAULT NULL,

    -- Date d'echeance (optionnelle)
    due_date DATE DEFAULT NULL,

    -- Definition de la cle primaire
    PRIMARY KEY(id),

    -- Index pour optimiser les recherches par statut
    INDEX idx_task_status (status),

    -- Index pour optimiser le tri par date de creation
    INDEX idx_task_created_at (created_at)

) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- ============================================================================
-- 3. INSERTION DE DONNEES DE TEST
-- ============================================================================

INSERT INTO task (title, description, status, created_at, due_date) VALUES
    ('Configurer l environnement de developpement',
     'Installer WAMP, Composer, et configurer PHP 8.2',
     'completed',
     '2025-01-01 09:00:00',
     '2025-01-05'),

    ('Creer l entite Task',
     'Definir les proprietes et les annotations Doctrine',
     'completed',
     '2025-01-02 10:30:00',
     '2025-01-06'),

    ('Implementer le CRUD',
     'Creer les methodes Create, Read, Update, Delete dans le controller',
     'in_progress',
     '2025-01-03 14:00:00',
     '2025-01-10'),

    ('Ecrire les requetes SQL brutes',
     'Demontrer l utilisation de raw queries pour le jury BTS',
     'in_progress',
     '2025-01-04 11:00:00',
     '2025-01-12'),

    ('Tester l API avec Postman',
     'Verifier tous les endpoints et documenter les tests',
     'pending',
     '2025-01-05 09:00:00',
     '2025-01-15'),

    ('Preparer la soutenance',
     'Creer le support de presentation et repeter',
     'pending',
     '2025-01-06 08:00:00',
     '2025-01-20');

-- ============================================================================
-- 4. REQUETES SQL UTILES POUR LE JURY BTS
-- ============================================================================

-- Ces requetes sont des exemples que vous pouvez presenter au jury
-- pour demontrer votre maitrise du SQL

-- SELECT : Lister toutes les taches
SELECT * FROM task ORDER BY created_at DESC;

-- SELECT avec WHERE : Filtrer par statut
SELECT * FROM task WHERE status = 'pending';

-- SELECT avec LIKE : Recherche textuelle
SELECT * FROM task WHERE title LIKE '%API%' OR description LIKE '%API%';

-- SELECT avec COUNT et GROUP BY : Statistiques
SELECT status, COUNT(*) AS nombre FROM task GROUP BY status;

-- UPDATE : Modifier le statut d'une tache
-- UPDATE task SET status = 'completed', updated_at = NOW() WHERE id = 3;

-- DELETE : Supprimer une tache
-- DELETE FROM task WHERE id = 6;

-- ============================================================================
-- 5. TABLE POUR DOCTRINE MIGRATIONS (optionnel)
-- ============================================================================

-- Cette table est utilisee par Doctrine pour tracker les migrations executees
CREATE TABLE IF NOT EXISTS doctrine_migration_versions (
    version VARCHAR(191) NOT NULL,
    executed_at DATETIME DEFAULT NULL,
    execution_time INT DEFAULT NULL,
    PRIMARY KEY(version)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;

-- Marquer notre migration comme executee
INSERT INTO doctrine_migration_versions (version, executed_at, execution_time)
VALUES ('DoctrineMigrations\\Version20250107000000', NOW(), 100);
