<?php

    namespace App;

    //    use Respect\Validation\Exceptions\ValidationException;
    //    use Respect\Validation\Exceptions\NestedValidationException;
    //    use Respect\Validation\Validator as v;

    use GUMP as Gump;

    //    use Wixel\Gump;

    class Validator {

        private $page;
        private $data;

        private $messages = [
            'lastname',
            'firstname',
            'phone',
            'email',
            'message'
        ];

        public function __construct($page, $data) {
            $this->page = $page;
            $this->data = $data;
        }

        public function __invoke() {

            $locale = (isset($_SESSION['_locale'])) ? $_SESSION['_locale'] : 'fr';
            $gump = new Gump($locale);

            switch ($this->page) {
                case 'contact';

                    $gump->set_field_names([
                        'lastname'  => 'nom',
                        'firstname' => 'prénom',
                        'email'     => 'email',
                        'phone'     => 'numéro de téléphone',
                        'subject'   => 'sujet',
                        'message'   => 'message',
                    ]);

                    $gump->validation_rules([
                        'lastname'  => 'required|valid_name',
                        'firstname' => 'valid_name',
                        'email'     => 'required',
                        'phone'     => 'phone_number',
                        'subject'   => 'required',
                        'message'   => 'required|max_len,500',
                    ]);

                    break;
                default;
                    break;

            }

            $gump->run($this->data);
            $result = $gump->get_errors_array();

            return $result;
        }

    }