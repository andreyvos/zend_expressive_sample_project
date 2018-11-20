<?php
namespace Assignment\Model;
use Cake\Chronos\Chronos;
use Cake\Chronos\Date;
use Cake\Chronos\MutableDate;
use Course\Model\Content;
use Course\Model\ContentTable;
use Course\Model\CourseTable;
use Topic\Model\Attachment;
use Topic\Model\AttachmentTable;
use Topic\Model\TopicTable;
use Tutor\Model\TutorStudentCourse;
use Tutor\Model\TutorStudentCourseTable;
use Uploader\Model\UploaderTable;
use User\Model\UserTable;
use User\Model\UserMetaTable;
use Yasumi\Yasumi;
use Zend\Db\ResultSet\ResultSet;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
class AssignmentWorkTable
{
    /**
     * @var AssignmentWorkTableGateway
     */
    private $assignmentWorkTableGateway;
    /**
     * @var AssignmentTable
     */
    private $assignmentTable;
    /**
     * @var UserTable
     */
    private $userTable;
    /**
     * @var UserMetaTable
     */
    private $userMetaTable;
    /**
     * @var TutorStudentCourseTable
     */
    private $tutorStudentCourseTable;
    /**
     * @var UploaderTable
     */
    private $uploaderTable;
    /**
     * @var CourseTable
     */
    private $courseTable;
    /**
     * @var ContentTable
     */
    private $contentTable;
    /**
     * @var TopicTable
     */
    private $topicTable;
    /**
     * @var AttachmentTable
     */
    private $attachmentTable;
    public function __construct(
        AssignmentWorkTableGateway $assignmentWorkTableGateway,
        AssignmentTable $assignmentTable,
        UserTable $userTable,
        UserMetaTable $userMetaTable,
        TutorStudentCourseTable $tutorStudentCourseTable,
        UploaderTable $uploaderTable,
        CourseTable $courseTable,
        ContentTable $contentTable,
        TopicTable $topicTable,
        AttachmentTable $attachmentTable
    )
    {
        $this->assignmentWorkTableGateway = $assignmentWorkTableGateway;
        $this->assignmentTable = $assignmentTable;
        $this->userTable = $userTable;
        $this->userMetaTable = $userMetaTable;
        $this->tutorStudentCourseTable = $tutorStudentCourseTable;
        $this->uploaderTable = $uploaderTable;
        $this->courseTable = $courseTable;
        $this->contentTable = $contentTable;
        $this->topicTable = $topicTable;
        $this->attachmentTable = $attachmentTable;
    }
    public function fetchById(int $id)
    {
        return $this->assignmentWorkTableGateway->select(['id' => $id]);
    }
    public function byWorkerAndAssignment(int $worker, int $assignment)
    {
        $select = $this->select()
            ->where([
                'worker = ?' => $worker,
                'assignment = ?' => $assignment
            ])
            ->order('id DESC');
        $resultSet = $this->assignmentWorkTableGateway->selectWith($select);
        return $this->buildAssignmentWork($resultSet);
    }
    public function byWorkerAndCourse(int $worker, int $course)
    {
        $statement = $this->assignmentWorkTableGateway->getAdapter()->query("
            SELECT * FROM assignment_work aw
            LEFT JOIN assignment a ON a.id=aw.assignment
            LEFT JOIN topic_attachment ta ON ta.attachment_id=aw.assignment
            LEFT JOIN topic t ON t.id=ta.topic_id
            LEFT JOIN course_content c ON c.content_id=t.id
            WHERE worker={$worker} AND course_id={$course} AND ta.attachment='Assignment'
        ");

        $resultSet = $this->assignmentWorkTableGateway->getResultSetPrototype();
        $result = $resultSet->initialize($statement->execute());
        return $this->buildAssignmentWork($result);
    }
    public function hasCompleteByWorkerAndTutorAndAssignment(int $worker, int $tutor, int $assignment): bool
    {
        $where = new Where();
        $where->EqualTo('worker', $worker);
        $where->EqualTo('tutor', $tutor);
        $where->EqualTo('assignment', $assignment);
        $where->EqualTo('status', AssignmentWork::STATUS_PASS);
        $statement = $this->assignmentWorkTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->assignmentWorkTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $completeCollection = $statement->execute();
        if ($completeCollection->count() > 0) {
            return true;
        }
        return false;
    }
    public function hasFailedByWorkerAndTutorAndAssignment(int $worker, int $tutor, int $assignment): bool
    {
        $where = new Where();
        $where->EqualTo('worker', $worker);
        $where->EqualTo('tutor', $tutor);
        $where->EqualTo('assignment', $assignment);
        $where->EqualTo('status', AssignmentWork::STATUS_FAIL);
        $statement = $this->assignmentWorkTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->assignmentWorkTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $optionCollection = $statement->execute();
        if ($optionCollection->count() > 0) {
            return true;
        }
        return false;
    }
    public function hasCompleteByWorkerAndAssignment(int $worker, int $assignment): bool
    {
        $where = new Where();
        $where->EqualTo('worker', $worker);
        $where->EqualTo('assignment', $assignment);
        $where->EqualTo('status', AssignmentWork::STATUS_PASS);
        $statement = $this->assignmentWorkTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->assignmentWorkTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $completeCollection = $statement->execute();
        if ($completeCollection->count() > 0) {
            return true;
        }
        return false;
    }
    public function hasFailedByWorkerAndAssignment(int $worker, int $assignment): bool
    {
        $where = new Where();
        $where->EqualTo('worker', $worker);
        $where->EqualTo('assignment', $assignment);
        $where->EqualTo('status', AssignmentWork::STATUS_FAIL);
        $statement = $this->assignmentWorkTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->assignmentWorkTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $optionCollection = $statement->execute();
        if ($optionCollection->count() > 0) {
            return true;
        }
        return false;
    }
    public function byTutor(int $tutor)
    {
        $select = $this->select()
            ->order('days_since_create ASC')
            ->where([
                'tutor = ?' => $tutor
            ]);
        $resultSet = $this->assignmentWorkTableGateway->selectWith($select);
        return $this->buildAssignmentWork($resultSet);
    }

    /**
     * @deprecated use AssignmentWorkTableGateway::countOverdueByTutor(int $tutor)
     * @param int $tutor
     * @return int
     */
    public function countOverdueByTutor(int $tutor)
    {
        $select = $this->select()
            ->where(['status = ?' => AssignmentWork::STATUS_WAIT, 'tutor' => $tutor])
            ->having(['days_since_create > marking_days']);
        $resultSet = $this->assignmentWorkTableGateway->selectWith($select);
        return count($resultSet);
    }

    /**
     * @deprecated use AssignmentWorkTableGateway::countUnmarkedByTutor(int $tutor)
     * @param int $tutor
     * @return int
     */
    public function countUnmarkedByTutor(int $tutor)
    {
        $select = $this->select()
            ->where(['status = ?' => AssignmentWork::STATUS_WAIT, 'tutor' => $tutor]);
        $resultSet = $this->assignmentWorkTableGateway->selectWith($select);
        return count($resultSet);
    }

    public function byWorker(int $worker)
    {
        $select = $this->select()
            ->order('days_since_create ASC')
            ->where([
                'worker = ?' => $worker
            ]);
        $resultSet = $this->assignmentWorkTableGateway->selectWith($select);
        return $this->buildAssignmentWork($resultSet);
    }

    /**
     * @param array $params
     * @return array
     * @throws \ReflectionException
     */
    public function usingFilter($params)
    {
        $draw = intval($params['draw']);
        $start = intval($params['start']);
        $length = intval($params['length']);
        $search = isset($params['search']) ? $params['search'] : [];
        $orders = isset($params['order']) ? $params['order']: [];
        $columns = isset($params['columns']) ? $params['columns'] : [];
        $status = isset($params['status']) ? $params['status'] : '';

        $attachmentTableName = $this->attachmentTable->tableGateway->getTable();
        $assignmentWorkTableName = $this->assignmentWorkTableGateway->getTable();
        $assignmentTableName = $this->assignmentTable->tableGateway->getTable();
        $userTableName = $this->userTable->userTableGateway->getTable();
        $topicTableName =  $this->topicTable->tableGateway->getTable();
        $courseTableName = $this->courseTable->tableGateway->getTable();
        $courseContentTableName = $this->contentTable->tableGateway->getTable();

        $where = new Where();
        $where->equalTo("$attachmentTableName.attachment", 'Assignment');
        if (!empty($params['tutor'])) {
            $where->equalTo("$assignmentWorkTableName.tutor", $params['tutor']);
        }

        switch ($status) {
            case 'marked':
                $where->greaterThan("$assignmentWorkTableName.status", AssignmentWork::STATUS_WAIT);
                break;
            case 'passed':
                $where->equalTo("$assignmentWorkTableName.status", AssignmentWork::STATUS_PASS);
                break;
            case 'failed':
                $where->equalTo("$assignmentWorkTableName.status", AssignmentWork::STATUS_FAIL);
                break;
            case 'due':
                $where->equalTo("$assignmentWorkTableName.status", AssignmentWork::STATUS_WAIT);
                break;
            default:
                if (is_int($status) && $status > 0) {
                    $where->equalTo("$assignmentWorkTableName.status", $status);
                }
                break;
        }

        if (!empty($search['value'])) {
            $value = $search['value'];
            $orexprs = [];
            foreach ($columns as $column) {
                if (!$column['searchable']) continue;

                $searchwhere = new Where();

                $name = $column['data'];
                if ($name == 'course_name') {
                    $searchwhere->like("$courseTableName.name", "%$value%");
                    $orexprs[] = $searchwhere;
                }
                if ($name == 'module_name') {
                    $searchwhere->like("$topicTableName.name", "%$value%");
                    $orexprs[] = $searchwhere;
                }

                if (in_array($name, ['worker_name'])) {
                    $metawhere = new Where();
                    $metawhere->in("name", ['first_name', 'last_name']);
                    $metawhere->like("value", "%$value%");
                    $meta = $this->userMetaTable->select($metawhere)->toArray();
                    $userids = [-1];
                    foreach ($meta as $item) {
                        $userids[] = $item['user_id'];
                    }

                    $searchwhere->in("$assignmentWorkTableName.worker", $userids);
                    $orexprs[] = $searchwhere;
                }
            }

            if (!empty($orexprs)) {
                $orexpr = new Where();
                $orexpr->addPredicates($orexprs, PredicateSet::OP_OR);
                $where->addPredicate($orexpr, PredicateSet::OP_AND);
            }
        }

        $selectcount = $this->assignmentWorkTableGateway->getSql()
            ->select()
            ->columns(array('total' => new Expression('COUNT(*)')))
            ->join(['worker' => $userTableName], "worker.id = $assignmentWorkTableName.worker", [], Select::JOIN_LEFT)
            ->join(['tutor' => $userTableName], "tutor.id = $assignmentWorkTableName.tutor", [], Select::JOIN_LEFT)
            ->join($attachmentTableName, "$attachmentTableName.attachment_id = $assignmentWorkTableName.assignment", [], Select::JOIN_INNER)
            ->join($topicTableName, "$topicTableName.id = $attachmentTableName.topic_id", [], Select::JOIN_LEFT)
            ->join($assignmentTableName, "$assignmentTableName.id = $assignmentWorkTableName.assignment", [], Select::JOIN_INNER)
            ->join($courseContentTableName, "$courseContentTableName.content_id = $topicTableName.id", [], Select::JOIN_INNER)
            ->join($courseTableName, "$courseTableName.id = $courseContentTableName.course_id", [], Select::JOIN_LEFT)
            ->where($where);
        $total = $this->assignmentWorkTableGateway->getSql()->prepareStatementForSqlObject($selectcount)->execute()->current();

        $sql = $this->assignmentWorkTableGateway->getSql()
            ->select()
            ->join(['worker' => $userTableName], "worker.id = $assignmentWorkTableName.worker", ['worker_id' => 'id'], Select::JOIN_LEFT)
            ->join(['tutor' => $userTableName], "tutor.id = $assignmentWorkTableName.tutor", ['tutor_id' => 'id'], Select::JOIN_LEFT)
            ->join($attachmentTableName, "$attachmentTableName.attachment_id = $assignmentWorkTableName.assignment", [], Select::JOIN_INNER)
            ->join($topicTableName, "$topicTableName.id = $attachmentTableName.topic_id", ['module_name' => 'name'], Select::JOIN_LEFT)
            ->join($assignmentTableName, "$assignmentTableName.id = $assignmentWorkTableName.assignment", ['assignment_name' => 'name'], Select::JOIN_INNER)
            ->join($courseContentTableName, "$courseContentTableName.content_id = $topicTableName.id", [], Select::JOIN_INNER)
            ->join($courseTableName, "$courseTableName.id = $courseContentTableName.course_id", ['course_name' => 'name'], Select::JOIN_LEFT)
            ->where($where)
            ->limit($length)->offset($start);

        foreach ($orders as $order) {
            $direction = $order['dir'];
            $column = $columns[$order['column']]['data'];
            if ($column === 'course_name') {
                $sql->order(["$courseTableName.name" => $direction]);
            }
            if ($column === 'module_name') {
                $sql->order(["$topicTableName.name" => $direction]);
            }
            if ($column === 'status') {
                $sql->order(["$assignmentWorkTableName.status" => $direction]);
            }
            if ($column === 'submission_date') {
                $sql->order([
                    "$assignmentWorkTableName.created_at" => $direction,
                    "$assignmentWorkTableName.updated_at" => $direction,
                ]);
            }
        }

        if (empty($orders)) {
            $sql->order([
                "$assignmentWorkTableName.created_at" => 'DESC',
                "$assignmentWorkTableName.updated_at" => 'DESC',
            ]);
        }

        $result = [];
        $statement = $this->assignmentWorkTableGateway->getSql()->prepareStatementForSqlObject($sql);
        $assigments = $statement->execute();
        foreach ($assigments as $item) {
            $workerFirstname = $this->userMetaTable->getMetaByName($item['worker'], 'first_name')->toArray();
            $workerLastname = $this->userMetaTable->getMetaByName($item['worker'], 'last_name')->toArray();
            $workerFirstname = empty($workerFirstname) ? '' : $workerFirstname[0]['value'];
            $workerLastname = empty($workerLastname) ? '' : $workerLastname[0]['value'];
            $statusText = $this->getStatus(intval($item['status']), \date('F j, Y, g:i a', $item['updated_at']));

            $markingDays = $this->userMetaTable->getMetaByName($item['worker'], 'marking_days')->toArray();
            $markingDays = empty($markingDays) ? 5 : intval($markingDays[0]['value']);

            $startDate = !empty($item['updated_at']) ? $item['updated_at'] : $item['created_at'];
            $endDate = !empty($item['updated_at']) ? \date("Y-m-d", $item['updated_at']) : Date::now()->format('Y-m-d');
            $markingDaysLeft = $this->marking_days_left(\date("Y-m-d", $startDate), $endDate, $markingDays);
            $markingDaysLeftText = $markingDaysLeft . (abs($markingDaysLeft) > 1 ? ' days' : ' day');

            $result[] = [
                "id" => $item['id'],
                "tutor_id" => $item["tutor"],
                "worker_id" => $item['worker'],
                "worker_name" => "$workerFirstname $workerLastname",
                "course_name" => $item['course_name'],
                "module_name" => $item['module_name'] . ' &raquo;&raquo; ' . $item['assignment_name'],
                "status" => $statusText,
                "submission_date" => \date('F j, Y, g:i a', $item['created_at']),
                "marking_days_left" => $markingDaysLeftText
            ];
        }

        return [
            'draw' => $draw,
            'recordsTotal' => !empty($total) ? intval($total['total']) : 0,
            'recordsFiltered' => !empty($total) ? intval($total['total']) : 0,
            'data' => $result
        ];
    }

    private function getStatus(int $status, $submitionDate)
    {
        $text = '';
        if ($status === AssignmentWork::STATUS_WAIT) {
            $text = 'Not Marked';
        }
        if ($status === AssignmentWork::STATUS_FAIL) {
            $text = "Referred $submitionDate";
        }
        if ($status === AssignmentWork::STATUS_PASS) {
            $text = "Passed $submitionDate";
        }
        return $text;
    }

    public function byWorkAndTutor(int $work, int $tutor)
    {
        $select = $this->select()
            ->where([
                'id' => $work,
                'tutor' => $tutor
            ]);
        $resultSet = $this->assignmentWorkTableGateway->selectWith($select);
        return $this->buildAssignmentWork($resultSet);
    }
    public function byWorkerAndTutor(int $worker, int $tutor)
    {
        return $this->assignmentWorkTableGateway->select([
            'worker' => $worker,
            'tutor' => $tutor
        ])->toArray();
    }
    public function fetchLastWorkByWorkerAndAssignment(int $worker, int $assignment)
    {
        $statement = $this->assignmentWorkTableGateway->getAdapter()->query("
            SELECT aw.id FROM assignment_work aw WHERE aw.worker = '$worker' AND aw.assignment = '$assignment' ORDER BY aw.id DESC LIMIT 1
        ");
        $resultSet = $this->assignmentWorkTableGateway->getResultSetPrototype();
        $lastWork = $resultSet->initialize($statement->execute())->toArray();
        return $lastWork[0]['id'] ?? 0;
    }
    public function updateTutor($id, $tutor)
    {
        return $this->assignmentWorkTableGateway->update([
            'tutor' => $tutor
        ], [
            'id' => $id,
        ]);
    }
    public function viewed($work, $tutor)
    {
        return $this->assignmentWorkTableGateway->update([
            'viewed' => 1
        ], [
            'id' => $work,
            'tutor' => $tutor
        ]);
    }
    public function countUnread($tutor)
    {
        return count($this->assignmentWorkTableGateway->select(['tutor' => $tutor, 'viewed' => 0]));
    }
    public function markWork(int $tutor, int $work, int $feedbackUploader, int $status)
    {
        return $this->assignmentWorkTableGateway->update([
            'feedback_uploader' => $feedbackUploader,
            'status' => $status,
            'updated_at' => time(),
        ], [
            'id' => $work,
            'tutor' => $tutor
        ]);
    }
    public function submitWork(int $worker, int $assignment, int $assignmentUploader)
    {
        /**
         * @var Attachment $attachment
         * @var Content $course
         * @var TutorStudentCourse $tutorStudentCourse
         */
        $attachment = $this->attachmentTable->oneAssignmentById($assignment);
        $topicId = $attachment->getTopicId();
        $course = $this->contentTable->oneByContentId($topicId);
        $courseId = $course->getCourseId();
        $tutorStudentCourse = $this->tutorStudentCourseTable->fetchTutorForStudentAndCourse($worker, $courseId);
        $this->assignmentWorkTableGateway->insert([
            'assignment' => $assignment,
            'worker' => $worker,
            'tutor' => $tutorStudentCourse->getTutorId(),
            'assignment_uploader' => $assignmentUploader,
            'status' => AssignmentWork::STATUS_WAIT,
            'created_at' => time()
        ]);
        return [1];
    }

    /**
     * @deprecated use AssignmentWorkTableGateway::sqlSelect
     * @return Select
     */
    private function select()
    {
        return $this->assignmentWorkTableGateway->getSql()
            ->select()
            ->columns([
                '*',
                new Expression('(' . time() . ' - updated_at) / 86400 as days_since_update'),
                new Expression('(' . time() . ' - created_at) / 86400 as days_since_create'),
                new Expression("(SELECT user_meta.value FROM user_meta RIGHT JOIN assignment_work ON (user_meta.name = 'marking_days' AND user_meta.user_id = assignment_work.tutor) LIMIT 1) as marking_days")
            ]);
    }

    /**
     * @param ResultSet $resultSet
     * @return array
     * @throws \ReflectionException
     */
    public function buildAssignmentWork(ResultSet $resultSet): array
    {
        foreach ($resultSet as $result) {
            /**
             * @var Attachment $attachment
             * @var Content $course
             */
            $attachment = $this->attachmentTable->oneAssignmentById($result->assignment);
            if (! $attachment) {
                continue;
            }
            $topicId = $attachment->getTopicId();
            unset($attachment);
            $course = $this->contentTable->oneByContentId($topicId);
            if (! $course) {
                continue;
            }
            $courseId = $course->getCourseId();
            unset($course);
            $feedbackUploader = $this->uploaderTable->fetchById($result->feedback_uploader)->current();
            $markingDays = 5;
            $markingRow = $this->userMetaTable->getMetaByName($result->tutor, 'marking_days')->current();
            if ($markingRow) {
                $markingDays = ! empty($markingRow->getValue()) ? (int) $markingRow->getValue() : 5;
            }
            if (AssignmentWork::STATUS_WAIT == $result->status) {
                //affects due and overdue
                $result->days_since_create = !empty($result->days_since_create) ? round($result->days_since_create) : 0;
                $start_date = date("Y-m-d", strtotime("now -{$result->days_since_create} days"));
                $markingDaysLeft = $this->marking_days_left($start_date, Date::now()->format('Y-m-d'), $markingDays);
            } else {
                //affects marked, passed, failed, overdue_marked
                $result->days_since_create = !empty($result->days_since_create) ? round($result->days_since_create) : 0;
                $result->days_since_update = !empty($result->days_since_update) ? round($result->days_since_update) : 0;
                $start_date = date("Y-m-d", strtotime("-" . ($result->days_since_create - $result->days_since_update) . " days"));
                $end_date = date("Y-m-d", strtotime("-" . ($result->days_since_update) . " days"));
                $markingDaysLeft = $this->marking_days_left($start_date, $end_date, $markingDays);
                
            }
            $results[] = new AssignmentWork(
                $result->id,
                $this->assignmentTable->fetchById($result->assignment),
                $this->userTable->oneById($result->worker),
                $this->userTable->oneById($result->tutor),
                $this->uploaderTable->fetchById($result->assignment_uploader)->current(),
                $feedbackUploader ? $feedbackUploader : null,
                $this->courseTable->fetchById($courseId),
                $this->topicTable->fetchById($topicId)->current(),
                $result->status,
                $result->created_at,
                $result->updated_at,
                $markingDaysLeft
            );
        }
        return $results ?? [];
    }

    /**
     * @param MutableDate $date
     * @return bool
     * @throws \ReflectionException
     */
    private function is_working_day(MutableDate $date)
    {
        $holidays = Yasumi::create('UnitedKingdom', $date->year);
        return $holidays->isWorkingDay($date);
    }

    /**
     * @param string $start_date in Y-m-d format
     * @param string $to_date in Y-m-d format
     * @param $marking_days_available
     * @return int|string
     * @throws \ReflectionException
     */
    private function marking_days_left($start_date, $to_date, $marking_days_available)
    {
        $from = new Date($start_date);
        $to = $from->addDay()->toMutable(); // always add 1 day as default

        while($marking_days_available > 0) {
            if (!$this->is_working_day($to)) {
                $to->addDay();
                continue;
            }

            $to->addDay();
            --$marking_days_available;
        }

        $now = Date::createFromFormat('Y-m-d', $to_date);
        $dayleft = $now->diffInDays($to, false);

	    return $dayleft;
	}

    /**
     * @param MutableDate $date
     * @return MutableDate
     * @throws \ReflectionException
     */
	public function get_next_working_days(MutableDate $date)
    {
        while (!$this->is_working_day($date)) {
            $date->addDay();
            continue;
        }

        return $date;
    }

    /**
     * @param $start
     * @param $length
     * @return array
     * @throws \ReflectionException
     */
    public function checkMarkingDaysAndNotify($start, $length)
    {
        $response = [];
        $params = [
            'start' => $start,
            'length' => $length,
            'draw' => -1,
            'status' => AssignmentWork::STATUS_WAIT
        ];

        $result = $this->usingFilter($params);
        foreach ($result['data'] as $item) {
            $markingDaysLeftArr = explode(" ", $item['marking_days_left']);
            $allowNotif = $this->userMetaTable->getMetaByName($item['tutor_id'], 'allow_over_due_notification')->toArray();
            $allowNotif = empty($allowNotif) || $allowNotif[0]['value'] !== 'no';

            if ($allowNotif || count($markingDaysLeftArr) === 2) {
                $markingDaysLeft = intval($markingDaysLeftArr[0]);

                $tutorFirstname = $this->userMetaTable->getMetaByName($item['tutor_id'], 'first_name')->toArray();
                $tutorLastname = $this->userMetaTable->getMetaByName($item['tutor_id'], 'last_name')->toArray();
                $tutorFirstname = empty($tutorFirstname) ? '' : $tutorFirstname[0]['value'];
                $tutorLastname = empty($tutorLastname) ? '' : $tutorLastname[0]['value'];

                $item['tutor_name'] = "$tutorFirstname $tutorLastname";
                $item['marking_days_left'] = $markingDaysLeft;

                $response[] = $item;
            }
        }

        $start += count($result['data']);
        $total = $result['recordsTotal'];
        if ($start < $total) {
            $response = array_merge($response, $this->checkMarkingDaysAndNotify($start, $length));
        }

        return $response;
    }
}