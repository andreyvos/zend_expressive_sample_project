<?php

namespace Credit\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator;

class Recruit extends InputFilter
{
    public function __construct()
    {

		$this->add(new CreditUserMeta(), 'meta');

        $this->add([
            'name' => 'recruitId',
            'required' => false,
            'validators' => [
                [
                    'name' => Validator\Digits::class
                ]
            ],
        ]);

        $this->add([
            'name' => 'id',
            'required' => false,
            'validators' => [
                [
                    'name' => Validator\Digits::class
                ]
            ],
        ]);

        $this->add([
            'name' => 'credit_user_id',
            'required' => false,
            'validators' => [
                [
                    'name' => Validator\Digits::class
                ]
            ],
        ]);

        $this->add([
            'name' => 'identity',
            'required' => true,
            'validators' => [
                [
                    'name' => Validator\EmailAddress::class,
                ],
            ],
        ]);

        $this->add([
            'name' => 'courses',
            'required' => false,
            'validators' => [
                [
                    'name' => Validator\Digits::class
                ]
            ],
        ]);

        $this->add([
            'name' => 'credit_courses',
            'required' => false
        ]);

        $this->add([
            'name' => 'username',
            'required' => false
        ]);

        $this->add([
            'name' => 'password',
            'required' => false
        ]);

        $this->add([
            'name' => 'pin',
            'required' => true
        ]);

        $this->add([
            'name' => 'role',
            'required' => false
        ]);
    }
}
