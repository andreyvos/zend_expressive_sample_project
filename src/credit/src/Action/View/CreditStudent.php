<?php

namespace Credit\Action\View;

use User\Model;
use User\Model\User;
use Course;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Zend\Session\Container;
use Credit;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Attempt\Model\AttemptTable;
use Exam\Model\ExamTable;
use Topic\Model\TopicTable;

class CreditStudent
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
     * @var Course\Model\CourseTable
     */
    private $courseTable;

	private $contentTable;

	private $examTable;

    /**
     * @var Credit\Model\CreditStudentTable
     */
    private $creditStudentTable;

    private $creditUserTable;

	private $recruitTable;

	private $attemptTable;
	
	private $topicTable;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string
     */
    private $role;

    private $urlHelper;

    public function __construct(
        Template\TemplateRendererInterface $template,
        Model\UserTable $userTable,
        Course\Model\CourseTable $courseTable,
		Course\Model\ContentTable $contentTable,
		Credit\Model\CreditStudentTable $creditStudentTable,
		Credit\Model\RecruitTable $recruitTable,
        Credit\Model\CreditUserTable $creditUserTable,
		TopicTable $topicTable,
		AttemptTable $attemptTable,
		ExamTable $examTable,
		UrlHelper $urlHelper
    ) {
        $this->template = $template;
        $this->userTable = $userTable;
        $this->courseTable = $courseTable;
		$this->contentTable = $contentTable;
		$this->creditStudentTable = $creditStudentTable;
        $this->creditUserTable = $creditUserTable;
        $this->recruitTable = $recruitTable;
		$this->attemptTable = $attemptTable;
		$this->topicTable = $topicTable;
		$this->examTable = $examTable;
        $this->urlHelper = $urlHelper;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
		$user_session = new Container('credit_user');

        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse('/credit/login');
		} 

        $currentUserId = (int) $currentUser->getId();
		
		$role = $currentUser->getRole();
		if($role == 'Rbac\Role\PersonalUser') {
			return new RedirectResponse(($this->urlHelper)('credit/view/student/personal'));
		} 

		$result = $this->creditStudentTable->fetchByCreditUserId($currentUserId);

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

        $courses = array();
        foreach($this->courseTable->fetchAll() as $course) {
            $courses[$course->getId()] = $course->getName();
        }
		
		//var_dump(count($result));

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
		
        //fetch filter info from GET request if available
        $routeResult = $request->getAttribute(RouteResult::class);
        $user = $request->getAttribute(User::class);
        $filter = $request->getAttribute('filter');

        if ('all' == $filter) {
            $filter = NULL;
        }

        $search = $request->getQueryParams()['search'] ?? [];

        return new HtmlResponse($this->template->render('credit::student-resultset', [
			'user' => $creditstudent,
            'search' => $search,
			'courses' => $courses,
			'totalcredit' => $totalCredit,
			'usedcredit' => $creditUsed,
			'attempt' => $this->attemptTable,
			'topic' => $this->topicTable,
			'content' => $this->contentTable,
			'exam' => $this->examTable,
			'userTable' => $this->userTable,
			'course' => $this->courseTable,
			'result' => $this->creditUserTable->oneById($currentUserId),
			'recruit' => $recruit
        ]));
    }
}
