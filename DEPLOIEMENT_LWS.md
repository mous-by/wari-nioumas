# Déploiement de WARI NIOUMA sur LWS

Guide pas-à-pas pour mettre l'application en ligne sur un hébergement **LWS** (mutualisé).
Fichiers utiles déjà préparés dans le projet :

- **`.env.production`** — modèle de configuration de production (à copier en `.env` sur le serveur)
- **`deploy/deploy.sh`** — script d'installation/mise à jour (si vous avez SSH)
- **`deploy/index-public_html.php`** — point d'entrée si le domaine ne peut pas viser le dossier `public/`

---

## 0. Prérequis dans le panneau LWS

1. **Version de PHP : 8.3 obligatoire.**
   L'application utilise `spatie/laravel-permission` qui exige **PHP ≥ 8.3**. Dans LWS,
   ouvrez « Gérer PHP / Version de PHP » et choisissez **PHP 8.3**.
2. **Extensions PHP** (normalement toutes actives chez LWS) : `pdo_mysql`, `mbstring`,
   `openssl`, `tokenizer`, `xml`/`dom`, `ctype`, `json`, `bcmath`, `fileinfo`, `gd`, `curl`, `zip`.
   (`gd` sert aux PDF/icônes, `dom`+`mbstring` à dompdf.)
3. **HTTPS/SSL** : activez le certificat SSL gratuit (Let's Encrypt) sur le domaine.

---

## 1. Créer la base de données MySQL

Dans le panneau LWS → **Bases de données MySQL** :
- Créez une base (ex. `wari_niouma`).
- Créez un utilisateur + mot de passe, et **associez-le à la base avec tous les privilèges**.
- Notez : **nom de la base, utilisateur, mot de passe, hôte** (souvent `localhost`).

---

## 2. Mettre les fichiers en ligne

Avant l'envoi, côté machine locale, assurez-vous que le front est compilé :
```bash
npm run build        # crée public/build (déjà présent ici)
```

**N'envoyez PAS** : `node_modules/`, `.git/`, `tests/`, votre `.env` de dev.
**Envoyez bien** : tout le reste, y compris `public/build/` et `vendor/`
(si vous ne pouvez pas lancer Composer sur le serveur — voir §4B).

Deux dispositions possibles :

### Disposition A (recommandée) — le domaine vise le dossier `public/`
Si LWS vous laisse choisir la « racine » du domaine/sous-domaine :
- Envoyez **tout le projet** dans un dossier (ex. `~/wari-niouma`).
- Faites pointer la racine du domaine sur `~/wari-niouma/public`.
- C'est la plus propre : rien à modifier.

### Disposition B (repli) — hébergement classique avec `public_html`
Quand la racine est figée sur `public_html` :
1. Envoyez **toute l'application** dans un dossier **hors web**, ex. `~/wari-niouma`.
2. Copiez **le contenu de `public/`** de l'app dans `~/public_html/`
   (donc `build/`, `assets/`, `favicon…`, `.htaccess`, `manifest.webmanifest`, `sw.js`…).
3. Remplacez `~/public_html/index.php` par **`deploy/index-public_html.php`**
   (renommez-le en `index.php`) et **adaptez la ligne `$app_dir`** au nom réel du dossier
   de l'application (`~/wari-niouma`).

> Le fichier `.htaccess` (réécriture d'URL) se trouve dans `public/.htaccess` — il doit être
> présent à côté du `index.php` du web.

---

## 3. Configurer le `.env`

Sur le serveur, à la racine de l'application :
```bash
cp .env.production .env
```
Puis éditez `.env` et renseignez :
- `APP_URL=https://votre-domaine.com`
- `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD` (et `DB_HOST=localhost`)
- Laissez `APP_ENV=production`, `APP_DEBUG=false`, `SESSION_SECURE_COOKIE=true`.

---

## 4. Installer l'application

### 4A. Vous avez un accès SSH (recommandé)
À la racine du projet :
```bash
bash deploy/deploy.sh
```
Le script fait : `composer install --no-dev`, génère la clé, lance les migrations,
crée les rôles + le **compte superadmin**, crée le lien de stockage, et met en cache
config/routes/vues. (Détail des commandes dans le script.)

### 4B. Pas de SSH (FTP uniquement)
1. **En local**, préparez le dossier prêt à l'emploi :
   ```bash
   composer install --no-dev --optimize-autoloader
   php artisan key:generate            # écrit APP_KEY dans votre .env local
   ```
   Récupérez la valeur `APP_KEY=base64:...` et mettez-la dans le `.env` du serveur
   (ou copiez tout `vendor/` + la clé).
2. Envoyez `vendor/` par FTP.
3. **Migrations sans SSH** : deux options —
   - via **phpMyAdmin** de LWS, importez un dump du schéma
     (générable en local par `php artisan schema:dump` → `database/schema/mysql-schema.sql`,
     puis exécutez ensuite les éventuelles migrations restantes), **ou**
   - créez une route temporaire protégée qui appelle `Artisan::call('migrate --force')`
     puis **supprimez-la** aussitôt.
4. Créez le superadmin en important les rôles/permissions via `RoleSeeder`
   (même logique : `Artisan::call('db:seed --class=RoleSeeder --force')`).

---

## 5. Droits, stockage, cache

- **Dossiers inscriptibles** : `storage/` et `bootstrap/cache/` doivent être accessibles en
  écriture par le serveur web (chmod `775` en général).
- **Lien de stockage** (photos de profil, signatures, cachets) :
  `php artisan storage:link` (déjà fait par `deploy.sh`).
  Si les liens symboliques sont bloqués chez LWS, créez un lien depuis le gestionnaire de
  fichiers, ou remplacez le lien par une copie du dossier `storage/app/public` vers `public/storage`.
- Après **toute modification du `.env`** en production, relancez :
  `php artisan config:cache` (sinon l'ancienne config reste en cache).

---

## 6. Première connexion

Compte **superadmin** créé automatiquement par le seed :
- **Téléphone : `70000000`**
- **Mot de passe : `password`**

➡️ **Changez ce mot de passe immédiatement** (menu Profil), puis créez les vrais comptes
(Directeur général, comptable, caissier…) depuis **Configuration → Liste Utilisateur**.

---

## 7. Vérifications finales

- Le site s'ouvre en **https** sans avertissement « mixed content » (assets bien en https —
  géré par `trustProxies` déjà configuré).
- La page de connexion s'affiche avec le carrousel (assets compilés `public/build`).
- Sur mobile : installation **PWA** possible (« Ajouter à l'écran d'accueil »), tableaux
  défilables, boutons visibles.
- Les PDF (bulletins, mandats, rapport financier) se génèrent.

---

## 8. Mises à jour ultérieures

Après avoir envoyé de nouveaux fichiers :
```bash
bash deploy/deploy.sh     # relance composer, migrations, et re-cache
# (en SSH). Sinon : réimportez les migrations + php artisan config:cache/route:cache/view:cache
```

---

## 9. Dépannage rapide

| Symptôme | Cause probable / solution |
|---|---|
| Erreur 500 blanche | `APP_KEY` manquante → `php artisan key:generate` ; ou droits `storage/` |
| Page sans style (CSS/JS) | mauvaise racine web (viser `public/`) ; ou `APP_URL` en http au lieu de https |
| « SQLSTATE… Access denied » | identifiants `DB_*` du `.env` incorrects |
| « There is no permission named… » | lancer `php artisan permission:cache-reset` après un seed |
| Version PHP | vérifier **PHP 8.3** dans le panneau LWS |
| Modif `.env` sans effet | `php artisan config:cache` (le cache config prime) |
| Uploads (photos/signatures) 404 | refaire `php artisan storage:link` |

---

### Récapitulatif express (SSH)
```bash
# 1) PHP 8.3 + base MySQL créés dans LWS
# 2) fichiers envoyés, domaine → dossier public/
cp .env.production .env      # puis remplir APP_URL + DB_*
bash deploy/deploy.sh        # installe tout
# 3) se connecter en 70000000 / password puis changer le mot de passe
```
