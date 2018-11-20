<?php

namespace Recruit\Action;

use Recruit\Model\RecruitTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Delete
{
    /**
     * @var RecruitTable
     */
    private $recruitTable;

    public function __construct(RecruitTable $recruitTable)
    {
        $this->recruitTable = $recruitTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $id = $request->getAttribute('id');
        $this->recruitTable->delete($id);
        return new JsonResponse(['status' => 200]);
    }
}