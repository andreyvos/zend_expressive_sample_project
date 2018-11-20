<?php

namespace Credit\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator;

class CreditUserMeta extends InputFilter
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

    }
}
