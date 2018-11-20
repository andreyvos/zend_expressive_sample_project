<?php

namespace Credit\Action\Json;

use Course\Model;
use User;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Session\Container;
use Zend\Diactoros\Response\RedirectResponse;
use Credit\Model\CreditStudentTable;
use Course;
use Credit\Model\RecruitTable;

class Credit
{
    /**
     * @var Model\CourseUserTable
     */
    private $courseUserTable;

	private $userTable;
    /**
     * @var User\Model\UserMetaTable
     */
    private $userMetaTable;

	private $creditStudentTable;

	private $recruitTable;

	private $courseTable;

    public function __construct(
		Model\CourseUserTable $courseUserTable, 
		User\Model\UserTable $userTable,
		User\Model\UserMetaTable $userMetaTable,
		CreditStudentTable $creditStudentTable,
		RecruitTable $recruitTable,
		Course\Model\CourseTable $courseTable
		)
    {
        $this->courseUserTable = $courseUserTable;
        $this->userMetaTable = $userMetaTable;
        $this->userTable = $userTable;
		$this->creditStudentTable = $creditStudentTable;
		$this->recruitTable = $recruitTable;
		$this->courseTable = $courseTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
			
		//get data from select
        $parsedBody = $request->getParsedBody();
        $courseIds = $parsedBody['courseIds'] ?? null;
		$lastCourseId = $parsedBody['clickedId'] ?? null;
		$countselect = count($courseIds);

		$alert = '';		
		$blocked = array();

		if(!is_array($courseIds)) {
			$data['alert'] = $alert;
			return new JsonResponse($data);
		}

		//get data current user logged in
		$user_session = new Container('credit_user');
        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse('/credit/login');
		} 

		$currentUserId = (int) $currentUser->getId();

		//get data student
		$studentCoursesList = [];
		$MyStudent = $this->creditStudentTable->fetchByCreditUserId($currentUserId);
		if (count($MyStudent) > 0) {
			foreach ($MyStudent as $student) {
				$studentId = $student->getStudentId();
				$getDataStudent= $this->userTable->oneById($studentId);
				$DataStudentArray = $getDataStudent->getArrayCopy();
				$studentCoursesList[$studentId] = $DataStudentArray['courses'];
				if(!is_array($DataStudentArray['courses']) or empty($DataStudentArray['courses'])) {
					$creditUsedbyStudent[] = 0;
				} else {
					$creditUsedbyStudent[] = count($DataStudentArray['courses']);
				}
			}
			$creditUsed = array_sum($creditUsedbyStudent);
		} else {
			$creditUsed = 0;
		}
		

		foreach ($this->courseTable->fetchByFilter() as $course) {
			$creditUsedbyCourse[$course->getId()] = 0;
		}
		
		//var_dump($studentCoursesiLst);
		if(count($studentCoursesList) > 1 ) {
			$creditUsedbyCourseNew = array_count_values(call_user_func_array('array_merge', $studentCoursesList));	
			$creditUsedbyCourse = array_replace($creditUsedbyCourse,$creditUsedbyCourseNew);
		} elseif (count($studentCoursesList) == 1){	
			$studentCoursesList = array_values($studentCoursesList);
			if (is_array($studentCoursesList[0])) {
				$creditUsedbyCourseNew = array_count_values($studentCoursesList[0]);
				$creditUsedbyCourse = array_replace($creditUsedbyCourse,$creditUsedbyCourseNew);
			}
		} 
		//var_dump($creditUsedbyCourse); 

		$MyCredit = $this->recruitTable->fetchByCreditUserId($currentUserId);
		$creditAnyCourses = $MyCredit->getCourses();
		$creditSomeCoursesJson = json_decode($MyCredit->getCreditCourses(),true);
		//var_dump($creditSomeCoursesJson);

		if(!is_null($creditSomeCoursesJson)) {
			$creditScourses = array_sum($creditSomeCoursesJson);
		} else {
			$creditScourses = 0;
		}
		$totalCredit = $creditAnyCourses + $creditScourses;
		//var_dump($totalCredit);		

		if($creditAnyCourses < 1) {
			foreach($creditSomeCoursesJson as $courseId => $number) {
				if ($courseId != 0) {
					$haved = $number;
					$used = $creditUsedbyCourse[$courseId];
					if (($haved - $used) < 1) {
						$blocked[] = $courseId;
					}
				}
			}
			if(in_array($lastCourseId,$blocked)) {
				$lastCourseId = $lastCourseId;
				$alert = 'This course have reach maximum credit';
			}
		} else {
			$Somecourses = [];
			foreach($creditSomeCoursesJson as $courseId => $number) {
				if ($courseId != 0) {
					$haved = $number;
					$used = $creditUsedbyCourse[$courseId];
					if (($haved - $used) < 1) {
						$blocked[] = $courseId;
					}
					$Somecourses[] = $courseId;
				}
			}
			$courseIds = array_diff($courseIds, $Somecourses);
			
			if(in_array($lastCourseId,$blocked)) {
				$lastCourseId = $lastCourseId;
				$alert = 'This course have reach maximum credit';
			} else {
				if(!in_array($lastCourseId,$Somecourses) && (count($courseIds) + array_sum($creditUsedbyCourse)) > $creditAnyCourses) {
					$alert = 'You have reach maximum credit';	
					$lastCourseId = $lastCourseId;
				} 
			}
		}

		$data['alert'] = $alert;
		$data['selected'] = $courseIds;
		$data['clicked'] = $lastCourseId;
		$data['blocked'] = $blocked;

        return new JsonResponse($data);
    }
}
