<?php

namespace Course\Action;

use Course\Model\CourseTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;

class Delete
{
    /**
     * @var CourseTable
     */
    private $courseTable;

    public function __construct(CourseTable $courseTable)
    {
        $this->courseTable = $courseTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $id = $request->getAttribute('id');
        $this->courseTable->delete($id);
        return new JsonResponse(['status' => 200]);
    }
}