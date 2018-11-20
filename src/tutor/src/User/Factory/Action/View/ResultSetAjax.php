<?php
namespace Tutor\User\Factory\Action\View;

use Psr\Container\ContainerInterface;
use User;
use Tutor;

class ResultSetAjax
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var User\Model\UserTable $userTable
         */
        $userTable = $container->get(User\Model\UserTable::class);

        return new User\Action\View\ResultSetAjax($userTable, \Rbac\Role\Tutor::class);
    }
}