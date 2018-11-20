<?php
namespace User\Model;

use Assignment\Model\AssignmentWorkTableGateway;
use Course\Model\CourseUserTable;
use Tutor\Model\TutorStudentCourseTable;
use Tutor\Model\TutorStudentCourseTableGateway;
use Exam\Model\ExamTriesTable;
use Exam\Model\ExamTriesTableGateway;
use Exclusive\Model\MessageTutorTable;
use Exclusive\Model\MessageTutorTableGateway;
use Exclusive\Model\CertificatePrintFreeTable;
use Exclusive\Model\CertificatePrintFreeTableGateway;
use Zend\Db\Sql\Expression;
use Zend\Db\Sql\Predicate\PredicateSet;
use Zend\Db\Sql\Select;
use Zend\Db\Sql\Where;
use Rbac;

class UserTable
{
    /**
     * @var UserTableGateway
     */
    public $userTableGateway;

    /**
     * @var UserMetaTable
     */
    private $userMetaTable;

    /**
     * @var CourseUserTable
     */
    private $courseUserTable;

    /**
     * @var TutorStudentCourseTable
     */
    private $tutorStudentCourseTable;

    /**
     * @var TutorStudentCourseTableGateway
     */
    private $tutorStudentCourseTableGateway;

    /**
     * @var ExamTriesTable
     */
    private $examTriesTable;

    /**
     * @var ExamTriesTableGateway
     */
    private $examTriesTableGateway;

    /**
     * @var MessageTutorTable
     */
    private $messageTutorTable;

    /**
     * @var MessageTutorTableGateway
     */
    private $messageTutorTableGateway;

    /**
     * @var CertificatePrintFreeTable
     */
    private $certificatePrintFreeTable;

    /**
     * @var CertificatePrintFreeTableGateway
     */
    private $certificatePrintFreeTableGateway;

    /**
     * @var AssignmentWorkTableGateway
     */
    private $assignmentWorkTableGateway;

    public function __construct(UserTableGateway $userTableGateway, UserMetaTable $userMetaTable,
                                CourseUserTable $courseUserTable, TutorStudentCourseTable $tutorStudentCourseTable,
                                TutorStudentCourseTableGateway $tutorStudentCourseTableGateway,
                                ExamTriesTable $examTriesTable, ExamTriesTableGateway $examTriesTableGateway,
                                MessageTutorTable $messageTutorTable, MessageTutorTableGateway $messageTutorTableGateway,
                                CertificatePrintFreeTable $certificatePrintFreeTable,
                                CertificatePrintFreeTableGateway $certificatePrintFreeTableGateway,
                                AssignmentWorkTableGateway $assignmentWorkTableGateway)
    {
        $this->userTableGateway = $userTableGateway;
        $this->userMetaTable = $userMetaTable;
        $this->courseUserTable = $courseUserTable;
        $this->tutorStudentCourseTable = $tutorStudentCourseTable;
        $this->tutorStudentCourseTableGateway = $tutorStudentCourseTableGateway;
        $this->examTriesTable = $examTriesTable;
        $this->examTriesTableGateway = $examTriesTableGateway;
        $this->messageTutorTable = $messageTutorTable;
        $this->messageTutorTableGateway = $messageTutorTableGateway;
        $this->certificatePrintFreeTable = $certificatePrintFreeTable;
        $this->certificatePrintFreeTableGateway = $certificatePrintFreeTableGateway;
        $this->assignmentWorkTableGateway = $assignmentWorkTableGateway;
    }

    /**
     * @param int $id
     *
     * @return array|\ArrayObject|User|null
     */
    public function oneById(int $id)
    {
        $resultSet = $this->userTableGateway->getResultSetPrototype();

        $select = (new Select($this->userTableGateway->getTable()))->where(['id' => $id]);
        $statement = $this->userTableGateway->getSql()->prepareStatementForSqlObject($select);
        $result = $statement->execute();

        if (!$result->count()) {
            return null;
        }
        $resultSet->initialize([
            [
                $result->current(),
                $this->userMetaTable->fetchByUserId($id),
                $this->courseUserTable->fetchByUserId($id),
                $this->tutorStudentCourseTable->fetchByStudentId($id)
            ]
        ]);

        return $resultSet->current();
    }

    public function byRole(string $role)
    {
        return $this->userTableGateway->select(['role' => $role]);
    }

    public function byIdentity(string $identity)
    {
        return $this->userTableGateway->select(['identity' => $identity]);
    }

    public function byToken(string $token)
    {
        $where = new Where();
        $where->equalTo('token', $token);
        $where->greaterThanOrEqualTo('token_time',time() - 7200);
        return $this->userTableGateway->select($where);
    }

    public function byUsername(string $username)
    {
        return $this->userTableGateway->select(['username' => $username]);
    }

    /**
     * @param User $loggedUser
     * @param array $params follow params sent by https://datatables.net/manual/server-side
     * @return array
     * @throws \Exception
     */
    public function getStudents($loggedUser, $params)
    {
        $start = intval($params['start']);
        $length = intval($params['length']);
        $search = $params['search'];
        $orders = isset($params['order']) ? $params['order']: [];
        $draw = intval($params['draw']);
        $columns = $params['columns'];

        $userTable = $this->userTableGateway->getTable();
        $tutorStudentCourseTable = $this->tutorStudentCourseTableGateway->getTable();

        $where = new Where();
        $where->equalTo($userTable . '.role', Rbac\Role\Student::class);

        foreach ($columns as $column) {
            if (!$column['searchable'] || empty($column['search']['value'])) continue;

            $name = $column['data'];
            $value = $column['search']['value'];
            if (in_array($name, ['identity', 'username'])) {
                $where->like("$userTable.$name", "%{$value}%");
            }
            if ($name == 'course_name') {
                $where->like("course.name", "%{$value}%");
            }
            if ($name == 'home_learning_number') {
                $where->like("$tutorStudentCourseTable.$name", "%{$value}%");
            }
            if ($name == 'end_of_support') {
                $date = \DateTime::createFromFormat('d/m/Y', $value);
                if ($date !== false) {
                    $date->setTime(0, 0, 0);
                    $where->greaterThanOrEqualTo("$tutorStudentCourseTable.$name", $date->getTimestamp());
                    $date->add(new \DateInterval("P1D"));
                    $where->lessThanOrEqualTo("$tutorStudentCourseTable.$name", $date->getTimestamp());
                }
            }
            if (in_array($name, ['first_name', 'last_name', 'phone'])) {
                $metawhere = new Where();
                $metawhere->equalTo("name", $name);
                $metawhere->like("value", "%$value%");
                $meta = $this->userMetaTable->select($metawhere)->toArray();
                $userids = [-1];
                foreach ($meta as $item) {
                    $userids[] = $item['user_id'];
                }
                $where->in("$userTable.id", $userids);
            }
            if ($name == 'tutor_name') {
                $tutorwhere = new Where();
                $tutorwhere->like("value", "%$value%");
                $tutorwhere->in("name", ['first_name', 'last_name']);
                $meta = $this->userMetaTable->select($tutorwhere)->toArray();
                $userids = [-1];
                foreach ($meta as $item) {
                    $userids[] = $item['user_id'];
                }
                $where->in("$tutorStudentCourseTable.tutor_id", $userids);
            }
        }

        if (!empty($search['value'])) {
            $value = $search['value'];
            $orexprs = [];
            foreach ($columns as $column) {
                if (!$column['searchable']) continue;

                $searchwhere = new Where();

                $name = $column['data'];
                if (in_array($name, ['identity', 'username'])) {
                    $searchwhere->like("$userTable.$name", "%$value%");
                    $orexprs[] = $searchwhere;
                }
                if ($name == 'course_name') {
                    $searchwhere->like("course.name", "%$value%");
                    $orexprs[] = $searchwhere;
                }
                if ($name == 'home_learning_number') {
                    $searchwhere->like("$tutorStudentCourseTable.$name", "%$value%");
                    $orexprs[] = $searchwhere;
                }
                if ($name == 'end_of_support') {
                    $date = \DateTime::createFromFormat('d/m/Y', $value);
                    if ($date !== false) {
                        $date->setTime(0, 0, 0);
                        $searchwhere->greaterThanOrEqualTo("$tutorStudentCourseTable.$name", $date->getTimestamp());
                        $date->add(new \DateInterval("P1D"));
                        $searchwhere->lessThanOrEqualTo("$tutorStudentCourseTable.$name", $date->getTimestamp());
                        $orexprs[] = $searchwhere;
                    }
                }
                if (in_array($name, ['first_name', 'last_name', 'phone'])) {
                    $metawhere = new Where();
                    $metawhere->equalTo("name", $name);
                    $metawhere->like("value", "%$value%");
                    $meta = $this->userMetaTable->select($metawhere)->toArray();
                    $userids = [-1];
                    foreach ($meta as $item) {
                        $userids[] = $item['user_id'];
                    }

                    $searchwhere->in("$userTable.id", $userids);
                    $orexprs[] = $searchwhere;
                }
                if ($name == 'tutor_name') {
                    $tutorwhere = new Where();
                    $tutorwhere->like("value", "%$value%");
                    $tutorwhere->in("name", ['first_name', 'last_name']);
                    $meta = $this->userMetaTable->select($tutorwhere)->toArray();
                    $userids = [-1];
                    foreach ($meta as $item) {
                        $userids[] = $item['user_id'];
                    }
                    $searchwhere->in("$tutorStudentCourseTable.tutor_id", $userids);
                    $orexprs[] = $searchwhere;
                }
            }

            if (!empty($orexprs)) {
                $orexpr = new Where();
                $orexpr->addPredicates($orexprs, PredicateSet::OP_OR);
                $where->addPredicate($orexpr, PredicateSet::OP_AND);
            }
        }

        if (Rbac\Role\Tutor::class == $loggedUser->getRole()) {
            $where->equalTo("$tutorStudentCourseTable.tutor_id", $loggedUser->getId());
        }

        $selectcount = $this->userTableGateway->getSql()
            ->select()
            ->columns(array('total' => new Expression('COUNT(*)')))
            ->join($tutorStudentCourseTable, "$userTable.id = $tutorStudentCourseTable.student_id", [], Select::JOIN_LEFT)
            ->join(["tutor" => $userTable], "tutor.id = $tutorStudentCourseTable.tutor_id", [], Select::JOIN_LEFT)
            ->join("course", "course.id = $tutorStudentCourseTable.course_id", [], Select::JOIN_LEFT)
            ->where($where);
        $total = $this->userTableGateway->getSql()->prepareStatementForSqlObject($selectcount)->execute()->current();

        $sql = $this->userTableGateway->getSql()
            ->select()
            ->join($tutorStudentCourseTable, "$userTable.id = $tutorStudentCourseTable.student_id", ['home_learning_number', 'end_of_support'])
            ->join(["tutor" => $userTable], "tutor.id = $tutorStudentCourseTable.tutor_id", ["tutor_id" => "id"])
            ->join("course", "course.id = $tutorStudentCourseTable.course_id", ["course_name" => "name"])
            ->where($where)
            ->limit($length)->offset($start);

        foreach ($orders as $order) {
            $direction = $order['dir'];
            $column = $columns[$order['column']]['data'];
            if (in_array($column, ['identity', 'username'])) {
                $sql->order(["$userTable.$column" => $direction]);
            }
            if ($column == 'course_name') {
                $sql->order(["course.name" => $direction]);
            }
            if (in_array($column, ['end_of_support', 'home_learning_number'])) {
                $sql->order(["$tutorStudentCourseTable.$column" => $direction]);
            }
        }

        $result = [];
        $statement = $this->userTableGateway->getSql()->prepareStatementForSqlObject($sql);
        $students = $statement->execute();
        foreach ($students as $student) {
            $firstname = $this->userMetaTable->getMetaByName($student['id'], 'first_name')->toArray();
            $lastname = $this->userMetaTable->getMetaByName($student['id'], 'last_name')->toArray();
            $phone = $this->userMetaTable->getMetaByName($student['id'], 'phone')->toArray();

            $tutorfirstname = $this->userMetaTable->getMetaByName($student['tutor_id'], 'first_name')->toArray();
            $tutorlastname = $this->userMetaTable->getMetaByName($student['tutor_id'], 'last_name')->toArray();
            $tutorfirstname = empty($tutorfirstname) ? "" : $tutorfirstname[0]['value'];
            $tutorlastname = empty($tutorlastname) ? "" : $tutorlastname[0]['value'];

            $result[] = [
                "id" => $student['id'],
                "identity" => $student['identity'],
                "username" => $student['username'],
                "first_name" => empty($firstname) ? '' : $firstname[0]['value'],
                "last_name" => empty($lastname) ? '' : $lastname[0]['value'],
                "phone" => empty($phone) ? '' : $phone[0]['value'],
                "course_name" => $student['course_name'],
                "tutor_name" => empty(trim("$tutorfirstname $tutorlastname")) ? $student["tutor_name"] : "$tutorfirstname $tutorlastname",
                "end_of_support" => empty($student['end_of_support']) ? '-' : date('d/m/Y', $student['end_of_support']),
                "home_learning_number" => $student['home_learning_number']
            ];
        }

        return [
            'draw' => $draw,
            'recordsTotal' => !empty($total) ? intval($total['total']) : 0,
            'recordsFiltered' => !empty($total) ? intval($total['total']) : 0,
            'data' => $result
        ];
    }

    public function usingSearch(?string $role, array $pairs = [])
    {
        $where = new Where();

        if ($role) {
            $where->equalTo('role', $role);
        }
        foreach ($pairs as $identifier => $value) {
            $where
                ->nest()
                ->like($identifier, '%' . $value . '%')
                ->or
                ->like($this->userMetaTable->getTableName() . '.value', '%' . $value . '%')
                ->unnest();
        }

        $resultSet = $this->userTableGateway->getResultSetPrototype();
        $statement = $this->userTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->userTableGateway->getSql()
                    ->select()
                    ->join(
                        $this->userMetaTable->getTableName(),
                        $this->userTableGateway->getTable() . '.id = ' . $this->userMetaTable->getTableName() . '.user_id',
                        [],
                        Select::JOIN_LEFT
                    )
                    ->where($where)
                    ->group($this->userTableGateway->getTable(). '.id')
            );
        $userCollection = $statement->execute();
        foreach ($userCollection as $user) {
            $result[] = [
                $user,
                $this->userMetaTable->fetchByUserId($user['id']),
                $this->courseUserTable->fetchByUserId($user['id']),
                $this->tutorStudentCourseTable->fetchByStudentId($user['id'])
            ];
        }

        $resultSet->initialize($result ?? []);
        return $resultSet;
    }

    /**
     * @param string $role
     * @param array $params follow params sent by https://datatables.net/manual/server-side
     * @return array
     * @throws \Exception
     */
    public function getUsers(string $role, array $params)
    {
        $start = intval($params['start']);
        $length = intval($params['length']);
        $search = $params['search'];
        $orders = isset($params['order']) ? $params['order']: [];
        $draw = intval($params['draw']);
        $columns = $params['columns'];

        $userTable = $this->userTableGateway->getTable();

        $where = new Where();
        $where->equalTo($userTable . '.role', $role);

        foreach ($columns as $index => $column) {
            if (!$column['searchable'] || empty($column['search']['value'])) continue;

            $value = $column['search']['value'];
            if ($index == 0 && !empty($value)) {
                $tutorids = [];
                $tutorStudentCourses = $this->tutorStudentCourseTable->fetchByCourseId($value)->toArray();
                foreach ($tutorStudentCourses as $item) {
                    $tutorids[] = $item['tutor_id'];
                }
                if (count($tutorids) > 0) {
                    $where->in("$userTable.id", $tutorids);
                }
            }
        }

        if (!empty($search['value'])) {
            $value = $search['value'];
            $orexprs = [];
            foreach ($columns as $column) {
                if (!$column['searchable']) continue;

                $searchwhere = new Where();

                $name = $column['data'];
                if (in_array($name, ['identity', 'username'])) {
                    $searchwhere->like("$userTable.$name", "%$value%");
                    $orexprs[] = $searchwhere;
                }
                if (in_array($name, ['first_name', 'last_name', 'phone'])) {
                    $metawhere = new Where();
                    $metawhere->equalTo("name", $name);
                    $metawhere->like("value", "%$value%");
                    $meta = $this->userMetaTable->select($metawhere)->toArray();
                    $userids = [-1];
                    foreach ($meta as $item) {
                        $userids[] = $item['user_id'];
                    }

                    $searchwhere->in("$userTable.id", $userids);
                    $orexprs[] = $searchwhere;
                }
            }

            if (!empty($orexprs)) {
                $orexpr = new Where();
                $orexpr->addPredicates($orexprs, PredicateSet::OP_OR);
                $where->addPredicate($orexpr, PredicateSet::OP_AND);
            }
        }


        $selectcount = $this->userTableGateway->getSql()
            ->select()
            ->columns(array('total' => new Expression('COUNT(*)')))
            ->where($where);
        $total = $this->userTableGateway->getSql()->prepareStatementForSqlObject($selectcount)->execute()->current();

        $sql = $this->userTableGateway->getSql()
            ->select()
            ->where($where)
            ->limit($length)
            ->offset($start);

        foreach ($orders as $order) {
            $direction = $order['dir'];
            $column = $columns[$order['column']]['data'];
            if (in_array($column, ['identity', 'username'])) {
                $sql->order(["$userTable.$column" => $direction]);
            }
        }

        $result = [];
        $statement = $this->userTableGateway->getSql()->prepareStatementForSqlObject($sql);
        $tutors = $statement->execute();
        foreach ($tutors as $tutor) {
            $firstname = $this->userMetaTable->getMetaByName($tutor['id'], 'first_name')->toArray();
            $lastname = $this->userMetaTable->getMetaByName($tutor['id'], 'last_name')->toArray();
            $phone = $this->userMetaTable->getMetaByName($tutor['id'], 'phone')->toArray();
            $markingCount = $this->userMetaTable->getMetaByName($tutor['id'], 'marking_count')->toArray();
            $markingTime = $this->userMetaTable->getMetaByName($tutor['id'], 'marking_time')->toArray();
            $markingCount = empty($markingCount) ? 0 : $markingCount[0]['value'];
            $markingTime = empty($markingTime) ? 0 : $markingTime[0]['value'];
            $overdue = $this->assignmentWorkTableGateway->countOverdueByTutor($tutor['id']);
            $unmarked = $this->assignmentWorkTableGateway->countUnmarkedByTutor($tutor['id']);

            $result[] = [
                "id" => $tutor['id'],
                "identity" => $tutor['identity'],
                "username" => $tutor['username'],
                "first_name" => empty($firstname) ? '' : $firstname[0]['value'],
                "last_name" => empty($lastname) ? '' : $lastname[0]['value'],
                "phone" => empty($phone) ? '' : $phone[0]['value'],
                "overdue" => $overdue,
                "unmarked" => $unmarked,
                "avg_marking_time" => $markingCount > 0 ? round($markingTime / $markingCount) . ' days' : ' 0 day'
            ];
        }

        return [
            'draw' => $draw,
            'recordsTotal' => !empty($total) ? intval($total['total']) : 0,
            'recordsFiltered' => !empty($total) ? intval($total['total']) : 0,
            'data' => $result
        ];
    }

    public function save(array $data): string
    {
        if (!empty($data['identity'])) {
            $data['identity'] = strtolower($data['identity']);
        }
        if (!empty($data['username'])) {
            $data['username'] = strtolower($data['username']);
        }
        if (! empty($data['identity']) && $this->duplicateIdentity($data['id'], $data['identity'])) {
            return 'duplicate_identity';
        }
        if (! empty($data['username']) && $this->duplicateUsername($data['id'], $data['username'])) {
            return 'duplicate_username';
        }
        if (! empty($data['pin']) && $this->duplicatePin($data['id'], $this->oldHashPassword($data['pin']))) {
            return 'duplicate_pin';
        }
        if (!$data['id']) {
            return $this->insert($data);
        }
        return $this->update($data['id'], $data);
    }

    public function duplicateIdentity($id, $identity)
    {
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->EqualTo('identity', $identity);
        $statement = $this->userTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->userTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $userCollection = $statement->execute();
        if ($userCollection->count() > 0) {
            return true;
        }
        return false;
    }

    public function duplicateUsername($id, $username)
    {
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->EqualTo('username', $username);
        $statement = $this->userTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->userTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $userCollection = $statement->execute();
        if ($userCollection->count() > 0) {
            return true;
        }
        return false;
    }

    public function duplicatePin($id, $pin)
    {
        $where = new Where();
        if ($id) {
            $where->notEqualTo('id', $id);
        }
        $where->EqualTo('pin', $pin);
        $statement = $this->userTableGateway->getSql()
            ->prepareStatementForSqlObject(
                $this->userTableGateway->getSql()
                    ->select()
                    ->where($where)
            );
        $userCollection = $statement->execute();
        if ($userCollection->count() > 0) {
            return true;
        }
        return false;
    }

    protected function insert(array $data): int
    {
        $this->userTableGateway->insert([
            'role' => $data['role'],
            'username' => $data['username'],
            'identity' => $data['identity'],
            'password' => $this->hashPassword($data['password']),
            'pin' => $this->oldHashPassword($data['pin']),
            'plainpin' => $data['pin'],
            'created_at' => time()
        ]);

        $lastInsertValue = $this->userTableGateway->lastInsertValue;

        foreach ($data['meta'] ?? [] as $name => $value) {
            $this->userMetaTable->add($lastInsertValue, $name, $value);
        }

        /**
         * @todo need to be decoupled
         */
        if (!empty($data['courses'])) {
            $this->courseUserTable->save($lastInsertValue, ...($data['courses'] ?? []));
        }

        /**
         * This is used for student
         * @todo need to be decoupled
         */
        if (!empty($data['courseTutor'])) {
            $new_data = array();
            foreach ($data['courseTutor'] as $info) {
                $new_data[$info['course']] =  $info;
            }
            $data['courseTutor'] = $new_data;
            foreach ($data['courseTutor'] as $courseTutor) {
                $courseIds[] = $courseTutor['course'];
            }
            $examcourseIds = array();
            foreach ($data['courseTutor'] as $courseExam) {
                if(array_key_exists('exam',$courseExam)) {
                    $examcourseIds[] = $courseExam['exam'][0];
                }
            }
            $messagetutorIds = array();
            foreach ($data['courseTutor'] as $messagetutor) {
                if(array_key_exists('messagetutor',$messagetutor)) {
                    $messagetutorIds[] = $messagetutor['messagetutor'][0];
                }
            }
            $certificateprintfreeIds = array();
            foreach ($data['courseTutor'] as $certificateprintfree) {
                if(array_key_exists('certificateprintfree',$certificateprintfree)) {
                    $certificateprintfreeIds[] = $certificateprintfree['certificateprintfree'][0];
                }
            }
            $this->courseUserTable->save($lastInsertValue, ...($courseIds ?? []));
            $this->tutorStudentCourseTable->save($lastInsertValue, ...($data['courseTutor'] ?? []));
            $this->examTriesTable->save($lastInsertValue, ...($examcourseIds ?? []));
            $this->messageTutorTable->save($lastInsertValue, ...($messagetutorIds ?? []));
            $this->certificatePrintFreeTable->save($lastInsertValue, ...($certificateprintfreeIds ?? []));
        }

        return $lastInsertValue;
    }

    protected function update(int $userId, array $data): int
    {
        $updateData = [
            'username' => $data['username'],
            'identity' => $data['identity'],
        ];

        if ($data['password']) {
            $updateData['password'] = $this->hashPassword($data['password']);
        }
        if ($data['pin']) {
            $updateData['pin'] = $this->oldHashPassword($data['pin']);
            $updateData['plainpin'] = $data['pin'];
        }
        $affectedRows = $this->userTableGateway->update($updateData, ['id' => $userId]);
        foreach ($data['meta'] ?? [] as $name => $value) {
            $oldMeta = $this->userMetaTable->getMetaByName($userId, $name);
            if ($oldMeta->count()) {
                $updateMeta = $this->userMetaTable->update($userId, $name, $value);
            } else {
                $updateMeta = $this->userMetaTable->add($userId, $name, $value);
            }
            $affectedRows += $updateMeta;
        }

        /**
         * This is used for tutor
         * @todo need to be decoupled
         */
        if (!empty($data['courses'])) {
            $affectedRows += $this->courseUserTable->save($userId, ...($data['courses'] ?? []));
        }

        /**
         * This is used for student
         * @todo need to be decoupled
         */
        if (!empty($data['courseTutor'])) {

            $new_data = array();
            foreach ($data['courseTutor'] as $info) {
                $new_data[$info['course']] =  $info;
            }
            $data['courseTutor'] = $new_data;

            foreach ($data['courseTutor'] as $courseTutor) {
                $courseIds[] = $courseTutor['course'];
            }

            $examcourseIds = array();
            foreach ($data['courseTutor'] as $courseExam) {
                if(array_key_exists('exam',$courseExam)) {
                    $examcourseIds[] = $courseExam['exam'][0];
                }
            }

            $messagetutorIds = array();
            foreach ($data['courseTutor'] as $messagetutor) {
                if(array_key_exists('messagetutor',$messagetutor)) {
                    $messagetutorIds[] = $messagetutor['messagetutor'][0];
                }
            }

            $certificateprintfreeIds = array();
            foreach ($data['courseTutor'] as $certificateprintfree) {
                if(array_key_exists('certificateprintfree',$certificateprintfree)) {
                    $certificateprintfreeIds[] = $certificateprintfree['certificateprintfree'][0];
                }
            }

            $affectedRows += $this->courseUserTable->save($userId, ...($courseIds ?? []));
            $affectedRows += $this->tutorStudentCourseTable->save($userId, ...($data['courseTutor'] ?? []));
            $affectedRows += $this->examTriesTable->save($userId, ...($examcourseIds ?? []));
            $affectedRows += $this->messageTutorTable->save($userId, ...($messagetutorIds ?? []));
            $affectedRows += $this->certificatePrintFreeTable->save($userId, ...($certificateprintfreeIds ?? []));
        }

        return $affectedRows;
    }

    public function generateToken($userId)
    {
        return sha1(md5($userId . '_lms_salt_' . time()));
    }

    public function updateToken($userId, $token)
    {
        $token_time = time();
        if (empty($token)) {
            $token_time = '';
        }
        $this->userTableGateway->update(['token' => $token, 'token_time' => $token_time], ['id' => $userId]);
    }

    public function updatePassword($userId, $password)
    {
        $this->userTableGateway->update(['password' => $this->hashPassword($password)], ['id' => $userId]);
    }

    /**
     * @deprecated used to ensure backward compatible
     * @param $password
     * @return string
     */
    public function oldHashPassword($password)
    {
        return sha1(md5($password));
    }

    public function hashPassword($password)
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }
}