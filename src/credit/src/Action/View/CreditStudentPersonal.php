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

class CreditStudentPersonal
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

    /**
     * @var Credit\Model\CreditStudentTable
     */
    private $creditStudentTable;

	private $recruitTable;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string
     */
    private $role;

    public function __construct(
        Template\TemplateRendererInterface $template,
        Model\UserTable $userTable,
        Course\Model\CourseTable $courseTable,
		Credit\Model\CreditStudentTable $creditStudentTable,
		Credit\Model\RecruitTable $recruitTable
    ) {
        $this->template = $template;
        $this->userTable = $userTable;
        $this->courseTable = $courseTable;
		$this->creditStudentTable = $creditStudentTable;
        $this->recruitTable = $recruitTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
		$user_session = new Container('credit_user');

        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse('/credit/login');
		} 

        $currentUserId = (int) $currentUser->getId();		 
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
					$creditUsed = 0;
				} else {
					$creditUsed = count($userArray['courses']);
				}
				$creditstudent[] = $one;
			}
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

        return new HtmlResponse($this->template->render('credit::student-personal', [
			'user' => $creditstudent,
            'search' => $search,
			'courses' => $courses,
			'totalcredit' => $totalCredit,
			'usedcredit' => $creditUsed
        ]));
    }
}
