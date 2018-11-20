<?php

namespace Credit\Form\Element\Select;

use Course\Model;
use Zend\Form\Element\Select;
use Zend\Session\Container;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Expressive\Helper\UrlHelper;
use Credit;
use Tutor\Model\TutorStudentCourseTable;

class Course extends Select
{
    public function __construct(
		UrlHelper $urlHelper, 
		Credit\Model\RecruitTable $recruitTable, 
		Model\CourseTable $courseTable, 
		Model\CourseUserTable $courseUserTable, 
		TutorStudentCourseTable $tutorStudentCourseTable,
		$name = 'courses',
		array $options = []
	)
    {
        if (empty($options)) {
            $options = [
                'label' => 'Course'
            ];
        }
        parent::__construct($name, $options);

        $this->setAttribute('multiple', 'multiple');
        $this->setAttribute('class', 'selectpicker show-tick form-control');
        $this->setAttribute('required', 'required');
        $this->setAttribute('data-live-search', 'true');
        $this->setAttribute('data-icon-base', 'fa');
        $this->setAttribute('data-tick-icon', 'fa-check');
        $this->setAttribute('data-none-selected-text', '');
		//$this->setAttribute('onchange', 'onChangeSelect(this)');

		$user_session = new Container('credit_user');

        $currentUser = $user_session->data;

		if(!isset($user_session->username)) {
			return new RedirectResponse(($urlHelper)('credit/form/login'));
		} 

        $currentUserId = (int) $currentUser->getId();

		$recruit = $recruitTable->fetchByCreditUserId($currentUserId);

		$request = $_SERVER["REQUEST_URI"];
		$requestArray = explode('/', $request);
		$studentId = (int) end($requestArray);
		
		$selectedcourse = array();
		$courseselected = $courseUserTable->fetchByUserId($studentId)->toArray();
		//$courseselected = $tutorStudentCourseTable->fetchByStudentId($studentId)->toArray();
		//var_dump($courseselected);
		foreach ($courseselected as $course) 
		{
			$selectedcourse[] = $course['id'];
			//$selectedcourse[] = $course['course_id'];
		}

		//get total credit
		$creditAcourses = $recruit->getCourses();
		$creditcoursesArray = json_decode($recruit->getCreditCourses(),true);
		if(!is_null($creditcoursesArray)) {
			$creditScourses = array_sum($creditcoursesArray);
			foreach($creditcoursesArray as $key => $course) {
				$coursehaveCredit[] = $key;
			}
		} else {
			$creditScourses = 0;
			$coursehaveCredit = [];
		}
		$totalCredit = $creditAcourses + $creditScourses;

		if($creditAcourses > 0) {

			foreach ($courseTable->fetchByFilter() as $course) {
				if (in_array($course->getId(),$selectedcourse)) {
					$selected = 1;
					$disabled = 1;
				} else {
					$selected = 0;
					$disabled = 0;
				}
				$valueOptions[] = [
					'value' => $course->getId(),
					'label' => $course->getName(),
					'selected' => $selected,
					'disabled' => $disabled
				];
			}

		} else {

			foreach ($courseTable->fetchByFilter() as $course) {
				if (in_array($course->getId(),$selectedcourse)) {
					$selected = 1;
					$disabled = 1;
				} else {
					$selected = 0;
					$disabled = 0;
				}
				if (in_array($course->getId(),$coursehaveCredit)) {
					$valueOptions[] = [
						'value' => $course->getId(),
						'label' => $course->getName(),
						'selected' => $selected,
						'disabled' => $disabled
					];
				} 
			}
		}

        $this->setValueOptions($valueOptions ?? []);
    }
}
