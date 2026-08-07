#!/usr/bin/env bash
# =====================================================================
#  WARI NIOUMA — Script d'installation / mise à jour en PRODUCTION
#  À lancer en SSH, à la racine du projet (là où se trouve « artisan ») :
#      bash deploy/deploy.sh
#  Prérequis : le fichier .env de production est déjà en place et rempli.
# =====================================================================
set -e

echo "==> Dépendances PHP (sans dev, autoload optimisé)"
composer install --no-dev --optimize-autoloader

# Génère APP_KEY seulement si elle est absente
if ! grep -q '^APP_KEY=base64:' .env; then
    echo "==> Génération de la clé d'application"
    php artisan key:generate --force
fi

echo "==> Migrations de la base"
php artisan migrate --force

echo "==> Rôles + permissions + compte superadmin"
php artisan db:seed --class=RoleSeeder --force
php artisan permission:cache-reset

echo "==> Lien symbolique du stockage (photos, signatures, cachets)"
php artisan storage:link || echo "  (lien déjà présent ou non supporté — voir la doc)"

echo "==> Mise en cache config / routes / vues (performance)"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo ""
echo "✅ Déploiement terminé."
echo "   Connexion superadmin par défaut : téléphone 70000000 / mot de passe password"
echo "   → CHANGEZ ce mot de passe immédiatement après la première connexion."
