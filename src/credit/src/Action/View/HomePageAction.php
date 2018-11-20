<?php

namespace Credit\Action\View;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Interop\Http\ServerMiddleware\MiddlewareInterface as ServerMiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Expressive\Router;
use Zend\Expressive\Template;
use Options;
use Zend\Session\Container;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Credit;
use Course;
use User;

class HomePageAction implements ServerMiddlewareInterface
{
    private $router;

    private $template;

    private $userTable;

	private $creditUserTable;

	private $creditStudentTable;

    private $optionsTable;

	private $recruitTable;

    private $urlHelper;

    public function __construct(
        Router\RouterInterface $router,
        Template\TemplateRendererInterface $template = null,
		User\Model\UserTable $userTable,
        Credit\Model\CreditUserTable $creditUserTable,
		Credit\Model\CreditStudentTable $creditStudentTable,
        Options\Model\OptionsTable $optionsTable,
		Credit\Model\RecruitTable $recruitTable,
		Course\Model\CourseTable $courseTable,
        UrlHelper $urlHelper
    )
    {
        $this->router   = $router;
        $this->template = $template;
        $this->userTable = $userTable;
		$this->creditUserTable = $creditUserTable;
		$this->creditStudentTable = $creditStudentTable;
        $this->recruitTable = $recruitTable;
		$this->courseTable = $courseTable;
        $this->optionsTable = $optionsTable;
        $this->urlHelper = $urlHelper;
    }

    public function process(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        if (! $this->template) {
            return new JsonResponse([
                'welcome' => 'Welcome to LMS Credit System'
            ]);
        }

        $data = [];

		$user_session = new Container('credit_user');

        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse(($this->urlHelper)('credit/form/login'));
		} 

        $currentUserId = (int) $currentUser->getId();

        $user = $this->creditUserTable->oneById($currentUserId);
		$recruit = $this->recruitTable->fetchByCreditUserId($currentUserId);

		if(!$user) {
			return new RedirectResponse(($this->urlHelper)('credit/logout'));	
		}

        $role = $user->getRole();
		
		$data['user'] = $currentUser;
		$data['options'] = $this->optionsTable->fetchAll()->toArray();
		$data['recruit'] = $recruit;
		$data['courseTable'] = $this->courseTable;
		$data['userTable'] = $this->userTable;
		$data['creditStudentTable'] = $this->creditStudentTable;
		
		if($role === 'Rbac\Role\CreditUser' || ($role !== 'Rbac\Role\BusinessUser' && $role !== 'Rbac\Role\PersonalUser')) {
			return new RedirectResponse(($this->urlHelper)('credit/form/register'));
		} 

		return new HtmlResponse($this->template->render('credit::home-page', $data));
    }
}
