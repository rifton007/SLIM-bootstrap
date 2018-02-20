<?php
require '../vendor/autoload.php';

session_start();

$app = new Slim\App();

use App\Controllers\PagesController as Controller;

require('../app/Containers.php');

$container->get('settings')
          ->replace($container->bootstrap->loadSettings('slim'));

switch ($container->bootstrap->get_state()) {
    case 'offline':
    case 'disabled':
    case 'upkeep':
        $app->any('/[{path:.*}]', Controller::class);
        break;
    case 'localhost':
    case 'online':
        loadMiddelwares();
        loadRouter();
        break;

}

function loadMiddelwares() {
    global $app, $container;
    $app->add(new \App\Middelwares\FlashMiddelware($container->view->getEnvironment()));
    $app->add(new \App\Middelwares\FeedbackMiddelware($container->view->getEnvironment()));
    $app->add(new \App\Middelwares\CsrfMiddelware($container->view->getEnvironment(), $container->csrf));
    $app->add($container->csrf);
}

function loadRouter() {
    global $app, $container;
    if ($container->bootstrap->i18nSupported()) {
        $app->add(new \App\Middelwares\i18nMiddelware($container->view->getEnvironment(), $container->bootstrap));
        $app->group('/{lang:[a-z]{2}}', function () use ($app, $container) {
            $app->get('', Controller::class)
                ->setName('home');
            $app->get('/contact', Controller::class)
                ->setName('contact');
            $app->get('/aboutus', Controller::class)
                ->setName('aboutus');
            $app->post('/postform', Controller::class . ':postform');
            $app->get('/policy', Controller::class)
                ->setName('policy');
            $app->get('/devmail', Controller::class . ':devmail')
                ->setName('devmail');
        });
    } else {
        $app->get('/', Controller::class)
            ->setName('home');
        $app->get('/contact', Controller::class)
            ->setName('contact');
        $app->get('/aboutus', Controller::class)
            ->setName('aboutus');
        $app->post('/postform', Controller::class . ':postform');
        $app->get('/policy', Controller::class)
            ->setName('policy');
        $app->get('/devmail', Controller::class . ':devmail')
            ->setName('devmail');
    }
}

$app->run();

