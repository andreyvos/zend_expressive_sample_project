<?php

namespace Credit\Model;

use Zend\Db\ResultSet\AbstractResultSet;

class CreditStudentTable
{
    /**
     * @var creditStudentTableGateway
     */
    private $creditStudentTableGateway;

    public function __construct(CreditStudentTableGateway $creditStudentTableGateway)
    {
        $this->creditStudentTableGateway = $creditStudentTableGateway;
    }

    public function fetchByStudentId(int $studentId): ?AbstractResultSet
    {
        return $this->creditStudentTableGateway->select(['student_id' => $studentId]);
    }

    public function fetchByCreditUserId(int $creditUserId): ?AbstractResultSet
    {
        return $this->creditStudentTableGateway->select(['credit_user_id' => $creditUserId]);
    }

    public function isCreditUserForStudent(int $creditUserId, int $studentId)
    {
        return $this->creditStudentTableGateway->select([
            'credit_user_id' => $creditUserId,
            'student_id' => $studentId
        ])->current();
    }

    public function insert(
        int $creditUserId,
        int $studentId
    )
    {
        $this->creditStudentTableGateway->insert([
            'credit_user_id' => $creditUserId,
            'student_id' => $studentId
        ]);

        return $this->creditStudentTableGateway->lastInsertValue;
    }
}
