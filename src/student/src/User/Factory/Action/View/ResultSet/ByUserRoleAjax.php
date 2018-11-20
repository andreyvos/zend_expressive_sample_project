<?php
namespace Student\User\Factory\Action\View\ResultSet;

use Psr\Container\ContainerInterface;
use Zend\Expressive\Template;
use User;
use Course;
use Zend\Authentication\AuthenticationService;


class ByUserRoleAjax
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        /**
         * @var User\Model\UserTable $userTable
         */
        $userTable = $container->get(User\Model\UserTable::class);

        /**
         * @var AuthenticationService $authenticationService
         */
        $authenticationService = $container->get(AuthenticationService::class);

        /**
         * @var User\Model\User $user
         */
        $user = $authenticationService->getStorage()->read();

        return new User\Action\View\ResultSet\ByUserRoleAjax($user, $userTable);
    }
}