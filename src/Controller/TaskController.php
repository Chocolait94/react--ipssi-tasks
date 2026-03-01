<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

/**
 * Controller API REST pour la gestion des taches
 *
 * Ce controller expose une API REST complete avec les operations CRUD :
 * - GET    /api/tasks      -> Liste toutes les taches
 * - GET    /api/tasks/{id} -> Recupere une tache par son ID
 * - POST   /api/tasks      -> Cree une nouvelle tache
 * - PUT    /api/tasks/{id} -> Met a jour une tache existante
 * - DELETE /api/tasks/{id} -> Supprime une tache
 *
 * DEUX VERSIONS DE CHAQUE ENDPOINT :
 * - Version "normale" : utilise Doctrine ORM (objets PHP)
 * - Version "/raw"    : utilise des requetes SQL brutes (pour le BTS)
 *
 * Prefixe : toutes les routes commencent par /api/tasks
 */
#[Route('/api/tasks', name: 'api_tasks_')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    // =========================================================================
    // ENDPOINTS UTILISANT DOCTRINE ORM
    // =========================================================================

    /**
     * GET /api/tasks
     * Liste toutes les taches (version Doctrine ORM)
     *
     * Query parameters optionnels :
     * - status : filtre par statut (pending, in_progress, completed)
     * - search : recherche dans le titre et la description
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(Request $request): JsonResponse
    {
        // Recuperation des parametres de requete
        $status = $request->query->get('status');
        $search = $request->query->get('search');

        // Selection de la methode appropriee selon les filtres
        if ($search) {
            $tasks = $this->taskRepository->searchByKeyword($search);
        } elseif ($status) {
            $tasks = $this->taskRepository->findByStatus($status);
        } else {
            $tasks = $this->taskRepository->findAllOrderedByDate();
        }

        // Conversion des entites en tableaux pour la reponse JSON
        $data = array_map(fn(Task $task) => $task->toArray(), $tasks);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * GET /api/tasks/{id}
     * Recupere une tache par son ID (version Doctrine ORM)
     */
    #[Route('/{id}', name: 'show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(int $id): JsonResponse
    {
        // find() est une methode heritee de ServiceEntityRepository
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tache non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $task->toArray(),
        ]);
    }

    /**
     * POST /api/tasks
     * Cree une nouvelle tache (version Doctrine ORM)
     *
     * Body JSON attendu :
     * {
     *   "title": "Ma tache",
     *   "description": "Description optionnelle",
     *   "status": "pending",
     *   "dueDate": "2025-12-31"
     * }
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        // Decodage du JSON envoye dans le corps de la requete
        $data = json_decode($request->getContent(), true);

        // Validation basique
        if (empty($data['title'])) {
            return $this->json([
                'success' => false,
                'message' => 'Le titre est obligatoire',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Creation de l'entite Task
        $task = new Task();
        $task->setTitle($data['title']);

        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }

        if (isset($data['status'])) {
            try {
                $task->setStatus($data['status']);
            } catch (\InvalidArgumentException $e) {
                return $this->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if (isset($data['dueDate'])) {
            $task->setDueDate(new \DateTime($data['dueDate']));
        }

        // Sauvegarde via le repository (utilise EntityManager)
        $this->taskRepository->save($task);

        return $this->json([
            'success' => true,
            'message' => 'Tache creee avec succes',
            'data' => $task->toArray(),
        ], Response::HTTP_CREATED);
    }

    /**
     * PUT /api/tasks/{id}
     * Met a jour une tache existante (version Doctrine ORM)
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function update(int $id, Request $request): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tache non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Mise a jour des champs fournis
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }

        if (array_key_exists('description', $data)) {
            $task->setDescription($data['description']);
        }

        if (isset($data['status'])) {
            try {
                $task->setStatus($data['status']);
            } catch (\InvalidArgumentException $e) {
                return $this->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        if (array_key_exists('dueDate', $data)) {
            $task->setDueDate($data['dueDate'] ? new \DateTime($data['dueDate']) : null);
        }

        // La sauvegarde declenche automatiquement le callback PreUpdate
        $this->taskRepository->save($task);

        return $this->json([
            'success' => true,
            'message' => 'Tache mise a jour avec succes',
            'data' => $task->toArray(),
        ]);
    }

    /**
     * DELETE /api/tasks/{id}
     * Supprime une tache (version Doctrine ORM)
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tache non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->taskRepository->remove($task);

        return $this->json([
            'success' => true,
            'message' => 'Tache supprimee avec succes',
        ]);
    }

    // =========================================================================
    // ENDPOINTS UTILISANT LES REQUETES SQL BRUTES (RAW SQL)
    // Ces endpoints sont identiques fonctionnellement mais utilisent du SQL pur
    // Ils sont parfaits pour demontrer vos competences SQL au jury BTS
    // =========================================================================

    /**
     * GET /api/tasks/raw
     * Liste toutes les taches (version SQL brut)
     */
    #[Route('/raw', name: 'index_raw', methods: ['GET'])]
    public function indexRaw(Request $request): JsonResponse
    {
        $status = $request->query->get('status');
        $search = $request->query->get('search');

        // Utilisation de la methode avec requete SQL brute
        if ($search || $status) {
            $tasks = $this->taskRepository->searchRaw($search, $status);
        } else {
            $tasks = $this->taskRepository->findAllRaw();
        }

        return $this->json([
            'success' => true,
            'count' => count($tasks),
            'data' => $tasks,
            '_info' => 'Cette reponse utilise des requetes SQL brutes',
        ]);
    }

    /**
     * GET /api/tasks/raw/{id}
     * Recupere une tache par son ID (version SQL brut)
     */
    #[Route('/raw/{id}', name: 'show_raw', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function showRaw(int $id): JsonResponse
    {
        $task = $this->taskRepository->findByIdRaw($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tache non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        return $this->json([
            'success' => true,
            'data' => $task,
            '_info' => 'Cette reponse utilise une requete SQL brute SELECT',
        ]);
    }

    /**
     * POST /api/tasks/raw
     * Cree une nouvelle tache (version SQL brut avec INSERT)
     */
    #[Route('/raw', name: 'create_raw', methods: ['POST'])]
    public function createRaw(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return $this->json([
                'success' => false,
                'message' => 'Le titre est obligatoire',
            ], Response::HTTP_BAD_REQUEST);
        }

        $status = $data['status'] ?? Task::STATUS_PENDING;

        // Validation du statut
        if (!in_array($status, Task::getValidStatuses())) {
            return $this->json([
                'success' => false,
                'message' => 'Statut invalide. Valeurs acceptees: ' . implode(', ', Task::getValidStatuses()),
            ], Response::HTTP_BAD_REQUEST);
        }

        $dueDate = isset($data['dueDate']) ? new \DateTime($data['dueDate']) : null;

        // Utilisation de la methode INSERT SQL brute
        $newId = $this->taskRepository->insertRaw(
            $data['title'],
            $data['description'] ?? null,
            $status,
            $dueDate
        );

        // Recuperation de la tache creee pour la reponse
        $task = $this->taskRepository->findByIdRaw($newId);

        return $this->json([
            'success' => true,
            'message' => 'Tache creee avec succes',
            'data' => $task,
            '_info' => 'Cette tache a ete creee avec une requete SQL INSERT brute',
        ], Response::HTTP_CREATED);
    }

    /**
     * PUT /api/tasks/raw/{id}
     * Met a jour une tache (version SQL brut avec UPDATE)
     */
    #[Route('/raw/{id}', name: 'update_raw', methods: ['PUT'], requirements: ['id' => '\d+'])]
    public function updateRaw(int $id, Request $request): JsonResponse
    {
        // Verification de l'existence
        $existingTask = $this->taskRepository->findByIdRaw($id);

        if (!$existingTask) {
            return $this->json([
                'success' => false,
                'message' => 'Tache non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        // Fusion des donnees existantes avec les nouvelles
        $title = $data['title'] ?? $existingTask['title'];
        $description = array_key_exists('description', $data) ? $data['description'] : $existingTask['description'];
        $status = $data['status'] ?? $existingTask['status'];
        $dueDate = array_key_exists('dueDate', $data)
            ? ($data['dueDate'] ? new \DateTime($data['dueDate']) : null)
            : ($existingTask['dueDate'] ? new \DateTime($existingTask['dueDate']) : null);

        // Validation du statut
        if (!in_array($status, Task::getValidStatuses())) {
            return $this->json([
                'success' => false,
                'message' => 'Statut invalide. Valeurs acceptees: ' . implode(', ', Task::getValidStatuses()),
            ], Response::HTTP_BAD_REQUEST);
        }

        // Utilisation de la methode UPDATE SQL brute
        $affectedRows = $this->taskRepository->updateRaw($id, $title, $description, $status, $dueDate);

        $task = $this->taskRepository->findByIdRaw($id);

        return $this->json([
            'success' => true,
            'message' => 'Tache mise a jour avec succes',
            'data' => $task,
            '_info' => "Cette tache a ete mise a jour avec une requete SQL UPDATE brute ({$affectedRows} ligne(s) affectee(s))",
        ]);
    }

    /**
     * DELETE /api/tasks/raw/{id}
     * Supprime une tache (version SQL brut avec DELETE)
     */
    #[Route('/raw/{id}', name: 'delete_raw', methods: ['DELETE'], requirements: ['id' => '\d+'])]
    public function deleteRaw(int $id): JsonResponse
    {
        // Verification de l'existence avant suppression
        $existingTask = $this->taskRepository->findByIdRaw($id);

        if (!$existingTask) {
            return $this->json([
                'success' => false,
                'message' => 'Tache non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        // Utilisation de la methode DELETE SQL brute
        $affectedRows = $this->taskRepository->deleteRaw($id);

        return $this->json([
            'success' => true,
            'message' => 'Tache supprimee avec succes',
            '_info' => "Cette tache a ete supprimee avec une requete SQL DELETE brute ({$affectedRows} ligne(s) supprimee(s))",
        ]);
    }

    // =========================================================================
    // ENDPOINT BONUS : STATISTIQUES
    // =========================================================================

    /**
     * GET /api/tasks/stats
     * Retourne des statistiques sur les taches (utilise SQL brut avec GROUP BY)
     */
    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function stats(): JsonResponse
    {
        $stats = $this->taskRepository->countByStatusRaw();

        // Transformation en format plus lisible
        $formattedStats = [];
        $total = 0;

        foreach ($stats as $stat) {
            $formattedStats[$stat['status']] = (int) $stat['count'];
            $total += (int) $stat['count'];
        }

        return $this->json([
            'success' => true,
            'data' => [
                'total' => $total,
                'byStatus' => $formattedStats,
            ],
            '_info' => 'Ces statistiques utilisent une requete SQL avec COUNT et GROUP BY',
        ]);
    }
}
