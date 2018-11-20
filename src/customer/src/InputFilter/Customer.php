<?php

namespace Customer\InputFilter;

use Zend\Filter;
use Zend\InputFilter\InputFilter;
use Zend\Validator;

class Customer extends InputFilter
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
            'name' => 'type',
            'required' => true,
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
            'name' => 'available_credits',
            'required' => false,
        ]);

        $this->add([
            'name' => 'credit_type',
            'required' => true,
        ]);

        $this->add([
            'name' => 'pricing_type',
            'required' => true,
        ]);

        $this->add([
            'name' => 'credit_expiration_date',
            'required' => false,
            'validators' => [
                [
                    'name' => Validator\Date::class,
                ],
            ],
        ]);

        $this->add([
            'name' => 'payment_type',
            'required' => true,
        ]);
    }
}
