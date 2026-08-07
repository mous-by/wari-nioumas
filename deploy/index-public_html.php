<?php

/**
 * WARI NIOUMA — point d'entrée à placer dans public_html/ SI vous ne pouvez pas
 * pointer le domaine directement vers le dossier « public » de l'application.
 *
 * Disposition attendue :
 *   /home/VOTRE_COMPTE/
 *        ├── wari-niouma/        ← toute l'application Laravel (hors web)
 *        └── public_html/        ← contenu du dossier public/ de l'app
 *                 ├── index.php  ← CE FICHIER
 *                 ├── .htaccess  ← copié depuis public/.htaccess
 *                 ├── build/ assets/ favicon… (copiés depuis public/)
 *
 * Adaptez la ligne $app_dir ci-dessous au nom réel de votre dossier.
 */

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 👉 Nom du dossier (au même niveau que public_html) contenant l'application :
$app_dir = __DIR__.'/../wari-niouma';

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
