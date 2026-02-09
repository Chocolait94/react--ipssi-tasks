# Documentation API Tasks - BTS SIO SLAM

## Introduction

Cette API REST permet de gerer des taches (tasks). Elle propose :
- **Version standard** : utilise Doctrine ORM (objets PHP)
- **Version Raw SQL** : utilise des requetes SQL brutes (pour demontrer vos competences SQL au jury)
- **Authentification JWT** : securisation de l'API avec tokens
- **Gestion des roles** : ROLE_USER et ROLE_ADMIN

## Configuration

### Base de donnees

Modifier le fichier `.env.local` avec vos identifiants MySQL :

```
DATABASE_URL="mysql://root:@127.0.0.1:3306/api_demo_tasks?serverVersion=8.0.32&charset=utf8mb4"
```

### Creation de la base

**Option 1 - Via le script SQL (phpMyAdmin) :**
```bash
# Executer le fichier docs/database.sql dans phpMyAdmin
# Ce fichier contient la creation de la BDD + donnees de test
```

**Option 2 - Via Doctrine + Fixtures (recommande) :**
```bash
# 1. Creer la base de donnees
php bin/console doctrine:database:create

# 2. Executer les migrations (cree les tables)
php bin/console doctrine:migrations:migrate

# 3. Charger les donnees de test via les fixtures
php bin/console doctrine:fixtures:load
```

> **Note** : La commande `doctrine:fixtures:load` demande confirmation car elle SUPPRIME
> toutes les donnees existantes. Pour ajouter sans supprimer : `--append`

## Authentification JWT

### Qu'est-ce que JWT ?

JWT (JSON Web Token) est un standard pour securiser les API REST.
Le principe :
1. L'utilisateur se connecte avec email/password
2. Le serveur retourne un **token** (chaine de caracteres)
3. L'utilisateur envoie ce token dans chaque requete protegee
4. Le serveur verifie le token et autorise (ou refuse) l'acces

### Utilisateurs de test

| Email | Mot de passe | Role | Droits |
|-------|--------------|------|--------|
| admin@example.com | admin123 | ROLE_ADMIN | Tout (y compris DELETE) |
| user@example.com | user123 | ROLE_USER | Lecture/Ecriture |
| marie@example.com | marie123 | ROLE_USER | Lecture/Ecriture |

---

### POST /api/register - Inscription

**Body JSON :**
```json
{
    "email": "nouveau@example.com",
    "password": "motdepasse123"
}
```

**Exemple :**
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{"email": "test@test.com", "password": "test123"}'
```

---

### POST /api/login - Connexion

**Body JSON :**
```json
{
    "email": "admin@example.com",
    "password": "admin123"
}
```

**Exemple :**
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "admin@example.com", "password": "admin123"}'
```

**Reponse :**
```json
{
    "token": "eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
}
```

---

### GET /api/me - Utilisateur connecte

**Headers requis :**
```
Authorization: Bearer <votre_token_jwt>
```

**Exemple :**
```bash
curl http://localhost:8000/api/me \
  -H "Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9..."
```

---

### Regles d'acces

| Endpoint | Methode | Acces requis |
|----------|---------|--------------|
| /api/register | POST | Public |
| /api/login | POST | Public |
| /api/me | GET | Authentifie |
| /api/tasks | GET | Public |
| /api/tasks/{id} | DELETE | **ROLE_ADMIN** |

---

## Endpoints API - Taches

### Base URL
```
http://localhost:8000/api/tasks
```

---

## Version Doctrine ORM (Standard)

### GET /api/tasks
Liste toutes les taches.

**Parametres optionnels :**
- `status` : filtre par statut (pending, in_progress, completed)
- `search` : recherche dans le titre et la description

**Exemple de requete :**
```bash
curl http://localhost/api_demo_ipssi/public/api/tasks
curl http://localhost/api_demo_ipssi/public/api/tasks?status=pending
curl http://localhost/api_demo_ipssi/public/api/tasks?search=API
```

**Reponse :**
```json
{
    "success": true,
    "count": 6,
    "data": [
        {
            "id": 1,
            "title": "Ma tache",
            "description": "Description",
            "status": "pending",
            "createdAt": "2025-01-07 10:00:00",
            "updatedAt": null,
            "dueDate": "2025-01-15"
        }
    ]
}
```

---

### GET /api/tasks/{id}
Recupere une tache par son ID.

**Exemple :**
```bash
curl http://localhost/api_demo_ipssi/public/api/tasks/1
```

---

### POST /api/tasks
Cree une nouvelle tache.

**Body JSON :**
```json
{
    "title": "Nouvelle tache",
    "description": "Description optionnelle",
    "status": "pending",
    "dueDate": "2025-12-31"
}
```

**Exemple :**
```bash
curl -X POST http://localhost/api_demo_ipssi/public/api/tasks \
  -H "Content-Type: application/json" \
  -d '{"title": "Ma nouvelle tache", "description": "Test"}'
```

---

### PUT /api/tasks/{id}
Met a jour une tache existante.

**Body JSON (tous les champs sont optionnels) :**
```json
{
    "title": "Titre modifie",
    "status": "completed"
}
```

**Exemple :**
```bash
curl -X PUT http://localhost/api_demo_ipssi/public/api/tasks/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

---

### DELETE /api/tasks/{id}
Supprime une tache.

**Exemple :**
```bash
curl -X DELETE http://localhost/api_demo_ipssi/public/api/tasks/1
```

---

## Version Raw SQL (Pour le jury BTS)

Ces endpoints utilisent des requetes SQL brutes au lieu de l'ORM Doctrine.
Ils ajoutent un champ `_info` dans la reponse pour indiquer quelle requete SQL a ete utilisee.

### GET /api/tasks/raw
```bash
curl http://localhost/api_demo_ipssi/public/api/tasks/raw
```

### GET /api/tasks/raw/{id}
```bash
curl http://localhost/api_demo_ipssi/public/api/tasks/raw/1
```

### POST /api/tasks/raw
```bash
curl -X POST http://localhost/api_demo_ipssi/public/api/tasks/raw \
  -H "Content-Type: application/json" \
  -d '{"title": "Tache SQL brut"}'
```

### PUT /api/tasks/raw/{id}
```bash
curl -X PUT http://localhost/api_demo_ipssi/public/api/tasks/raw/1 \
  -H "Content-Type: application/json" \
  -d '{"status": "completed"}'
```

### DELETE /api/tasks/raw/{id}
```bash
curl -X DELETE http://localhost/api_demo_ipssi/public/api/tasks/raw/1
```

---

## Endpoint Statistiques

### GET /api/tasks/stats
Retourne des statistiques sur les taches (utilise SQL avec GROUP BY).

**Reponse :**
```json
{
    "success": true,
    "data": {
        "total": 6,
        "byStatus": {
            "pending": 2,
            "in_progress": 2,
            "completed": 2
        }
    },
    "_info": "Ces statistiques utilisent une requete SQL avec COUNT et GROUP BY"
}
```

---

## Codes HTTP

| Code | Signification |
|------|---------------|
| 200  | Succes |
| 201  | Ressource creee |
| 400  | Requete invalide (donnees manquantes ou incorrectes) |
| 401  | Non authentifie (token manquant ou invalide) |
| 403  | Acces refuse (role insuffisant) |
| 404  | Ressource non trouvee |
| 409  | Conflit (email deja utilise) |
| 500  | Erreur serveur |

---

## Statuts des taches

| Valeur | Signification |
|--------|---------------|
| `pending` | En attente |
| `in_progress` | En cours |
| `completed` | Terminee |

---

## Tester avec Postman

1. Importer les endpoints ci-dessus dans Postman
2. Configurer le Content-Type a `application/json` pour les requetes POST/PUT
3. Utiliser le body "raw" en format JSON

---

## Structure du code

```
src/
├── Controller/
│   ├── SecurityController.php  # Authentification (register, login)
│   └── TaskController.php      # Endpoints API REST taches
├── DataFixtures/
│   ├── TaskFixtures.php        # Donnees de test (taches)
│   └── UserFixtures.php        # Donnees de test (utilisateurs)
├── Entity/
│   ├── Task.php                # Entite Tache
│   └── User.php                # Entite Utilisateur
└── Repository/
    ├── TaskRepository.php      # Acces BDD (ORM + Raw SQL)
    └── UserRepository.php      # Acces BDD utilisateurs

config/
├── jwt/                        # Cles JWT (private.pem, public.pem)
└── packages/
    └── security.yaml           # Configuration securite et firewalls

docs/
├── database.sql                # Script SQL manuel
├── API_DOCUMENTATION.md        # Cette documentation
└── GUIDE_INSTALLATION.md       # Guide complet etape par etape
```

---

## Les Fixtures (DataFixtures)

Les fixtures sont une fonctionnalite de Doctrine qui permet de charger des donnees
de test de maniere reproductible.

### Fichier : `src/DataFixtures/TaskFixtures.php`

```php
// Exemple simplifie
public function load(ObjectManager $manager): void
{
    $task = new Task();
    $task->setTitle('Ma tache de test');
    $task->setStatus(Task::STATUS_PENDING);

    $manager->persist($task);  // Prepare l'insertion
    $manager->flush();         // Execute le SQL
}
```

### Commandes utiles

```bash
# Charger les fixtures (ATTENTION: supprime les donnees existantes)
php bin/console doctrine:fixtures:load

# Charger sans supprimer les donnees existantes
php bin/console doctrine:fixtures:load --append

# Repondre automatiquement "yes" a la confirmation
php bin/console doctrine:fixtures:load --no-interaction
```

---

## Points importants pour le jury BTS

### 1. Entity (Task.php)
- Mapping objet-relationnel avec les attributs PHP 8 (`#[ORM\Column]`)
- Lifecycle callbacks (`#[ORM\PrePersist]`, `#[ORM\PreUpdate]`)
- Validation des donnees dans les setters

### 2. Repository (TaskRepository.php)
Contient DEUX types de methodes :
- **Doctrine ORM** : `findAllOrderedByDate()`, `findByStatus()` - utilise QueryBuilder
- **Raw SQL** : `findAllRaw()`, `insertRaw()`, `updateRaw()`, `deleteRaw()` - SQL pur

### 3. Controller (TaskController.php)
- API REST avec reponses JSON (`$this->json()`)
- Deux versions des endpoints : standard et `/raw`
- Validation des donnees entrantes
- Codes HTTP appropries (200, 201, 400, 404)

### 4. Fixtures (TaskFixtures.php)
- Chargement de donnees de test
- Utilisation de `persist()` et `flush()`
- Pattern de creation d'entites en boucle

### 5. Authentification JWT (SecurityController.php)
- Hashage securise : `$this->passwordHasher->hashPassword($user, $password)`
- Recuperation utilisateur connecte : `$this->getUser()`
- Token JWT dans le header : `Authorization: Bearer <token>`

### 6. Securite (security.yaml)
- Firewalls : `login`, `register`, `api`
- Regles `access_control` par role
- Protection des routes sensibles (DELETE = ROLE_ADMIN)
