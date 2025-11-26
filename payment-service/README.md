# Payment Service - Microservice de Paiement avec Stripe

Microservice autonome basé sur Laravel 12 qui gère toutes les opérations de paiement via Stripe pour l'application TicketTheatre.

## Table des Matières

- [Installation Rapide](#installation-rapide)
- [Architecture](#architecture)
- [API Endpoints](#api-endpoints)
- [Base de Données](#base-de-données)
- [Webhooks Stripe](#webhooks-stripe)
- [Tests et Développement](#tests-et-développement)
- [Sécurité](#sécurité)
- [Déploiement](#déploiement)

---

## Installation Rapide

### Prérequis

- PHP 8.2 ou supérieur
- Composer
- SQLite, MySQL ou PostgreSQL
- Compte Stripe (https://stripe.com)

### 1. Installation des dépendances

```bash
cd payment-service
composer install
```

### 2. Configuration de l'environnement

```bash
cp .env.example .env
```

Éditez le fichier `.env` et configurez:

```env
APP_NAME="Payment Service"
APP_ENV=local
APP_DEBUG=true

# Database (SQLite par défaut)
DB_CONNECTION=sqlite

# Stripe (IMPORTANT - à configurer!)
STRIPE_KEY=pk_test_votre_cle_publique
STRIPE_SECRET=sk_test_votre_cle_secrete
STRIPE_WEBHOOK_SECRET=whsec_votre_secret_webhook
```

### 3. Obtenir vos clés Stripe

1. Créez un compte sur https://dashboard.stripe.com/register
2. Activez le mode test
3. Allez dans **Developers** → **API keys**
4. Copiez:
   - **Publishable key** → `STRIPE_KEY`
   - **Secret key** → `STRIPE_SECRET`

### 4. Configuration de la base de données

```bash
# Générer la clé d'application
php artisan key:generate

# Créer le fichier de base de données SQLite (si vous utilisez SQLite)
touch database/database.sqlite

# Exécuter les migrations
php artisan migrate
```

### 5. Configuration des Webhooks Stripe (Développement)

Pour le développement local, utilisez Stripe CLI:

```bash
# Installer Stripe CLI
# Windows (avec Scoop): scoop install stripe
# macOS: brew install stripe/stripe-cli/stripe
# Linux: voir https://stripe.com/docs/stripe-cli#install

# Se connecter à Stripe
stripe login

# Écouter les webhooks en local
stripe listen --forward-to http://localhost:8000/api/webhooks/stripe
```

La commande `stripe listen` affichera votre webhook secret. Copiez-le dans `.env`:

```env
STRIPE_WEBHOOK_SECRET=whsec_...
```

### 6. Démarrer le serveur

```bash
php artisan serve
```

Le service sera disponible sur http://localhost:8000

### Tester l'Installation

**Health Check:**

```bash
curl http://localhost:8000/api/health
```

Réponse attendue:
```json
{
  "status": "ok",
  "service": "payment-service"
}
```

---

## Architecture

### Vue d'ensemble

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

### Composants Principaux

#### PaymentController
- Gère les requêtes HTTP liées aux paiements
- Endpoints: création, confirmation, annulation, remboursement

#### WebhookController
- Gère les événements webhook de Stripe
- Événements: `payment_intent.succeeded`, `payment_intent.payment_failed`, `charge.refunded`, etc.

#### PaymentService
- Logique métier et interaction avec l'API Stripe
- Méthodes: `createPaymentIntent()`, `confirmPayment()`, `refundPayment()`, etc.

#### Models
- **Payment**: Représente un paiement avec ses relations et méthodes utiles
- **Transaction**: Représente une transaction (charge, remboursement, litige)
- **WebhookEvent**: Traçabilité des événements webhook reçus

---

## API Endpoints

### Health Check

```
GET /api/health
```

### Créer un Payment Intent

```http
POST /api/payments
Content-Type: application/json

{
  "amount": 50.00,
  "currency": "eur",
  "user_id": 1,
  "order_id": 123,
  "customer_email": "customer@example.com",
  "description": "Achat de billets",
  "metadata": {
    "ticket_ids": [1, 2, 3]
  }
}
```

**Réponse:**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "stripe_payment_intent_id": "pi_xxx",
    "amount": 50.00,
    "status": "pending",
    ...
  },
  "client_secret": "pi_xxx_secret_yyy"
}
```

### Confirmer un paiement

```http
POST /api/payments/{paymentIntentId}/confirm
Content-Type: application/json

{
  "payment_method_id": "pm_xxx"
}
```

**Note:** Le `payment_method_id` est généré par Stripe.js côté frontend. Pour tester sur Postman, utilisez: `pm_card_visa`

### Récupérer un paiement

```
GET /api/payments/{paymentId}
```

### Récupérer les paiements d'un utilisateur

```
GET /api/payments/user/{userId}
```

### Annuler un paiement

```
POST /api/payments/{paymentIntentId}/cancel
```

### Rembourser un paiement

```http
POST /api/payments/{paymentId}/refund
Content-Type: application/json

{
  "amount": 25.00,  // Optionnel: pour un remboursement partiel
  "reason": "requested_by_customer"  // Optionnel: duplicate, fraudulent, requested_by_customer
}
```

### Webhook Stripe

```
POST /api/webhooks/stripe
X-Stripe-Signature: xxx
```

---

## Base de Données

### Table `payments`

| Colonne | Type | Description |
|---------|------|-------------|
| id | BIGINT | ID du paiement |
| stripe_payment_intent_id | VARCHAR | ID du Payment Intent Stripe |
| user_id | BIGINT | ID de l'utilisateur |
| order_id | BIGINT | ID de la commande (nullable) |
| amount | DECIMAL(10,2) | Montant du paiement |
| currency | VARCHAR(3) | Devise (par défaut: EUR) |
| status | ENUM | pending, processing, succeeded, failed, canceled, refunded |
| payment_method | VARCHAR | Méthode de paiement utilisée |
| customer_email | VARCHAR | Email du client |
| description | TEXT | Description du paiement |
| metadata | JSON | Métadonnées JSON |
| paid_at | TIMESTAMP | Date du paiement |

### Table `transactions`

| Colonne | Type | Description |
|---------|------|-------------|
| id | BIGINT | ID de la transaction |
| payment_id | BIGINT | Référence au paiement |
| stripe_transaction_id | VARCHAR | ID Stripe de la transaction |
| type | ENUM | charge, refund, partial_refund, dispute, fee |
| amount | DECIMAL(10,2) | Montant |
| currency | VARCHAR(3) | Devise |
| status | ENUM | pending, succeeded, failed |
| reason | TEXT | Raison (pour les remboursements/litiges) |
| metadata | JSON | Métadonnées JSON |

### Table `webhook_events`

| Colonne | Type | Description |
|---------|------|-------------|
| id | BIGINT | ID de l'événement |
| stripe_event_id | VARCHAR | ID de l'événement Stripe |
| type | VARCHAR | Type d'événement |
| payload | JSON | Données de l'événement |
| processed | BOOLEAN | Indique si l'événement a été traité |
| processed_at | TIMESTAMP | Date de traitement |
| error | TEXT | Erreur éventuelle |

---

## Webhooks Stripe

### Configuration en Production

1. Allez dans votre Dashboard Stripe
2. Naviguez vers **Developers** > **Webhooks**
3. Cliquez sur **Add endpoint**
4. URL: `https://votre-domaine.com/api/webhooks/stripe`
5. Sélectionnez les événements suivants:
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
   - `payment_intent.canceled`
   - `charge.refunded`
   - `charge.dispute.created`
6. Copiez le **Signing secret** dans votre `.env` comme `STRIPE_WEBHOOK_SECRET`

### Événements Gérés

- `payment_intent.succeeded`: Paiement réussi
- `payment_intent.payment_failed`: Échec du paiement
- `payment_intent.canceled`: Paiement annulé
- `charge.refunded`: Remboursement effectué
- `charge.dispute.created`: Litige créé

---

## Tests et Développement

### Cartes de Test Stripe

| Carte | Numéro | Résultat |
|-------|--------|----------|
| Visa | `4242424242424242` | Succès |
| Visa (3D Secure) | `4000002500003155` | Authentification requise |
| Mastercard | `5555555555554444` | Succès |
| Declined | `4000000000000002` | Refusé |
| Insufficient funds | `4000000000009995` | Fonds insuffisants |

**Valeurs génériques:**
- **Date d'expiration:** N'importe quelle date future (ex: `12/2025`)
- **CVV:** N'importe quel 3 chiffres (ex: `123`)
- **Code postal:** N'importe lequel (ex: `12345`)

### Payment Methods de test prêts à l'emploi

Pour tester avec Postman ou curl:

```json
{
  "payment_method_id": "pm_card_visa"
}
```

Autres options: `pm_card_mastercard`, `pm_card_amex`, `pm_card_discover`

### Exemple d'Utilisation (Frontend avec Stripe.js)

```javascript
// 1. Créer un Payment Intent
const response = await fetch('/api/payments', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json' },
  body: JSON.stringify({
    amount: 50.00,
    currency: 'eur',
    user_id: 1,
    customer_email: 'customer@example.com',
  }),
});

const { client_secret } = await response.json();

// 2. Confirmer avec Stripe.js
const stripe = Stripe('pk_test_your_publishable_key');
const { error, paymentIntent } = await stripe.confirmCardPayment(
  client_secret,
  {
    payment_method: {
      card: cardElement,
      billing_details: {
        email: 'customer@example.com',
      },
    },
  }
);

if (paymentIntent.status === 'succeeded') {
  console.log('Payment succeeded!');
}
```

### Exécuter les Tests

```bash
composer test
```

### Lancer le serveur de développement

```bash
composer dev
```

Ou individuellement:

```bash
php artisan serve
php artisan queue:listen
```

---

## Sécurité

### 1. Authentification Stripe
- Clés API Stripe (secret/publishable)
- Signature des webhooks (webhook secret)

### 2. Validation des Données
- Form Requests pour toutes les entrées
- Validation stricte des montants et devises

### 3. Protection CSRF
- Désactivée uniquement pour `/api/webhooks/stripe`
- Active pour tous les autres endpoints

### 4. Inter-Service (Optionnel)
- Middleware `VerifyServiceApiKey`
- Header `X-Service-Api-Key`

### Recommandations

- Toutes les requêtes webhook sont vérifiées avec la signature Stripe
- Les clés API sont stockées dans des variables d'environnement
- Les événements webhook sont enregistrés pour audit
- Ne commitez JAMAIS vos clés Stripe dans Git

---

## Déploiement

### Checklist Production

- [ ] Clés Stripe en mode Production (`pk_live_xxx`, `sk_live_xxx`)
- [ ] HTTPS activé
- [ ] Webhook configuré sur Stripe Dashboard
- [ ] `APP_DEBUG=false`
- [ ] `APP_ENV=production`
- [ ] Migrations exécutées
- [ ] Logs configurés
- [ ] Monitoring activé
- [ ] Système de queue configuré (Redis/SQS)

### Variables d'Environnement Critiques

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://votre-domaine.com

# Stripe Production
STRIPE_KEY=pk_live_xxx
STRIPE_SECRET=sk_live_xxx
STRIPE_WEBHOOK_SECRET=whsec_live_xxx

# Service API (Optionnel)
SERVICE_API_KEY=xxx
```

### Recommandations

1. Utilisez HTTPS en production
2. Configurez un système de monitoring pour les webhooks
3. Vérifiez régulièrement les événements webhook non traités
4. Configurez des alertes pour les disputes
5. Sauvegardez régulièrement la base de données
6. Configurez une queue pour les tâches asynchrones

---

## Problèmes Courants

### Erreur "Class 'Stripe\StripeClient' not found"

```bash
composer install
composer dump-autoload
```

### Erreur de migration

```bash
php artisan migrate:fresh
```

### Webhook ne fonctionne pas

Vérifiez que:
- Stripe CLI est en cours d'exécution (développement)
- Le `STRIPE_WEBHOOK_SECRET` est correct dans `.env`
- Le serveur Laravel est démarré
- La signature Stripe est valide

---

## Support et Documentation

- [Documentation Stripe](https://stripe.com/docs/api)
- [Documentation Laravel](https://laravel.com/docs)
- [Stripe Support](https://support.stripe.com)

## Dépendances Principales

- **stripe/stripe-php**: SDK Stripe pour PHP
- **Laravel 12**: Framework PHP
