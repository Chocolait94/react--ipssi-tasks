<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

/**
 * Fixtures pour charger des donnees de test dans la table Task
 *
 * Les fixtures permettent de peupler la base de donnees avec des donnees
 * de test de maniere reproductible. C'est tres utile pour :
 * - Le developpement (avoir des donnees pour tester)
 * - Les tests automatises
 * - Les demonstrations
 *
 * Commande pour executer les fixtures :
 *   php bin/console doctrine:fixtures:load
 *
 * ATTENTION : Cette commande SUPPRIME toutes les donnees existantes !
 * Pour ajouter sans supprimer, utilisez l'option --append
 */
class TaskFixtures extends Fixture
{
    /**
     * Methode principale appelee par Doctrine pour charger les fixtures
     */
    public function load(ObjectManager $manager): void
    {
        // Tableau de donnees de test
        // Chaque element represente une tache a creer
        $tasksData = [
            [
                'title' => 'Configurer l\'environnement de developpement',
                'description' => 'Installer WAMP, Composer, et configurer PHP 8.2. Verifier que MySQL fonctionne correctement.',
                'status' => Task::STATUS_COMPLETED,
                'dueDate' => '+5 days',
                'createdAt' => '-6 days',
            ],
            [
                'title' => 'Creer l\'entite Task avec Doctrine',
                'description' => 'Definir les proprietes (id, title, description, status, dates) et les annotations ORM pour le mapping objet-relationnel.',
                'status' => Task::STATUS_COMPLETED,
                'dueDate' => '+4 days',
                'createdAt' => '-5 days',
            ],
            [
                'title' => 'Implementer le repository avec Raw SQL',
                'description' => 'Creer les methodes CRUD en utilisant des requetes SQL brutes (SELECT, INSERT, UPDATE, DELETE) pour demontrer les competences SQL au jury BTS.',
                'status' => Task::STATUS_COMPLETED,
                'dueDate' => '+3 days',
                'createdAt' => '-4 days',
            ],
            [
                'title' => 'Developper le controller API REST',
                'description' => 'Creer les endpoints GET, POST, PUT, DELETE avec des reponses JSON. Implementer la validation des donnees entrantes.',
                'status' => Task::STATUS_IN_PROGRESS,
                'dueDate' => '+7 days',
                'createdAt' => '-3 days',
            ],
            [
                'title' => 'Tester l\'API avec Postman',
                'description' => 'Creer une collection Postman avec tous les endpoints. Documenter les tests et les reponses attendues.',
                'status' => Task::STATUS_IN_PROGRESS,
                'dueDate' => '+10 days',
                'createdAt' => '-2 days',
            ],
            [
                'title' => 'Ajouter l\'authentification JWT',
                'description' => 'Securiser l\'API avec des tokens JWT. Implementer login, register et la protection des routes.',
                'status' => Task::STATUS_PENDING,
                'dueDate' => '+14 days',
                'createdAt' => '-1 day',
            ],
            [
                'title' => 'Ecrire la documentation technique',
                'description' => 'Rediger la documentation de l\'API, les diagrammes UML et le dossier technique pour le jury.',
                'status' => Task::STATUS_PENDING,
                'dueDate' => '+20 days',
                'createdAt' => 'now',
            ],
            [
                'title' => 'Preparer la soutenance BTS',
                'description' => 'Creer le support de presentation PowerPoint, preparer les demonstrations et repeter le passage devant le jury.',
                'status' => Task::STATUS_PENDING,
                'dueDate' => '+30 days',
                'createdAt' => 'now',
            ],
        ];

        // Boucle de creation des entites Task
        foreach ($tasksData as $index => $taskData) {
            // Creation d'une nouvelle instance de Task
            $task = new Task();

            // Hydratation de l'entite avec les donnees
            $task->setTitle($taskData['title']);
            $task->setDescription($taskData['description']);
            $task->setStatus($taskData['status']);

            // Gestion des dates relatives (ex: '+5 days', '-2 days', 'now')
            if ($taskData['dueDate']) {
                $task->setDueDate(new \DateTime($taskData['dueDate']));
            }

            // Date de creation (normalement geree par PrePersist, mais on la force ici)
            $task->setCreatedAt(new \DateTime($taskData['createdAt']));

            // Persist : indique a Doctrine de gerer cette entite
            // A ce stade, rien n'est encore ecrit en base de donnees
            $manager->persist($task);

            // Optionnel : ajouter une reference pour d'autres fixtures
            // Utile si on a des relations entre entites
            $this->addReference('task_' . $index, $task);
        }

        // Flush : execute reellement les requetes INSERT en base de donnees
        // C'est ici que Doctrine genere et execute le SQL
        $manager->flush();
    }
}
