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
use User\Model\UserTable;
use Zend\Session\Container;
use Zend\Form\FormInterface;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Diactoros\Response\RedirectResponse;

class Students
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\CourseseTable
     */
    private $courseTable;

    /**
     * @var Model\UserTable
     */
    private $userTable;

    private $urlHelper;

    public function __construct(Template\TemplateRendererInterface $template, CourseTable $courseTable, UserTable $userTable, CreditUserTable $creditUserTable, UrlHelper $urlHelper)
    {
        $this->template = $template;
		$this->courseTable = $courseTable;
		$this->userTable = $userTable;
		$this->creditUserTable = $creditUserTable;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
		$isop = false;

		$op = new Container('op_session');
		$action = $op->action;

		if($action == 'post') {
			$isop = true;
			$op->getManager()->getStorage()->clear('op_session');
		}

		$user_session = new Container('credit_user');
        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse(($this->urlHelper)('credit/form/login'));
		} 

		$creditUserId = $currentUser->getId();

		$credit_courses = null;

        if ($creditUserId) {			
			$creditUser = $this->creditUserTable->oneById($creditUserId);
			$students = $creditUser->student;
        }

		$form = new Form\Students();

		//var_dump($recruit);

        // bind object when id passed in url
        if (isset($creditUser) && $creditUser instanceof CreditUser) {
            $form->bind($creditUser);
        }

		$courses_list = $this->courseTable->fetchByFilter();

        return new HtmlResponse($this->template->render('credit::students-form', [
            'form' => $form,
			'creditUserId' => $creditUserId,
			'courses_list' => $courses_list,
			'students' => $students,
			'userTable' => $this->userTable,
			'op' => $isop
        ]));
    }
}
