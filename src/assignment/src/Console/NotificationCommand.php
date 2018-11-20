<?php
namespace Assignment\Console;

use Assignment\Model\AssignmentWorkTable;
use Cake\Chronos\MutableDate;
use Options\Model\Options;
use Options\Model\OptionsTable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use User\Model\UserTable;
use Zend\Mail as ZendMail;
use Zend\Mail\Transport\TransportInterface;
use Zend\Mime\Message as MimeMessage;
use Zend\Mime\Part as MimePart;
use Zend\Uri\Uri;

class NotificationCommand extends Command
{
    /**
     * @var AssignmentWorkTable
     */
    private $assignmentWorkTable;

    /**
     * @var OptionsTable
     */
    private $optionsTable;

    /**
     * @var TransportInterface
     */
    private $transportMail;

    /**
     * @var UserTable
     */
    private $userTable;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor
     * @param AssignmentWorkTable $assignmentWorkTable
     * @param OptionsTable $optionsTable
     * @param UserTable $userTable
     * @param TransportInterface $transportMail
     * @param $config
     */
    public function __construct(AssignmentWorkTable $assignmentWorkTable, OptionsTable $optionsTable,
                                UserTable $userTable, TransportInterface $transportMail, $config)
    {
        $this->assignmentWorkTable = $assignmentWorkTable;
        $this->optionsTable = $optionsTable;
        $this->userTable = $userTable;
        $this->transportMail = $transportMail;
        $this->config = $config;

        parent::__construct();
    }

    /**
     * Configures the command
     */
    protected function configure()
    {
        $this->setName('assignment:check:notification')
            ->setDescription('Check marking days left, and notify tutors');
    }

    /**
     * Executes the current command
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \ReflectionException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Start: Checking overdue marking...");

        $awaitings = $this->assignmentWorkTable->checkMarkingDaysAndNotify(0, 100);
        foreach ($awaitings as  $index => $item) {
            if ($item['marking_days_left'] == 1) {
                $output->writeln("Sending Overdue Marking 1st Notification To:  {$item['tutor_name']}");
                $this->sendFirstNotification($item);
            }
            if ($item['marking_days_left'] == 0) {
                $output->writeln("Sending Overdue Marking 2nd Notification To:  {$item['tutor_name']}");
                $this->sendSecondNotification($item);
            }
            if ($item['marking_days_left'] == -3) {
                $output->writeln("Sending Overdue Marking 3rd Notification To:  {$item['tutor_name']}");
                $this->sendThirdNotification($item);
            }
        }

        $output->writeln("End: Checking overdue marking complete.");
    }

    /**
     * @param $item
     * @throws \ReflectionException
     */
    private function sendFirstNotification($item)
    {
        $title = $this->optionsTable->fetchByName('marking_day_first_notice_title');
        $title = !empty($title['value']) ? $title['value'] : Options::FIST_ASSIGNMENT_NOTIF_TEMPLATE_TITLE;
        $body = $this->optionsTable->fetchByName('marking_day_first_notice_body');
        $body = !empty($body['value']) ? $body['value'] : Options::FIST_ASSIGNMENT_NOTIF_TEMPLATE_BODY;

        $searches = [
            "{tutor_name}" => $item['tutor_name'],
            "{student_name}" => $item['worker_name'],
            "{course}" => $item['course_name'],
            "{assignment_upload_date}" => $item['submission_date'],
            "{course_module}" => $item['module_name'],
            "{date_marking_due}" => $this->assignmentWorkTable->get_next_working_days(MutableDate::now()->addDay())->format('F j, Y'),
            "{marking_link}" => $this->getLink($item['id'])
        ];
        $body = str_replace(array_keys($searches), array_values($searches), $body);

        $tutor = $this->userTable->oneById($item['tutor_id']);
        $this->sendEmail($tutor->getIdentity(), $body, $title);
    }

    /**
     * @param $item
     * @throws \ReflectionException
     */
    private function sendSecondNotification($item)
    {
        $title = $this->optionsTable->fetchByName('marking_day_second_warning_title');
        $title = !empty($title['value']) ? $title['value'] : Options::SECOND_ASSIGNMENT_NOTIF_TEMPLATE_TITLE;
        $body = $this->optionsTable->fetchByName('marking_day_second_warning_body');
        $body = !empty($body['value']) ? $body['value'] : Options::SECOND_ASSIGNMENT_NOTIF_TEMPLATE_BODY;

        $searches = [
            "{tutor_name}" => $item['tutor_name'],
            "{student_name}" => $item['worker_name'],
            "{course}" => $item['course_name'],
            "{assignment_upload_date}" => $item['submission_date'],
            "{course_module}" => $item['module_name'],
            "{date_marking_due}" => $this->assignmentWorkTable->get_next_working_days(MutableDate::now())->format('F j, Y'),
            "{marking_link}" => $this->getLink($item['id'])
        ];
        $body = str_replace(array_keys($searches), array_values($searches), $body);

        $tutor = $this->userTable->oneById($item['tutor_id']);
        $this->sendEmail($tutor->getIdentity(), $body, $title);
    }

    /**
     * @param $item
     * @throws \ReflectionException
     */
    private function sendThirdNotification($item)
    {
        $title = $this->optionsTable->fetchByName('marking_day_last_warning_title');
        $title = !empty($title['value']) ? $title['value'] : Options::THIRD_ASSIGNMENT_NOTIF_TEMPLATE_TITLE;
        $body = $this->optionsTable->fetchByName('marking_day_last_warning_body');
        $body = !empty($body['value']) ? $body['value'] : Options::THIRD_ASSIGNMENT_NOTIF_TEMPLATE_BODY;

        $searches = [
            "{tutor_name}" => $item['tutor_name'],
            "{student_name}" => $item['worker_name'],
            "{course}" => $item['course_name'],
            "{assignment_upload_date}" => $item['submission_date'],
            "{course_module}" => $item['module_name'],
            "{date_marking_due}" => $this->assignmentWorkTable->get_next_working_days(MutableDate::now()->subDay(3))->format('F j, Y'),
            "{marking_link}" => $this->getLink($item['id'])
        ];
        $body = str_replace(array_keys($searches), array_values($searches), $body);

        $tutor = $this->userTable->oneById($item['tutor_id']);
        $this->sendEmail($tutor->getIdentity(), $body, $title);
    }

    /**
     * @param $receiver
     * @param $body
     * @param $title
     */
    private function sendEmail($receiver, $body, $title): void
    {
        $htmlMarkup = "<html>$body</html>";
        $html = new MimePart($htmlMarkup);
        $html->type = "text/html";

        $body = new MimeMessage();
        $body->addPart($html);

        $sender = $this->optionsTable->fetchByName('from_email');
        $sender = !empty($sender['value']) ? $sender['value'] : 'do_not_reply@lms.ncchomelearning.co.uk';

        $cc = $this->optionsTable->fetchByName('cc_notification_to');
        $cc = !empty($cc['value']) ? explode(',', $cc['value']) : [];
        $cc = array_filter($cc, function ($item) {
            return filter_var($item, FILTER_VALIDATE_EMAIL) !== false;
        });

        $message = new ZendMail\Message();
        $message->addTo($receiver)
            ->addFrom($sender)
            ->setSubject($title)
            ->setBody($body);

        if (!empty($cc)) {
            $message->addCc($cc);
        }
        
        $this->transportMail->send($message);
    }

    private function getLink($id)
    {
        $uri = new Uri($this->config['base_url']);
        $uri->setPath("/tutor/assignment/view/work/single/{$id}");
        return $uri->toString();
    }
}
