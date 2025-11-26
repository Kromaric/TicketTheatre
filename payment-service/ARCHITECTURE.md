# Architecture du Payment Service

## Vue d'ensemble

Le Payment Service est un microservice autonome basé sur Laravel 12 qui gère toutes les opérations de paiement via Stripe pour l'application TicketTheatre.

## Architecture en Couches

```
┌─────────────────────────────────────────┐
│         API Routes (routes/api.php)     │
│  - Health check                         │
│  - Payment endpoints                    │
│  - Webhook endpoints                    │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│      Controllers (Http/Controllers)     │
│  - PaymentController                    │
│  - WebhookController                    │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│      Services (app/Services)            │
│  - PaymentService (logique métier)      │
│  - Interaction avec Stripe SDK          │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│       Models (app/Models)               │
│  - Payment                              │
│  - Transaction                          │
│  - WebhookEvent                         │
└─────────────────────────────────────────┘
                    ↓
┌─────────────────────────────────────────┐
│      Database (SQLite/MySQL/PG)         │
│  - payments                             │
│  - transactions                         │
│  - webhook_events                       │
└─────────────────────────────────────────┘
```

## Composants Principaux

### 1. Controllers

#### PaymentController
- **Responsabilité**: Gérer les requêtes HTTP liées aux paiements
- **Endpoints**:
  - `POST /api/payments` - Créer un payment intent
  - `GET /api/payments/{id}` - Récupérer un paiement
  - `GET /api/payments/user/{userId}` - Paiements d'un utilisateur
  - `POST /api/payments/{id}/confirm` - Confirmer un paiement
  - `POST /api/payments/{id}/cancel` - Annuler un paiement
  - `POST /api/payments/{id}/refund` - Rembourser un paiement

#### WebhookController
- **Responsabilité**: Gérer les événements webhook de Stripe
- **Événements gérés**:
  - `payment_intent.succeeded`
  - `payment_intent.payment_failed`
  - `payment_intent.canceled`
  - `charge.refunded`
  - `charge.dispute.created`

### 2. Services

#### PaymentService
- **Responsabilité**: Logique métier et interaction avec l'API Stripe
- **Méthodes principales**:
  - `createPaymentIntent()` - Crée un payment intent
  - `confirmPayment()` - Confirme un paiement
  - `cancelPayment()` - Annule un paiement
  - `refundPayment()` - Effectue un remboursement
  - `retrievePaymentIntent()` - Récupère un payment intent
  - `getPayment()` - Récupère un paiement par ID
  - `getUserPayments()` - Récupère les paiements d'un utilisateur

### 3. Models

#### Payment
- **Relations**: `hasMany(Transaction::class)`
- **Scopes**:
  - `succeeded()` - Paiements réussis
  - `failed()` - Paiements échoués
  - `status($status)` - Filtrer par statut
- **Méthodes utiles**:
  - `markAsSucceeded()`
  - `markAsFailed()`
  - `markAsRefunded()`

#### Transaction
- **Relations**: `belongsTo(Payment::class)`
- **Types**: charge, refund, partial_refund, dispute, fee
- **Scopes**:
  - `type($type)` - Filtrer par type
  - `refunds()` - Uniquement les remboursements

#### WebhookEvent
- **Usage**: Traçabilité des événements webhook
- **Méthodes**:
  - `markAsProcessed()`
  - `markAsFailed($error)`

### 4. Requests (Validation)

#### CreatePaymentRequest
- Validation des données de création de paiement
- Règles strictes sur amount, currency, user_id

#### RefundPaymentRequest
- Validation des demandes de remboursement
- Validation des raisons de remboursement

### 5. Middleware

#### VerifyServiceApiKey
- **Optionnel**: Sécurité inter-microservices
- Vérifie l'en-tête `X-Service-Api-Key`
- Peut être désactivé en développement

## Flux de Données

### Flux de Création de Paiement

```
Client
  │
  ├─► POST /api/payments
  │   (amount, user_id, etc.)
  │
  ▼
PaymentController::createPaymentIntent()
  │
  ├─► Validation (CreatePaymentRequest)
  │
  ▼
PaymentService::createPaymentIntent()
  │
  ├─► Appel API Stripe
  │   stripe.paymentIntents.create()
  │
  ├─► Création Payment en DB
  │
  ├─► Création Transaction en DB
  │
  ▼
Réponse JSON
  {
    "success": true,
    "data": {...},
    "client_secret": "pi_xxx"
  }
```

### Flux de Webhook

```
Stripe
  │
  ├─► POST /api/webhooks/stripe
  │   (Event: payment_intent.succeeded)
  │
  ▼
WebhookController::handleWebhook()
  │
  ├─► Vérification de la signature
  │   Webhook::constructEvent()
  │
  ├─► Enregistrement WebhookEvent en DB
  │
  ├─► Traitement selon le type d'événement
  │   handlePaymentIntentSucceeded()
  │
  ├─► Mise à jour Payment en DB
  │   markAsSucceeded()
  │
  ├─► Mise à jour Transaction en DB
  │
  ▼
WebhookEvent::markAsProcessed()
```

## Sécurité

### 1. Authentification Stripe
- Clés API Stripe (secret/publishable)
- Signature des webhooks (webhook secret)

### 2. Validation des Données
- Form Requests pour toutes les entrées
- Validation stricte des montants
- Validation des devises

### 3. Protection CSRF
- Désactivée uniquement pour `/api/webhooks/stripe`
- Active pour tous les autres endpoints

### 4. Inter-Service (Optionnel)
- Middleware `VerifyServiceApiKey`
- Header `X-Service-Api-Key`

## Base de Données

### Schema

```sql
-- Table payments
CREATE TABLE payments (
    id BIGINT PRIMARY KEY,
    stripe_payment_intent_id VARCHAR UNIQUE,
    user_id BIGINT,
    order_id BIGINT NULL,
    amount DECIMAL(10,2),
    currency VARCHAR(3),
    status ENUM(...),
    payment_method VARCHAR NULL,
    customer_email VARCHAR NULL,
    description TEXT NULL,
    metadata JSON NULL,
    paid_at TIMESTAMP NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP NULL
);

-- Table transactions
CREATE TABLE transactions (
    id BIGINT PRIMARY KEY,
    payment_id BIGINT FOREIGN KEY,
    stripe_transaction_id VARCHAR NULL,
    type ENUM(...),
    amount DECIMAL(10,2),
    currency VARCHAR(3),
    status ENUM(...),
    reason TEXT NULL,
    metadata JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

-- Table webhook_events
CREATE TABLE webhook_events (
    id BIGINT PRIMARY KEY,
    stripe_event_id VARCHAR UNIQUE,
    type VARCHAR,
    payload JSON,
    processed BOOLEAN,
    processed_at TIMESTAMP NULL,
    error TEXT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

## Configuration

### Variables d'Environnement Critiques

```env
# Stripe
STRIPE_KEY=pk_xxx          # Clé publique
STRIPE_SECRET=sk_xxx        # Clé secrète
STRIPE_WEBHOOK_SECRET=whsec_xxx  # Secret webhook

# Service API (Optionnel)
SERVICE_API_KEY=xxx         # Authentification inter-services
```

## Tests

### Structure des Tests

```
tests/
├── Feature/
│   └── PaymentTest.php     # Tests d'intégration
└── Unit/
    └── (à venir)           # Tests unitaires
```

### Factories

- `PaymentFactory` - Génération de paiements de test
- États: `succeeded()`, `pending()`, `failed()`, `refunded()`

## Extensibilité

### Ajouter un Nouveau Type de Transaction

1. Modifier l'enum dans la migration `transactions`
2. Ajouter la logique dans `PaymentService`
3. Ajouter un scope si nécessaire dans `Transaction` model

### Ajouter un Nouveau Webhook

1. Ajouter le case dans `WebhookController::handleWebhook()`
2. Créer une méthode `handleXxxEvent()`
3. Tester avec Stripe CLI

### Ajouter une Nouvelle Devise

1. Modifier la validation dans `CreatePaymentRequest`
2. Tester avec Stripe (vérifier si supportée)

## Performance

### Optimisations

- Index sur `user_id`, `order_id`, `status` dans `payments`
- Index sur `payment_id`, `type`, `status` dans `transactions`
- Index sur `type`, `processed` dans `webhook_events`
- Eager loading: `Payment::with('transactions')`

### Monitoring

- Log des webhooks non traités
- Alert sur les disputes
- Monitoring des paiements échoués

## Déploiement

### Checklist Production

- [ ] Clés Stripe en mode Production
- [ ] HTTPS activé
- [ ] Webhook configuré sur Stripe Dashboard
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Migrations exécutées
- [ ] Logs configurés
- [ ] Monitoring activé

## Dépendances Externes

- **Stripe PHP SDK** (`stripe/stripe-php`): Communication avec API Stripe
- **Laravel Framework** (`^12.0`): Framework PHP

## Licence

MIT
