<?php

namespace Recruit\Form;

use Recruit\InputFilter;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Element\Collection;
use Recruit\Form\Fieldset;

class Recruit extends Form
{
    public function __construct(
		$name = 'recruit',
        array $options = [])
    {
        parent::__construct($name, $options);

        // input filter
        $this->setInputFilter(new InputFilter\Recruit);


        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class
        ]);

        $this->add([
            'name' => 'name',
            'type' => Element\Text::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'Name'
            ],
        ]);

        $this->add([
            'name' => 'email',
            'type' => Element\Email::class,
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => 'E-Mail'
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
