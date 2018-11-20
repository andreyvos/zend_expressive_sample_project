<?php

namespace Credit\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator;

class RegisterPersonal extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name' => 'id',
            'required' => true,
            'validators' => [
                [
                    'name' => Validator\Digits::class
                ]
            ],
        ]);

        $this->add(new PersonalUserMeta(), 'meta');

        $this->add([
            'name' => 'identity',
            'required' => true,
        ]);

        $this->add([
            'name' => 'username',
            'required' => true,
        ]);

        $this->add([
            'name' => 'password',
            'required' => true,
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                    'options' => [
                        'min' => 2
                    ],
                ],
            ],
        ]);

        $this->add([
            'name' => 'role',
            'required' => true,
            'validators' => [
            ],
        ]);

        $this->add([
            'name' => 'agree',
            'required' => true,
            'validators' => [
            ],
        ]);

	}
}