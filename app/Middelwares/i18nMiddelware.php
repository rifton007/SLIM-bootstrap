<?php

namespace App\Middelwares;

use Slim\Http\Request;
use Slim\Http\Response;

class i18nMiddelware {

    private $twig;

    private $i18nDefault = null;
    private $i18nSupported = null;
    private $locale = null;
    private $locales = null;

    public function __construct(\Twig_Environment $twig, $container) {
        $this->twig = $twig;

        $this->i18nDefault = $container->get_i18n()[0];
        $this->i18nSupported = $container->get_i18n();
        $this->locales = $container->get_locales();

    }

    public function __invoke(Request $request, Response $response, $next) {

        $this->locale = $this->extractLocale($request);

        $url = $request->getUri()
                       ->getPath();

        //TODO doublon du code voir extractLocale() ???
        if ($url[0] == '/') {
            $url = substr($request->getUri()
                                  ->getPath(), 1);
        }

        $cond1 = $this->checkUrl($url);
        $cond2 = !empty($this->locale);
        $cond3 = $this->checkLocale($this->locale);

        if ($cond1 && $cond2 && $cond3) {

            //A UTILISER POUR SUPPORTER LA TRADUCTION PAR DOSSIER
            //$STAILang = new STAILang($this->requestLang, $this->langFolder);
            //$langArray = $STAILang->getFileAsArray();

            if (!empty($this->twig)) {
                $this->twig->addGlobal('locale', $this->locale);
            }

        } else {

            //si langue non valide on essaye avec la langue du navigateur
            $browserLocale = $this->getBrowserLocale();

            //                die($browserLocale);

            if ($this->checkLocale($browserLocale)) {
                //si la langue du navigateur est connue on redirige sur l'url corespondant à celle ci
                //TODO faire en sorte que les paramètre dans l'url ne soit pas effacé
                // (ex : quand on tape example.org/page que ça nous redirique pas sur example.org/fr mais sur example.org/fr/page)
                return $response->withRedirect($request->getUri()
                                                       ->getBasePath() . '/' . $browserLocale);
            } else {
                //si la langue du navigateur n'est pas disponible on redirique vers la langue par défault
                //TODO faire en sorte que les paramètre dans l'url ne soit pas effacé
                // (ex : quand on tape example.org/page que ça nous redirique pas sur example.org/fr mais sur example.org/fr/page)
                return $response->withRedirect($request->getUri()
                                                       ->getBasePath() . '/' . $this->i18nDefault);
            }

        }

        $this->updateSession();

        return $next($request, $response);

    }

    private function checkLocale($lang) {

        $result = (!empty($lang) && in_array($lang, $this->i18nSupported)) ? true : false;

        return $result;
    }

    private function checkUrl($url) {
        if ($url == '/') {
            return true;
        } elseif (empty($url)) {
            return true;
        } elseif (preg_match("/^[a-z]{2}$/", $url, $matches, PREG_OFFSET_CAPTURE, 0)) {
            return true;
        } elseif (preg_match("/^[a-z]{2}\//", $url, $matches, PREG_OFFSET_CAPTURE, 0)) {
            return true;
        } else {
            return false;
        }
    }

    private function updateSession() {

        putenv("LC_ALL={$this->locales[$this->locale]}");
        setlocale(LC_ALL, $this->locales[$this->locale]);
        bindtextdomain('messages', '../app/Locale');
        bind_textdomain_codeset('messages', 'UTF-8');
        textdomain('messages');
        $_SESSION['_locale'] = $this->locale;
        $this->twig->addGlobal('_locale', $_SESSION['_locale']);

    }

    public function extractLocale(Request $request) {
        $url = $request->getUri()
                       ->getPath();
        if ($url[0] == '/') {
            $url = substr($url, 1);
        }

        return explode('/', $url)[0];
    }

    public function getBrowserLocale() {
        if (isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
            return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        } else {
            return $this->i18nDefault;
        }
    }

}