# Guide d'installation TicketTheatre

## Prérequis système

Activez les extensions suivantes dans votre fichier php.ini:

- extension=iconv

- extension=pdo_sqlite

- extension=sqlite3

Outils nécessaires: PHP 8.2+, Composer, Node.js, npm.

## Configuration

1. Dans payment-service/.env, renseignez vos clés Stripe:

   - STRIPE_KEY=pk_test_...

   - STRIPE_SECRET=sk_test_...
   
   - STRIPE_WEBHOOK_SECRET=whsec_...

2. Les scripts de lancement configurent automatiquement la base SQLite partagée.

## Lancement

### Linux / macOS


```chmod +x run_app.sh
./run_app.sh
```

### Windows

Lancez le fichier `run_app.bat`. 

Accès :

```
Frontend: http://localhost:5173
Auth API: http://localhost:8081
Core API: http://localhost:8082
Payment API: http://localhost:8083
```
## Tests de paiement

Utilisez la Stripe CLI pour les webhooks: stripe listen --forward-to http://localhost:8083/api/webhooks/stripe