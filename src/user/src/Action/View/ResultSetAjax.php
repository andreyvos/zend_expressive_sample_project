<?php
/**
 * Created by PhpStorm.
 * User: pujangga
 * Date: 18/08/18
 * Time: 4:11
 */

namespace User\Action\View;

use User\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class ResultSetAjax
{
    /** @var Model\UserTable */
    private $userTable;

    /** @var string */
    private $role;

    public function __construct(Model\UserTable $userTable, string $role) {
        $this->userTable = $userTable;
        $this->role = $role;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        try {
            $params = $request->getQueryParams();
            $resultSet = $this->userTable->getUsers($this->role, $params);
            return new JsonResponse($resultSet);

        } catch (\Exception $e) {
            return new JsonResponse([ 'error' => $e->getMessage() ]);
        }
    }
}