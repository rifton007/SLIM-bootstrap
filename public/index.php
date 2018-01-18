<?php
    require '../vendor/autoload.php';

    $app = new Slim\app([
        //Affiche les erreurs
        'settings' => [
            'displayErrorDetails' => true
        ]
    ]);

    require('../app/container.php');

    $app->get('/', App\Controllers\PagesController::class . ':home');
    $app->run();