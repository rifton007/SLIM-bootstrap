<?php

    namespace App;

    use Respect\Validation\Exceptions\ValidationException;
    use Respect\Validation\Exceptions\NestedValidationException;
    use Respect\Validation\Validator as v;

    class ValidatorOLD {

        private $page;
        private $data;
        private $errors;

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
            $this->validationData();
        }

        private function validationData() {

            $rule = null;

            switch ($this->page) {
                case 'contact';
                    $rule = v::key('lastname', v::notEmpty()
                                                ->alpha("-'"))
                             ->key('firstname', v::optional(v::alpha("-'")))
                             ->key('phone', v::optional(v::phone()))
                             ->key('email', v::email())
                             ->key('message', v::optional(v::length(5, 500)));
                    break;
                default;
                    break;

            }

            try {
                $rule->assert($this->data);
            } catch (ValidationException $exception) {
                var_dump($exception->findMessages($this->messages));
            }

        }

        private function output() {
            print_r([
                $this->errors,
                '302'
            ]);
        }

    }