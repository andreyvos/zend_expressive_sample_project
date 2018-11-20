<?php

namespace Customer\Form;

use Customer\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class Customer extends Form
{
    public function __construct(
        $name = 'customer',
        array $options = [])
    {
        parent::__construct($name, $options);

        // input filter
        $this->setInputFilter(new InputFilter\Customer);

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class
        ]);

        $this->add([
            'name' => 'email',
            'type' => Element\Email::class,
            'attributes' => [
                'class' => 'form-control col-md-4',
                'placeholder' => 'E-Mail'
            ],
        ]);

        $this->add([
            'name' => 'available_credits',
            'type' => Element\Number::class,
            'attributes' => [
                'class' => 'form-control col-md-4',
                'style' => 'margin-right: 58px;',
                'placeholder' => 'Available Credits'
            ],
        ]);

        $this->add([
            'name' => 'credit_type',
            'type' => Element\Radio::class,
            'attributes' => [
                'style' => 'margin-right: 5px;',
                'value' => 'One Time Credit'
            ],
            'options' => [
                'value_options' => [
                    'One Time Credit' => 'One Time Credit',
                    'Subscription Credit' => 'Subscription Credit',
                    'Individual Cost' => 'Individual Cost'
                ]
            ],
        ]);

        $this->add([
            'name' => 'pricing_type',
            'type' => Element\Radio::class,
            'attributes' => [
                'style' => 'margin-right: 5px;',
                'value' => 'Default Pricing'
            ],
            'options' => [
                'value_options' => [
                    'Default Pricing' => 'Default Pricing',
                    'Set Specific Course Pricing' => 'Set Specific Course Pricing'
                ]
            ],
        ]);

        $this->add([
            'name' => 'payment_type',
            'type' => Element\Radio::class,
            'attributes' => [
                'style' => 'margin-right: 5px;',
                'value' => 'Manual Payment'
            ],
            'options' => [
                'value_options' => [
                    'Manual Payment' => 'Manual Payment',
                    'Invoice' => 'Invoice',
                    'Card Payment' => 'Card Payment',
                    'Invoice & Card' => 'Invoice & Card',
                ]
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => [
                'style' => 'border-radius: 30px;width: 120px;',
                'value' => 'Finish'
            ],
        ]);
    }
}
