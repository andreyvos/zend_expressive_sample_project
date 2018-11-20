<?php

namespace Credit;

use Credit;
use Credit\Form;
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
                'name' => 'credit/form/login',
                'path' => '/credit/login',
                'middleware' => Action\Form\Login::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/form/loginas',
                'path' => '/credit/loginas[/{userId}]',
                'middleware' => Action\Form\Loginas::class,
                'allowed_methods' => ['GET']
            ],
			[
                'name' => 'credit/post/authenticate',
                'path' => '/credit/authenticate',
                'middleware' => Action\Post\Authenticate::class,
                'allowed_methods' => ['POST']
            ],
            [
                'name' => 'credit/logout',
                'path' => '/credit/logout[/{instruction}]',
                'middleware' => Action\Logout::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/form/reset',
                'path' => '/credit/reset',
                'middleware' => Action\Form\Reset::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/post/reset',
                'path' => '/credit/token',
                'middleware' => Action\Post\Reset::class,
                'allowed_methods' => ['POST'],
                'options' => [
                ]
            ],
            [
                'name' => 'credit/form/newpass',
                'path' => '/credit/newpass[/{pin}]',
                'middleware' => Action\Form\Newpass::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/post/newpass',
                'path' => '/credit/setnewpass',
                'middleware' => Action\Post\Newpass::class,
                'allowed_methods' => ['POST'],
                'options' => [
                ]
            ],
            [
                'name' => 'credit/home',
                'path' => '/credit',
                'middleware' => Action\View\HomePageAction::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/form/register',
                'path' => '/credit/register',
                'middleware' => Action\Form\Register::class,
                'allowed_methods' => ['GET']
            ],
			[
                'name' => 'credit/post/register',
                'path' => '/credit/save',
                'middleware' => Action\Post\Register::class,
                'allowed_methods' => ['POST']
            ],
            [
                'name' => 'credit/form/profile',
                'path' => '/credit/profile',
                'middleware' => Action\Form\Profile::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/post/profile',
                'path' => '/credit/update',
                'middleware' => Action\Post\Profile::class,
                'allowed_methods' => ['POST']
            ],
			[
                'name' => 'credit/view/success',
                'path' => '/credit/success',
                'middleware' => Action\View\Success::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/post/student',
                'path' => '/credit/student/save',
                'middleware' => 'Credit\Action\Post\Student',
                'allowed_methods' => ['POST']
            ],
            [
                'name' => 'credit/form/student',
                'path' => '/credit/student/form[/{id:\d+}]',
                'middleware' => 'Credit\Action\Form\Student',
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/post/students',
                'path' => '/credit/students/post',
                'middleware' => 'Credit\Action\Post\Students',
                'allowed_methods' => ['POST']
            ],
            [
                'name' => 'credit/form/students',
                'path' => '/credit/students/form',
                'middleware' => 'Credit\Action\Form\Students',
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/view/student/single',
                'path' => '/credit/student/{id:\d+}',
                'middleware' => Action\View\CreditStudentSingle::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/view/student/resultset',
                'path' => '/credit/student/resultset',
                'middleware' => Action\View\CreditStudent::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/view/student/personal',
                'path' => '/credit/student/personal',
                'middleware' => Action\View\CreditStudentPersonal::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'credit/json/credit',
                'path' => '/credit/json',
                'middleware' => Action\Json\Credit::class,
                'allowed_methods' => ['POST']
            ],
			[
                'name' => 'client/view/user',
                'path' => '/client',
                'middleware' => Action\View\CreditUser::class,
                'allowed_methods' => ['GET']
            ],
			[
                'name' => 'client/view/user/details',
                'path' => '/client/details/{id:\d+}',
                'middleware' => Action\View\CreditUserDetails::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'client/delete',
                'path' => '/client/delete/{id:\d+}',
                'middleware' => Action\Delete::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'client/form/send',
                'path' => '/client/form[/{id:\d+}]',
                'middleware' => Action\Form\Send::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'client/post/send',
                'path' => '/client/send',
                'middleware' => Action\Post\Send::class,
                'allowed_methods' => ['POST']
            ],
	        [
                'name' => 'client/view/student/resultset',
                'path' => '/client/student/{id:\d+}',
                'middleware' => Action\View\RecruitCreditStudent::class,
                'allowed_methods' => ['GET']
            ],
            [
                'name' => 'client/post/options',
                'path' => '/client/options/save',
                'middleware' => Action\Post\CreditOptions::class,
                'allowed_methods' => ['POST']
            ],
            [
                'name' => 'client/form/options',
                'path' => '/client/options',
                'middleware' => Action\Form\CreditOptions::class,
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
                            'routeName' => 'client/view/user',
                            'active' => '/client',
                            'label' => 'Credit Users'
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
            'factories' => [
                'Credit\Action\Post\Student' => Credit\Factory\Action\Post\Student::class,
                'Credit\Action\Form\Student' => Credit\Factory\Action\Form\Student::class,
                'Credit\Action\Post\Students' => Credit\Factory\Action\Post\Students::class,
                'Credit\Action\View\Single\ByUserId' => Credit\Factory\Action\View\Single\ByUserId::class,
                'Credit\Action\View\ResultSet\ByUserRole' => Credit\Action\View\ResultSet\ByUserRole::class
            ]
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
                Role\Anonymous::class => [
                    'credit/form/login',
					'credit/post/authenticate',
                    'credit/form/reset',
                    'credit/post/reset',
					'credit/form/newpass',
					'credit/post/newpass',
                    'credit/logout',
                    'credit/form/profile',
                    'credit/post/profile',
					'credit/view/success',
                    'credit/home',
                    'credit/form/register',
                    'credit/post/register',
					'credit/form/student',
					'credit/post/student',
					'credit/form/students',
					'credit/post/students',
					'credit/view/student/single',
					'credit/view/student/resultset',
					'credit/view/student/personal',
					'credit/json/credit',
                    'credit/form/loginas'
                ],
				Role\Administrator::class => [
					'client/view/user',
					'client/view/user/details',
					'client/delete',
					'client/form/send',
					'client/post/send',
					'client/view/student/resultset',
                    'client/post/options',
                    'client/form/options'
				]
            ]
        ];
    }
}
