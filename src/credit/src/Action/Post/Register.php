<?php

namespace Credit\Action\Post;

use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Credit\InputFilter;
use Credit\Model;
use Zend\Authentication;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Session\Container;
use User;
use Tutor\Model\TutorStudentCourseTable;
use Course\Model\CourseUserTable;
use Credit\Model\CreditOptionsTable;
use Exam\Model\ExamTriesTable;
use Exclusive\Model\MessageTutorTable;
use Exclusive\Model\CertificatePrintFreeTable;

class Register
{
    /**
     * @var Authentication\AuthenticationService
     */
    private $authenticationService;

    /**
     * @var UrlHelper
     */
    private $urlHelper;

    /**
     * @var Model\UserTable
     */
    private $userTable;

    /**
     * @var Model\UserTable
     */
    private $creditUserTable;

    /**
     * @var Model\UserMetaTable
     */
    private $userMetaTable;

    private $tutorStudentCourseTable;

	private $creditOptionsTable;

	private $courseUserTable;

    private $examTriesTable;

    private $messageTutorTable;

    private $certificatePrintFreeTable;

    public function __construct(
        Authentication\AuthenticationService $authenticationService,
        UrlHelper $urlHelper,
        Model\CreditUserTable $creditUserTable,
        Model\CreditUserMetaTable $userMetaTable,
		Model\CreditStudentTable $creditStudentTable,
		User\Model\UserTable $userTable,
        TutorStudentCourseTable $tutorStudentCourseTable,
		CourseUserTable $courseUserTable,
		CreditOptionsTable $creditOptionsTable,
		ExamTriesTable $examTriesTable, 
		MessageTutorTable $messageTutorTable,
		CertificatePrintFreeTable $certificatePrintFreeTable
    )
    {
        $this->authenticationService = $authenticationService;
        $this->creditUserTable = $creditUserTable;
        $this->creditUserMetaTable = $userMetaTable;
		$this->creditStudentTable = $creditStudentTable;
		$this->userTable = $userTable;
        $this->urlHelper = $urlHelper;
        $this->tutorStudentCourseTable = $tutorStudentCourseTable;
		$this->courseUserTable = $courseUserTable;
		$this->creditOptionsTable = $creditOptionsTable;
        $this->examTriesTable = $examTriesTable;
        $this->messageTutorTable = $messageTutorTable;
        $this->certificatePrintFreeTable = $certificatePrintFreeTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $data = $request->getParsedBody() ?? [];

		$user_session = new Container('credit_user');
        $currentUser = $user_session->data;
		$currentUserId = $currentUser->getId();	 
		$courses = $currentUser->getCreditCoursesArray();
		$datacourse = array_keys($courses);

		$user = $this->creditUserTable->oneById($currentUserId);
		$role = isset($data['role']) ? $data['role'] : $data['user_role'];

		$data['id'] = $currentUserId;
		$data['username'] = $user->getUsername();
		$data['identity'] = $user->getIdentity();
		
		if ($role == 'personal') {
			$data['role'] = "Rbac\Role\PersonalUser";
			$sessionRole = "Rbac\Role\PersonalUser";
			$filter = new InputFilter\RegisterPersonal();
		} else {
			$data['role'] = "Rbac\Role\BusinessUser";
			$sessionRole = "Rbac\Role\BusinessUser";
			$filter = new InputFilter\RegisterBusiness();
		}
        
        $filter->setData($data);

        if (!$filter->isValid()) {
            return new JsonResponse([
                'errors' => $filter->getMessages()
            ]);
        }

		$result = $this->creditUserTable->save($data);
		if ($data['role'] == "Rbac\Role\PersonalUser") {
			

			$length = 8;
			$charset = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
			$retVal = "";

			do {
				for ($n=0;$n<=$length;$n++) {
					$retVal .= substr($charset, rand(1, strlen($charset)), 1);
				}
				$retValFinal = $retVal;
			} while( $this->creditUserTable->byPlainpin($retValFinal)->current() );

			$data['pin'] = $retValFinal;
			$data['id'] = '';
			$data['role'] = "Rbac\Role\Student";
			
			$response = $this->userTable->save($data);
			$this->creditStudentTable ->insert($currentUserId, $response);

		if(count($datacourse) > 0) {
			foreach($datacourse as $course) {
				$tutor = $this->creditOptionsTable->fetchByName('tutor');
				$caT['tutor'] = $tutor['value'];
				$caT['course'] = $course;
				$caT['exam'] = array($course);
				$caT['messagetutor'] = array($course);
				$caT['certificateprintfree'] = array($course);
				$caT['end_of_support'] = 0;
				$caT['home_learning_number'] = 'na';
				$caT['order_number'] = 'na';
				$courseAndTutor[] = $caT;
			}

			$this->courseUserTable->save( $response,...($datacourse ?? []));
			$this->tutorStudentCourseTable->save($response,...$courseAndTutor);
			$this->examTriesTable->save( $response, ...($datacourse ?? []));
			$this->messageTutorTable->save( $response, ...($datacourse ?? []));
			$this->certificatePrintFreeTable->save( $response, ...($datacourse ?? []));
		}
				$student = $this->userTable->oneById($response);
				$storage = $this->authenticationService->getStorage();
				$storage->write($student);
		}

		$currentUser->setRole($sessionRole);
		
		return new JsonResponse([
                'redirectTo' => ($this->urlHelper)('credit/home')
        ]);

    }
}
