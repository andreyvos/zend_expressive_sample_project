<?php
namespace Credit\Form;

use Zend\Form\Fieldset;
use Zend\Form\Element;
use Zend\Form\Form;

class CreditUserMeta extends Fieldset
{
   public function __construct($name = 'meta', array $options = [])
   {
	
		parent::__construct($name, $options);

        $this->add([
            'name' => 'first_name',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'First Name',
				'class' => 'form-control',
				'data-validation' => '[NOTEMPTY]'
			]
        ]);

        $this->add([
            'name' => 'last_name',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Last Name',
				'class' => 'form-control',
				'data-validation' => '[NOTEMPTY]'
			]
        ]);

        $this->add([
            'name' => 'email',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Email',
				'class' => 'form-control'
			]
        ]);

        $this->add([
            'name' => 'address',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Address',
				'class' => 'form-control',				
			]
        ]);

        $this->add([
            'name' => 'city',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'City',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'county',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'County',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'postcode',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Post Code',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'phone',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Phone Number',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'age',
            'type' => Element\Radio::class,
            'options' => [
				'value_options' => [
                    [
                        'value' => '15 - 21',
                        'label' => '15 - 21',
                        'selected' => false,
						'attributes' => [
							'data-validation' => '[NOTEMPTY]',
							'data-validation-group' => 'age-label'
						]
                    ],
                    [
                        'value' => '22 - 30',
                        'label' => '22 - 30',
                        'selected' => false,
						'attributes' => [
							'data-validation-group' => 'age-label'
						]
                    ],
                    [
                        'value' => '31 - 40',
                        'label' => '31 - 40',
                        'selected' => false,

                    ],
                    [
                        'value' => '40 +',
                        'label' => '40 +',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Prefer not to say',
                        'label' => 'Prefer not to say',
                        'selected' => false,

                    ]
				]
            ]
        ]);

        $this->add([
            'name' => 'ethnic',
            'type' => Element\Radio::class,
            'options' => [
				'value_options' => [
                    [
                        'value' => 'White, UK Heritage',
                        'label' => 'White, UK Heritage',
                        'selected' => false,
						'attributes' => [
							'data-validation' => '[NOTEMPTY]',
							'data-validation-group' => 'ethnic-label'
						]
                    ],
                    [
                        'value' => 'White, Europe',
                        'label' => 'White, Europe',
                        'selected' => false,
						'attributes' => [
							'data-validation-group' => 'ethnic-label'
						]

                    ],
                    [
                        'value' => 'White, Other',
                        'label' => 'White, Other',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Black, Carribean Heritage',
                        'label' => 'Black, Carribean Heritage',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Black, African Heritage',
                        'label' => 'Black, African Heritage',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Black (Other)',
                        'label' => 'Black (Other)',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Indian',
                        'label' => 'Indian',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Pakistan',
                        'label' => 'Pakistan',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Chinese',
                        'label' => 'Chinese',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Other',
                        'label' => 'Other',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Prefer not to say',
                        'label' => 'Prefer not to say',
                        'selected' => false,

                    ]
				]
            ],
        ]);

        $this->add([
            'name' => 'gender',
            'type' => Element\Radio::class,
            'options' => [
				'value_options' => [
                    [
                        'value' => 'Female',
                        'label' => 'Female',
                        'selected' => false,
						'attributes' => [
							'data-validation' => '[NOTEMPTY]',
							'data-validation-group' => 'gender-label'
						]
                    ],
                    [
                        'value' => 'Male',
                        'label' => 'Male',
                        'selected' => false,
						'attributes' => [
							'data-validation-group' => 'gender-label'
						]
                    ],
                    [
                        'value' => 'Other',
                        'label' => 'Other',
                        'selected' => false,

                    ],
                    [
                        'value' => 'Prefer not to say',
                        'label' => 'Prefer not to say',
                        'selected' => false,

                    ]
				]
            ],
        ]);

        $this->add([
            'name' => 'company_name',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Company Name',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'company_email',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Email',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'company_phone',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Phone Number',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'company_address',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Business Address',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'company_city',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'City',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'company_county',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'County',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'company_postcode',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Post Code',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'finance_email',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Invoicing Contact Email',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'finance_name',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Invoicing Contact Name',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'vat_number',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'VAT Number (optional)',
				'class' => 'form-control'				
			]
        ]);

        $this->add([
            'name' => 'company_number',
            'type' => Element\Text::class,
            'options' => [
            ],
			'attributes' => [
				'placeholder' => 'Company Number (optional)',
				'class' => 'form-control'				
			]
        ]);

   }
}