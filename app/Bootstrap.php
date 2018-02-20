<?php namespace App;

class Bootstrap {

    private $settings = null;
    private $company = null;
    private $website = null;
    private $socials = null;
    private $state = 'offline';
    private $states = [
        'offline',
        'upkeep',
        'localhost',
        'online',
        'disabled'
    ];

    private $colors = [
        'blue'   => '#007bff',
        'indigo' => '#6610f2',
        'purple' => '#6f42c1',
        'pink'   => '#e83e8c',
        'red'    => '#dc3545',
        'orange' => '#fd7e14',
        'yellow' => '#ffc107',
        'green'  => '#28a745',
        'teal'   => '#20c997',
        'cyan'   => '#17a2b8',
    ];

    public $locales = [
        'fr' => 'fr_BE',
        'es' => 'es_ES',
        'en' => 'en_GB',
        'nl' => 'nl_BE',
        'de' => 'de_DE',
    ];

    public function __construct() {

        $this->settings = require dirname(__DIR__) . '/app/settings.php';

        $this->company = $this->settings['company'];
        $this->website = $this->settings['website'];
        $this->socials = $this->settings['socials'];

        if (in_array($this->website['state'], $this->states)) {
            $this->state = $this->website['state'];
        }

        if (isset($this->settings['colors'])) {
            $this->colors = array_merge($this->colors, $this->settings['colors']);
        }

    }

    public function get($category, $key = null) {

        $extractData = null;

        if (empty($key)) {
            $extractData = (!isset($this->$category)) ? [] : $this->$category;
        } else {

            $extractData = (!isset($this->$category[$key])) ? '' : $this->$category[$key];
        }

        return $extractData;
    }

    public function loadSettings($framework) {

        $framework = strtolower($framework);

        $settings = [];

        switch ($this->state) {
            case 'offline':
            case 'upkeep':
            case 'online':
            case 'disabled':
                switch ($framework) {
                    case 'slim' :
                        $settings = [
                            'displayErrorDetails' => false
                        ];
                        break;
                    case 'twig' :
                        $settings = [
                            'cache' => '../app/Cache/tmp',
                            'debug' => false,
                        ];
                        break;
                    default:
                        $settings = [];
                        break;
                }

                break;
            case 'localhost':
                switch ($framework) {
                    case 'slim' :
                        $settings = [
                            'displayErrorDetails' => true
                        ];
                        break;
                    case 'twig' :
                        $settings = [
                            'cache'             => false,
                            'debug'             => true,
                            'strict_variables ' => true
                        ];
                        break;
                    default:
                        $settings = [];
                        break;
                }

                break;
            default:
                break;

        }

        return $settings;

    }

    public function i18nSupported() {

        if (isset($this->website['i18n']) && (count($this->website['i18n']) > 1)) {
            return true;
        } else {
            return false;
        }

    }

    public function get_i18nDefault() {

        $cond1 = (!isset($this->website['i18n']));
        $cond2 = (empty($this->website['i18n']));
        $i18n = ($cond1 || $cond2) ? 'fr' : $this->website['i18n'][0];

        putenv("LC_ALL={$this->locales[$i18n]}");
        setlocale(LC_ALL, $this->locales[$i18n]);
        bindtextdomain('messages', '../app/Locale');
        bind_textdomain_codeset('messages', 'UTF-8');
        textdomain('messages');
        $_SESSION['_locale'] = $i18n;

        return $i18n;
    }

    public function get_locales() {
        return $this->locales;
    }

    public function get_i18n() {
        return (!isset($this->website['i18n']) || empty($this->website['i18n'])) ? 'fr' : $this->website['i18n'];
    }

    public function get_smtp() {

        if (isset($this->website['smtp'])) {
            $smtp = $this->website['smtp'];

            $cond1 = (isset($smtp['server']) && !empty($smtp['server'])) ? true : false;
            $cond2 = (isset($smtp['port']) && !empty($smtp['port'])) ? true : false;

            if (!$cond1 || !$cond2) {
                goto localhost;
            }

        } else {
            localhost:
            $this->website['smtp']['server'] = 'mail';
            $this->website['smtp']['port'] = 25;
            $this->website['smtp']['user'] = null;
            $this->website['smtp']['password'] = null;
        }

        return $this->website['smtp'];

    }

    public function get_socials() {

        $socials = [];
        foreach ($this->socials as $key => $social) {
            if (!empty($social)) {
                $socials[$key] = $social;
            }
        }

        return $socials;

    }

    public function get_colors() {
        return $this->colors;
    }

    public function get_state() {
        return $this->state;
    }

}
