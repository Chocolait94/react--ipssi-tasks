# API Demo Tasks - BTS SIO SLAM

Projet de demonstration Symfony pour les etudiants BTS SIO SLAM 2eme annee.

Une API REST simple de gestion de taches qui demontre :

- L'utilisation de **Doctrine ORM** (mapping objet-relationnel)
- L'ecriture de **requetes SQL brutes** (Raw SQL) pour le jury BTS
- La creation d'une **API REST** avec Symfony
- L'**authentification JWT** (JSON Web Token)
- La gestion des **roles** (ROLE_USER, ROLE_ADMIN)

## Prerequis

- PHP 8.2+
- Composer
- MySQL / MariaDB
- WAMP, XAMPP ou Symfony CLI

## Installation

```bash
# 1. Cloner le projet
git clone <url-du-repo>
cd api_demo_ipssi

# 2. Installer les dependances
composer install

# 3. Configurer la base de donnees
# Copier .env en .env.local et modifier DATABASE_URL
cp .env .env.local
```

Editer `.env.local` :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/api_demo_tasks?serverVersion=8.0.32&charset=utf8mb4"
```

```bash
# 4. Creer la base de donnees
php bin/console doctrine:database:create

# 5. Executer les migrations
php bin/console doctrine:migrations:migrate

# 6. Charger les donnees de test
php bin/console doctrine:fixtures:load
```

## Gérer les CORS :

Voir pour installer

```bash
composer require nelmio/cors-bundle
```

Voir la doc si besoin :
[ici](https://symfony.com/bundles/NelmioCorsBundle/current/index.html)

Dans /config/packages/nelmio_cors.yaml

```nelmio_cors:
    defaults:
        origin_regex: true
        allow_origin: ["%env(CORS_ALLOW_ORIGIN)%"]
        allow_methods: ["GET", "OPTIONS", "POST", "PUT", "PATCH", "DELETE"]
        allow_headers: ["Content-Type", "Authorization"]
        expose_headers: ["Link"]
        max_age: 3600
    paths:
        "^/": null
```

Pensez a mettre a jour le .env =D

## Générer les clés public et privé pour le JWT :

```bash
php bin/console lexik:jwt:generate-keypair
```

Voir la doc : [package jwt lexik] (https://symfony.com/bundles/LexikJWTAuthenticationBundle/current/index.html)

## Lancer le serveur

```bash
# Avec Symfony CLI
symfony serve

# Ou avec PHP
php -S localhost:8000 -t public
```

L'API est accessible sur `http://localhost:8000/api/tasks`

## Endpoints API

### Authentification

| Methode | URL             | Description                    | Acces       |
| ------- | --------------- | ------------------------------ | ----------- |
| POST    | `/api/register` | Inscription                    | Public      |
| POST    | `/api/login`    | Connexion (retourne token JWT) | Public      |
| GET     | `/api/me`       | Infos utilisateur connecte     | Authentifie |

### Taches

| Methode | URL                | Description             | Acces          |
| ------- | ------------------ | ----------------------- | -------------- |
| GET     | `/api/tasks`       | Liste toutes les taches | Public         |
| GET     | `/api/tasks/{id}`  | Recupere une tache      | Public         |
| POST    | `/api/tasks`       | Cree une tache          | Public         |
| PUT     | `/api/tasks/{id}`  | Modifie une tache       | Public         |
| DELETE  | `/api/tasks/{id}`  | Supprime une tache      | **ROLE_ADMIN** |
| GET     | `/api/tasks/stats` | Statistiques            | Public         |

### Version Raw SQL (pour le jury BTS)

Les memes endpoints avec `/raw` utilisent des requetes SQL ecrites a la main :

| Methode | URL                   | Requete SQL         |
| ------- | --------------------- | ------------------- |
| GET     | `/api/tasks/raw`      | SELECT              |
| GET     | `/api/tasks/raw/{id}` | SELECT WHERE        |
| POST    | `/api/tasks/raw`      | INSERT              |
| PUT     | `/api/tasks/raw/{id}` | UPDATE              |
| DELETE  | `/api/tasks/raw/{id}` | DELETE (ROLE_ADMIN) |

## Utilisateurs de test

Les fixtures creent les utilisateurs suivants :

| Email             | Mot de passe | Role       |
| ----------------- | ------------ | ---------- |
| admin@example.com | admin123     | ROLE_ADMIN |
| user@example.com  | user123      | ROLE_USER  |
| marie@example.com | marie123     | ROLE_USER  |

## Structure du projet

```
src/
├── Controller/
│   ├── SecurityController.php  # Authentification (register, login)
│   └── TaskController.php      # Endpoints API REST
├── DataFixtures/
│   ├── TaskFixtures.php        # Donnees de test (taches)
│   └── UserFixtures.php        # Donnees de test (utilisateurs)
├── Entity/
│   ├── Task.php                # Entite Tache
│   └── User.php                # Entite Utilisateur
└── Repository/
    ├── TaskRepository.php      # Acces BDD (ORM + Raw SQL)
    └── UserRepository.php      # Acces BDD utilisateurs
```

## Exemples de requetes

### Inscription

```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "motdepasse"}'
```

### Connexion (obtenir un token JWT)

```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "admin123"}'
```

Reponse :

```json
{ "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..." }
```

### Lister les taches (public)

```bash
curl http://localhost:8000/api/tasks
```

### Supprimer une tache (admin uniquement)

```bash
curl -X DELETE http://localhost:8000/api/tasks/1 \
  -H "Authorization: Bearer <VOTRE_TOKEN_JWT>"
```

## Points cles pour le jury BTS

### 1. Requetes SQL brutes (TaskRepository.php)

```php
// SELECT
$sql = "SELECT * FROM task WHERE id = :id";
$result = $this->connection->executeQuery($sql, ['id' => $id]);

// INSERT
$sql = "INSERT INTO task (title, status) VALUES (:title, :status)";
$this->connection->executeStatement($sql, $params);

// UPDATE
$sql = "UPDATE task SET title = :title WHERE id = :id";
$this->connection->executeStatement($sql, $params);

// DELETE
$sql = "DELETE FROM task WHERE id = :id";
$this->connection->executeStatement($sql, ['id' => $id]);
```

### 2. Mapping Doctrine (Task.php)

```php
#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: Types::INTEGER)]
    private ?int $id = null;

    #[ORM\Column(type: Types::STRING, length: 255)]
    private ?string $title = null;
}
```

### 3. API REST (TaskController.php)

```php
#[Route('/api/tasks', name: 'api_tasks_')]
class TaskController extends AbstractController
{
    #[Route('', methods: ['GET'])]
    public function index(): JsonResponse
    {
        return $this->json(['data' => $tasks]);
    }
}
```

### 4. Authentification JWT (SecurityController.php)

```php
// Hashage du mot de passe (TRES IMPORTANT !)
$hashedPassword = $this->passwordHasher->hashPassword($user, $password);
$user->setPassword($hashedPassword);

// Recuperer l'utilisateur connecte via le token JWT
$user = $this->getUser();
```

### 5. Protection par role (security.yaml)

```yaml
access_control:
    - { path: ^/api/tasks/\d+$, methods: [DELETE], roles: ROLE_ADMIN }
```

## Documentation

Voir `docs/API_DOCUMENTATION.md` pour la documentation complete.

## Technologies

- Symfony 7.4
- Doctrine ORM 3.x
- LexikJWTAuthenticationBundle (JWT)
- PHP 8.2+
- MySQL 8.0

## Licence

Projet pedagogique - BTS SIO SLAM
