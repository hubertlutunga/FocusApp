# Architecture Focus Group ERP

## Vue d'ensemble

Application web de facturation, stock et services conçue en PHP 8+, MySQL, Bootstrap 5 et JavaScript avec une architecture MVC légère et extensible.

## Arborescence cible

```text
www.focusapp.com/
├── app/
│   ├── Controllers/
│   ├── Core/
│   ├── Helpers/
│   ├── Middleware/
│   ├── Models/
│   └── Views/
│       ├── auth/
│       ├── dashboard/
│       ├── errors/
│       ├── layouts/
│       └── partials/
├── config/
│   ├── app.php
│   └── database.php
├── database/
│   └── schema.sql
├── docs/
│   └── architecture.md
├── public/
│   └── assets/
│       ├── css/
│       └── js/
├── routes/
│   └── web.php
├── storage/
│   ├── cache/
│   └── logs/
├── .htaccess
└── index.php
```

## Modules métier prévus

1. Authentification et utilisateurs
2. Paramètres entreprise
3. Clients
4. Fournisseurs
5. Catégories et unités
6. Produits
7. Services
8. Stock et mouvements
9. Approvisionnements
10. Devis
11. Factures
12. Paiements
13. Dépenses
14. Tableau de bord
15. Rapports
16. Journal d’activité

## Règles de conception

- Front controller unique via `index.php`
- Router HTTP simple avec middlewares
- Contrôleurs responsables du flux
- Modèles PDO avec requêtes préparées
- Vues Bootstrap 5, SweetAlert2, DataTables et Chart.js
- Sessions sécurisées et protection CSRF
- Suppression logique (`deleted_at`) sur les tables métier
- Journalisation systématique des actions sensibles
- Numérotation centralisée via `number_sequences`

## Stratégie de livraison

- Étape 1 : socle MVC, schéma SQL et authentification
- Étape 2 : paramètres entreprise, clients, fournisseurs, catégories, unités
- Étape 3 : produits, services, stock, approvisionnements
- Étape 4 : devis, factures, paiements, PDF
- Étape 5 : dépenses, rapports, journal d’activité avancé
- Étape 6 : raffinement UX, contrôles métiers et sécurité
