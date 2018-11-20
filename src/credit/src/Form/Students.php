<?php

namespace Credit\Form;

use Zend\Form\Element;
use Zend\Form\Form;

class Students extends Form
{
    public function __construct($name = 'students', array $options = [])
    {
        parent::__construct($name, $options);

        $this->add([
            'name' => 'recruitId',
            'type' => Element\Hidden::class
        ]);

        $this->add([
            'name' => 'id',
            'type' => Element\Hidden::class
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
