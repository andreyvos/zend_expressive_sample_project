<?php

namespace Credit\Form;

use Credit\InputFilter;
use Zend\Form\Element;
use Zend\Form\Form;

class CreditOptions extends Form
{
    public function __construct($name = 'creditoptions', array $options = [])
    {

        #$v = new \EventLogger\Helper\EventLogger;

        parent::__construct($name, $options);

        // input filter
        $this->setInputFilter(new InputFilter\CreditOptions);

        $this->add([
            'name' => 'tutor',
            'type' => Element\Select::class,
            'options' => [
                'label' => 'Tutor',
            ],
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
