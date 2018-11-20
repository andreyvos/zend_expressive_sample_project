<?php

namespace Credit\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator;

class PersonalUserMeta extends InputFilter
{
    public function __construct()
    {

        $this->add([
            'name' => 'first_name',
            'required' => true,
        ]);

        $this->add([
            'name' => 'last_name',
            'required' => true,
        ]);

        $this->add([
            'name' => 'address',
            'required' => false,
        ]);

        $this->add([
            'name' => 'city',
            'required' => false,
        ]);

        $this->add([
            'name' => 'county',
            'required' => false,
        ]);

        $this->add([
            'name' => 'postcode',
            'required' => false,
        ]);

        $this->add([
            'name' => 'phone',
            'required' => false,
        ]);

        $this->add([
            'name' => 'age',
            'required' => false,
        ]);

        $this->add([
            'name' => 'ethnic',
            'required' => false,
        ]);

        $this->add([
            'name' => 'gender',
            'required' => false,
        ]);

    }
}
