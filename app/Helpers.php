<?php namespace App;

class Helpers extends \Twig_Extension {

    /**
     * @var \Slim\Interfaces\RouterInterface
     */
    private $router;

    /**
     * @var string|\Slim\Http\Uri
     */
    private $uri;

    //    protected $container;

    public function getName() {
        return 'Helpers';
    }

    //    public function __construct() {
    //        $this->container = $container;
    //    }

    public function __construct($router, $uri) {
        $this->router = $router;
        $this->uri = $uri;
    }

    public function getFunctions() {
        return [
            new \Twig_SimpleFunction('current_page', [
                $this,
                'currentPage'
            ])
        ];

    }

    public function currentPage() {
        $subdomain = explode('/', $this->uri->getPath());

        return (count($subdomain) < 3) ? 'home' : end($subdomain);

    }
}