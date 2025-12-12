# TicketTheatre - Core Service API

## Vue d'ensemble

API REST pour la gestion complète d'un système de réservation de spectacles de théâtre.

- **Version**: 1.0.0
- **Base URL**: `http://localhost:8082/api`
- **Auth Service URL**: `http://localhost:8081/api`
- **Database**: MySQL `db_core` (partagée avec auth-service)
- **Documentation API**: `http://localhost:8082/api-docs.html`

---

## Installation rapide

### 1. Créer la base de données
```bash
docker compose exec main-db-service mysql -usail -ppassword -e "CREATE DATABASE IF NOT EXISTS db_core;"
```

### 2. Migrer et peupler la base
```bash
# Core Service (crée toutes les tables)
cd core-service
docker compose exec core-service-app php artisan migrate:fresh --seed

# Auth Service (ajoute personal_access_tokens)
cd ../auth-service
docker compose exec auth-service-app php artisan migrate
```

### 3. Clear cache
```bash
docker compose exec core-service-app php artisan optimize:clear
docker compose exec auth-service-app php artisan optimize:clear
```

---

## Authentification

### Obtenir un token

**Endpoint**: `POST http://localhost:8081/api/login`

**Body**:
```json
{
  "email": "admin@tickettheatre.com",
  "password": "password"
}
```

**Réponse**:
```json
{
  "success": true,
  "token": "1|abcdefghijklmnopqrstuvwxyz123456789",
  "user": {
    "id": 1,
    "first_name": "Admin",
    "last_name": "Système",
    "full_name": "Admin Système",
    "email": "admin@tickettheatre.com",
    "role": "admin",
    "avatar": "https://...",
    "is_active": true
  }
}
```

### Utiliser le token

Pour toutes les routes protégées, ajouter le header :
```
Authorization: Bearer {token}
```

---

## Endpoints disponibles

### Catégories

#### Liste des catégories
```http
GET /api/public/categories
```

**Réponse**:
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Comédie",
      "slug": "comedie",
      "description": "Spectacles humoristiques et légers",
      "icon": "😄",
      "color": "#FFD700",
      "spectacles_count": 3
    }
  ]
}
```

#### Détails d'une catégorie
```http
GET /api/public/categories/{id}
```

#### Créer une catégorie 
```http
POST /api/categories
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Opéra",
  "description": "Spectacles lyriques",
  "icon": "🎵",
  "color": "#8B008B"
}
```

**Validation**:
- `name`: requis, unique, max 255 caractères
- `description`: optionnel, texte
- `icon`: optionnel, max 255 caractères
- `color`: optionnel, format hex (#RRGGBB)

#### Modifier une catégorie 
```http
PUT /api/categories/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Opéra Moderne",
  "description": "Opéras contemporains"
}
```

#### Supprimer une catégorie 
```http
DELETE /api/categories/{id}
Authorization: Bearer {token}
```

**Note**: Impossible de supprimer si des spectacles sont associés.

---

### Salles

#### Liste des salles
```http
GET /api/public/halls?is_active=true&type=Grande%20salle
```

**Query params**:
- `is_active`: boolean (filtrer par statut actif)
- `type`: string (filtrer par type)

#### Détails d'une salle
```http
GET /api/public/halls/{id}
```

**Réponse inclut**:
- Informations de la salle
- Séances à venir dans cette salle

#### Créer une salle 
```http
POST /api/halls
Authorization: Bearer {token}
Content-Type: application/json

{
  "name": "Salle Mozart",
  "location": "Niveau 2",
  "capacity": 200,
  "description": "Salle acoustique",
  "type": "Salle de concert",
  "is_active": true,
  "image_url": "https://...",
  "amenities": ["Climatisation", "Bar", "PMR"]
}
```

**Validation**:
- `name`: requis, max 255
- `capacity`: requis, entier >= 1
- `location`: optionnel, max 255
- `type`: optionnel, max 255
- `is_active`: boolean, défaut true
- `amenities`: array de strings

#### Modifier une salle 
```http
PUT /api/halls/{id}
Authorization: Bearer {token}
```

#### Supprimer une salle 
```http
DELETE /api/halls/{id}
Authorization: Bearer {token}
```

**Note**: Impossible de supprimer si des séances à venir existent.

#### Salles disponibles 
```http
GET /api/halls/available?date_start=2024-12-15T19:00:00&date_end=2024-12-15T22:00:00
Authorization: Bearer {token}
```

Retourne les salles sans séances dans la plage horaire.

---

### Spectacles

#### Liste des spectacles
```http
GET /api/public/spectacles?category_id=1&status=ongoing&is_published=true&search=moliere&sort_by=title&sort_order=asc&page=1&per_page=10
```

**Query params**:
- `category_id`: filtrer par catégorie
- `status`: `upcoming`, `ongoing`, `finished`, `cancelled`
- `is_published`: boolean
- `search`: recherche dans le titre
- `sort_by`: champ de tri (défaut: `created_at`)
- `sort_order`: `asc` ou `desc` (défaut: `desc`)
- `page`: numéro de page
- `per_page`: résultats par page (défaut: 15)

#### Spectacles à venir
```http
GET /api/public/spectacles/upcoming
```

Retourne les 10 derniers spectacles publiés avec séances à venir.

#### Détails d'un spectacle
```http
GET /api/public/spectacles/{id}
```

**Réponse inclut**:
- Informations du spectacle
- Catégorie
- Séances à venir avec salles

#### Créer un spectacle 
```http
POST /api/spectacles
Authorization: Bearer {token}
Content-Type: application/json

{
  "title": "Cyrano de Bergerac",
  "description": "Comédie héroïque d'Edmond Rostand",
  "duration": 140,
  "base_price": 42.00,
  "image_url": "https://...",
  "poster_url": "https://...",
  "trailer_url": "https://...",
  "language": "fr",
  "age_restriction": 10,
  "category_id": 5,
  "director": "Denis Podalydès",
  "actors": ["Denis Podalydès", "Florence Viala"],
  "is_published": true,
  "status": "upcoming"
}
```

**Validation**:
- `title`: requis, max 255
- `duration`: optionnel, entier >= 1 (en minutes)
- `base_price`: requis, numérique >= 0
- `language`: string, max 10, défaut "fr"
- `age_restriction`: optionnel, entier 0-18
- `category_id`: optionnel, doit exister
- `actors`: array de strings
- `is_published`: boolean, défaut false
- `status`: enum, défaut "upcoming"

#### Modifier un spectacle 
```http
PUT /api/spectacles/{id}
Authorization: Bearer {token}
```

#### Supprimer un spectacle 
```http
DELETE /api/spectacles/{id}
Authorization: Bearer {token}
```

**Note**: Impossible de supprimer si des séances à venir existent.

---

### Séances

#### Liste des séances
```http
GET /api/public/seances?spectacle_id=1&hall_id=1&status=scheduled&date_from=2024-12-10&date_to=2024-12-31&upcoming_only=true&per_page=15
```

**Query params**:
- `spectacle_id`: filtrer par spectacle
- `hall_id`: filtrer par salle
- `status`: `scheduled`, `cancelled`, `completed`
- `date_from`: date de début (YYYY-MM-DD)
- `date_to`: date de fin (YYYY-MM-DD)
- `upcoming_only`: boolean (seulement à venir)
- `per_page`: résultats par page (défaut: 15)

#### Détails d'une séance
```http
GET /api/public/seances/{id}
```

**Réponse inclut**:
- Informations de la séance
- Spectacle complet avec catégorie
- Salle
- Réservations
- Places restantes calculées

#### Places disponibles
```http
GET /api/public/seances/{id}/available-seats
```

**Réponse**:
```json
{
  "success": true,
  "data": {
    "total_seats": 500,
    "booked_seats": 45,
    "remaining_seats": 455,
    "is_available": true
  }
}
```

#### Créer une séance 
```http
POST /api/seances
Authorization: Bearer {token}
Content-Type: application/json

{
  "spectacle_id": 1,
  "hall_id": 1,
  "date_seance": "2024-12-25 20:00:00",
  "available_seats": 500,
  "price": 45.00,
  "status": "scheduled"
}
```

**Validation**:
- `spectacle_id`: requis, doit exister
- `hall_id`: requis, doit exister
- `date_seance`: requis, date future
- `available_seats`: requis, entier >= 1
- `price`: requis, numérique >= 0
- `status`: enum, défaut "scheduled"

**Vérifications automatiques**:
- ✅ Pas de conflit de salle à cette date/heure
- ✅ Nombre de places <= capacité de la salle

#### Modifier une séance 
```http
PUT /api/seances/{id}
Authorization: Bearer {token}
```

#### Supprimer une séance 
```http
DELETE /api/seances/{id}
Authorization: Bearer {token}
```

**Note**: Impossible de supprimer si des réservations confirmées existent.

---

###  Réservations

#### Liste des réservations 
```http
GET /api/reservations?user_id=2&status=confirmed&payment_status=paid&booking_reference=TH-2024&per_page=15
Authorization: Bearer {token}
```

**Query params**:
- `user_id`: filtrer par utilisateur
- `status`: `pending`, `confirmed`, `cancelled`, `expired`
- `payment_status`: `pending`, `paid`, `refunded`, `failed`
- `booking_reference`: recherche partielle
- `per_page`: résultats par page (défaut: 15)

#### Créer une réservation 
```http
POST /api/reservations
Authorization: Bearer {token}
Content-Type: application/json

{
  "user_id": 2,
  "seance_id": 10,
  "quantity": 2,
  "seats": ["A12", "A13"]
}
```

**Validation**:
- `user_id`: requis, doit exister
- `seance_id`: requis, doit exister
- `quantity`: requis, entier 1-10
- `seats`: optionnel, array de strings (max 10 caractères chacun)

**Vérifications automatiques**:
- ✅ Séance disponible (status = scheduled)
- ✅ Séance non passée
- ✅ Places suffisantes
- ✅ Génération automatique de `booking_reference`
- ✅ Calcul automatique du `total_price`
- ✅ Expiration à 15 minutes

**Réponse**:
```json
{
  "success": true,
  "message": "Réservation créée avec succès",
  "data": {
    "id": 7,
    "booking_reference": "TH-2024-ABC123",
    "quantity": 2,
    "total_price": 90.00,
    "status": "pending",
    "payment_status": "pending",
    "expires_at": "2024-12-12T10:15:00",
    "seance": {...},
    "user": {...}
  }
}
```

#### Détails d'une réservation 
```http
GET /api/reservations/{id}
Authorization: Bearer {token}
```

#### Réservation par référence
```http
GET /api/public/reservations/reference/TH-2024-ABC123
```

Permet au client de consulter sa réservation sans authentification.

#### Confirmer le paiement 
```http
POST /api/reservations/{id}/confirm-payment
Authorization: Bearer {token}
Content-Type: application/json

{
  "payment_id": "PAY-STRIPE-123456789"
}
```

**Validation**:
- `payment_id`: requis, max 255

**Vérifications**:
- ✅ Réservation en status pending
- ✅ Réservation non expirée

**Effets**:
- Status → `confirmed`
- Payment_status → `paid`
- Confirmed_at → maintenant

#### Annuler une réservation 
```http
POST /api/reservations/{id}/cancel
Authorization: Bearer {token}
Content-Type: application/json

{
  "cancellation_reason": "Empêchement de dernière minute"
}
```

**Validation**:
- `cancellation_reason`: optionnel, max 500 caractères

**Effets**:
- Status → `cancelled`
- Cancelled_at → maintenant

#### Modifier une réservation 
```http
PUT /api/reservations/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
  "status": "confirmed",
  "payment_status": "paid"
}
```

#### Réservations d'un utilisateur 
```http
GET /api/users/{userId}/reservations
Authorization: Bearer {token}
```

Retourne toutes les réservations d'un utilisateur, triées par date décroissante.

---

---

## Codes de réponse HTTP

| Code | Signification |
|------|---------------|
| `200 OK` | Succès |
| `201 Created` | Ressource créée |
| `401 Unauthorized` | Non authentifié ou token invalide |
| `404 Not Found` | Ressource introuvable |
| `422 Unprocessable Entity` | Erreur de validation |
| `500 Internal Server Error` | Erreur serveur |

---

## Format des réponses

### Succès
```json
{
  "success": true,
  "data": {...}
}
```

### Succès avec message
```json
{
  "success": true,
  "message": "Opération réussie",
  "data": {...}
}
```

### Erreur de validation
```json
{
  "success": false,
  "message": "Les données fournies sont invalides",
  "errors": {
    "email": ["Le champ email est requis"],
    "quantity": ["La quantité doit être au moins 1"]
  }
}
```

### Erreur métier
```json
{
  "success": false,
  "message": "Pas assez de places disponibles. Places restantes : 5"
}
```

---


### Gestion admin

1. Login admin
2. Créer catégories
3. Créer salles
4. Créer spectacles
5. Créer séances
6. Gérer réservations

---

## 🔧 Configuration

### Variables d'environnement (.env)

```env
# Application
APP_NAME="Core Service"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://core-service-app

# Base de données partagée
DB_CONNECTION=mysql
DB_HOST=main-db-service
DB_PORT=3306
DB_DATABASE=db_core
DB_USERNAME=
DB_PASSWORD=
```

---

## Debugging

### Logs
```bash
# Logs en temps réel
docker compose exec core-service-app tail -f storage/logs/laravel.log

# Dernières 100 lignes
docker compose exec core-service-app tail -100 storage/logs/laravel.log
```

### Tinker (console Laravel)
```bash
docker compose exec core-service-app php artisan tinker

# Exemples
>>> User::count()
>>> Spectacle::with('category')->first()
>>> Seance::where('date_seance', '>=', now())->count()
```

### Réinitialiser la base
```bash
docker compose exec core-service-app php artisan migrate:fresh --seed
```

---

## Données de test

### Comptes utilisateurs
| Email | Mot de passe | Rôle |
|-------|--------------|------|
| admin@tickettheatre.com | password | admin |
| jean.dupont@example.com | password | user |
| marie.martin@example.com | password | user |
| pierre.dubois@example.com | password | user |
| sophie.bernard@example.com | password | user |

### Données créées par les seeders
- **8 catégories** : Comédie, Drame, Musical, Danse, Classique, Contemporain, Jeune Public, One-Man-Show
- **5 salles** : Grande Salle (500), Petit Théâtre (150), Scène Studio (80), Amphithéâtre (300), Salle Polyvalente (200)
- **10 spectacles** : Le Bourgeois Gentilhomme, Roméo et Juliette, Les Misérables, etc.
- **~100 séances** : Réparties sur les 30 prochains jours
- **6 réservations** : Exemples avec différents statuts

---


---

## 🔄 Workflow de développement

```bash
# 1. Modifier le code
# 2. Clear le cache
docker compose exec core-service-app php artisan optimize:clear

# 3. Si modification de migration
docker compose exec core-service-app php artisan migrate

# 4. Si modification de routes
docker compose exec core-service-app php artisan route:cache

# 5. Tester
```

---

## 📝 Changelog

### Version 1.0.0 (2024-12-12)
- ✅ Architecture microservices avec base partagée
- ✅ Authentification Sanctum
- ✅ CRUD complet pour toutes les entités
- ✅ Système de réservation avec expiration
- ✅ Gestion des places disponibles
- ✅ Seeders avec données réalistes
- ✅ Validation complète
- ✅ Documentation API

---

**Happy coding! 🎭**
