<?php

namespace Credit\Form;

use Credit\InputFilter;
use Zend\Form\Form;
use Zend\Form\Element;

class Recruit extends Form
{
    public function __construct(
		$name = 'recruit',
        array $options = [])
    {
        parent::__construct($name, $options);

        // input filter
        //$this->setInputFilter(new InputFilter\Recruit);


        $this->add([
            'name' => 'recruitId',
            'type' => Element\Hidden::class
        ]);

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class
        ]);

         $this->add(array(
             'name' => 'meta',
             'type' => 'Credit\Form\CreditUserMeta'
         ));

        $this->add([
            'name' => 'identity',
            'type' => Element\Email::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'E-Mail'
            ],
        ]);

        $this->add([
            'name' => 'role',
            'type' => Element\Radio::class,
            'options' => [
                'value_options' => [
                    [
                        'value' => '',
                        'label' => 'Let user choose',
                        'selected' => true
                    ],
                    [
                        'value' => 'personal',
                        'label' => 'Personal use only',
                        'selected' => false
                    ],
                    [
                        'value' => 'business',
                        'label' => 'Business use',
                        'selected' => false
                    ]
                ]
            ],
        ]);

        $this->add([
            'name' => 'courses',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Credit Value',
				'type' => 'number'
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'Submit',
            'attributes' => [
                'style' => 'border-radius: 30px;width: 120px;',
                'value' => 'Send'
            ],
        ]);
    }
}
