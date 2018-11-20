<?php

namespace Recruit\InputFilter;

use Zend\Filter;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

class Recruit extends InputFilter
{
    public function __construct()
    {
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
            'name' => 'name',
            'required' => true,
            'validators' => [
                [
                    'name' => Validator\StringLength::class,
                ],
            ],
        ]);

        $this->add([
            'name' => 'email',
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

    }
}
