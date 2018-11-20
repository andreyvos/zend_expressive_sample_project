<?php

namespace Credit\Action\Post;

use User\Model\UserTable;
use Topic\Model\AttachmentTable;
use Course\Model\ContentTable;
use Assignment\Model\AssignmentWorkTable;
use Tutor\Model\TutorStudentCourseTable;
use Options;
use Interop\Http\ServerMiddleware\DelegateInterface;
use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\InputFilter\InputFilterInterface;
use Zend\Mail;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Expressive\Template;
use Course\Model\CourseTable;
use User\Model\UserMetaTable;
use Zend\Session\Container;
use Credit\Model\CreditStudentTable;
use Course\Model\CourseUserTable;
use Credit\Model\CreditOptionsTable;
use Exam\Model\ExamTriesTable;
use Exclusive\Model\MessageTutorTable;
use Exclusive\Model\CertificatePrintFreeTable;

class Students
{
    /**
     * @var Template\TemplateRendererInterface
     */
    private $template;

    /**
     * @var UserTable
     */
    private $userTable;

    /**
     * @var UserMetaTable
     */
    private $userMetaTable;

    /**
     * @var CourseTable
     */
    private $courseTable;

    /**
     * @var AttachmentTable
     */
    private $attachmentTable;

    /**
     * @var ContentTable;
     */
    private $contentTable;

    /**
     * @var AssignmentWorkTable
     */
    private $assignmentWorkTable;

    /**
     * @var TutorStudentCourseTable
     */
    private $tutorStudentCourseTable;

    /**
     * @var Options
     */
    private $optionsTable;

	private $creditOptionsTable;

    /**
     * @var InputFilterInterface
     */
    private $inputFilter;

    /**
     * @var string
     */
    private $successUrl;

	private $creditStudentTable;
	private $courseUserTable;

    /**
     * @var ExamTriesTable
     */
    private $examTriesTable;

    /**
     * @var MessageTutorTable
     */
    private $messageTutorTable;

    /**
     * @var CertificatePrintFreeTable
     */
    private $certificatePrintFreeTable;

    public function __construct(
        Template\TemplateRendererInterface $template,
        UserTable $userTable,
        UserMetaTable $userMetaTable,
        CourseTable $courseTable,
        AttachmentTable $attachmentTable,
        ContentTable $contentTable,
        AssignmentWorkTable $assignmentWorkTable,
        TutorStudentCourseTable $tutorStudentCourseTable,
        Options\Model\OptionsTable $optionsTable,
        InputFilterInterface $inputFilter,
        string $successUrl,
        Mail\Transport\TransportInterface $transportMail,
		CreditStudentTable $creditStudentTable,
		CourseUserTable $courseUserTable,
		CreditOptionsTable $creditOptionsTable,
		ExamTriesTable $examTriesTable, 
		MessageTutorTable $messageTutorTable,
		CertificatePrintFreeTable $certificatePrintFreeTable
    )
    {
        $this->template = $template;
        $this->userTable = $userTable;
        $this->userMetaTable = $userMetaTable;
        $this->courseTable = $courseTable;
        $this->attachmentTable = $attachmentTable;
        $this->contentTable = $contentTable;
        $this->assignmentWorkTable = $assignmentWorkTable;
        $this->tutorStudentCourseTable = $tutorStudentCourseTable;
        $this->optionsTable = $optionsTable;
        $this->inputFilter = $inputFilter;
        $this->successUrl = $successUrl;
        $this->transportMail = $transportMail;
		$this->creditStudentTable = $creditStudentTable;
		$this->courseUserTable = $courseUserTable;
		$this->creditOptionsTable = $creditOptionsTable;
        $this->examTriesTable = $examTriesTable;
        $this->messageTutorTable = $messageTutorTable;
        $this->certificatePrintFreeTable = $certificatePrintFreeTable;
    }

    public function __invoke(ServerRequestInterface $request, DelegateInterface $delegate)
    {
        $collection_data = $request->getParsedBody() ?? [];
		$notifytoemail =  $collection_data['notify'] ?? false;

		//check permission
		$user_session = new Container('credit_user');
		$currentUser = $user_session->data;
		if(!isset($user_session->username)) {
			return new RedirectResponse(($this->urlHelper)('credit/form/login'));
		} 
		$currentUserId = (int) $currentUser->getId();
		
		//insert data each student
		$students = [];
		$n = 0;
		foreach($collection_data['student'] as $data) { 
			
			$data['role'] = 'Rbac\Role\Student';
			$data['username'] = $data['identity'];
			$data['password'] = 'secret';
			
			$datacourse = [];
			if (isset($data['course'])) {
				$datacourse = $data['course'];
			}

			//var_dump($data);
			$this->inputFilter->setData($data);
			
			if (!$this->inputFilter->isValid() or count($datacourse) < 1) {
				continue;
			}

			$response = $this->userTable->save($data);

			$firstName = $data['meta']['first_name'] ?? '';
			$lastName = $data['meta']['last_name'] ?? '';
			
			
			$students[$n]['name'] = $firstName.' '.$lastName;
			$students[$n]['pin'] = $data['pin'];
			$n++;

			$data['notify-user'] = 1;	
			$notify = ! empty($data['notify-user']) ?? false;
			
			$email = $this->inputFilter->getValues()['identity'] ?? '';
			$pin = $this->inputFilter->getValues()['pin'] ?? '';


			if ('duplicate_identity' === $response) {
				continue;
			} elseif ('duplicate_username' === $response) {
				continue;
			} elseif ('duplicate_pin' === $response) {
				continue;
			}

			if($data['id']) {
				if(!$this->creditStudentTable->isCreditUserForStudent($currentUserId,$data['id'])) {
					$this->creditStudentTable ->insert($currentUserId,$data['id']);
				}
			} else {
				$this->creditStudentTable ->insert($currentUserId, $response);
			}

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

			if($data['id']) {
				$this->courseUserTable->save($data['id'],...($datacourse ?? []));
				$this->tutorStudentCourseTable->save($data['id'],...$courseAndTutor);
				$this->examTriesTable->save($data['id'], ...($datacourse ?? []));
				$this->messageTutorTable->save($data['id'], ...($datacourse ?? []));
				$this->certificatePrintFreeTable->save($data['id'], ...($datacourse ?? []));
			} else {
				$this->courseUserTable->save( $response,...($datacourse ?? []));
				$this->tutorStudentCourseTable->save($response,...$courseAndTutor);
				$this->examTriesTable->save( $response, ...($datacourse ?? []));
				$this->messageTutorTable->save( $response, ...($datacourse ?? []));
				$this->certificatePrintFreeTable->save( $response, ...($datacourse ?? []));
			}

			if ($this->optionsTable->optionExists('from_email')) {
				$from = $this->optionsTable->fetchByName('from_email')['value'];
			}

			$email = $data['identity'];
			$password = $data['password'];

			if (!$data['id'] && $notify && ! empty($email) && ! empty($password)) {
				$courseIds = $datacourse ?? [];
				$courseName = '';
				$courseNames = array();
				if (is_array($courseIds) && count($courseIds) > 0) {
					foreach($courseIds as $courseId) {
						$course = $this->courseTable->fetchById($courseId);
						$courseNames[] = $course->getName();
					}
				}
				$courseName = implode(", ", $courseNames);

				//send welcome message to student
				$htmlMarkup = $this->template->render('emails::newaccountstudent', [
					'layout' => false,
					'fullName' => $firstName . ' ' . $lastName,
					'username' => $this->inputFilter->getValues()['username'],
					'password' => $password,
					'pin' => $pin,
					'courseName' => $courseName
				]);
				$html = new MimePart($htmlMarkup);
				$html->type = "text/html";

				$body = new MimeMessage();
				$body->addPart($html);

				if (! empty($from)) {
					$message = new Mail\Message();
					$message->addTo($email)
						->addFrom($from)
						->setSubject('Welcome to NCC Home Learning')
						->setBody($body);
					$this->transportMail->send($message);
				}
			}
		}

		//send email to credit user
		if ($notifytoemail) {
			$credituseremail = $currentUser->getIdentity();
			$htmlMarkup = $this->template->render('credit::emails/students', [
				'layout' => false,
				'students' => $students
			]);
			$html = new MimePart($htmlMarkup);
			$html->type = "text/html";

			$body = new MimeMessage();
			$body->addPart($html);

			if (! empty($from)) {
				$message = new Mail\Message();
				$message->addTo($credituseremail)
					->addFrom($from)
					->setSubject('Your Student Details')
					->setBody($body);
				$this->transportMail->send($message);
			}
		}

		if(isset($collection_data['op'])) {
			//$op = new Container('op_session');
			//$op->action = 'post';
		}

        return new JsonResponse([
            'redirectTo' => ($this->successUrl)
        ]);
    }
}