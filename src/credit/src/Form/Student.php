<?php

namespace Credit\Form;

use Rbac\Role;
use Credit\Form\Fieldset;
use Credit\Form;
use User\Form\User;
use Zend\Form\Element;
use Zend\Form\Element\Collection;
use Credit\InputFilter;

class Student extends User
{
    public function __construct(
		Form\Element\Select\Course $select,
        string $action,
        $name = 'creditstudent',
        array $options = []
    ) {
        parent::__construct($name, $options);

        // form action
        $this->setAttribute('action', $action);

        $this->setInputFilter(new InputFilter\Student);

        // fieldset
        $this->add(new Fieldset\Meta);

        $this->add([
            'type' => Collection::class,
            'name' => 'course',
            'options' => [
                'target_element' => $select,
                'allow_add' => false,
                'should_create_template' => false,
                'count' => 1
            ]
        ]);

        $this->add([
            'name' => 'notify-user',
            'type' => Element\Checkbox::class,
            'attributes' => [
                'value' => 1,
                'style' => 'width: 25px; cursor: pointer;'
            ],
            'options' => [
                'label' => 'Send Notification Email',
            ],
        ]);

        $this->setData([
            'role' => Role\Student::class
        ]);
    }
}
