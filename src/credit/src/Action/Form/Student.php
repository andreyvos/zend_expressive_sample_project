<?php

namespace Credit\Action\Form;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use User\Model;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Form\FormInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Session\Container;
use Credit;

class Student
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var Model\UserTable
     */
    private $userTable;

    /**
     * @var FormInterface
     */
    private $form;

    /**
     * @var string
     */
    private $templateName;

	private $recruitTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        Model\UserTable $userTable,
		Credit\Model\CreditStudentTable $creditStudentTable,
		Credit\Model\RecruitTable $recruitTable,
        FormInterface $form,
        string $templateName = 'user::form',
        UrlHelper $urlHelper
    ) {

        $this->template = $template;
        $this->userTable = $userTable;
		$this->creditStudentTable = $creditStudentTable;
        $this->recruitTable = $recruitTable;
        $this->form = $form;
        $this->templateName = $templateName;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
		$id = $request->getAttribute('id');

		$user_session = new Container('credit_user');
        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse(($this->urlHelper)('credit/form/login'));
		} 
		
        $currentUserId = (int) $currentUser->getId();
		
		$role = $currentUser->getRole();

		$result = $this->creditStudentTable->fetchByCreditUserId($currentUserId);

		if($role == 'Rbac\Role\PersonalUser' && count($result) > 0 && $id == '') {
			return new RedirectResponse(($this->urlHelper)('credit/view/student/personal'));
		} 

		$recruit = $this->recruitTable->fetchByCreditUserId($currentUserId);

		//get total credit
		$creditAcourses = $recruit->getCourses();
		$creditcoursesArray = json_decode($recruit->getCreditCourses(),true);
		if(!is_null($creditcoursesArray)) {
			$creditScourses = array_sum($creditcoursesArray);
		} else {
			$creditScourses = 0;
		}
		$totalCredit = $creditAcourses + $creditScourses;
	
		if (count($result) > 0) {
			foreach ($result as $student) {
				$one = $this->userTable->oneById($student->getStudentId());
				$userArray = $one->getArrayCopy();
				if(!is_array($userArray['courses'])) {
					$creditUsedbyStudent[] = 0;
				} else {
					$creditUsedbyStudent[] = count($userArray['courses']);
				}
				$creditstudent[] = $one;
			}
			$creditUsed = array_sum($creditUsedbyStudent);
		} else {
			$creditstudent = [];
			$creditUsed = 0;
		}
	
		if($creditUsed >= $totalCredit && $id == '') {
			return new RedirectResponse(($this->urlHelper)('credit/view/student/resultset'));
		} 
		

        if ($id) {
            /**
             * @var Model\User $user
             */
            $user = $this->userTable->oneById($id);
        }

        // bind object when id passed in url
        if (isset($user) && $user instanceof Model\User) {
            $this->form->setData($user->getArrayCopy());
        }

        return new HtmlResponse($this->template->render($this->templateName, [
            'form' => $this->form
        ]));
    }
}
