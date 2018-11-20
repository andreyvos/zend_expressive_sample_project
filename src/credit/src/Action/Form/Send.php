<?php

namespace Credit\Action\Form;

use Credit\Form;
use Credit\Model;
use Credit\Model\CreditUser;
use Credit\Model\CreditUserTable;
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

    public function __construct(Template\TemplateRendererInterface $template, Model\RecruitTable $recruitTable, CourseTable $courseTable, CreditUserTable $creditUserTable)
    {
        $this->template = $template;
        $this->recruitTable = $recruitTable;
		$this->courseTable = $courseTable;
		$this->creditUserTable = $creditUserTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $creditUserId = $request->getAttribute('id');
		$credit_courses = null;

        $form = new Form\Recruit();

        if ($creditUserId) {
			
			$creditUser = $this->creditUserTable->oneById($creditUserId);
            $recruit = $this->recruitTable->fetchById($creditUser->getRecruitId());
			$credit_courses = $creditUser->getCreditCourses();

			$role = $creditUser->getRole();
            if ($role === 'Rbac\Role\PersonalUser') {
                $role = 'personal';
            } elseif ($role === 'Rbac\Role\BusinessUser') {
                $role = 'business';
            }

            if (in_array($role, ['personal', 'business'])) {
                $form->get('role')->setOptions([
                    'value_options' => [
                        [
                            'value' => '',
                            'label' => 'Let user choose',
                            'selected' => $role !== 'personal' && $role !== 'business'
                        ],
                        [
                            'value' => 'personal',
                            'label' => 'Personal use only',
                            'selected' => $role === 'personal'
                        ],
                        [
                            'value' => 'business',
                            'label' => 'Business use',
                            'selected' => $role === 'business'
                        ]
                    ]
                ]);
            }
        }

        // bind object when id passed in url
        if (isset($creditUser) && $creditUser instanceof CreditUser) {
            $form->bind($creditUser);
        }

		$courses_list = $this->courseTable->fetchByFilter();

        return new HtmlResponse($this->template->render('credit::recruit-send', [
            'form' => $form,
			'creditUserId' => $creditUserId,
			'courses_list' => $courses_list,
			'credit_courses' => $credit_courses,
			'courseTable' => $this->courseTable,
        ]));
    }
}
