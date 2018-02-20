<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

class AppController {

    protected $container;

    public function __construct($container) {
        $this->container = $container;
    }

    public function flash($message, $type = 'success') {
        if (!isset($_SESSION['flash'])) {
            $_SESSION['flash'] = [];
        }

        return $_SESSION['flash'][$type] = $message;
    }

    public function render(ResponseInterface $response, $file, $params = []) {

        $this->container->view->render($response, $file, $params);
    }

    public function mailer(): \Swift_Mailer {
        return $this->container->mailer;
    }

    public function getEmail(ResponseInterface $response, $page, $data = []) {


        //TODO peut être remplacer par getGlobals de TWIG
        $data['colors'] = $this->container->bootstrap->get_colors();
        $data['company'] = $this->container->bootstrap->get('company');
        $data['website'] = $this->container->bootstrap->get('website');
        $data['_locale'] = $this->container->bootstrap->get_i18nDefault();

        $page = '/Emails/' . $page . '.twig';
        $bodyHtml = $this->container->view->fetchBlock($page, 'body_html', $data);
        $bodyText = $this->container->view->fetchBlock($page, 'body_text', $data);

        return [
            'from' => $data['website']['smtp']['user'],
            'to'   => $data['company'],
            'html' => $bodyHtml,
            'text' => $bodyText
        ];

    }

    public function redirect(ResponseInterface $response, $page, $status = 301) {

        $lang = [];
        if (isset($_SESSION['_locale'])) {
            $lang = ['lang' => $_SESSION['_locale']];
        }

        $response = $response->withHeader('Location', $this->container->router->pathFor($page, $lang))
                             ->withStatus(301);

        return $response;

    }

}