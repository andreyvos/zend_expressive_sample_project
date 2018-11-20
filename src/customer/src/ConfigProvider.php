<?php

namespace Customer;

use Customer\Action;
use Customer\Form;
use Rbac\Role;
use Zend\ServiceManager;

class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     *
     * @return array
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes' => $this->getRoutes(),
            'shared' => $this->getShared(),
            'rbac' => $this->getRbac()
        ];
    }

    /**
     * Returns the routes array
     *
     * @return array
     */
    public function getRoutes(): array
    {
        return [
            [
                'name' => 'customer/view/single',
                'path' => '/customer/{id:\d+}',
                'middleware' => Action\View\Single::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'customer/form/customer',
                'path' => '/customer/form[/{id:\d+}]',
                'middleware' => Action\Form\Customer::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'customer/post/customer',
                'path' => '/customer',
                'middleware' => Action\Post\Customer::class,
                'allowed_methods' => ['POST']
            ],
            [
                'name' => 'customer/form/register',
                'path' => '/customer/register[/{token}]',
                'middleware' => Action\Form\Register::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'customer/view/resultset',
                'path' => '/customer/resultset[/{filter}]',
                'middleware' => Action\View\ResultSet::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'customer/delete',
                'path' => '/customer/delete/{id:\d+}',
                'middleware' => Action\Delete::class,
                'allowed_methods' => ['GET']
            ]
        ];
    }

    public function getShared(): array
    {
        return [
            'navigation' => [
                'primary' => [
                    Role\Administrator::class => [
                        7000 => [
                            'routeName' => 'customer/view/resultset',
                            'active' => '/customer',
                            'label' => 'Customers'
                        ]
                    ]
                ]
            ]
        ];
    }

    /**
     * Returns the container dependencies
     *
     * @return array
     */
    public function getDependencies(): array
    {
        return [
        ];
    }

    /**
     * Returns the rbac array
     *
     * @return array
     */
    public function getRbac(): array
    {
        return [
            'permissions' => [
                Role\Administrator::class => [
                    'customer/post/customer',
                    'customer/form/customer',
                    'customer/view/single',
                    'customer/view/resultset',
                    'customer/form/register',
                    'customer/delete',
                ]
            ]
        ];
    }
}
