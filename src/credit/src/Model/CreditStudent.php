<?php

namespace Credit\Model;

class CreditStudent
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var int
     */
    protected $creditUserId;

    /**
     * @var int
     */
    protected $studentId;


    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @return TutorStudentCourse
     */
    public function setId(int $id): CreditStudent
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getCreditUserId(): int
    {
        return $this->credit_user_id;
    }

    /**
     * @param int $tutorId
     *
     * @return TutorStudentCourse
     */
    public function setCreditUserId(int $credit_user_id): CreditStudent
    {
        $this->credit_user_id = $credit_user_id;
        return $this;
    }

    /**
     * @return int
     */
    public function getStudentId(): int
    {
        return $this->student_id;
    }

    /**
     * @param int $studentId
     *
     * @return TutorStudentCourse
     */
    public function setStudentId(int $student_id): CreditStudent
    {
        $this->student_id = $student_id;
        return $this;
    }

}
