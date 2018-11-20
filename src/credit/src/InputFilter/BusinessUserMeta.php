<?php

namespace Credit\InputFilter;

use Zend\InputFilter\InputFilter;
use Zend\Validator;

class BusinessUserMeta extends InputFilter
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

        $this->add([
            'name' => 'company_name',
            'required' => false,
        ]);

        $this->add([
            'name' => 'company_email',
            'required' => false,
        ]);

        $this->add([
            'name' => 'company_phone',
            'required' => false,
        ]);

        $this->add([
            'name' => 'company_address',
            'required' => false,
        ]);

        $this->add([
            'name' => 'company_city',
            'required' => false,
        ]);

        $this->add([
            'name' => 'company_county',
            'required' => false,
        ]);

        $this->add([
            'name' => 'company_postcode',
            'required' => false,
        ]);

        $this->add([
            'name' => 'finance_email',
            'required' => false,
        ]);

        $this->add([
            'name' => 'finance_name',
            'required' => false,
        ]);

        $this->add([
            'name' => 'vat_number',
            'required' => false,
        ]);

        $this->add([
            'name' => 'company_number',
            'required' => false,
        ]);

    }
}
