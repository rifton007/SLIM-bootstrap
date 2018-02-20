<?php

$container = $app->getContainer();

$container['bootstrap'] = function () {
    return new App\Bootstrap();
};

$container['PagesController::class'] = function () use ($container) {
    return new App\Controllers\PagesController($container);
};

$container['view'] = function ($container) {

    $src = dirname(__DIR__);
    $view = new Slim\Views\Twig($src . '/app/Views', $container->bootstrap->loadSettings('twig'));

    $basePath = rtrim(str_ireplace('index.php', '', $container['request']->getUri()
                                                                         ->getBasePath()), '/');

    $view->addExtension(new Slim\Views\TwigExtension($container['router'], $container['request']->getUri()));
    $view->getEnvironment()
         ->addGlobal('company', $container['bootstrap']->get('company'));
    $view->getEnvironment()
         ->addGlobal('socials', $container['bootstrap']->get_socials());
    $view->getEnvironment()
         ->addGlobal('colors', $container['bootstrap']->get_colors());

    if ($container['bootstrap']->i18nSupported()) {
        $view->getEnvironment()
             ->addGlobal('i18n', $container['bootstrap']->get_i18n());

    } else {
        $view->getEnvironment()
             ->addGlobal('_locale', $container['bootstrap']->get_i18nDefault());
    }

    $view->addExtension(new Twig\Extensions\I18nExtension());
    $view->addExtension(new Twig\Extensions\IntlExtension());

    if ($view->getEnvironment()
             ->isDebug()) {
        $view->addExtension(new Twig_Extension_Debug());
    }

    $view->addExtension(new Twig\Extensions\TextExtension());
    $view->addExtension(new \App\Helpers($container['router'], $container['request']->getUri()));

    return $view;
};

$container['csrf'] = function ($container) {
    $guard = new Slim\Csrf\Guard();
    $guard->setFailureCallable(function ($request, $response, $next) use ($container) {

        $request = $request->withAttribute('csrf_status', false);
        $_SESSION['flash']['error'] = 'Cette requête a déjà été soumise';

        return $response->withRedirect('contact');

    });

    return $guard;
};

$container['mailer'] = function ($container) {
    $smtp = $container['bootstrap']->get_smtp();

    $transport = (new Swift_SmtpTransport($smtp['server'], $smtp['port']));

    $transport->setUsername($smtp['user'])
              ->setPassword($smtp['password'])
              ->setEncryption('tls');
    $mailer = new Swift_Mailer($transport);

    return $mailer;
};

$container['notFoundHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['view']->render($response, '/Errors/404.twig')
                                 ->withStatus(404);
    };
};

$container['notAllowedHandler'] = function ($container) {
    return function ($request, $response) use ($container) {
        return $container['view']->render($response, '/Errors/500.twig')
                                 ->withStatus(500);
    };
};
