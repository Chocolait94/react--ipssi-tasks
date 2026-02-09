# Guide d'Installation Complet - API Tasks

Ce guide vous accompagne pas a pas dans l'installation et la configuration du projet API Tasks avec Symfony.

---

## Table des matieres

1. [Prerequis](#1-prerequis)
2. [Installation de l'environnement](#2-installation-de-lenvironnement)
3. [Creation du projet](#3-creation-du-projet)
4. [Configuration de la base de donnees](#4-configuration-de-la-base-de-donnees)
5. [Creation de l'entite Task](#5-creation-de-lentite-task)
6. [Creation du Repository](#6-creation-du-repository)
7. [Creation du Controller API](#7-creation-du-controller-api)
8. [Les Fixtures (donnees de test)](#8-les-fixtures-donnees-de-test)
9. [Authentification JWT](#9-authentification-jwt)
10. [Tests de l'API](#10-tests-de-lapi)
11. [Commandes utiles](#11-commandes-utiles)

---

## 1. Prerequis

### Logiciels requis

| Logiciel | Version minimale | Verification |
|----------|------------------|--------------|
| PHP | 8.2+ | `php -v` |
| Composer | 2.x | `composer -V` |
| MySQL/MariaDB | 5.7+ / 10.x+ | `mysql --version` |
| WAMP/XAMPP | Derniere version | - |

### Extensions PHP requises

Verifiez que ces extensions sont activees dans votre `php.ini` :

```ini
extension=pdo_mysql
extension=openssl
extension=mbstring
extension=intl
extension=sodium
```

Pour verifier :
```bash
php -m | grep -E "(pdo_mysql|openssl|mbstring|intl|sodium)"
```

---

## 2. Installation de l'environnement

### Etape 2.1 : Installer Composer (si pas deja fait)

Telechargez et installez Composer depuis : https://getcomposer.org/download/

Verifiez l'installation :
```bash
composer -V
# Composer version 2.x.x
```

### Etape 2.2 : Installer Symfony CLI (optionnel mais recommande)

```bash
# Windows (avec Scoop)
scoop install symfony-cli

# Ou telecharger depuis https://symfony.com/download
```

Verifiez :
```bash
symfony check:requirements
```

---

## 3. Creation du projet

### Etape 3.1 : Creer un nouveau projet Symfony

```bash
# Se placer dans le dossier www de WAMP
cd C:/wamp64/www

# Creer le projet avec Composer
composer create-project symfony/skeleton api_demo_tasks

# Entrer dans le dossier
cd api_demo_tasks
```

### Etape 3.2 : Installer les dependances necessaires

```bash
# ORM Doctrine (base de donnees)
composer require symfony/orm-pack

# Maker Bundle (generation de code)
composer require --dev symfony/maker-bundle

# Annotations/Attributes
composer require doctrine/annotations

# Serializer (pour les reponses JSON)
composer require symfony/serializer

# Validator
composer require symfony/validator

# Security Bundle
composer require symfony/security-bundle
```

### Etape 3.3 : Structure du projet

Apres installation, vous avez cette structure :

```
api_demo_tasks/
├── bin/
│   └── console              # CLI Symfony
├── config/
│   ├── packages/            # Configuration des bundles
│   └── routes.yaml          # Routes
├── migrations/              # Migrations BDD
├── public/
│   └── index.php            # Point d'entree
├── src/
│   ├── Controller/          # Vos controllers
│   ├── Entity/              # Vos entites Doctrine
│   └── Repository/          # Vos repositories
├── var/                     # Cache et logs
├── vendor/                  # Dependances
├── .env                     # Variables d'environnement
└── composer.json
```

---

## 4. Configuration de la base de donnees

### Etape 4.1 : Configurer la connexion

Creez le fichier `.env.local` (ne sera pas commite dans Git) :

```bash
# Copier le fichier .env
cp .env .env.local
```

Editez `.env.local` :

```env
# Configuration MySQL pour WAMP
# Syntaxe : mysql://UTILISATEUR:MOT_DE_PASSE@SERVEUR:PORT/NOM_BDD

DATABASE_URL="mysql://root:@127.0.0.1:3306/api_demo_tasks?serverVersion=8.0.32&charset=utf8mb4"
```

> **Note** : Avec WAMP, l'utilisateur par defaut est `root` sans mot de passe.

### Etape 4.2 : Creer la base de donnees

```bash
# Creer la base de donnees
php bin/console doctrine:database:create
```

Resultat attendu :
```
Created database `api_demo_tasks` for connection named default
```

### Etape 4.3 : Verifier la connexion

```bash
php bin/console doctrine:query:sql "SELECT 1"
```

Si ca fonctionne, vous verrez :
```
array(1) { [0]=> array(1) { [1]=> string(1) "1" } }
```

---

## 5. Creation de l'entite Task

### Etape 5.1 : Generer l'entite avec Maker

```bash
php bin/console make:entity Task
```

Repondez aux questions :
```
New property name: title
Field type: string
Field length: 255
Can this field be null: no

New property name: description
Field type: text
Can this field be null: yes

New property name: status
Field type: string
Field length: 20
Can this field be null: no

New property name: createdAt
Field type: datetime
Can this field be null: no

New property name: updatedAt
Field type: datetime
Can this field be null: yes

New property name: dueDate
Field type: date
Can this field be null: yes

(Appuyez sur Entree pour terminer)
```

### Etape 5.2 : Ameliorer l'entite

Editez `src/Entity/Task.php` pour ajouter :

```php
<?php

namespace App\Entity;

use App\Repository\TaskRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\Table(name: 'task')]
#[ORM\HasLifecycleCallbacks]
class Task
{
    // Constantes pour les statuts
    public const STATUS_PENDING = 'pending';
    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::STRING, length: 20)]
    private string $status = self::STATUS_PENDING;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $createdAt = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $updatedAt = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $dueDate = null;

    // Getters et Setters...

    /**
     * Callback automatique AVANT l'insertion
     */
    #[ORM\PrePersist]
    public function onPrePersist(): void
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Callback automatique AVANT chaque mise a jour
     */
    #[ORM\PreUpdate]
    public function onPreUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Convertit l'entite en tableau pour l'API
     */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'createdAt' => $this->createdAt?->format('Y-m-d H:i:s'),
            'updatedAt' => $this->updatedAt?->format('Y-m-d H:i:s'),
            'dueDate' => $this->dueDate?->format('Y-m-d'),
        ];
    }
}
```

### Etape 5.3 : Creer la migration

```bash
# Generer la migration
php bin/console make:migration
```

Resultat : Un fichier est cree dans `migrations/`

```bash
# Executer la migration (cree la table en BDD)
php bin/console doctrine:migrations:migrate
```

Repondez `yes` a la confirmation.

### Etape 5.4 : Verifier la table

Dans phpMyAdmin ou via la console :
```bash
php bin/console doctrine:query:sql "DESCRIBE task"
```

---

## 6. Creation du Repository

### Etape 6.1 : Comprendre le Repository

Le Repository est automatiquement cree dans `src/Repository/TaskRepository.php`.

Il herite de `ServiceEntityRepository` et permet d'acceder a la base de donnees.

### Etape 6.2 : Ajouter les methodes Doctrine ORM

```php
// src/Repository/TaskRepository.php

/**
 * Trouve toutes les taches triees par date
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
```

### Etape 6.3 : Ajouter les methodes SQL brutes (Raw SQL)

**IMPORTANT pour le BTS** : Ces methodes montrent votre maitrise du SQL.

```php
// src/Repository/TaskRepository.php

use Doctrine\DBAL\Connection;

class TaskRepository extends ServiceEntityRepository
{
    private Connection $connection;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Task::class);
        $this->connection = $registry->getConnection();
    }

    /**
     * SELECT - Requete SQL brute
     */
    public function findAllRaw(): array
    {
        $sql = "
            SELECT id, title, description, status,
                   created_at AS createdAt,
                   updated_at AS updatedAt,
                   due_date AS dueDate
            FROM task
            ORDER BY created_at DESC
        ";

        $result = $this->connection->executeQuery($sql);
        return $result->fetchAllAssociative();
    }

    /**
     * INSERT - Requete SQL brute
     */
    public function insertRaw(string $title, ?string $description, string $status): int
    {
        $sql = "
            INSERT INTO task (title, description, status, created_at)
            VALUES (:title, :description, :status, :createdAt)
        ";

        $this->connection->executeStatement($sql, [
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'createdAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);

        return (int) $this->connection->lastInsertId();
    }

    /**
     * UPDATE - Requete SQL brute
     */
    public function updateRaw(int $id, string $title, ?string $description, string $status): int
    {
        $sql = "
            UPDATE task
            SET title = :title,
                description = :description,
                status = :status,
                updated_at = :updatedAt
            WHERE id = :id
        ";

        return $this->connection->executeStatement($sql, [
            'id' => $id,
            'title' => $title,
            'description' => $description,
            'status' => $status,
            'updatedAt' => (new \DateTime())->format('Y-m-d H:i:s'),
        ]);
    }

    /**
     * DELETE - Requete SQL brute
     */
    public function deleteRaw(int $id): int
    {
        $sql = "DELETE FROM task WHERE id = :id";
        return $this->connection->executeStatement($sql, ['id' => $id]);
    }
}
```

---

## 7. Creation du Controller API

### Etape 7.1 : Creer le controller

```bash
php bin/console make:controller TaskController
```

### Etape 7.2 : Implementer les endpoints CRUD

```php
// src/Controller/TaskController.php

<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/tasks', name: 'api_tasks_')]
class TaskController extends AbstractController
{
    public function __construct(
        private TaskRepository $taskRepository
    ) {
    }

    /**
     * GET /api/tasks - Liste toutes les taches
     */
    #[Route('', name: 'index', methods: ['GET'])]
    public function index(): JsonResponse
    {
        $tasks = $this->taskRepository->findAllOrderedByDate();
        $data = array_map(fn(Task $task) => $task->toArray(), $tasks);

        return $this->json([
            'success' => true,
            'count' => count($data),
            'data' => $data,
        ]);
    }

    /**
     * GET /api/tasks/{id} - Recupere une tache
     */
    #[Route('/{id}', name: 'show', methods: ['GET'])]
    public function show(int $id): JsonResponse
    {
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
     * POST /api/tasks - Cree une tache
     */
    #[Route('', name: 'create', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return $this->json([
                'success' => false,
                'message' => 'Le titre est obligatoire',
            ], Response::HTTP_BAD_REQUEST);
        }

        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? null);
        $task->setStatus($data['status'] ?? Task::STATUS_PENDING);

        $this->taskRepository->save($task, true);

        return $this->json([
            'success' => true,
            'message' => 'Tache creee',
            'data' => $task->toArray(),
        ], Response::HTTP_CREATED);
    }

    /**
     * PUT /api/tasks/{id} - Modifie une tache
     */
    #[Route('/{id}', name: 'update', methods: ['PUT'])]
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

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }

        $this->taskRepository->save($task, true);

        return $this->json([
            'success' => true,
            'message' => 'Tache mise a jour',
            'data' => $task->toArray(),
        ]);
    }

    /**
     * DELETE /api/tasks/{id} - Supprime une tache
     */
    #[Route('/{id}', name: 'delete', methods: ['DELETE'])]
    public function delete(int $id): JsonResponse
    {
        $task = $this->taskRepository->find($id);

        if (!$task) {
            return $this->json([
                'success' => false,
                'message' => 'Tache non trouvee',
            ], Response::HTTP_NOT_FOUND);
        }

        $this->taskRepository->remove($task, true);

        return $this->json([
            'success' => true,
            'message' => 'Tache supprimee',
        ]);
    }
}
```

### Etape 7.3 : Ajouter la methode save() au Repository

```php
// src/Repository/TaskRepository.php

public function save(Task $task, bool $flush = true): void
{
    $this->getEntityManager()->persist($task);

    if ($flush) {
        $this->getEntityManager()->flush();
    }
}

public function remove(Task $task, bool $flush = true): void
{
    $this->getEntityManager()->remove($task);

    if ($flush) {
        $this->getEntityManager()->flush();
    }
}
```

---

## 8. Les Fixtures (donnees de test)

### Etape 8.1 : Installer le bundle Fixtures

```bash
composer require --dev doctrine/doctrine-fixtures-bundle
```

### Etape 8.2 : Creer les fixtures

```php
// src/DataFixtures/TaskFixtures.php

<?php

namespace App\DataFixtures;

use App\Entity\Task;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TaskFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tasksData = [
            [
                'title' => 'Configurer l\'environnement',
                'description' => 'Installer WAMP et Composer',
                'status' => Task::STATUS_COMPLETED,
            ],
            [
                'title' => 'Creer l\'API REST',
                'description' => 'Implementer les endpoints CRUD',
                'status' => Task::STATUS_IN_PROGRESS,
            ],
            [
                'title' => 'Ajouter l\'authentification',
                'description' => 'Implementer JWT',
                'status' => Task::STATUS_PENDING,
            ],
        ];

        foreach ($tasksData as $data) {
            $task = new Task();
            $task->setTitle($data['title']);
            $task->setDescription($data['description']);
            $task->setStatus($data['status']);

            $manager->persist($task);
        }

        $manager->flush();
    }
}
```

### Etape 8.3 : Charger les fixtures

```bash
php bin/console doctrine:fixtures:load
```

Repondez `yes` a la confirmation.

> **Attention** : Cette commande SUPPRIME toutes les donnees existantes !

---

## 9. Authentification JWT

### Etape 9.1 : Installer LexikJWTAuthenticationBundle

```bash
composer require lexik/jwt-authentication-bundle
```

### Etape 9.2 : Generer les cles JWT

```bash
php bin/console lexik:jwt:generate-keypair
```

Si la commande echoue sur Windows, generez manuellement :

```bash
# Creer le dossier
mkdir config/jwt

# Generer la cle privee (avec passphrase)
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096 -pass pass:votre_passphrase

# Generer la cle publique
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout -passin pass:votre_passphrase
```

### Etape 9.3 : Configurer le passphrase

Dans `.env.local` :

```env
JWT_PASSPHRASE=votre_passphrase
```

### Etape 9.4 : Creer l'entite User

```bash
php bin/console make:user User
```

Repondez :
- Store in database: yes
- Unique identifier: email
- Hash passwords: yes

### Etape 9.5 : Configurer la securite

Editez `config/packages/security.yaml` :

```yaml
security:
    password_hashers:
        Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface: 'auto'

    providers:
        app_user_provider:
            entity:
                class: App\Entity\User
                property: email

    firewalls:
        dev:
            pattern: ^/(_profiler|_wdt|assets|build)/
            security: false

        login:
            pattern: ^/api/login
            stateless: true
            json_login:
                check_path: /api/login
                username_path: email
                password_path: password
                success_handler: lexik_jwt_authentication.handler.authentication_success
                failure_handler: lexik_jwt_authentication.handler.authentication_failure

        register:
            pattern: ^/api/register
            stateless: true
            security: false

        api:
            pattern: ^/api
            stateless: true
            provider: app_user_provider
            jwt: ~

    access_control:
        - { path: ^/api/login, roles: PUBLIC_ACCESS }
        - { path: ^/api/register, roles: PUBLIC_ACCESS }
        - { path: ^/api/tasks/\d+$, methods: [DELETE], roles: ROLE_ADMIN }
        - { path: ^/api, roles: PUBLIC_ACCESS }
```

### Etape 9.6 : Creer le SecurityController

```php
// src/Controller/SecurityController.php

<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api', name: 'api_')]
class SecurityController extends AbstractController
{
    public function __construct(
        private UserRepository $userRepository,
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email et mot de passe obligatoires',
            ], Response::HTTP_BAD_REQUEST);
        }

        // Verifier si l'email existe deja
        if ($this->userRepository->findOneBy(['email' => $data['email']])) {
            return $this->json([
                'success' => false,
                'message' => 'Cet email est deja utilise',
            ], Response::HTTP_CONFLICT);
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setCreatedAt(new \DateTime());

        // IMPORTANT : Toujours hasher le mot de passe !
        $hashedPassword = $this->passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($hashedPassword);

        $this->userRepository->save($user, true);

        return $this->json([
            'success' => true,
            'message' => 'Utilisateur cree',
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(): JsonResponse
    {
        // Cette methode est geree par le firewall json_login
        return $this->json(['message' => 'Route geree par le firewall']);
    }
}
```

### Etape 9.7 : Executer les migrations User

```bash
php bin/console make:migration
php bin/console doctrine:migrations:migrate
```

### Etape 9.8 : Creer les fixtures User

```php
// src/DataFixtures/UserFixtures.php

<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
    }

    public function load(ObjectManager $manager): void
    {
        // Admin
        $admin = new User();
        $admin->setEmail('admin@example.com');
        $admin->setRoles(['ROLE_ADMIN']);
        $admin->setCreatedAt(new \DateTime());
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin123'));
        $manager->persist($admin);

        // User standard
        $user = new User();
        $user->setEmail('user@example.com');
        $user->setCreatedAt(new \DateTime());
        $user->setPassword($this->passwordHasher->hashPassword($user, 'user123'));
        $manager->persist($user);

        $manager->flush();
    }
}
```

---

## 10. Tests de l'API

### Etape 10.1 : Lancer le serveur

```bash
# Avec Symfony CLI
symfony serve

# Ou avec PHP
php -S localhost:8000 -t public
```

### Etape 10.2 : Tester avec curl

**Inscription :**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email": "test@test.com", "password": "test123"}'
```

**Connexion :**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "admin123"}'
```

Reponse :
```json
{"token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."}
```

**Lister les taches :**
```bash
curl http://localhost:8000/api/tasks
```

**Supprimer une tache (admin) :**
```bash
curl -X DELETE http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer VOTRE_TOKEN_JWT"
```

### Etape 10.3 : Tester avec Postman

1. Importez les endpoints dans Postman
2. Pour les requetes POST/PUT, configurez :
   - Headers : `Content-Type: application/json`
   - Body : `raw` > `JSON`
3. Pour les requetes protegees :
   - Headers : `Authorization: Bearer <token>`

---

## 11. Commandes utiles

### Base de donnees

```bash
# Creer la BDD
php bin/console doctrine:database:create

# Supprimer la BDD
php bin/console doctrine:database:drop --force

# Generer une migration
php bin/console make:migration

# Executer les migrations
php bin/console doctrine:migrations:migrate

# Voir le statut des migrations
php bin/console doctrine:migrations:status

# Charger les fixtures
php bin/console doctrine:fixtures:load
```

### Debug

```bash
# Lister les routes
php bin/console debug:router

# Verifier la configuration securite
php bin/console debug:config security

# Vider le cache
php bin/console cache:clear
```

### Generation de code

```bash
# Creer une entite
php bin/console make:entity

# Creer un controller
php bin/console make:controller

# Creer un utilisateur
php bin/console make:user
```

---

## Ressources

- [Documentation Symfony](https://symfony.com/doc/current/index.html)
- [Documentation Doctrine](https://www.doctrine-project.org/projects/orm.html)
- [LexikJWTAuthenticationBundle](https://github.com/lexik/LexikJWTAuthenticationBundle)

---

**Bon courage pour votre projet BTS SIO SLAM !**
