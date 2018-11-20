<?php

namespace Credit\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class Profile extends Form
{
    public function __construct($name = 'profile', array $options = [])
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
            'name' => 'identity',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'identity',
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
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'role',
            'type' => Element\Hidden::class,
        ]);

        $this->add([
            'name' => 'submit',
            'type' => Element\Submit::class,
            'attributes' => [
                'value' => 'Save',
            ],
        ]);
    }
}
