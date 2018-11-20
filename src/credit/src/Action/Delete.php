<?php

namespace Credit\Action;

use Credit\Model\RecruitTable;
use Credit\Model\RecruitTableGateway;
use Credit;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Delete
{
    /**
     * @var RecruitTable
     */
    private $recruitTable;

    /**
     * @var RecruitTableGateway
     */
    private $recruitTableGateway;

    /**
     * @var CreditUserTable
     */
    private $creditUserTable;

    /**
     * @var CreditUserMetaTable
     */
    private $creditUserMetaTable;

    public function __construct(RecruitTable $recruitTable, RecruitTableGateway $recruitTableGateway, Credit\Model\CreditUserTable $creditUserTable, Credit\Model\CreditUserMetaTable $creditUserMetaTable)
    {
        $this->recruitTable = $recruitTable;
		$this->recruitTableGateway = $recruitTableGateway;
		$this->creditUserTable = $creditUserTable;
		$this->creditUserMetaTable = $creditUserMetaTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $id = $request->getAttribute('id');

		$user = $this->recruitTableGateway->select(['id' => $id])->current();

		//var_dump($user);
		$credit_user_id = $user->getCreditUserId();

        $this->recruitTable->delete($id);
		$this->creditUserTable->delete($credit_user_id);
		$this->creditUserMetaTable->delete($credit_user_id);
        return new JsonResponse(['status' => 200]);
    }
}