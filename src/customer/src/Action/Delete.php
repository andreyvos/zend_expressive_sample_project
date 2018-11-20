<?php

namespace Customer\Action;

use Customer\Model\CustomerTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Delete
{
    /**
     * @var CustomerTable
     */
    private $customerTable;

    public function __construct(CustomerTable $customerTable)
    {
        $this->customerTable = $customerTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $id = $request->getAttribute('id');
        $this->customerTable->delete($id);
        return new JsonResponse(['status' => 200]);
    }
}