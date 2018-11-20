<?php

namespace Credit\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class Register extends Form
{
    public function __construct($name = 'register', array $options = [])
    {
        parent::__construct($name, $options);

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class
        ]);

         $this->add(array(
             'name' => 'meta',
             'type' => 'Credit\Form\CreditUserMeta'
         ));

        $this->add([
            'name' => 'username',
            'type' => Element\Text::class,
            'options' => [
				'label' => '',
            ],
			'attributes' => [
				'placeholder' => 'Username',
				'class' => 'form-control',
			]
        ]);

        $this->add([
            'name' => 'identity',
            'type' => Element\Text::class,
            'options' => [
				'label' => ''
            ],
			'attributes' => [
				'placeholder' => 'Email',
				'class' => 'form-control',
				'data-validation' => '[EMAIL]',
				'disabled' => 'disabled'
			]
        ]);

        $this->add([
            'name' => 'password',
            'type' => Element\Password::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Password',
				'class' => 'form-control',
				'data-validation' => '[NOTEMPTY]'
			]
        ]);

        $this->add([
            'name' => 'role',
            'type' => Element\Radio::class,
            'options' => [
				'value_options' => [
                    [
                        'value' => 'personal',
                        'label' => 'Personal use only  <i class="fa fa-info-circle"></i>',
                        'selected' => false
                    ],
                    [
                        'value' => 'business',
                        'label' => 'Business use  <i class="fa fa-info-circle"></i>',
                        'selected' => true
                    ]
				]
            ],
        ]);

        $this->add([
            'name' => 'user_role',
            'type' => Element\Hidden::class,
            'value' => ''
        ]);

        $this->add([
            'name' => 'authority',
            'type' => Element\Checkbox::class,
            'options' => [
				'label' => 'Do you have authority to setup account on your behalf of the business ?',
				'use_hidden_element' => false,
				'checked_value' => 'yes',
				'unchecked_value' => 'no'
            ],
			'attributes' => [
				'class' => 'form-control',
				'data-validation' => '[NOTEMPTY]',
				'data-validation-message' => 'You must have authority to setup account'
			]
        ]);

        $this->add([
            'name' => 'agree',
            'type' => Element\Checkbox::class,
            'options' => [
 				'label' => 'I agree terms & conditions',
				'use_hidden_element' => false,
				'checked_value' => 'agree',
				'unchecked_value' => 'disagree' 
		],
			'attributes' => [
				'class' => 'form-control',
				'data-validation' => '[NOTEMPTY]',
				'data-validation-message' => 'You must agree the terms and conditions'
			]
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Apply',
            ],
        ]);
    }
}
