<?php

namespace Credit\InputFilter;

use Zend\InputFilter\InputFilter;

class CreditOptions extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name' => 'tutor',
            'required' => false,
        ]);

    }
}
