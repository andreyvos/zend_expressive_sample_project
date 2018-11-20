<?php

namespace Credit\Action\View;

use User\Model;
use Assignment\Model\AssignmentWorkTable;
use Tutor\Model\TutorStudentCourseTable;
use Course\Model\ContentTable;
use Course\Model\CourseTable;
use Topic\Model\AttachmentTable;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\HtmlResponse;
use Zend\Expressive\Template;
use Course\Model\CourseUserTable;

class CreditStudentSingle
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
     * @var Model\userMetaTable
     */
    private $userMetaTable;

    /**
     * @var AssignmentWorkTable
     */
    private $assignmentWorkTable;

    /**
     * @var ContentTable;
     */
    private $contentTable;

    /**
     * @var CourseTable;
     */
    private $courseTable;

    /**
     * @var AttachmentTable
     */
    private $attachmentTable;

    /**
     * @var string
     */
    private $templateName;

    /**
     * @var string
     */
    private $singleRouteName;

    /**
     * @var string
     */
    private $templateNamespace;

	private $courseUserTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        Model\UserTable $userTable,
        Model\UserMetaTable $userMetaTable,
        TutorStudentCourseTable $tutorStudentCourseTable,
        AssignmentWorkTable $assignmentWorkTable,
        ContentTable $contentTable,
        CourseTable $courseTable,
        AttachmentTable $attachmentTable,
		CourseUserTable $courseUserTable
    ) {

        $this->template = $template;
        $this->userTable = $userTable;
        $this->userMetaTable = $userMetaTable;
        $this->tutorStudentCourseTable = $tutorStudentCourseTable;
        $this->assignmentWorkTable = $assignmentWorkTable;
        $this->contentTable = $contentTable;
        $this->courseTable = $courseTable;
        $this->attachmentTable = $attachmentTable;
		$this->courseUserTable = $courseUserTable;

    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        /**
         * @var string
         */
        $id = $request->getAttribute('id');

        if (false) {
            return new HtmlResponse($this->template->render('error::404'), 404);
        }

        $assignmentWorkResultSet = $this->assignmentWorkTable->byWorker($id);

        $courses = [];

            $studentId = $id;
            $courses = $this->courseUserTable->fetchByUserId($studentId)->toArray();
			//var_dump($courses);
            foreach ($courses as $key => $course) {
                $modules = $this->contentTable->contentByCourseId($course['id'])->toArray();
                foreach ($modules as $key1 => $module) {
                    if (empty($module['required'])) {
                        unset($modules[$key1]);
                        continue;
                    }
                    $assignments = $this->attachmentTable->fetchByAttachmentAndTopicId('Assignment', $module['id'])->toArray();
                    foreach($assignments as $key2 => $assignment) {
                        if($this->assignmentWorkTable->hasCompleteByWorkerAndAssignment($studentId, $assignment['attachment_id'])) {
                            $modules[$key1]['status'] = 'btn-success';
                            break;
                        } elseif($this->assignmentWorkTable->hasFailedByWorkerAndAssignment($studentId, $assignment['attachment_id'])) {
                            $modules[$key1]['status'] = 'btn-danger';
                            break;
                        }
                    }
                    if (empty($modules[$key1]['status'])) {
                        $modules[$key1]['status'] = 'btn-secondary';
                    }
                }
                $courseInfo =  $this->courseTable->fetchById($course['id']);

                $courses[$key]['name'] = $courseInfo->getName();
                $courses[$key]['modules'] = $modules;
            }
        

        /**
         * @var Model\Course $course
         */
        $user = $this->userTable->oneById($id);

        if (false === $user) {
            return $delegate->process($request);
        }
        return new HtmlResponse($this->template->render('credit::student-single', [
            'user' => $user,
            'usermeta' => $this->userMetaTable->fetchByUserId($id),
            'assignmentWorkResultSet' => $assignmentWorkResultSet,
            'courses' => $courses
        ]));
    }
}
