<?php

namespace App\Middelwares;

use Slim\Http\Request;
use Slim\Http\Response;

class FlashMiddelware {

    private $twig;

    public function __construct(\Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, $next) {

        //TODO créer une classe Flash pour gérer les instructions qui sont liées.
        $this->twig->addGlobal('flash', isset($_SESSION['flash']) ? $_SESSION['flash'] : []);

        if (isset($_SESSION['flash'])) {
            unset($_SESSION['flash']);
        }

        return $next($request, $response);
    }
}