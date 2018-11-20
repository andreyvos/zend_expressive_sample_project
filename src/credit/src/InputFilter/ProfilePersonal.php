<?php

namespace Credit\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator;

class ProfilePersonal extends InputFilter
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
            'name' => 'password',
            'required' => false,
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
            'name' => 'username',
            'required' => false,
        ]);

        $this->add([
            'name' => 'identity',
            'required' => false,
        ]);

        $this->add([
            'name' => 'role',
            'required' => false,
        ]);

        $this->add([
            'name' => 'pin',
            'required' => false,
        ]);

        $this->add([
            'name' => 'plainpin',
            'required' => false,
        ]);
    }
}
