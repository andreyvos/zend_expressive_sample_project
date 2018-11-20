<?php

namespace User\Action\View\ResultSet;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use User\Model;

class ByUserRoleAjax
{
    /**
     * @var Model\UserTable
     */
    private $userTable;

    /**
     * @var Model\User
     */
    private $user;

    public function __construct(Model\User $user, Model\UserTable $userTable) {
        $this->userTable = $userTable;
        $this->user = $user;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        try {
            $params = $request->getQueryParams();
            $resultSet = $this->userTable->getStudents($this->user, $params);
            return new JsonResponse($resultSet);

        } catch (\Exception $e) {
            return new JsonResponse([ 'error' => $e->getMessage() ]);
        }
    }
}
