<?php

namespace App\Repository;

use App\Entity\Task;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\DBAL\Connection;
use Doctrine\Persistence\ManagerRegistry;

/**
 * Repository pour l'entite Task
 *
 * Ce repository demontre DEUX approches differentes :
 * 1. L'utilisation de Doctrine ORM (methodes heritees + QueryBuilder)
 * 2. L'utilisation de requetes SQL brutes (Raw SQL) via DBAL
 *
 * IMPORTANT POUR LE BTS SIO :
 * Les methodes avec le suffixe "Raw" utilisent des requetes SQL ecrites a la main.
 * Cela permet de demontrer vos competences en SQL lors du passage devant le jury.
 *
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
        // On recupere la connexion DBAL pour les requetes SQL brutes
        $this->connection = $registry->getConnection();
    }

    // =========================================================================
    // METHODES DOCTRINE ORM CLASSIQUES
    // Ces methodes utilisent l'ORM Doctrine (plus simple, plus securise)
    // =========================================================================

    /**
     * Trouve toutes les taches avec tri par date de creation (plus recente en premier)
     * Utilise le QueryBuilder de Doctrine
     */
    public function findAllOrderedByDate(): array
    {
        return $this->createQueryBuilder('t')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouve les taches par statut
     * Utilise le QueryBuilder de Doctrine
     */
    public function findByStatus(string $status): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.status = :status')
            ->setParameter('status', $status)
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Recherche de taches par mot-cle dans le titre ou la description
     * Utilise le QueryBuilder de Doctrine
     */
    public function searchByKeyword(string $keyword): array
    {
        return $this->createQueryBuilder('t')
            ->where('t.title LIKE :keyword')
            ->orWhere('t.description LIKE :keyword')
            ->setParameter('keyword', '%' . $keyword . '%')
            ->orderBy('t.createdAt', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Sauvegarde une tache (insertion ou mise a jour)
     * Utilise l'EntityManager de Doctrine
     */
    public function save(Task $task, bool $flush = true): void
    {
        $this->getEntityManager()->persist($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Supprime une tache
     * Utilise l'EntityManager de Doctrine
     */
    public function remove(Task $task, bool $flush = true): void
    {
        $this->getEntityManager()->remove($task);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    // =========================================================================
    // METHODES AVEC REQUETES SQL BRUTES (RAW SQL)
    // Ces methodes demontrent la maitrise du SQL pour le jury BTS
    // =========================================================================

    /**
     * SELECT - Recupere toutes les taches avec une requete SQL brute
     *
     * Cette methode demontre :
     * - L'ecriture d'une requete SELECT complete
     * - L'utilisation de ORDER BY
     * - La recuperation des resultats sous forme de tableau associatif
     *
     * @return array Tableau de tableaux associatifs (pas d'objets Task)
     */
    public function findAllRaw(): array
    {
        $sql = "
            SELECT
                id,
                title,
                description,
                status,
                created_at AS createdAt,
                updated_at AS updatedAt,
                due_date AS dueDate
            FROM task
            ORDER BY created_at DESC
        ";

        // executeQuery() pour les SELECT
        $result = $this->connection->executeQuery($sql);

        // fetchAllAssociative() retourne un tableau de tableaux associatifs
        return $result->fetchAllAssociative();
    }

    /**
     * SELECT avec parametre - Recupere une tache par son ID
     *
     * Cette methode demontre :
     * - L'utilisation de parametres prepares (securite contre injection SQL)
     * - La clause WHERE avec un parametre nomme
     *
     * @return array|null Tableau associatif ou null si non trouve
     */
    public function findByIdRaw(int $id): ?array
    {
        $sql = "
            SELECT
                id,
                title,
                description,
                status,
                created_at AS createdAt,
                updated_at AS updatedAt,
                due_date AS dueDate
            FROM task
            WHERE id = :id
        ";

        $result = $this->connection->executeQuery($sql, ['id' => $id]);
        $task = $result->fetchAssociative();

        // fetchAssociative() retourne false si aucun resultat
        return $task ?: null;
    }

    /**
     * INSERT - Insere une nouvelle tache avec une requete SQL brute
     *
     * Cette methode demontre :
     * - L'ecriture d'une requete INSERT INTO
     * - L'utilisation de parametres nommes pour la securite
     * - La recuperation de l'ID auto-genere (lastInsertId)
     *
     * @return int L'ID de la tache inseree
     */
    public function insertRaw(string $title, ?string $description, string $status, ?\DateTimeInterface $dueDate = null): int
    {
        $sql = "
            INSERT INTO task (title, description, status, created_at, due_date)
            VALUES (:title, :description, :status, :createdAt, :dueDate)
        ";

        $params = [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'dueDate' => $dueDate?->format('Y-m-d'),
        ];

        // executeStatement() pour INSERT, UPDATE, DELETE
        $this->connection->executeStatement($sql, $params);

        // Retourne l'ID genere automatiquement
        return (int) $this->connection->lastInsertId();
    }

    /**
     * UPDATE - Met a jour une tache avec une requete SQL brute
     *
     * Cette methode demontre :
     * - L'ecriture d'une requete UPDATE avec SET
     * - La clause WHERE pour cibler un enregistrement specifique
     * - La mise a jour automatique du champ updated_at
     *
     * @return int Nombre de lignes affectees (0 ou 1)
     */
    public function updateRaw(int $id, string $title, ?string $description, string $status, ?\DateTimeInterface $dueDate = null): int
    {
        $sql = "
            UPDATE task
            SET
                title = :title,
                description = :description,
                status = :status,
                updated_at = :updatedAt,
                due_date = :dueDate
            WHERE id = :id
        ";

        $params = [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'updatedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
            'dueDate' => $dueDate?->format('Y-m-d'),
        ];

        // executeStatement() retourne le nombre de lignes affectees
        return $this->connection->executeStatement($sql, $params);
    }

    /**
     * DELETE - Supprime une tache avec une requete SQL brute
     *
     * Cette methode demontre :
     * - L'ecriture d'une requete DELETE
     * - L'importance de la clause WHERE (sans elle, toutes les donnees seraient supprimees !)
     *
     * @return int Nombre de lignes supprimees (0 ou 1)
     */
    public function deleteRaw(int $id): int
    {
        $sql = "DELETE FROM task WHERE id = :id";

        return $this->connection->executeStatement($sql, ['id' => $id]);
    }

    /**
     * SELECT avec filtres multiples - Exemple de requete plus complexe
     *
     * Cette methode demontre :
     * - Construction dynamique d'une requete SQL
     * - Utilisation de LIKE pour la recherche textuelle
     * - Conditions multiples avec AND
     */
    public function searchRaw(?string $keyword = null, ?string $status = null): array
    {
        $sql = "
            SELECT
                id,
                title,
                description,
                status,
                created_at AS createdAt,
                updated_at AS updatedAt,
                due_date AS dueDate
            FROM task
            WHERE 1=1
        ";

        $params = [];

        // Ajout conditionnel des filtres
        if ($keyword !== null && $keyword !== '') {
            $sql .= " AND (title LIKE :keyword OR description LIKE :keyword)";
            $params['keyword'] = '%' . $keyword . '%';
        }

        if ($status !== null && $status !== '') {
            $sql .= " AND status = :status";
            $params['status'] = $status;
        }

        $sql .= " ORDER BY created_at DESC";

        $result = $this->connection->executeQuery($sql, $params);
        return $result->fetchAllAssociative();
    }

    /**
     * Compte le nombre de taches par statut
     *
     * Cette methode demontre :
     * - L'utilisation de COUNT() et GROUP BY
     * - Les fonctions d'agregation SQL
     */
    public function countByStatusRaw(): array
    {
        $sql = "
            SELECT
                status,
                COUNT(*) AS count
            FROM task
            GROUP BY status
        ";

        $result = $this->connection->executeQuery($sql);
        return $result->fetchAllAssociative();
    }
}
