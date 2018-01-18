<?php

    $container = $app->getContainer();
    $container['view'] = function ($container) {
        $src = dirname(__DIR__);
        $view = new Slim\Views\Twig($src . '/app/views', [
            //todo DEV: cache désactiver
            'cache' => false //$src . '/app/cache'
        ]);

        $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()
                                                                             ->getBasePath()), '/');
        $view->addExtension(new Slim\Views\TwigExtension($container['router'], $basePath));

        return $view;
    };