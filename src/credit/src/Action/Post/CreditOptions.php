<?php

namespace Credit\Action\Post;

use Credit\InputFilter;
use Credit\Model\CreditOptionsTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Helper\UrlHelper;
use Course\Model\CourseUserTable;
use Course\Model\CourseTable;

class CreditOptions
{
    /**
     * @var OptionsTable
     */
    private $optionsTable;

	private $courseUserTable;

	private $courseTable;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    public function __construct(CreditOptionsTable $optionsTable, CourseUserTable $courseUserTable, CourseTable $courseTable, UrlHelper $urlHelper)
    {
        $this->optionsTable = $optionsTable;
		$this->courseUserTable = $courseUserTable;
		$this->courseTable = $courseTable;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];

        $filter = new InputFilter\CreditOptions();
        $filter->setData($data);

        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }

        $values = $filter->getValues();

        $this->optionsTable->save($values);

		$courses = $this->courseTable->fetchByFilter();

		foreach ($courses as $course) {
			$courseRow[] = $course->getId();
		}

		$this->courseUserTable->save($data['tutor'], ...$courseRow);

        return new JsonResponse(['successMessage' => 'Options updated.']);
    }
}
