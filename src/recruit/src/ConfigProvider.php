<?php

namespace Recruit;

use Recruit\Action;
use Recruit\Form;
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
                'name' => 'recruit/view/resultset',
                'path' => '/recruit/resultset[/{filter}]',
                'middleware' => Action\View\ResultSet::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'recruit/view/single',
                'path' => '/recruit/{id:\d+}',
                'middleware' => Action\View\Single::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'recruit/delete',
                'path' => '/recruit/delete/{id:\d+}',
                'middleware' => Action\Delete::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'recruit/form/send',
                'path' => '/recruit/form[/{id:\d+}]',
                'middleware' => Action\Form\Send::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'recruit/post/send',
                'path' => '/recruit',
                'middleware' => Action\Post\Send::class,
                'allowed_methods' => ['POST']
            ],
        ];
    }

    public function getShared(): array
    {
        return [
            'navigation' => [
                'primary' => [
                    Role\Administrator::class => [
                        7000 => [
                            'routeName' => 'recruit/view/resultset',
                            'active' => '/recruit',
                            'label' => 'Recruits'
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
                    'recruit/view/resultset',
					'recruit/view/single',
					'recruit/delete',
					'recruit/form/send',
					'recruit/post/send'
                ]
            ]
        ];
    }
}
