# Runbook urgence - perte de tables MySQL (Production)

## Objectif
Remettre l'application en service le plus vite possible apres suppression accidentelle de tables/donnees.

## Perimetre projet
- Application: FocusApp (PHP MVC)
- Base de donnees production: focuszez_db
- Hebergeur: Namecheap (cPanel + sauvegardes automatiques)
- Configuration locale sensible: config/database.local.php (ne pas versionner)

## T0 - Actions immediates (0-5 min)
1. Stopper toute operation risquee dans phpMyAdmin.
2. Mettre le site en mode maintenance (ou limiter les ecritures).
3. Noter l'heure exacte de l'incident (timezone serveur).
4. Ouvrir un ticket Namecheap en priorite P1 (production outage).

## T1 - Restauration prioritaire (Namecheap)
Demander explicitement:
1. Restauration directe de la base focuszez_db depuis le dernier backup avant incident.
2. A defaut, point-in-time recovery (si disponible) juste avant suppression.
3. Confirmation de:
   - timestamp exact du restore
   - heure de debut
   - ETA
   - fin de restauration

Message type (EN):
"Please restore production database focuszez_db immediately from the latest backup before incident time. This is a P1 production outage. I accept potential data loss between backup time and incident time. Please confirm restore timestamp and completion."

## T2 - Verification post-restore (10-20 min)
1. Connexion admin OK.
2. Modules critiques OK:
   - factures
   - paiements
   - stock
   - depenses
3. Test fonctionnel minimal:
   - creer 1 facture test
   - enregistrer 1 paiement test
4. Verifier que les donnees recentes manquantes correspondent a la fenetre entre backup et incident.

## T3 - Plan B si aucune sauvegarde exploitable
1. Restaurer la structure via database/schema.sql pour remettre l'app en ligne.
2. Reappliquer les ajustements necessaires si besoin.
3. Informer metier: les donnees historiques supprimees ne sont pas recuperables sans backup/binlogs.

## Prevention (a faire dans les 24h)
1. Export manuel SQL immediat apres retour a la normale.
2. Politique 3-2-1 simplifiee:
   - 3 copies
   - 2 supports differents
   - 1 copie hors hebergeur
3. Limiter privileges SQL des comptes applicatifs (pas de DROP/ALTER en routine).
4. Revue mensuelle de restauration (test sur environnement de preproduction).

## References projet
- Structure SQL: database/schema.sql
- Config DB par defaut: config/database.php
- Doc architecture: docs/architecture.md
