<?php

/**
 * WARI NIOUMA — point d'entrée « relais » à placer dans le dossier web du domaine
 * lorsque l'on NE PEUT PAS faire pointer le domaine directement vers le dossier
 * « public » de l'application (racine figée).
 *
 * Disposition attendue (exemple formule avec ~/htdocs/<domaine>/) :
 *   ~/                              (= /home)
 *     ├── wariniouma_app/          ← toute l'application Laravel (HORS web)
 *     └── htdocs/
 *            └── wariniouma.com/   ← dossier web du domaine (racine figée)
 *                   ├── index.php  ← CE FICHIER
 *                   ├── .htaccess  ← copié depuis public/.htaccess
 *                   └── build/ assets/ favicon… (copiés depuis public/)
 *
 * Le dossier de l'application est détecté automatiquement : inutile d'éditer ce
 * fichier tant que l'app se trouve dans l'un des emplacements testés ci-dessous.
 * Au besoin, on peut aussi définir la variable d'environnement WARINIOUMA_APP_DIR.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Détection automatique du dossier de l'application (celui qui contient vendor/).
$app_dir = null;
foreach ([
    __DIR__.'/../../wariniouma_app',   // web = ~/htdocs/<domaine>/ , app = ~/wariniouma_app
    __DIR__.'/../wariniouma_app',      // web = ~/public_html/ , app = ~/wariniouma_app
    __DIR__.'/../wari-niouma',         // ancienne disposition
    getenv('WARINIOUMA_APP_DIR') ?: null,
] as $candidat) {
    if ($candidat && is_file($candidat.'/vendor/autoload.php')) {
        $app_dir = $candidat;
        break;
    }
}

if ($app_dir === null) {
    http_response_code(500);
    exit('WARI NIOUMA : dossier de l\'application introuvable (vendor/autoload.php manquant). Vérifiez l\'emplacement de l\'app.');
}

// Mode maintenance...
if (file_exists($maintenance = $app_dir.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Autoloader Composer...
require $app_dir.'/vendor/autoload.php';

// Démarrage de Laravel...
/** @var Application $app */
$app = require_once $app_dir.'/bootstrap/app.php';

$app->handleRequest(Request::capture());
