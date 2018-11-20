<?php

namespace Tutor\User\Form\Element\Select;

use Zend\Form\Element\Select;

class AllowNotification extends Select
{
    public function __construct($name = 'allow_over_due_notification', array $options = ['label' => 'Allow Overdue Marking Notification'])
    {
        parent::__construct($name, $options);

        $this->setValueOptions([
            'yes' => 'Yes',
            'no' => 'No'
        ]);
    }
}
