<?php

namespace Credit\Factory\Action\Post;

use Psr\Container\ContainerInterface;
use Credit\InputFilter;
use Credit\Action;
use User\Model\UserTable;
use User\Model\UserMetaTable;
use Course\Model\CourseTable;
use Topic\Model\AttachmentTable;
use Course\Model\ContentTable;
use Assignment\Model\AssignmentWorkTable;
use Tutor\Model\TutorStudentCourseTable;
use Options\Model\OptionsTable;
use Zend\Expressive\Helper\UrlHelper;
use Zend\Mail;
use Zend\Expressive\Template;
use Credit\Model\CreditStudentTable;
use Course\Model\CourseUserTable;
use Credit\Model\CreditOptionsTable;
use Exam\Model\ExamTriesTable;
use Exclusive\Model\MessageTutorTable;
use Exclusive\Model\CertificatePrintFreeTable;

class Student
{
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $template = $container->get(Template\TemplateRendererInterface::class);
        $userTable = $container->get(UserTable::class);
        $userMetaTable = $container->get(UserMetaTable::class);
        $courseTable = $container->get(CourseTable::class);
        $attachmentTable = $container->get(AttachmentTable::class);
        $contentTable = $container->get(ContentTable::class);
        $assignmentWorkTable = $container->get(AssignmentWorkTable::class);
        $tutorStudentCourseTable = $container->get(TutorStudentCourseTable::class);
        $optionsTable = $container->get(OptionsTable::class);
        $inputFilter = new InputFilter\Student();
        $transportMail = $container->get(Mail\Transport\TransportInterface::class);
        $successRouteName = $container->get(UrlHelper::class);
		$creditStudentTable = $container->get(CreditStudentTable::class);
		$courseUserTable = $container->get(CourseUserTable::class);
		$creditOptionsTable = $container->get(CreditOptionsTable::class);
		$examTriesTable = $container->get(ExamTriesTable::class);
		$messageTutorTable = $container->get(MessageTutorTable::class);
		$certificatePrintFreeTable = $container->get(CertificatePrintFreeTable::class);
        return new Action\Post\Student(
            $template,
            $userTable,
            $userMetaTable,
            $courseTable,
            $attachmentTable,
            $contentTable,
            $assignmentWorkTable,
            $tutorStudentCourseTable,
            $optionsTable,
            $inputFilter,
            $successRouteName('credit/view/student/resultset'),
            $transportMail,
			$creditStudentTable,
			$courseUserTable,
			$creditOptionsTable,
			$examTriesTable,
			$messageTutorTable,
			$certificatePrintFreeTable
        );
    }
}