<?php

namespace App\Controllers;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use App\Validator;

class PagesController extends AppController {

    private $i18nSupported = null;

    public function __construct($container) {
        parent::__construct($container);
        $this->i18nSupported = $this->container->bootstrap->i18nSupported();
    }

    public function __invoke(RequestInterface $request, ResponseInterface $response, $name) {

        $name = $this->get_page($request);

        if (method_exists($this, $name)) {
            $this->$name($request, $response);
        } else {
            $name = (empty($name)) ? 'home' : $name;

            switch ($this->container->bootstrap->get_state()) {
                case 'offline':
                    $name = 'offline';
                    break;
                case 'upkeep':
                    $name = 'upkeep';
                    break;
                case 'disabled':
                    $name = 'disabled';
                    break;

            }

            $this->render($response, "Pages/{$name}.twig");
        }

    }

    private function get_page($request) {

        $getPath = explode('/', $request->getUri()
                                        ->getPath());

        if ($this->i18nSupported) {
            if (count($getPath) <= 2) {
                $name = 'home';
            } else {
                $name = end($getPath);
            }
        } else {
            $name = end($getPath);
        }

        return $name;

    }

    public function postform($request, $response) {

        $data = $request->getParams();
        $validation = new Validator('contact', $data);
        $errors = $validation();
        $status = null;

        if (empty($errors)) {

            $getMail = $this->getEmail($response, 'contact', $data);
            $data['lastname'] = ucfirst($data['lastname']);

            $message = new \Swift_Message("{$data['lastname']} | {$data['subject']}");
            $message->setFrom([$data['email'] => $data['lastname']])
                    ->setTo($getMail['to']['email'])
                    ->setSender([$getMail['from'] => $getMail['to']['name']])
                    ->setBody($getMail['text'], 'text/plain')
                    ->addPart($getMail['html'], 'text/html');

            if (!$this->mailer()
                      ->send($message, $errors)) {
                print_r($errors);
            }
            $this->flash('Votre message a bien été envoyé');

        } else {

            $this->flash("Votre message n'a pu être envoyé", 'error');
            $this->flash($errors, 'validator');

        }

        return $this->redirect($response, 'contact', 400);

    }

    public function devmail($request, $response) {

        $this->render($response, 'Emails/contact.twig');
    }

}