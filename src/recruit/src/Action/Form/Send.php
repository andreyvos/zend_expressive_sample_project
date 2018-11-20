<?php

namespace Recruit\Action\Form;

use Recruit\Form;
use Recruit\Model;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;

use Course\Model\CourseTable;

class Send
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\RecruitTable
     */
    private $recruitTable;

    /**
     * @var Model\CourseTable
     */
    private $courseTable;

    public function __construct(Template\TemplateRendererInterface $template, Model\RecruitTable $recruitTable, CourseTable $courseTable)
    {
        $this->template = $template;
        $this->recruitTable = $recruitTable;
		$this->courseTable = $courseTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $customerId = $request->getAttribute('id');
		$credit_courses = null;

        if ($customerId) {
            /**
             * @var Model\Customer
             */
            $customer = $this->recruitTable->fetchById($customerId);
			$credit_courses = $customer->getCreditCourses();
        }

		$form = new Form\Recruit();

        // bind object when id passed in url
        if (isset($customer) && $customer instanceof Model\Recruit) {
            $form->bind($customer);
        }

		$courses_list = $this->courseTable->fetchAll();

        return new HtmlResponse($this->template->render('recruit::send', [
            'form' => $form,
			'courses_list' => $courses_list,
			'credit_courses' => $credit_courses,
			'courseTable' => $this->courseTable,
        ]));
    }
}
