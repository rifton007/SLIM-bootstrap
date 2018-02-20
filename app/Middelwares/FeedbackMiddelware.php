<?php

namespace App\Middelwares;

use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Class FeedbackMiddelware
 * Reload data informations posted by user in contact form.
 * @package App\Middelwares
 */
class FeedbackMiddelware {

    private $twig;

    public function __construct(\Twig_Environment $twig) {
        $this->twig = $twig;
    }

    public function __invoke(Request $request, Response $response, $next) {

        $feedback = (isset($_SESSION['feedback']) && isset($_SESSION['flash']['validator'])) ? $_SESSION['feedback'] : [];

        $this->twig->addGlobal('feedback', $feedback);

        if (isset($_SESSION['feedback'])) {
            unset($_SESSION['feedback']);
        }

        if ($request->getParsedBody()) {
            $_SESSION['feedback'] = $request->getParsedBody();
        }

        return $next($request, $response);

    }

}